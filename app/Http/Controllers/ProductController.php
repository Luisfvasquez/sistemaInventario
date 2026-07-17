<?php

namespace App\Http\Controllers;

use App\Models\BulkType;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Sanitizar el input de búsqueda por seguridad (letras, números y caracteres simples)
        $searchSanitized = $search ? preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-\.\_\@]/u', '', $search) : null;

        $products = Product::with(['category', 'inventory'])
            ->when($searchSanitized, function ($query) use ($searchSanitized) {
                $searchTerm = '%' . $searchSanitized . '%';
                $query->where(function ($subQ) use ($searchTerm) {
                    $subQ->where('name', 'like', $searchTerm)
                         ->orWhere('sku', 'like', $searchTerm)
                         ->orWhere('sku_barcode', 'like', $searchTerm);
                });
            })
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $bulkTypes = BulkType::all();

        return view('admin.products.create', compact('categories', 'bulkTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Verificar si existe un producto eliminado (soft-deleted) con el mismo sku_barcode o sku
        $trashedProduct = Product::onlyTrashed()
            ->where(function ($query) use ($request) {
                $query->where('sku_barcode', $request->sku_barcode)
                    ->orWhere('sku', $request->sku);
            })
            ->first();

        // 2. Validación de los datos de entrada
        // Si encontramos un producto eliminado, excluimos su ID de las reglas unique
        $skuUniqueRule = ['required_without:sku_barcode', 'string', 'regex:/^[a-zA-Z0-9\-\_]+$/', 'unique:products,sku'.($trashedProduct ? ','.$trashedProduct->id : '')];
        $skuBarcodeUniqueRule = ['required_without:sku', 'string', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:products,sku_barcode'.($trashedProduct ? ','.$trashedProduct->id : '')];

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'sku' => $skuUniqueRule,
            'sku_barcode' => $skuBarcodeUniqueRule,
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit_type' => 'required|in:unit,gram',
            'brand' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\&\']+$/'],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
            'minimum_stock' => 'nullable|numeric|min:0',
            // Validamos el array de presentaciones adicionales (bultos, cajas) si vienen
            'presentations' => 'nullable|array',
            'presentations.*.bulk_type_id' => 'required|exists:bulk_types,id', // <-- Validación dinámica
            'presentations.*.name' => ['required', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'presentations.*.quantity' => 'required|numeric|min:0.01',
            'presentations.*.purchase_price' => 'required|numeric|min:0',
            'presentations.*.sale_price' => 'required|numeric|min:0',
            'presentations.*.sku' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-\_]+$/'],
            'presentations.*.sku_barcode' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Máximo 10MB por imagen original
        ], [
            'name.required' => 'El nombre del producto es requerido.',
            'sku.required' => 'El SKU del producto es requerido.',
            'sku_barcode.required' => 'El código de barras del producto es requerido.',
            'sku.unique' => 'El SKU ya existe en la base de datos.',
            'sku_barcode.unique' => 'El código de barras ya existe en la base de datos.',
            'presentations.*.name.required' => 'El nombre de la presentación es requerido.',
            'presentations.*.type.required' => 'El tipo de presentación es requerido.',
            'presentations.*.quantity.required' => 'La cantidad de la presentación es requerida.',
            'presentations.*.purchase_price.required' => 'El precio de compra de la presentación es requerido.',
            'presentations.*.sale_price.required' => 'El precio de venta de la presentación es requerido.',
            'presentations.*.sku.unique' => 'El SKU de la presentación ya existe en la base de datos.',
            'presentations.*.sku_barcode.unique' => 'El código de barras de la presentación ya existe en la base de datos.',
        ]);

        try {
            // Iniciamos la transacción
            DB::beginTransaction();

            if ($trashedProduct) {
                // ── RESTAURAR producto eliminado y actualizar todos sus campos ──
                $trashedProduct->restore();

                $trashedProduct->update([
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'slug' => Str::slug($validated['name']).'-'.uniqid(),
                    'description' => $validated['description'] ?? null,
                    'sku' => $validated['sku'],
                    'sku_barcode' => $validated['sku_barcode'],
                    'brand' => $validated['brand'] ?? null,
                    'cost' => $validated['cost'],
                    'price' => $validated['price'],
                    'unit_type' => $validated['unit_type'],
                    'status' => 'active',
                    'created_by' => Auth::user()->id,
                ]);

                $product = $trashedProduct;

                // Limpiar datos antiguos del producto restaurado
                $product->bulks()->delete();
                $this->deleteAllProductImages($product);
                $product->inventory()->delete();

                $successMessage = 'Producto restaurado y actualizado exitosamente (existía uno eliminado con el mismo SKU/Código de barras).';

            } else {
                // ── CREAR producto nuevo ──
                $product = Product::create([
                    'category_id' => $validated['category_id'],
                    'uuid' => Str::uuid(),
                    'name' => $validated['name'],
                    'slug' => Str::slug($validated['name']).'-'.uniqid(),
                    'description' => $validated['description'] ?? null,
                    'sku' => $validated['sku'],
                    'sku_barcode' => $validated['sku_barcode'],
                    'brand' => $validated['brand'] ?? null,
                    'cost' => $validated['cost'],
                    'price' => $validated['price'],
                    'unit_type' => $validated['unit_type'],
                    'created_by' => Auth::user()->id,
                ]);

                $successMessage = 'Producto creado exitosamente junto con su inventario inicial.';
            }

            // 3. Crear la Presentación Base (La Unidad)
            $product->bulks()->create([
                'product_id' => $product->id,
                'bulk_type_id' => 1,
                'name' => $validated['name'],
                'quantity' => 1,
                'purchase_price' => $validated['cost'],
                'sale_price' => $validated['price'],
                'sku' => $validated['sku'],
                'sku_barcode' => $validated['sku_barcode'],
                'is_default' => true,
                'is_active' => true,
            ]);

            // 4. Crear Presentaciones Adicionales (Bultos/Cajas) si el admin las agregó en el form
            if ($request->has('presentations')) {
                foreach ($request->presentations as $presentation) {

                    // Buscamos el tipo en la BD para obtener su nombre (Ej: "Bulto", "Kilo")
                    $bulkType = BulkType::find($presentation['bulk_type_id']);
                    $prefix = $bulkType ? $bulkType->name : 'Bulto';

                    // Concatenamos dinámicamente Tipo + Nombre escrito
                    $finalName = $prefix.' '.trim($presentation['name']);

                    $product->bulks()->create([
                        'bulk_type_id' => $presentation['bulk_type_id'],
                        'name' => $finalName, // Se guarda como "Bulto Harina Pan" o "Kilo Queso"
                        'quantity' => $presentation['quantity'],
                        'purchase_price' => $presentation['purchase_price'],
                        'sale_price' => $presentation['sale_price'],
                        'sku' => $presentation['sku'],
                        'sku_barcode' => $presentation['sku_barcode'],
                        'is_default' => false,
                        'is_active' => true,
                    ]);
                }
            }

            // 5. Inicializar el Inventario en Cero
            $product->inventory()->create([
                'stock' => 0,
                'reserved_stock' => 0,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'maximum_stock' => null, // Opcional
            ]);

            if ($request->hasFile('images')) {
                // Instanciamos el manager con la versión 4
                $manager = ImageManager::usingDriver(Driver::class);

                // Agregamos $index para saber cuál es la primera imagen
                foreach ($request->file('images') as $index => $imageFile) {
                    $filename = uniqid('img_').'.webp';
                    $path = 'products/'.$product->id.'/'.$filename;
                    $thumbPath = 'products/'.$product->id.'/thumb_'.$filename;

                    // A. Imagen Principal
                    $mainImage = $manager->decode($imageFile);
                    $encodedMain = $mainImage->encodeUsingFormat(Format::WEBP, quality: 80);
                    Storage::disk('public')->put($path, (string) $encodedMain);

                    // B. Miniatura (Thumbnail)
                    $thumbImage = $manager->decode($imageFile)->scale(width: 300);
                    $encodedThumb = $thumbImage->encodeUsingFormat(Format::WEBP, quality: 80);
                    Storage::disk('public')->put($thumbPath, (string) $encodedThumb);

                    // C. Guardar en BD usando tu estructura exacta
                    $product->images()->create([
                        'path' => $path,
                        'disk' => 'public',
                        'original_name' => $imageFile->getClientOriginalName(),
                        'mime_type' => 'image/webp', // Lo forzamos a webp porque lo convertimos
                        'size' => strlen((string) $encodedMain), // Tamaño real del archivo convertido en bytes
                        'is_primary' => $index === 0 ? true : false, // La primera imagen será la principal
                        'sort_order' => $index, // Orden basado en cómo las subió
                    ]);
                }
            }

            // Si todo salió bien, confirmamos los cambios en la Base de Datos
            DB::commit();

            // Redirigimos con un mensaje de éxito
            return redirect()->route('admin.products.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            // Si algo falla, deshacemos todos los inserts para evitar datos corruptos
            DB::rollBack();

            // Regresamos a la vista con el error para depurar
            return back()->withInput()->withErrors([
                'error' => 'Hubo un problema al guardar el producto: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Encontrar el producto o lanzar error 404
        $product = Product::findOrFail($id);

        // 2. Validación de los datos de entrada (con excepciones para el SKU del propio producto)
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'sku' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\-\_]+$/', 'unique:products,sku,'.$product->id],
            'sku_barcode' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:products,sku_barcode,'.$product->id],
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit_type' => 'required|in:unit,gram',
            'brand' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\&\']+$/'],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
            'minimum_stock' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // 3. Actualizar el Producto Base
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.uniqid(), // Re-generamos el slug por si cambió el nombre
                'description' => $validated['description'] ?? null,
                'sku_barcode' => $validated['sku_barcode'],
                'brand' => $validated['brand'] ?? null,
                'cost' => $validated['cost'],
                'price' => $validated['price'],
                'unit_type' => $validated['unit_type'],
                'status' => $validated['status'],
            ]);

            // 4. Actualizar automáticamente la Presentación Base (La Unidad)
            // Buscamos la presentación por defecto del producto para mantener costos/precios al día
            $defaultBulk = $product->bulks()->where('is_default', true)->first();
            if ($defaultBulk) {
                $defaultBulk->update([
                    'purchase_price' => $validated['cost'],
                    'sale_price' => $validated['price'],
                    'sku' => $validated['sku'],
                    'sku_barcode' => $validated['sku_barcode'],
                ]);
            }

            // 5. Actualizar la configuración del Inventario (Stock Mínimo)
            $product->inventory()->update([
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            // 6. Procesar nuevas imágenes si fueron anexadas en la edición
            if ($request->hasFile('images')) {
                $manager = ImageManager::usingDriver(Driver::class);

                // Contamos cuántas imágenes ya tiene para mantener el orden consecutivo
                $currentImagesCount = $product->images()->count();

                foreach ($request->file('images') as $index => $imageFile) {
                    $filename = uniqid('img_').'.webp';
                    $path = 'products/'.$product->id.'/'.$filename;
                    $thumbPath = 'products/'.$product->id.'/thumb_'.$filename;

                    // A. Optimizar imagen principal
                    $mainImage = $manager->decode($imageFile);
                    $encodedMain = $mainImage->encodeUsingFormat(Format::WEBP, quality: 80);
                    Storage::disk('public')->put($path, (string) $encodedMain);

                    // B. Optimizar miniatura
                    $thumbImage = $manager->decode($imageFile)->scale(width: 300);
                    $encodedThumb = $thumbImage->encodeUsingFormat(Format::WEBP, quality: 80);
                    Storage::disk('public')->put($thumbPath, (string) $encodedThumb);

                    // C. Guardar registro indexado con tus columnas exactas de la BD
                    $product->images()->create([
                        'path' => $path,
                        'disk' => 'public',
                        'original_name' => $imageFile->getClientOriginalName(),
                        'mime_type' => 'image/webp',
                        'size' => strlen((string) $encodedMain),
                        'is_primary' => ($currentImagesCount == 0 && $index === 0) ? true : false, // Si no tenía imágenes, la primera será la principal
                        'sort_order' => $currentImagesCount + $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Producto actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return back()->withInput()->withErrors([
                'error' => 'Hubo un problema al actualizar el producto: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Elimina bulks, imágenes (archivos + BD) e inventario antes del soft-delete.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            // 1. Eliminar todas las presentaciones (bulks) del producto
            $product->bulks()->delete();

            // 2. Eliminar todas las imágenes del producto (archivos del disco + registros BD)
            $this->deleteAllProductImages($product);

            // 3. Eliminar el registro de inventario
            $product->inventory()->delete();

            // 4. Soft-delete del producto
            $product->delete();

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Producto eliminado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return back()->withErrors([
                'error' => 'Hubo un problema al eliminar el producto.',
            ]);
        }
    }

    /**
     * Elimina TODAS las imágenes de un producto (archivos del disco + registros BD).
     */
    private function deleteAllProductImages(Product $product): void
    {
        foreach ($product->images as $image) {
            $directory = dirname($image->path);
            $filename = basename($image->path);
            $thumbPath = $directory.'/thumb_'.$filename;

            Storage::disk($image->disk ?? 'public')->delete([$image->path, $thumbPath]);
            $image->delete();
        }

        // Eliminar el directorio de imágenes del producto si existe
        $productDir = 'products/'.$product->id;
        if (Storage::disk('public')->exists($productDir)) {
            Storage::disk('public')->deleteDirectory($productDir);
        }
    }

    public function destroyImage($imageId)
    {
        // Importamos el modelo
        $image = Image::findOrFail($imageId);

        // 1. Reconstruimos la ruta de la miniatura basándonos en tu columna 'path'
        $directory = dirname($image->path);
        $filename = basename($image->path);
        $thumbPath = $directory.'/thumb_'.$filename;

        // 2. Eliminamos los archivos del disco (usando el disco que guardaste en BD)
        Storage::disk($image->disk ?? 'public')->delete([$image->path, $thumbPath]);

        // 3. Eliminamos el registro de la base de datos
        $image->delete();

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    public function forzarActualizacionDolar()
    {
        Artisan::call('exchange:update-usd');
        
        return back()->with('success', 'La tasa del dólar ha sido actualizada.');
    }
}
