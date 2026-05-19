<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $products = Product::all();
        $clients = Client::all();
        $orders = Order::all();
        $purchases = Purchase::all();

        return view('admin.dashboard.index', compact('products', 'clients', 'orders', 'purchases'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::all();
        $payment_methods = PaymentMethod::all();
        $categories = Category::all();
        $suppliers = Supplier::all();

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
            'name' => 'required|string|max:50',
            'dni' => 'nullable|string|max:12|unique:users,dni',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|string|email|max:25|unique:users,email',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create($request->all());

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
            'name' => 'required|string|max:50',
            'dni' => 'nullable|string|max:12|unique:users,dni,'.$id,
            'last_name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|string|email|max:25|unique:users,email,'.$id,
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
