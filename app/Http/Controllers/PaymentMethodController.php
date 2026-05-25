<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Almacena un nuevo método de pago en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
            'requires_reference' => 'boolean',
            'show_in_checkout' => 'boolean',
            'is_active' => 'boolean',
        ]);

        PaymentMethod::create($validated);

        return redirect()->back()->with('success', 'Método de pago creado exitosamente.');
    }

    /**
     * Actualiza un método de pago existente.
     */
    public function update(Request $request, string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
            'requires_reference' => 'boolean',
            'show_in_checkout' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        // Si necesitas que el modal se cierre y vuelva a la pestaña correcta:
        return redirect()->back()
            ->with('success', 'Método de pago actualizado exitosamente.')
            ->withInput(['modal_type' => 'payment_methods']);
    }
}
