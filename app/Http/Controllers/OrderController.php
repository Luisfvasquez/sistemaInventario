<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\Bulk;
use App\Models\Client;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['client', 'details.product', 'details.bulk'])->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('admin.orders.create', compact('paymentMethods'));
    }

    public function show($id)
    {
        $order = Order::with([
            'client',
            'details.product',
            'details.bulk',
            'payments.paymentMethod',
            'paymentProofs.images'
        ])->findOrFail($id);
        $paymentMethods = PaymentMethod::where('is_active', true)->where('show_in_checkout', true)->get();

        return view('admin.orders.show', compact('order', 'paymentMethods'));
    }

    public function uploadProof(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status !== 'pending') {
            return back()->withErrors(['error' => 'No puedes subir comprobantes a una orden ya procesada.']);
        }

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:100',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('payment_proof');
            $path = $file->store('receipts', 'public');

            // 1. Crear el registro visual del comprobante
            $proof = \App\Models\PaymentProof::create([
                'order_id' => $order->id,
                'uploaded_by' => auth()->id(),
                'reference' => $request->reference,
                'status' => 'pending',
                'notes' => 'Comprobante reportado manualmente por administración (ej: Vía WhatsApp).',
            ]);

            $proof->images()->create([
                'path' => $path,
                'disk' => 'public',
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_primary' => true,
            ]);

            // 2. Registrar la intención de pago (quedará pendiente hasta hacer clic en "Aprobar")
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'payment_date' => now(),
                'status' => 'pending',
                'notes' => 'Cargado manualmente por administración.',
            ]);

            DB::commit();

            return back()->with('success', '¡Comprobante adjuntado con éxito! Ahora puedes verificar la información y Aprobar la orden.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al subir el comprobante: ' . $e->getMessage()]);
        }
    }

    public function approve(Request $request, $id)
    {
        // 1. Validar que nos envíen el estado
        $request->validate([
            'status' => 'required|in:ready_for_pickup,completed'
        ]);

        $order = Order::findOrFail($id);

        if ($order->status === 'completed') {
            return back()->withErrors(['error' => 'La orden ya fue procesada y completada anteriormente.']);
        }

        try {
            DB::beginTransaction();

            // 2. Preparamos los datos base para actualizar
            $updateData = [
                'status' => $request->status, // Toma el valor del select (ready_for_pickup o completed)
                'verification_status' => 'verified',
                'payment_status' => 'paid',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ];

            // 3. Si se marcó como 'completed', significa que ya se entregó físicamente.
            // Le asignamos la fecha de entrega si aún no la tenía.
            if ($request->status === 'completed' && is_null($order->delivered_at)) {
                $updateData['delivered_at'] = now();
            }

            // 4. Actualizar Orden
            $order->update($updateData);

            // 5. Actualizar Comprobantes vinculados
            $order->paymentProofs()->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // 6. Actualizar los pagos registrados en la orden
            $order->payments()->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
            ]);

            // 7. Liquidar la cuenta por cobrar (Fiado/Deuda) si existe
            $account = \App\Models\AccountReceivable::where('order_id', $order->id)->first();
            if ($account && $account->status !== 'paid') {
                $account->update([
                    'paid_amount' => $account->total_amount,
                    'pending_amount' => 0.00,
                    'status' => 'paid',
                    'notes' => trim($account->notes . ' | Liquidada automáticamente al aprobar pago.')
                ]);
            }

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'El pago ha sido verificado y el estado de la orden actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar la orden: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, $id)
    {
        $order = Order::with('details.product.inventory')->findOrFail($id);

        if ($order->payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden rechazar órdenes en estado pendiente.']);
        }

        try {
            DB::beginTransaction();

            // 1. Devolver Stock e Inventario
            foreach ($order->details as $detail) {
                $product = $detail->product;

                if ($product && $product->track_inventory && $product->inventory) {
                    $previousStock = $product->inventory->stock;
                    $qtyToReturn = $detail->base_quantity;

                    $product->inventory->increment('stock', $qtyToReturn);

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'return',
                        'reference_type' => get_class($order),
                        'reference_id' => $order->id,
                        'quantity' => $qtyToReturn,
                        'previous_stock' => $previousStock,
                        'new_stock' => $previousStock + $qtyToReturn,
                        'notes' => 'Reintegro por orden rechazada: ' . $order->order_number,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // 2. Cancelar Orden
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'rejected',
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $order->notes . ' | Rechazada el ' . now()->format('d/m/Y') . ' por: ' . ($request->notes ?? 'Sin justificación'),
            ]);

            // 3. Cancelar Comprobantes y Pagos
            $order->paymentProofs()->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $order->payments()->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
            ]);

            // 4. NUEVO: Anular la cuenta por cobrar (Fiado/Deuda) si existe
            $account = \App\Models\AccountReceivable::where('order_id', $order->id)->first();
            if ($account) {
                $account->update([
                    'status' => 'cancelled', // Marcamos como cancelada
                    'pending_amount' => 0.00, // Ponemos en 0 para que no sume como deuda global
                    'notes' => trim($account->notes . ' | Anulada por rechazo de la orden.')
                ]);
            }

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Orden rechazada, deuda anulada y stock reintegrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al rechazar la orden: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // API ENDPOINTS PARA ALPINE.JS
    // ==========================================
    public function searchProduct(Request $request)
    {
        $query = $request->get('q');
        if (! $query) {
            return response()->json([]);
        }

        // Buscar primero coincidencia exacta por código de barras en Producto o Bulto
        $exactProduct = Product::with(['inventory', 'bulks'])->where('sku_barcode', $query)->where('status', 'active')->first();
        if ($exactProduct) {
            return response()->json(['exact' => true, 'data' => $exactProduct]);
        }

        $exactBulk = Bulk::with('product.inventory')->where('sku_barcode', $query)->first();
        if ($exactBulk && $exactBulk->product->status === 'active') {
            // Transformamos para que el frontend lo lea igual
            $bulkProduct = clone $exactBulk->product;
            $bulkProduct->bulks = collect([$exactBulk]);

            return response()->json(['exact' => true, 'data' => $bulkProduct]);
        }

        // Si no es código de barras, buscar por nombre
        $products = Product::with(['inventory', 'bulks'])
            ->where('name', 'like', "%{$query}%")
            ->where('status', 'active')
            ->take(10)
            ->get();

        return response()->json(['exact' => false, 'data' => $products]);
    }

    public function searchClient(Request $request)
    {
        $query = $request->get('q');
        $client = Client::where('identification', $query)->first();

        return response()->json(['client' => $client]); // Retorna null si no existe
    }

    public function storeClient(Request $request)
    {
        $request->validate(['identification' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:clients,identification']]);

        $client = Client::create([
            'uuid' => Str::uuid(),
            'identification' => $request->identification,
            'name' => 'Consumidor Final', // Nombre por defecto
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'client' => $client]);
    }

    // ==========================================
    // PROCESAMIENTO DE LA VENTA
    // ==========================================
    public function store(Request $request)
    {
        // El request vendrá como JSON desde Alpine
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'cart' => 'required|array|min:1',
            'payments' => 'nullable|array',
            'exchange_rate' => 'required|numeric',
        ]);

        $total_order = collect($data['cart'])->sum('subtotal');
        $amount_received = collect($data['payments'] ?? [])->sum(fn($p) => (float) ($p['amount'] ?? 0));
        $amount_pending = round($total_order - $amount_received, 2);

        // Determinar estado
        $payment_status = 'pending';
        if ($amount_received >= $total_order) {
            $payment_status = 'paid';
        } elseif ($amount_received > 0) {
            $payment_status = 'partial';
        }

        $verification_status = $payment_status === 'paid' ? 'verified' : 'pending';

        try {
            DB::beginTransaction();

            $orderNumber = 'ORD-' . date('Ym') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);

            // 1. Crear Orden
            $order = Order::create([
                'uuid' => Str::uuid(),
                'client_id' => $data['client_id'],
                'verified_by' => auth()->id(),
                'order_number' => $orderNumber,
                'order_type' => 'store',
                'payment_status' => $payment_status,
                'verification_status' => $verification_status,
                'status' => 'completed',
                'subtotal' => $total_order,
                'exchange_rate' => $data['exchange_rate'],
                'total' => $total_order,
                'notes' => 'Tasa de cambio: Bs. ' . $data['exchange_rate'],
            ]);

            // 2. Insertar Detalles y Descontar Inventario
            foreach ($data['cart'] as $item) {
                $baseQty = $item['quantity'] * $item['conversion_factor'];
                $product = Product::with('inventory')->find($item['product_id']);

                if (! $item['allow_negative'] && $product->inventory->stock < $baseQty) {
                    throw new \Exception("Stock insuficiente: {$item['name']}");
                }

                $order->details()->create([
                    'product_id' => $item['product_id'],
                    'bulk_id' => $item['bulk_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQty,
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $previousStock = $product->inventory->stock;

                // Descuento Físico
                $product->inventory()->decrement('stock', $baseQty);

                // Auditoría de Movimiento
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'reference_type' => get_class($order),
                    'reference_id' => $order->id,
                    'quantity' => $baseQty,
                    'previous_stock' => $previousStock,
                    'new_stock' => $previousStock - $baseQty,
                    'created_by' => auth()->id(),
                ]);
            }

            // 3. Registrar Pagos Realizados
            if (! empty($data['payments'])) {
                foreach ($data['payments'] as $payment) {
                    if ((float) $payment['amount'] > 0) {
                        OrderPayment::create([
                            'order_id' => $order->id,
                            'payment_method_id' => $payment['payment_method_id'],
                            'amount' => $payment['amount'],
                            'reference' => $payment['reference'] ?? null,
                            'payment_date' => now(),
                            'status' => 'verified',
                            'verified_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // 4. Si quedó debiendo (Fiado -> Cuentas por Cobrar)
            if ($amount_pending > 0) {
                AccountReceivable::create([
                    'order_id' => $order->id,
                    'client_id' => $data['client_id'],
                    'total_amount' => $total_order,
                    'paid_amount' => $amount_received,
                    'pending_amount' => $amount_pending,
                    'status' => $payment_status === 'partial' ? 'partial' : 'pending',
                    'due_date' => now()->addDays(15),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Venta Procesada! Orden: ' . $orderNumber,
                'redirect' => route('admin.orders.index'), // O donde quieras mandarlo tras el éxito
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deliver(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Validar que la orden esté pagada/aprobada y no haya sido entregada ya
        if ($order->status !== 'completed' || $order->delivered_at !== null) {
            return back()->withErrors(['error' => 'La orden no está lista para entrega o ya fue entregada.']);
        }

        try {
            DB::beginTransaction();

            $order->update([
                'delivered_at' => now(),
                // Opcional: si tienes un estado 'delivered' puedes cambiarlo aquí
                // 'status' => 'delivered' 
            ]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'La orden ha sido marcada como entregada al cliente exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar la entrega: ' . $e->getMessage()]);
        }
    }
}
