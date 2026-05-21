<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\AccountReceivable;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ClientPanelController extends Controller
{
    /**
     * Helper para obtener el cliente del usuario autenticado o crearlo si no existe.
     */
    private function getClient()
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            $client = Client::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identification' => 'CI-' . rand(10000000, 30000000),
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
        $products = Product::with(['category', 'inventory', 'images'])
            ->where('status', 'active')
            ->get();
            
        $categories = Category::where('is_active', true)->get();

        // Cargar datos de cliente si está autenticado para permitir checkout directo
        $client = null;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'client') {
                $client = $this->getClient();
            }
        }

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
        
        $pendingOrders = (clone $ordersQuery)->where('payment_status', '!=', 'paid')->count();
        
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
        $exchangeRate = Cache::get('exchange_rate');
        $rateVal = $exchangeRate ? $exchangeRate : 1.0;

        $validated = $request->validate([
            'delivery_address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'cart_items' => 'required|json', // Formato JSON enviado desde Alpine.js [{id, quantity}]
        ]);

        $cartItems = json_decode($validated['cart_items'], true);

        if (empty($cartItems)) {
            return back()->withErrors(['error' => 'El carrito está vacío.']);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $tax = 0;
            $discount = 0;
            $detailsData = [];

            // Procesar cada ítem del carrito
            foreach ($cartItems as $item) {
                $product = Product::with('inventory')->findOrFail($item['id']);
                $qty = (float) $item['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $qtyInBaseUnit = $product->unit_type === 'gram' ? ($qty * 1000) : $qty;

                // Verificar stock disponible si se trackea inventario
                if ($product->track_inventory && !$product->allow_negative_stock) {
                    $available = $product->inventory ? (float) $product->inventory->stock : 0.0;
                    if ($qtyInBaseUnit > $available) {
                        $displayAvailable = $product->unit_type === 'gram' ? ($available / 1000) : $available;
                        $lbl = $product->unit_type === 'gram' ? 'Kgs' : 'Unds';
                        throw new \Exception("El producto '{$product->name}' no cuenta con inventario suficiente. Disponible: " . number_format($displayAvailable, $product->unit_type === 'gram' ? 3 : 0, ',', '.') . " {$lbl}");
                    }
                }

                // Calcular total para este producto
                // Si el producto es pesable (gram), la cantidad viene expresada en kilos (ej: 0.25 para 250g)
                // y el precio en DB está por gramo (o unitario). En el modelo display_price = price * 1000.
                $itemSubtotal = $product->price * $qtyInBaseUnit;
                $subtotal += $itemSubtotal;

                $detailsData[] = [
                    'product_id' => $product->id,
                    'bulk_id' => null,
                    'quantity' => $qty,
                    'base_quantity' => $qtyInBaseUnit,
                    'unit_price' => $product->display_price, // precio comercial
                    'unit_cost' => $product->display_cost,   // costo comercial
                    'subtotal' => $itemSubtotal,
                    'discount' => 0.0,
                ];
            }

            $total = $subtotal; // Impuestos/Descuentos simplificados a 0

            // Generar correlativo de orden de compra
            $lastOrder = Order::orderBy('id', 'desc')->first();
            $nextNum = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            // Crear la Orden
            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'client_id' => $client->id,
                'order_number' => $orderNumber,
                'order_type' => 'delivery', // Pedido web / digital
                'payment_status' => 'pending',
                'verification_status' => 'pending',
                'status' => 'pending',
                'client_name' => $client->name . ' ' . ($client->last_name ?? ''),
                'client_phone' => $client->phone_number ?? '',
                'delivery_address' => $validated['delivery_address'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'notes' => $validated['notes'],
                'exchange_rate' => $rateVal,
            ]);

            // Guardar detalles y descontar stock de inventario
            foreach ($detailsData as $detail) {
                $detail['order_id'] = $order->id;
                OrderDetail::create($detail);

                // Descontar inventario
                $product = Product::with('inventory')->findOrFail($detail['product_id']);
                if ($product->track_inventory && $product->inventory) {
                    $previousStock = (float) $product->inventory->stock;
                    $qtyToSubtract = $detail['base_quantity'];
                    
                    $product->inventory->stock -= $qtyToSubtract;
                    $product->inventory->save();

                    // Registrar auditoría de movimiento de inventario
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'reference_type' => get_class($order),
                        'reference_id' => $order->id,
                        'quantity' => $qtyToSubtract,
                        'previous_stock' => $previousStock,
                        'new_stock' => $previousStock - $qtyToSubtract,
                        'notes' => 'Pedido web ' . $order->order_number,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Registrar cuenta por cobrar de forma automática
            AccountReceivable::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'client_id' => $client->id,
                'total_amount' => $total,
                'paid_amount' => 0.00,
                'pending_amount' => $total,
                'due_date' => now()->addDays(7), // 7 días límite
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('client.purchases')
                ->with('success', "Orden de compra {$orderNumber} registrada con éxito. Por favor espera la verificación de tu pago.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
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
            ->get();

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
            ->get();

        // Cuentas por cobrar asociadas con su desglose de abonos (installments)
        $accounts = AccountReceivable::where('client_id', $client->id)
            ->with(['order', 'installments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.invoices', compact('client', 'invoices', 'accounts'));
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'identification' => ['required', 'string', 'max:50', Rule::unique('clients')->ignore($client->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar Datos Generales de Usuario
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            if (!empty($validated['password'])) {
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
            return back()->withInput()->withErrors(['error' => 'Error al actualizar perfil: ' . $e->getMessage()]);
        }
    }
}
