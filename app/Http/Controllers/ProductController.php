<?php

namespace App\Http\Controllers;

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Usamos eager loading ('category', 'inventory')
        $products = Product::with(['category', 'inventory'])->get();
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $exchangeRate = Cache::get('usd_exchange_rate');

        return view('admin.products.create', compact('categories', 'exchangeRate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos de entrada
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'sku_barcode' => 'required|string|unique:products,sku_barcode',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'minimum_stock' => 'nullable|numeric|min:0',
            // Validamos el array de presentaciones adicionales (bultos, cajas) si vienen
            'presentations' => 'nullable|array',
            'presentations.*.name' => 'required|string',
            'presentations.*.type' => 'required|in:pack,box,bulk',
            'presentations.*.quantity' => 'required|numeric|min:2', // Un bulto debe traer más de 1
            'presentations.*.purchase_price' => 'required|numeric|min:0',
            'presentations.*.sale_price' => 'required|numeric|min:0',
            'presentations.*.sku' => 'required|string',
            'presentations.*.sku_barcode' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Máximo 10MB por imagen original
        ]);

        try {
            // Iniciamos la transacción
            DB::beginTransaction();

            // 2. Crear el Producto Base
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'uuid' => Str::uuid(),
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.uniqid(), // Evita colisiones de slugs
                'description' => $validated['description'] ?? null,
                'sku' => $validated['sku'],
                'sku_barcode' => $validated['sku_barcode'],
                'brand' => $validated['brand'] ?? null,
                'cost' => $validated['cost'],
                'price' => $validated['price'],
                'created_by' => Auth::user()->id, // Quien lo creó
            ]);

            // 3. Crear la Presentación Base (La Unidad)
            $product->bulks()->create([
                'type' => 'unit',
                'name' => 'Unidad',
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
                    $product->bulks()->create([
                        'type' => $presentation['type'],
                        'name' => $presentation['name'], // Ej: "Bulto x 24"
                        'quantity' => $presentation['quantity'], // Ej: 24
                        'purchase_price' => $presentation['purchase_price'],
                        'sale_price' => $presentation['sale_price'],
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
                ->with('success', 'Producto creado exitosamente junto con su inventario inicial.');

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
            'name' => 'required|string|max:255',
            'sku_barcode' => 'nullable|string|unique:products,sku_barcode,'.$product->id,
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
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
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            $this->destroyImage($product->images()->first()->id);

            return redirect()->route('admin.products.index')
                ->with('success', 'Producto eliminado con éxito.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors([
                'error' => 'Hubo un problema al eliminar el producto.',
            ]);
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
