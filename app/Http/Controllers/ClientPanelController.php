<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\Category;
use App\Models\Client;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientPanelController extends Controller
{
    /**
     * Helper para obtener el cliente del usuario autenticado o crearlo si no existe.
     */
    private function getClient()
    {
        $user = Auth::user();
        $client = $user->client;

        if (! $client) {
            $client = Client::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identification' => 'CI-'.rand(10000000, 30000000),
                'phone_number' => $user->phone_number ?? '',
                'address' => '',
                'is_active' => true,
            ]);
        }

        return $client;
    }

    /**
     * Vista pública del catálogo/escaparate de productos para visitantes e invitados.
     */
    public function storefront()
    {
        // 1. Redirecciones prioritarias si el usuario ya está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Si es Admin, va a su dashboard
            if ($user->hasRole('admin')) {
                return redirect()->route('dashboard');
            }

            // Si es Client, va a su dashboard de cliente
            if ($user->hasRole('client')) {
                return redirect()->route('client.dashboard');
            }
        }

        // 2. Si no está autenticado o no coincide con los roles de arriba, ve la tienda pública
        $products = Product::with(['category', 'inventory', 'images'])
            ->where('status', 'active')
            ->get();

        $categories = Category::where('is_active', true)->get();

        // Cargar datos de cliente (Por si acaso tu vista 'welcome' maneja alguna lógica condicional)
        $client = null;
        if (Auth::check() && Auth::user()->hasRole('client')) {
            $client = $this->getClient();
        }

        // Nota: Recuerda remover el dd() para que el flujo continúe normalmente
        return view('welcome', compact('products', 'categories', 'client'));
    }

    /**
     * Vista principal del panel de cliente.
     */
    public function dashboard()
    {
        $client = $this->getClient();

        // 1. Estadísticas
        $ordersQuery = Order::where('client_id', $client->id);
        $totalOrders = (clone $ordersQuery)->count();

        $pendingOrders = (clone $ordersQuery)->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')->count();

        // Suma de deudas pendientes desde cuentas por cobrar
        $totalDebt = AccountReceivable::where('client_id', $client->id)
            ->where('status', '!=', 'paid')
            ->sum('pending_amount');

        // Últimos 3 pedidos
        $recentOrders = (clone $ordersQuery)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('client.dashboard', compact('client', 'totalOrders', 'pendingOrders', 'totalDebt', 'recentOrders'));
    }

    public function checkoutView()
    {
        $client = $this->getClient();

        // Traer métodos de pago activos y habilitados para la web
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->where('show_in_checkout', true)
            ->get();

        return view('client.checkout', compact('client', 'paymentMethods'));
    }

    /**
     * Vista del catálogo de productos y carrito de compras.
     */
    public function products()
    {
        $client = $this->getClient();

        // Cargar productos que tengan stock positivo (o si se permiten existencias negativas)
        $products = Product::with(['category', 'inventory', 'images'])
            ->where('status', 'active')
            ->get();

        $categories = Category::where('is_active', true)->get();

        return view('client.products', compact('client', 'products', 'categories'));
    }

    /**
     * Procesar la orden de compra desde el carrito de compras del cliente.
     */
    public function checkout(Request $request)
    {
        $client = $this->getClient();
        $exchangeRate = Cache::get('usd_exchange_rate'); // Usando la llave corregida
        $rateVal = $exchangeRate ? (float) str_replace(',', '.', $exchangeRate) : 1.0;
        if ($rateVal <= 0) {
            $rateVal = 1.0;
        }

        $validated = $request->validate([
            'cart_items' => 'required|json',
            'delivery_type' => 'required|in:store_pickup,delivery',
            'delivery_address' => ['nullable', 'required_if:delivery_type,delivery', 'string', 'max:500', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\#\-\/°]+$/'],
            'payment_method_id' => 'required|exists:payment_methods,id',
            'reference' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9@\.\-\_\s\#]+$/'],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Máx 5MB
            'notes' => ['nullable', 'string', 'max:1000', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
        ]);

        $cartItems = json_decode($validated['cart_items'], true);

        if (empty($cartItems)) {
            return back()->withErrors(['error' => 'El carrito está vacío.']);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $detailsData = [];

            // Procesar cada ítem del carrito (Misma lógica original)
            foreach ($cartItems as $item) {
                $product = Product::with('inventory')->findOrFail($item['id']);
                $qty = (float) $item['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $qtyInBaseUnit = $product->unit_type === 'gram' ? ($qty * 1000) : $qty;

                if ($product->track_inventory && ! $product->allow_negative_stock) {
                    $available = $product->inventory ? (float) $product->inventory->stock : 0.0;
                    if ($qtyInBaseUnit > $available) {
                        $displayAvailable = $product->unit_type === 'gram' ? ($available / 1000) : $available;
                        $lbl = $product->unit_type === 'gram' ? 'Kgs' : 'Unds';
                        throw new \Exception("El producto '{$product->name}' excede el inventario. Disponible: ".number_format($displayAvailable, $product->unit_type === 'gram' ? 3 : 0, ',', '.')." {$lbl}");
                    }
                }

                $itemSubtotal = $product->price * $qtyInBaseUnit;
                $subtotal += $itemSubtotal;

                $detailsData[] = [
                    'product_id' => $product->id,
                    'bulk_id' => null,
                    'quantity' => $qty,
                    'base_quantity' => $qtyInBaseUnit,
                    'unit_price' => $product->display_price,
                    'unit_cost' => $product->display_cost,
                    'subtotal' => $itemSubtotal,
                    'discount' => 0.0,
                ];
            }

            $total = $subtotal;

            $lastOrder = Order::orderBy('id', 'desc')->first();
            $nextNum = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-'.str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            // 1. Crear la Orden
            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'client_id' => $client->id,
                'order_number' => $orderNumber,
                'order_type' => $validated['delivery_type'], // store_pickup o delivery
                'payment_status' => 'pending',
                'verification_status' => 'pending',
                'status' => 'pending',
                'client_name' => $client->name.' '.($client->last_name ?? ''),
                'client_phone' => $client->phone_number ?? '',
                'delivery_address' => $validated['delivery_type'] === 'store_pickup' ? 'Retiro en Tienda' : $validated['delivery_address'],
                'subtotal' => $subtotal,
                'tax' => 0,
                'discount' => 0,
                'total' => $total,
                'notes' => $validated['notes'],
                'exchange_rate' => $rateVal,
            ]);

            // 2. Guardar detalles e inventario (Igual que el original)
            foreach ($detailsData as $detail) {
                $detail['order_id'] = $order->id;
                OrderDetail::create($detail);

                $product = Product::with('inventory')->findOrFail($detail['product_id']);
                if ($product->track_inventory && $product->inventory) {
                    $previousStock = (float) $product->inventory->stock;
                    $qtyToSubtract = $detail['base_quantity'];
                    $product->inventory->stock -= $qtyToSubtract;
                    $product->inventory->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'reference_type' => get_class($order),
                        'reference_id' => $order->id,
                        'quantity' => $qtyToSubtract,
                        'previous_stock' => $previousStock,
                        'new_stock' => $previousStock - $qtyToSubtract,
                        'notes' => 'Pedido web '.$order->order_number,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // 3. Procesar el comprobante de pago subido
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $path = $file->store('receipts', 'public');

                $proof = PaymentProof::create([
                    'order_id' => $order->id,
                    'uploaded_by' => Auth::id(),
                    'reference' => $validated['reference'],
                    'status' => 'pending',
                    'notes' => 'Comprobante cargado durante el checkout web.',
                ]);

                $proof->images()->create([
                    'path' => $path,
                    'disk' => 'public',
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'is_primary' => true,
                ]);

                // Registrar el pago en la orden como pendiente de verificación
                OrderPayment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'amount' => $total, // Se asume pago completo por la compra
                    'reference' => $validated['reference'],
                    'payment_date' => now(),
                    'status' => 'pending', // Administrador debe cambiarlo a 'verified'
                    'notes' => 'Pago total reportado en checkout.',
                ]);
            }

            // 4. Registrar cuenta por cobrar
            AccountReceivable::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'client_id' => $client->id,
                'total_amount' => $total,
                'paid_amount' => 0.00,
                'pending_amount' => $total,
                'due_date' => now()->addDays(7),
                'status' => 'pending',
            ]);

            DB::commit();

            event(new \App\Events\SaleCreated($order));

            return redirect()->route('client.purchases')
                ->with('success', "Orden de compra {$orderNumber} registrada con éxito. Comprobante en revisión.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Error al procesar la compra: '.$e->getMessage()]);
        }
    }

    /**
     * Historial de compras.
     */
    public function purchases()
    {
        $client = $this->getClient();
        $orders = Order::where('client_id', $client->id)
            ->with(['details.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('client.purchases', compact('client', 'orders'));
    }

    /**
     * Historial de facturas y abonos pendientes.
     */
    public function invoices()
    {
        $client = $this->getClient();

        // Facturas pendientes (órdenes no pagadas del todo)
        $invoices = Order::where('client_id', $client->id)
            ->where('payment_status', '!=', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(3);

        // Cuentas por cobrar asociadas con su desglose de abonos (installments)
        $accounts = AccountReceivable::where('client_id', $client->id)
            ->with(['order.payments.paymentMethod', 'installments'])
            ->orderBy('created_at', 'desc')
            ->paginate(3);

        $paymentMethods = PaymentMethod::where('is_active', true)->where('show_in_checkout', true)->get();

        return view('client.invoices', compact('client', 'invoices', 'accounts', 'paymentMethods'));
    }

    /**
     * Procesa el reporte de pago/abono subido por el cliente.
     */
    public function reportPayment(Request $request)
    {
        $client = $this->getClient();

        $validated = $request->validate([
            'account_receivable_id' => 'required|exists:accounts_receivable,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:100',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Máximo 5MB
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verificar que la deuda pertenezca realmente a este cliente
        $account = AccountReceivable::where('client_id', $client->id)
            ->findOrFail($validated['account_receivable_id']);

        if ($validated['amount'] > $account->pending_amount) {
            return back()->withInput()->withErrors(['error' => 'El monto del abono no puede superar el saldo pendiente ('.number_format($account->pending_amount, 2, ',', '.').' BS).']);
        }

        try {
            DB::beginTransaction();

            $order = $account->order;

            // 1. Guardar la imagen del comprobante
            $file = $request->file('payment_proof');
            $path = $file->store('receipts', 'public');

            // 2. Crear el registro PaymentProof (Para visualizar la imagen en el panel admin)
            $proof = PaymentProof::create([
                'order_id' => $order->id,
                'uploaded_by' => Auth::id(),
                'reference' => $validated['reference'],
                'status' => 'pending',
                'notes' => 'Abono reportado desde panel cliente. '.($validated['notes'] ?? ''),
            ]);

            $proof->images()->create([
                'path' => $path,
                'disk' => 'public',
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_primary' => true,
            ]);

            // 3. Registrar la intención de pago en la orden (Queda pendiente hasta que el admin lo concilie)
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'reference' => $validated['reference'],
                'payment_date' => now(),
                'status' => 'pending',
                'notes' => 'Abono a deuda. Reporte web pendiente de verificación.',
            ]);

            DB::commit();

            event(new \App\Events\OrderStatusUpdated($order));

            return redirect()->route('client.invoices')
                ->with('success', '¡Comprobante enviado exitosamente! Será verificado por administración en breve.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Error al reportar el abono: '.$e->getMessage()]);
        }
    }

    /**
     * Formulario unificado de perfil.
     */
    public function profile()
    {
        $client = $this->getClient();
        $user = Auth::user();

        return view('client.profile', compact('client', 'user'));
    }

    /**
     * Actualización de datos de perfil (Usuario + Cliente).
     */
    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'identification' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-]+$/', Rule::unique('clients')->ignore($client->id)],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'address' => ['nullable', 'string', 'max:1000', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\#\-\/°]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar Datos Generales de Usuario
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            // 2. Actualizar Datos de Perfil de Cliente
            $client->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identification' => $validated['identification'],
                'phone' => $validated['phone'], // utiliza el mutador virtual para phone_number
                'address' => $validated['address'],
            ]);

            DB::commit();

            return redirect()->route('client.profile')
                ->with('success', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Error al actualizar perfil: '.$e->getMessage()]);
        }
    }
}
