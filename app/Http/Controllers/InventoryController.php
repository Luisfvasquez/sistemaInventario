<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.inventories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'adjustment_type' => 'required|in:addition,subtraction',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\;\:\-\/\(\)\¿\?\¡\!\@\#\%\&\=\+\'\"°\n\r]+$/'],
        ]);

        $quantity = $request->quantity;

        // Si es resta, convertimos el número a negativo para la suma algebraica
        if ($request->adjustment_type === 'subtraction') {
            $quantity = -$quantity;
        }

        // Actualizamos el stock
        $inventory->increment('stock', $quantity);

        event(new \App\Events\InventoryUpdated($inventory));

        // TODO: Registrar en la tabla inventory_movements el motivo ($request->reason)
        // El paquete de auditoría que configuramos registrará quién hizo este cambio.

        return back()->with('success', 'Stock ajustado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
