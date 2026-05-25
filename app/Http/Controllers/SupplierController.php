<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'rif' => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\.]+$/'],
            'email' => 'required|email|unique:suppliers,email',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'address' => ['nullable', 'string', 'max:200', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\#\-\/°]+$/'],
            'contact_person' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
        ]);

        try {
            Supplier::create([
                'name' => $request->name,
                'rif' => $request->rif,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'address' => $request->address,
                'contact_person' => $request->contact_person,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.index')->with('success', 'Proveedor creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.index')->with('error', 'Error creating supplier: '.$e->getMessage());
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
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-\/\(\)\&\%]+$/'],
            'rif' => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\.]+$/'],
            'email' => ['nullable', 'email', 'unique:suppliers,email,'.$id],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'address' => ['nullable', 'string', 'max:200', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\,\#\-\/°]+$/'],
            'contact_person' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
        ]);

        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'name' => $request->name,
            'rif' => $request->rif,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('admin.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}
