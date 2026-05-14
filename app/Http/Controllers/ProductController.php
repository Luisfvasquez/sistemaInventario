<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        return view('admin.products.create', compact('categories'));
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
