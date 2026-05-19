<?php

namespace App\Http\Controllers;

use App\Models\Bulk;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        // Cargamos clientes activos
        $clients = Client::where('is_active', true)->get();

        // Cargamos productos activos con su inventario y presentaciones
        // Es vital cargar el inventario para que AlpineJS sepa cuánto stock queda
        $products = Product::with(['bulks', 'inventory'])->where('status', 'active')->get();

        return view('admin.orders.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.bulk_id' => 'required|exists:bulks,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $currentRate = ExchangeRate::where('is_active', true)->first();
        if (! $currentRate) {
            return back()->withInput()->withErrors(['error' => 'No hay una tasa de cambio activa para procesar la venta.']);
        }

        try {
            DB::beginTransaction();

            // 1. Crear la Cabecera de la Orden
            // Generamos un número de orden único (Ej: ORD-202605-001)
            $orderNumber = 'ORD-'.date('Ym').'-'.str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'uuid' => Str::uuid(),
                'client_id' => $request->client_id,
                'user_id' => auth()->id(), // El cajero/admin que hizo la venta
                'order_number' => $orderNumber,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'exchange_rate' => $currentRate->rate,
                'status' => 'completed', // O 'pending' si es un apartado
                'payment_status' => 'pending', // Faltaría pasarlo por caja
                'notes' => $request->notes,
            ]);

            $calculatedTotal = 0;

            // 2. Procesar cada ítem y descontar inventario
            foreach ($request->items as $item) {
                $bulk = Bulk::find($item['bulk_id']);
                $product = Product::with('inventory')->find($item['product_id']);

                // Calculamos cuántas unidades base se están vendiendo (Ej: 2 Kilos = 2000 gramos)
                $baseQuantityToDeduct = $item['quantity'] * $bulk->quantity;

                // VALIDACIÓN CRÍTICA: ¿Hay suficiente stock?
                if (! $product->allow_negative_stock && $product->inventory->stock < $baseQuantityToDeduct) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}. Requerido: {$baseQuantityToDeduct}, Disponible: {$product->inventory->stock}");
                }

                $subtotalItem = $item['quantity'] * $item['unit_price'];
                $calculatedTotal += $subtotalItem;

                // A. Guardar el detalle de la orden
                $order->details()->create([
                    'product_id' => $product->id,
                    'bulk_id' => $bulk->id,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantityToDeduct,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotalItem,
                ]);

                // B. Descontar del Inventario Físico
                $product->inventory()->decrement('stock', $baseQuantityToDeduct);
            }

            // 3. Actualizar el Total Final
            $order->update([
                'subtotal' => $calculatedTotal,
                'total' => $calculatedTotal,
            ]);

            DB::commit();

            // Redirigimos a la vista de la orden (o al listado) para proceder al pago
            return redirect()->route('admin.orders.index')
                ->with('success', "Orden {$orderNumber} generada exitosamente por un total de Bs. ".number_format($calculatedTotal, 2));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
