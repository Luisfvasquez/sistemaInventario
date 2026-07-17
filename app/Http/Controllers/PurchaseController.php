<?php

namespace App\Http\Controllers;

use App\Models\Bulk;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('admin.purchases.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        // Cargamos los productos con sus presentaciones (bulks) para usarlos en JavaScript (Alpine)
        $products = Product::with(['bulks.bulkType', 'inventory'])->where('status', 'active')->get();

        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required_without:new_supplier_rif|nullable|exists:suppliers,id',
            'new_supplier_rif' => ['required_without:supplier_id', 'nullable', 'string', 'regex:/^[a-zA-Z0-9\-\.]+$/'],
            'new_supplier_name' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'purchase_code' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-\_\.]+$/', 'unique:purchases,purchase_code'],
            'purchased_at' => 'required|date',
            'notes' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
            // Validación del array dinámico de productos
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.bulk_id' => 'required|exists:bulks,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $currentRate = ExchangeRate::where('is_active', true)->first();
        if (! $currentRate) {
            return back()->withInput()->withErrors(['error' => 'No hay una tasa de cambio activa. No se puede procesar la compra.']);
        }

        try {
            DB::beginTransaction();

            $supplierId = $request->supplier_id;
            
            // Si el usuario proporcionó un RIF para un nuevo proveedor
            if ($request->filled('new_supplier_rif')) {
                $supplier = Supplier::firstOrCreate(
                    ['rif' => $request->new_supplier_rif],
                    ['name' => $request->new_supplier_name]
                );
                $supplierId = $supplier->id;
            }

            // 1. Crear la Cabecera de la Compra
            $purchase = Purchase::create([
                'uuid' => Str::uuid(),
                'supplier_id' => $supplierId,
                'user_id' => auth()->id(),
                'purchase_code' => $request->purchase_code,
                'subtotal' => 0, // Lo calcularemos iterando por seguridad
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
                'exchange_rate' => $currentRate->rate ?? Cache::get('usd_exchange_rate'), // Inmutabilidad histórica
                'status' => 'completed',
                'purchased_at' => $request->purchased_at,
                'notes' => $request->notes,
            ]);

            $calculatedTotal = 0;

            // 2. Procesar cada producto del array
            foreach ($request->items as $item) {
                $bulk = Bulk::find($item['bulk_id']);
                $product = Product::find($item['product_id']);

                // Calcular base (Ej: 5 Kilos * 1000 gramos = 5000 unidades base)
                $baseQuantity = $item['quantity'] * $bulk->quantity;
                $subtotalItem = $item['quantity'] * $item['unit_cost'];
                $calculatedTotal += $subtotalItem;

                // A. Crear el detalle de la compra
                $purchase->details()->create([
                    'product_id' => $product->id,
                    'bulk_id' => $bulk->id,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotalItem,
                    'previous_cost' => $product->cost, // Auditoría del costo anterior
                    'new_cost' => $item['unit_cost'] / $bulk->quantity, // Nuevo costo por unidad base
                ]);

                // B. Actualizar Inventario
                $product->inventory()->increment('stock', $baseQuantity);

                // C. Opcional: Actualizar el costo del producto en el catálogo
                // Calculamos cuánto costaría 1 unidad base ahora
                $newBaseCost = $item['unit_cost'] / $bulk->quantity;
                $product->update([
                    'cost' => $newBaseCost,
                    // 'price' => $newBaseCost * 1.30 // (Si deseas que el precio de venta suba automáticamente, quita el comentario)
                ]);
            }

            // 3. Actualizar el total real en la cabecera
            $purchase->update([
                'subtotal' => $calculatedTotal,
                'total' => $calculatedTotal,
            ]);

            DB::commit();

            event(new \App\Events\PurchaseCreated($purchase));

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Compra procesada. El inventario ha sido actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Error al procesar: '.$e->getMessage()]);
        }
    }
}
