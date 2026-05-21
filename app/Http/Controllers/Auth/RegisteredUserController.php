<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'cedula' => ['required', 'string', 'max:20', 'unique:'.User::class.',dni'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'dni' => $request->cedula,
            'last_name' => '',
            'phone_number' => '',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol de cliente por defecto (asegurando que exista para compatibilidad y entornos de test)
        $roleName = 'client';
        if (!\Spatie\Permission\Models\Role::where('name', $roleName)->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => $roleName]);
        }
        $user->assignRole($roleName);

        // Crear perfil de cliente asociado
        \App\Models\Client::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'identification' => 'CI-' . $request->cedula,
            'phone_number' => '',
            'address' => '',
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($user->hasRole('client')) {
            return redirect(route('client.dashboard', absolute: false));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
