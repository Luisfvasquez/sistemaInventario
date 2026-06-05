<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Display a listing of users and their assigned roles.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query users, eager load roles, and paginate
        $users = User::query()
            ->with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        $roles = Role::all();

        return view('admin.users_roles.index', compact('users', 'roles', 'search'));
    }

    /**
     * Update the roles assigned to a user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        // Prevent removing the admin role from the current logged-in user if they are the only admin
        if ($user->id === auth()->id() && !in_array('admin', $request->roles ?? [])) {
            // Check if there are other active admin users
            $otherAdminsCount = User::role('admin')->where('id', '!=', $user->id)->count();
            if ($otherAdminsCount === 0) {
                return redirect()->route('admin.users-roles.index')->with('error', 'No puedes quitarte el rol Administrador a ti mismo si eres el único administrador del sistema.');
            }
        }

        // Sync the selected roles using Spatie's helper
        $user->syncRoles($request->roles ?? []);

        return redirect()->route('admin.users-roles.index')->with('success', "Roles actualizados correctamente para el usuario {$user->name}.");
    }
}
