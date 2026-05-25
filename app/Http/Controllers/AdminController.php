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
      $admins = User::query()->paginate(10);
        $payment_methods = PaymentMethod::query()->paginate(10);
        $categories = Category::query()->paginate(10);
        $suppliers = Supplier::query()->paginate(10);

        // ── Dashboard Statistics ──
        $totalProducts   = Product::count();
        $totalClients    = Client::count();
        $totalOrders     = Order::count();
        $totalSuppliers  = Supplier::count();

        // Revenue this month
        $monthlyRevenue = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');

        // Revenue last month (for comparison)
        $lastMonthRevenue = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthlyRevenue > 0 ? 100 : 0);

        // Orders this month vs last month
        $monthlyOrders = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $lastMonthOrders = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $ordersChange = $lastMonthOrders > 0
            ? round((($monthlyOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : ($monthlyOrders > 0 ? 100 : 0);

        // Low stock products (stock <= minimum_stock)
        $lowStockProducts = Product::whereHas('inventory', function ($q) {
            $q->whereColumn('stock', '<=', 'minimum_stock');
        })->with('inventory')->take(5)->get();

        $lowStockCount = Product::whereHas('inventory', function ($q) {
            $q->whereColumn('stock', '<=', 'minimum_stock');
        })->count();

        // Recent orders (latest 5)
        $recentOrders = Order::with('client')->latest()->take(5)->get();

        // Pending accounts receivable
        $pendingReceivables = AccountReceivable::where('status', '!=', 'paid')->sum('pending_amount');
        $totalReceivablesCount = AccountReceivable::where('status', '!=', 'paid')->count();

        // Orders by status (for chart)
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Purchases this month
        $monthlyPurchases = Purchase::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');

        return view('admin.dashboard.index', [
            'admins' => $admins,
            'payment_methods' => $payment_methods,
            'categories' => $categories,
            'suppliers' => $suppliers,
            // Dashboard data
            'totalProducts' => $totalProducts,
            'totalClients' => $totalClients,
            'totalOrders' => $totalOrders,
            'totalSuppliers' => $totalSuppliers,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueChange' => $revenueChange,
            'monthlyOrders' => $monthlyOrders,
            'ordersChange' => $ordersChange,
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => $recentOrders,
            'pendingReceivables' => $pendingReceivables,
            'totalReceivablesCount' => $totalReceivablesCount,
            'ordersByStatus' => $ordersByStatus,
            'monthlyPurchases' => $monthlyPurchases,
        ]); }

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
