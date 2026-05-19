<?php

namespace App\Livewire\Admin;

use App\Models\AccountReceivable;
use App\Models\Bulk;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class PosComponent extends Component
{
    // --- CLIENTE ---
    public $client_identification = '';

    public $client_id = null;

    public $client_name = '';

    // --- BÚSQUEDA DE PRODUCTOS (Lectora / Texto) ---
    public $search_query = '';

    public $search_results = [];

    // --- CARRITO ---
    public $cart = [];

    public $exchange_rate = 1;

    // --- PAGOS ---
    public $available_payment_methods = [];

    public $payments = [];

    public $amount_received = 0;

    public $amount_pending = 0;

    public function mount()
    {
        $rate = ExchangeRate::where('is_active', true)->first();
        $this->exchange_rate = $rate ? (float) $rate->rate : 1;
        $this->available_payment_methods = PaymentMethod::where('is_active', true)->get();

        if ($this->available_payment_methods->count() > 0) {
            $this->addPaymentLine();
        }
    }

    // ==========================================
    // 1. LÓGICA DE CLIENTES (Creación On-the-fly)
    // ==========================================
    public function searchClient()
    {
        $this->client_identification = trim($this->client_identification);
        if (empty($this->client_identification)) {
            $this->client_id = null;
            $this->client_name = '';

            return;
        }

        $client = Client::where('identification', $this->client_identification)->first();

        if ($client) {
            $this->client_id = $client->id;
            $this->client_name = $client->name ?? 'Cliente Registrado';
        } else {
            // Evaluando tu migración, 'identification' es requerido, los demás son nullables.
            $newClient = Client::create([
                'uuid' => Str::uuid(),
                'identification' => $this->client_identification,
                'name' => 'Consumidor Final', // Se puede editar luego
                'is_active' => true,
            ]);

            $this->client_id = $newClient->id;
            $this->client_name = 'Nuevo Cliente (Autoregistrado)';
        }
    }

    // ==========================================
    // 2. LÓGICA DEL BUSCADOR / LECTORA DE CÓDIGOS
    // ==========================================
    public function scanProduct()
    {
        $query = trim($this->search_query);
        if (empty($query)) {
            $this->search_results = [];

            return;
        }

        // A. Buscar coincidencia EXACTA por código de barras en Productos (Unidad base)
        $product = Product::with(['inventory', 'bulks'])->where('sku_barcode', $query)->first();
        if ($product) {
            $defaultBulk = $product->bulks->where('is_default', true)->first();
            if ($defaultBulk) {
                $this->addToCart($product, $defaultBulk);
            }

            return;
        }

        // B. Buscar coincidencia EXACTA en Presentaciones Especiales (Bultos/Cajas)
        $bulk = Bulk::with('product.inventory')->where('sku_barcode', $query)->first();
        if ($bulk) {
            $this->addToCart($bulk->product, $bulk);

            return;
        }

        // C. Si la lectora no encontró nada, o el usuario está tipeando un nombre...
        $this->search_results = Product::with(['inventory', 'bulks'])
            ->where('name', 'like', '%'.$query.'%')
            ->where('status', 'active')
            ->take(8) // Limitamos a 8 para no saturar la vista
            ->get();
    }

    public function addToCart(Product $product, Bulk $bulk)
    {
        $existingIndex = collect($this->cart)->search(function ($item) use ($product, $bulk) {
            return $item['product_id'] == $product->id && $item['bulk_id'] == $bulk->id;
        });

        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['subtotal'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'bulk_id' => $bulk->id,
                'name' => $product->name,
                'presentation' => $bulk->name,
                'price' => $bulk->sale_price,
                'quantity' => 1,
                'conversion_factor' => $bulk->quantity,
                'subtotal' => $bulk->sale_price,
                'allow_negative' => $product->allow_negative_stock,
                'current_stock' => $product->inventory->stock ?? 0,
            ];
        }

        $this->search_query = ''; // Limpiamos la lectora
        $this->search_results = [];
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotals();
    }

    // ==========================================
    // 3. MULTIPLES MÉTODOS DE PAGO Y FIADOS
    // ==========================================
    public function addPaymentLine()
    {
        $this->payments[] = [
            'payment_method_id' => $this->available_payment_methods->first()->id ?? null,
            'amount' => '',
            'reference' => '',
        ];
    }

    public function removePaymentLine($index)
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $total_order = collect($this->cart)->sum('subtotal');

        $this->amount_received = collect($this->payments)->sum(function ($payment) {
            return (float) ($payment['amount'] ?: 0);
        });

        $this->amount_pending = round($total_order - $this->amount_received, 2);
    }

    // ==========================================
    // 4. FACTURACIÓN Y BASE DE DATOS
    // ==========================================
    public function processOrder()
    {
        $this->calculateTotals();
        $total_order = collect($this->cart)->sum('subtotal');

        if (empty($this->client_id) || empty($this->cart) || $total_order <= 0) {
            session()->flash('error', 'Faltan datos (Cliente o Productos) para procesar la venta.');

            return;
        }

        // Determinar estado de la orden
        $payment_status = 'pending';
        if ($this->amount_received >= $total_order) {
            $payment_status = 'paid';
        } elseif ($this->amount_received > 0) {
            $payment_status = 'partial';
        }

        try {
            DB::beginTransaction();

            $orderNumber = 'ORD-'.date('Ym').'-'.str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);

            // A. CREAR ORDEN
            $order = Order::create([
                'uuid' => Str::uuid(),
                'client_id' => $this->client_id,
                'verified_by' => auth()->id(),
                'order_number' => $orderNumber,
                'order_type' => 'store_pickup',
                'payment_status' => $payment_status,
                'status' => 'completed',
                'subtotal' => $total_order,
                'total' => $total_order,
                // Nota: Tu migración de 'orders' no tiene 'exchange_rate', así que lo guardamos en notas para historial
                'notes' => 'Tasa de cambio aplicada: Bs. '.$this->exchange_rate,
            ]);

            // B. DETALLES Y MOVIMIENTOS DE INVENTARIO
            foreach ($this->cart as $item) {
                $baseQty = $item['quantity'] * $item['conversion_factor'];
                $product = Product::with('inventory')->find($item['product_id']);

                if (! $item['allow_negative'] && $product->inventory->stock < $baseQty) {
                    throw new \Exception("Stock insuficiente: {$item['name']}");
                }

                // Detalle
                $order->details()->create([
                    'product_id' => $item['product_id'],
                    'bulk_id' => $item['bulk_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQty,
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $previousStock = $product->inventory->stock;

                // Restar físico
                $product->inventory()->decrement('stock', $baseQty);

                // Auditoría Profesional en InventoryMovements
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

            // C. PROCESAR PAGOS (Múltiples métodos)
            foreach ($this->payments as $payment) {
                if ((float) $payment['amount'] > 0) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'payment_method_id' => $payment['payment_method_id'],
                        'amount' => $payment['amount'],
                        'reference' => $payment['reference'],
                        'payment_date' => now(),
                        'status' => 'verified',
                        'verified_by' => auth()->id(),
                    ]);
                }
            }

            // D. CUENTAS POR COBRAR (FIADOS)
            if ($this->amount_pending > 0) {
                AccountReceivable::create([
                    'order_id' => $order->id,
                    'client_id' => $this->client_id,
                    'total_amount' => $total_order,
                    'paid_amount' => $this->amount_received,
                    'pending_amount' => $this->amount_pending,
                    'status' => $payment_status === 'partial' ? 'partial' : 'pending',
                    'due_date' => now()->addDays(15),
                ]);
            }

            DB::commit();

            // Resetear todo para el próximo cliente
            $this->reset(['cart', 'payments', 'client_identification', 'client_id', 'client_name', 'search_query', 'amount_received', 'amount_pending']);
            $this->addPaymentLine();

            session()->flash('success', '¡Factura Procesada! Orden: '.$orderNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pos-component');
    }
}
