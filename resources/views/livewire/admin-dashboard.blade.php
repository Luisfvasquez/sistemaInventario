<div>
    {{-- Primary KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        {{-- Card: Revenue --}}
        <div
            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-full opacity-80">
            </div>
            <div class="flex items-start justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ventas del Mes</p>
                    <p class="text-2xl font-extrabold text-gray-800 mt-1">Bs {{ number_format($monthlyRevenue, 2) }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        @if ($revenueChange >= 0)
                            <span
                                class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                +{{ $revenueChange }}%
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                                {{ $revenueChange }}%
                            </span>
                        @endif
                        <span class="text-xs text-gray-400">vs mes anterior</span>
                    </div>
                </div>
                <div
                    class="p-3 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl shadow-lg shadow-emerald-200 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Card: Orders --}}
        <div
            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full opacity-80">
            </div>
            <div class="flex items-start justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pedidos del Mes</p>
                    <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $monthlyOrders }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        @if ($ordersChange >= 0)
                            <span
                                class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                +{{ $ordersChange }}%
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                                {{ $ordersChange }}%
                            </span>
                        @endif
                        <span class="text-xs text-gray-400">vs mes anterior</span>
                    </div>
                </div>
                <div
                    class="p-3 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Card: Products --}}
        <div
            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-violet-50 to-transparent rounded-bl-full opacity-80">
            </div>
            <div class="flex items-start justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Productos</p>
                    <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalProducts }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        @if ($lowStockCount > 0)
                            <span
                                class="inline-flex items-center text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                {{ $lowStockCount }} bajo stock
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                ✓ Stock OK
                            </span>
                        @endif
                    </div>
                </div>
                <div
                    class="p-3 bg-gradient-to-br from-violet-400 to-violet-600 rounded-xl shadow-lg shadow-violet-200 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Card: Clients --}}
        <div
            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 relative overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-rose-50 to-transparent rounded-bl-full opacity-80">
            </div>
            <div class="flex items-start justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Clientes</p>
                    <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalClients }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="text-xs text-gray-400">{{ $totalSuppliers }} proveedores registrados</span>
                    </div>
                </div>
                <div
                    class="p-3 bg-gradient-to-br from-rose-400 to-rose-600 rounded-xl shadow-lg shadow-rose-200 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Panels Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        {{-- Panel: Recent Orders --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Últimos Pedidos</h3>
                </div>
                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">{{ $totalOrders }} total</span>
            </div>
            @if ($recentOrders->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach ($recentOrders as $order)
                        <div wire:key="order-{{ $order->id }}" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center flex-shrink-0">
                                    <span
                                        class="text-xs font-bold text-white">{{ substr($order->client->name ?? ($order->client_name ?? 'N'), 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $order->order_number ?? '#' . $order->id }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $order->client->name ?? ($order->client_name ?? 'Cliente') }} ·
                                        {{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-800">Bs {{ number_format($order->total, 2) }}</p>
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'completada' => 'bg-emerald-100 text-emerald-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'pendiente' => 'bg-amber-100 text-amber-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'cancelada' => 'bg-red-100 text-red-700',
                                        'processing' => 'bg-blue-100 text-blue-700',
                                        'en_proceso' => 'bg-blue-100 text-blue-700',
                                    ];
                                    $color =
                                        $statusColors[strtolower($order->status ?? '')] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span
                                    class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $color }}">{{ $order->status ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center">
                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-sm text-gray-400">No hay pedidos registrados aún</p>
                </div>
            @endif
        </div>

        {{-- Panel: Quick Stats Sidebar --}}
        <div class="space-y-5">

            {{-- Mini Panel: Accounts Receivable --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-orange-50 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2 2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Cuentas por Cobrar</h3>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-2xl font-extrabold text-gray-800">Bs {{ number_format($pendingReceivables, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $totalReceivablesCount }}
                            {{ $totalReceivablesCount == 1 ? 'cuenta pendiente' : 'cuentas pendientes' }}</p>
                    </div>
                    @if ($totalReceivablesCount > 0)
                        <div class="p-2 bg-orange-100 rounded-full animate-pulse">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mini Panel: Order Status Breakdown --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-50 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Estado de Pedidos</h3>
                </div>
                @if (count($ordersByStatus) > 0)
                    <div class="space-y-3">
                        @php
                            $statusLabels = [
                                'pending' => ['Pendientes', 'bg-amber-400'],
                                'pendiente' => ['Pendientes', 'bg-amber-400'],
                                'processing' => ['En Proceso', 'bg-blue-400'],
                                'en_proceso' => ['En Proceso', 'bg-blue-400'],
                                'completed' => ['Completados', 'bg-emerald-400'],
                                'completada' => ['Completados', 'bg-emerald-400'],
                                'cancelled' => ['Cancelados', 'bg-red-400'],
                                'cancelada' => ['Cancelados', 'bg-red-400'],
                                'delivered' => ['Entregados', 'bg-teal-400'],
                                'entregada' => ['Entregados', 'bg-teal-400'],
                            ];
                            $totalStatusOrders = array_sum($ordersByStatus);
                        @endphp
                        @foreach ($ordersByStatus as $status => $count)
                            @php
                                $label = $statusLabels[strtolower($status)][0] ?? ucfirst($status);
                                $barColor = $statusLabels[strtolower($status)][1] ?? 'bg-gray-400';
                                $percentage = $totalStatusOrders > 0 ? round(($count / $totalStatusOrders) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-600">{{ $label }}</span>
                                    <span class="font-bold text-gray-800">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all duration-700"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">Sin pedidos registrados</p>
                @endif
            </div>

            {{-- Mini Panel: Monthly Purchases --}}
            <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-2xl shadow-sm p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-white bg-opacity-15 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold uppercase tracking-wider opacity-90">Compras del Mes</h3>
                </div>
                <p class="text-2xl font-extrabold">Bs {{ number_format($monthlyPurchases, 2) }}</p>
                <p class="text-xs opacity-60 mt-1">Total invertido en compras este mes</p>
            </div>
        </div>
    </div>

    {{-- Low Stock Alert Panel (only shows if there are low stock items) --}}
    @if ($lowStockCount > 0)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 shadow-sm p-5 mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider">Alerta de Stock Bajo</h3>
                    <p class="text-xs text-amber-600">{{ $lowStockCount }}
                        {{ $lowStockCount == 1 ? 'producto necesita' : 'productos necesitan' }} reposición</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                @foreach ($lowStockProducts as $product)
                    <div wire:key="low-stock-{{ $product->id }}"
                        class="bg-white rounded-xl border border-amber-100 p-3 flex items-center gap-3 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-amber-600 font-bold">Stock:
                                {{ number_format($product->inventory->stock ?? 0, 0) }} / Mín:
                                {{ number_format($product->inventory->minimum_stock ?? 0, 0) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
