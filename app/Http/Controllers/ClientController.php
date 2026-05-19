<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        // Cargamos la relación user para saber quién tiene acceso web directo desde el listado
        $clients = Client::with('user')->get();

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
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
