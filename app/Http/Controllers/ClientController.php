<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\AccountReceivable;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        // Cargamos la relación user y deudas para comprobar deudas rápidamente desde el listado
        $clients = Client::with(['user', 'accountsReceivable'])->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        // 1. Validaciones base para cualquier cliente
        $rules = [
            'name' => 'required|string|max:255',
            'identification' => 'required|string|unique:clients,identification', // Cédula o RIF
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'create_account' => 'nullable|boolean',
        ];

        // 2. Si se marca "Crear cuenta web", el correo se vuelve obligatorio y único en la tabla users
        if ($request->has('create_account') && $request->create_account == true) {
            $rules['email'] = 'required|email|unique:users,email';
        } else {
            $rules['email'] = 'nullable|email|unique:clients,email';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $userId = null;

            // 3. Lógica Maestra: Si solicitó cuenta web, creamos el usuario automáticamente
            if ($request->has('create_account') && $request->create_account == true) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    // La contraseña inicial es su propia cédula/identificación despojada de espacios
                    'password' => Hash::make(trim($validated['identification'])),
                ]);

                $userId = $user->id;

                // Si estás usando Spatie Roles, aquí asignarías el rol de cliente:
                // $user->assignRole('client');
            }

            // 4. Crear el perfil del cliente
            Client::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'user_id' => $userId, // Queda en null si no se creó cuenta web
                'name' => $validated['name'],
                'identification' => trim($validated['identification']),
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'],
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Cliente registrado correctamente'.($userId ? ' junto con su cuenta de acceso web.' : '.'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors([
                'error' => 'Hubo un error al registrar el cliente: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with([
            'orders.details.product',
            'orders.details.bulk',
            'orders.payments.paymentMethod',
            'accountsReceivable.installments'
        ])->findOrFail($id);

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('admin.clients.show', compact('client', 'paymentMethods'));
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
        $client = Client::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:50',
            'identification' => 'required|string|max:50|unique:clients,identification,'.$client->id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'email' => 'nullable|email|unique:clients,email,'.$client->id,
        ];

        $validated = $request->validate($rules);

        $client->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'email' => $validated['email'] ?? $client->email,
            'is_active' => $request->boolean('is_active', $client->is_active),
        ]);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Registrar un abono a una cuenta por cobrar de un cliente.
     */
    public function registerAbono(Request $request, string $clientId)
    {
        $validated = $request->validate([
            'account_receivable_id' => 'required|exists:accounts_receivable,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'reference' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($clientId);
        $account = AccountReceivable::where('client_id', $client->id)
            ->findOrFail($validated['account_receivable_id']);

        $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);
        $isTransferenciaOrPagoMovil = in_array($paymentMethod->id, [6, 7]) 
            || str_contains(strtolower($paymentMethod->name), 'transferencia') 
            || str_contains(strtolower($paymentMethod->name), 'pago móvil') 
            || str_contains(strtolower($paymentMethod->name), 'pago movil');

        if (($paymentMethod->requires_reference || $isTransferenciaOrPagoMovil) && empty($validated['reference'])) {
            return back()->withInput()->withErrors([
                'error' => 'La referencia es obligatoria para el método de pago seleccionado: ' . $paymentMethod->name
            ]);
        }

        if ($validated['amount'] > $account->pending_amount) {
            return back()->withInput()->withErrors([
                'error' => 'El monto del abono no puede superar el saldo pendiente de la deuda (' . number_format($account->pending_amount, 2, ',', '.') . ').'
            ]);
        }

        try {
            DB::beginTransaction();

            // 1. Obtener número de cuota
            $installmentNumber = $account->installments()->count() + 1;

            // 2. Registrar la cuota de abono
            $account->installments()->create([
                'installment_number' => $installmentNumber,
                'amount' => $validated['amount'],
                'paid_amount' => $validated['amount'],
                'pending_amount' => 0.00,
                'due_date' => $validated['payment_date'],
                'paid_at' => $validated['payment_date'],
                'status' => 'paid',
                'notes' => $validated['notes'],
            ]);

            // 3. Registrar el pago en el historial de la Orden
            $order = $account->order;
            $paymentNotes = "Abono #" . $installmentNumber . " a cuenta por cobrar.";
            if (!empty($validated['notes'])) {
                $paymentNotes .= " Observaciones: " . $validated['notes'];
            }

            OrderPayment::create([
                'order_id' => $order->id,
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'reference' => $validated['reference'],
                'payment_date' => $validated['payment_date'],
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'notes' => $paymentNotes,
            ]);

            // 4. Actualizar saldos en AccountReceivable
            $account->paid_amount += $validated['amount'];
            $account->pending_amount -= $validated['amount'];

            if ($account->pending_amount <= 0) {
                $account->status = 'paid';
            } else {
                $account->status = 'partial';
            }
            $account->save();

            // 5. Actualizar estado de pago en la Orden
            if ($account->status === 'paid') {
                $order->payment_status = 'paid';
                $order->verification_status = 'verified'; // Si la deuda se salda completamente, marcamos la orden como verificada
                $order->status = 'completed'; // Si la deuda se salda completamente, marcamos la orden como verificada
            } else {
                $order->payment_status = 'partial';
            }
            $order->save();

            DB::commit();

            return redirect()->route('admin.clients.show', $client->id)
                ->with('success', 'Abono registrado correctamente de ' . number_format($validated['amount'], 2, ',', '.') . '.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors([
                'error' => 'Hubo un error al registrar el abono: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar el estado de verificación de un pedido.
     */
    public function updateOrderVerification(Request $request, string $orderId)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified',
        ]);

        $order = \App\Models\Order::findOrFail($orderId);
        
        if ($order->verification_status !== 'pending') {
            return redirect()->back()->with('error', 'El estado de verificación del pedido ya no se encuentra pendiente.');
        }

        $order->verification_status = 'verified';
        $order->verified_at = now();
        $order->verified_by = auth()->id();
        $order->save();

        return redirect()->back()->with('success', 'Estado de verificación del pedido ' . $order->order_number . ' actualizado a Verificado.');
    }
}
