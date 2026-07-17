<?php

namespace App\Livewire;

use App\Models\AccountReceivable;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class AdminDashboard extends Component
{
    #[On('purchase-created')]
    #[On('purchase-cancelled')]
    #[On('sale-created')]
    #[On('sale-cancelled')]
    #[On('sale-rejected')]
    #[On('order-status-updated')]
    #[On('inventory-updated')]
    #[On('client-created')]
    #[On('client-updated')]
    #[On('echo:purchases,PurchaseCreated')]
    #[On('echo:purchases,PurchaseCancelled')]
    #[On('echo:sales,SaleCreated')]
    #[On('echo:sales,SaleRejected')]
    #[On('echo:orders,OrderStatusUpdated')]
    #[On('echo:inventory,InventoryUpdated')]
    #[On('echo:clients,ClientCreated')]
    #[On('echo:clients,ClientUpdated')]
    public function refreshDashboard()
    {
        // El re-render automático recalculará todas las estadísticas en tiempo real
    }

    public function render()
    {
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

        return view('livewire.admin-dashboard', [
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
        ]);
    }
}
