<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\Category;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::query()->paginate(10);
        $payment_methods = PaymentMethod::query()->paginate(10);
        $categories = Category::query()->paginate(10);
        $suppliers = Supplier::query()->paginate(10);

        return view('admin.index', [
            'admins' => $admins,
            'payment_methods' => $payment_methods,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
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
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
            'dni' => ['nullable', 'string', 'max:12', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:users,dni'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
            'phone_number' => ['required', 'string', 'max:15', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'email' => 'required|string|email|max:25|unique:users,email',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create($request->all());

        $user->assignRole('admin');

        return redirect()->route('admin.index')->with('success', 'Admin creado exitosamente');
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
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
            'dni' => ['nullable', 'string', 'max:12', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:users,dni,'.$id],
            'last_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\-\']+$/'],
            'phone_number' => ['required', 'string', 'max:15', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'email' => ['required', 'string', 'email', 'max:25', 'unique:users,email,'.$id],
            'password' => 'nullable|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'dni' => $request->dni,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'is_active' => $request->is_active,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        return redirect()->route('admin.index')->with('success', 'Admin actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.index')->with('success', 'Admin eliminado exitosamente');
    }
}
