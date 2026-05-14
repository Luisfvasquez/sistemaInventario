<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
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
        $clients = Client::all();
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('admin.index', [
            'admins' => $admins,
            'clients' => $clients,
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
