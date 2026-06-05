<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load roles with counts of associated users and permissions, eager loading the permissions
        $roles = Role::with('permissions')->withCount(['users', 'permissions'])->get();
        $permissions = Permission::all();

        // Group permissions logically to display them nicely in the UI
        $groupedPermissions = [
            'General / Dashboard' => [],
            'Productos' => [],
            'Clientes' => [],
            'Administradores' => [],
            'Categorías' => [],
            'Proveedores' => [],
            'Inventario' => [],
            'Compras' => [],
            'Ventas y Órdenes' => [],
            'Punto de Venta (POS)' => [],
            'Métodos de Pago' => [],
            'Configuración' => [],
        ];

        foreach ($permissions as $permission) {
            $name = $permission->name;

            if (str_starts_with($name, 'admin.products')) {
                $groupedPermissions['Productos'][] = $permission;
            } elseif (str_starts_with($name, 'admin.clients')) {
                $groupedPermissions['Clientes'][] = $permission;
            } elseif (str_starts_with($name, 'admin.admins')) {
                $groupedPermissions['Administradores'][] = $permission;
            } elseif (str_starts_with($name, 'admin.categories')) {
                $groupedPermissions['Categorías'][] = $permission;
            } elseif (str_starts_with($name, 'admin.suppliers')) {
                $groupedPermissions['Proveedores'][] = $permission;
            } elseif (str_starts_with($name, 'admin.inventories')) {
                $groupedPermissions['Inventario'][] = $permission;
            } elseif (str_starts_with($name, 'admin.purchases')) {
                $groupedPermissions['Compras'][] = $permission;
            } elseif (str_starts_with($name, 'admin.orders')) {
                $groupedPermissions['Ventas y Órdenes'][] = $permission;
            } elseif (str_starts_with($name, 'admin.pos')) {
                $groupedPermissions['Punto de Venta (POS)'][] = $permission;
            } elseif (str_starts_with($name, 'admin.payment_methods')) {
                $groupedPermissions['Métodos de Pago'][] = $permission;
            } elseif (str_starts_with($name, 'admin.roles') || str_starts_with($name, 'admin.users-roles')) {
                $groupedPermissions['Configuración'][] = $permission;
            } else {
                $groupedPermissions['General / Dashboard'][] = $permission;
            }
        }

        return view('admin.roles.index', compact('roles', 'groupedPermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este nombre de rol ya está registrado.',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este nombre de rol ya está registrado.',
        ]);

        // Prevent modification of the main 'admin' role name for stability
        if ($role->name === 'admin' && $request->name !== 'admin') {
            return redirect()->route('admin.roles.index')->with('error', 'No se puede cambiar el nombre del rol Administrador principal.');
        }

        $role->update([
            'name' => $request->name,
        ]);

        // Admins should always keep all permissions. Let's still sync or manage.
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        // Protected system roles
        if (in_array($role->name, ['admin', 'client', 'seller', 'warehouse'])) {
            return redirect()->route('admin.roles.index')->with('error', 'No se pueden eliminar los roles predeterminados del sistema.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente');
    }
}
