<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                {{ __('Bienvenido, ') }} <span class="text-indigo-600">{{ $client->name }}</span>
            </h2>
            <div class="text-sm font-semibold bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full border border-indigo-100 shadow-sm self-start sm:self-auto">
                Cliente Activo
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Banner Principal de Bienvenida -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-900 rounded-3xl shadow-xl overflow-hidden relative border border-slate-800 transition-all duration-500 hover:shadow-2xl">
                <div class="px-8 py-10 text-white relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="bg-indigo-500/20 text-indigo-300 font-bold text-xs uppercase tracking-widest px-3 py-1 rounded-full border border-indigo-400/20 mb-3 inline-block">
                            Panel de Control
                        </span>
                        <h3 class="text-3xl font-extrabold mb-2 tracking-tight">¡Hola, {{ Auth::user()->name }}!</h3>
                        <p class="text-slate-300 text-lg max-w-xl">
                            Desde aquí puedes realizar tus pedidos, llevar el control de tus deudas, consultar tus abonos cargados y actualizar tus datos personales.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('client.products') }}" class="bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-500 hover:to-teal-600 text-slate-955 font-extrabold py-3.5 px-8 rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Comenzar Compra
                        </a>
                    </div>
                </div>
                <!-- Decoración de fondo premium -->
                <div class="absolute top-0 right-0 -mr-24 -mt-24 w-80 h-80 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-24 -mb-24 w-64 h-64 rounded-full bg-emerald-500 opacity-10 blur-3xl"></div>
            </div>

            <!-- Grid de Estadísticas Reales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Compras Realizadas -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center justify-between transition-all duration-300 hover:shadow-md">
                    <div class="space-y-2">
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-400">Total Compras</p>
                        <h4 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $totalOrders }}</h4>
                        <p class="text-xs text-slate-500">Pedidos registrados históricamente</p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center border border-indigo-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                </div>

                <!-- Facturas por Pagar -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center justify-between transition-all duration-300 hover:shadow-md">
                    <div class="space-y-2">
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-400">Facturas Pendientes</p>
                        <h4 class="text-4xl font-extrabold text-amber-600 tracking-tight">{{ $pendingOrders }}</h4>
                        <p class="text-xs text-slate-500">Facturas pendientes por cancelar</p>
                    </div>
                    <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center border border-amber-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Saldo Pendiente -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center justify-between transition-all duration-300 hover:shadow-md">
                    <div class="space-y-2">
                        <p class="text-sm font-bold uppercase tracking-wider text-slate-400">Saldo Adeudado</p>
                        <h4 class="text-3.5xl font-extrabold text-rose-600 tracking-tight">
                            {{ number_format($totalDebt, 2, ',', '.') }} BS
                        </h4>
                        @if($exchangeRate && $exchangeRate > 0)
                            <p class="text-xs text-indigo-600 font-bold">Ref. ${{ number_format($totalDebt / $exchangeRate, 2, ',', '.') }}</p>
                        @endif
                        <p class="text-xs text-slate-500">Monto total por amortizar</p>
                    </div>
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center border border-rose-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos Interactivos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Tienda -->
                <a href="{{ route('client.products') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-6 border border-slate-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white rounded-xl flex items-center justify-center mb-4 transition-all duration-300">
                        <svg class="w-6 h-6 text-indigo-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Comprar / Catálogo</h4>
                    <p class="text-slate-500 mb-4 text-xs">Explora y añade productos al carrito para realizar tu orden de compra.</p>
                    <span class="text-indigo-600 font-bold group-hover:text-indigo-800 inline-flex items-center text-xs transition-colors">
                        Ver catálogo <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Mis Compras -->
                <a href="{{ route('client.purchases') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-6 border border-slate-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white rounded-xl flex items-center justify-center mb-4 transition-all duration-300">
                        <svg class="w-6 h-6 text-emerald-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Mis Compras</h4>
                    <p class="text-slate-500 mb-4 text-xs">Revisa tus pedidos, verifica los estados de entrega y detalles técnicos.</p>
                    <span class="text-emerald-600 font-bold group-hover:text-emerald-800 inline-flex items-center text-xs transition-colors">
                        Historial compras <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Mis Facturas -->
                <a href="{{ route('client.invoices') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-6 border border-slate-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-amber-50 group-hover:bg-amber-600 group-hover:text-white rounded-xl flex items-center justify-center mb-4 transition-all duration-300">
                        <svg class="w-6 h-6 text-amber-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Facturas / Abonos</h4>
                    <p class="text-slate-500 mb-4 text-xs">Controla tus balances de deudas, cuotas vencidas e historial de abonos realizados.</p>
                    <span class="text-amber-600 font-bold group-hover:text-amber-800 inline-flex items-center text-xs transition-colors">
                        Ver estados de cuenta <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Mi Perfil -->
                <a href="{{ route('client.profile') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-6 border border-slate-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-slate-100 group-hover:bg-slate-700 group-hover:text-white rounded-xl flex items-center justify-center mb-4 transition-all duration-300">
                        <svg class="w-6 h-6 text-slate-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Mi Perfil</h4>
                    <p class="text-slate-500 mb-4 text-xs">Mantén al día tus datos personales, teléfonos de contacto y dirección de entrega.</p>
                    <span class="text-slate-600 font-bold group-hover:text-slate-800 inline-flex items-center text-xs transition-colors">
                        Configurar cuenta <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>
            </div>

            <!-- Tabla de Órdenes Recientes -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h4 class="text-lg font-extrabold text-slate-800">Últimos Pedidos Cargados</h4>
                    <a href="{{ route('client.purchases') }}" class="text-indigo-600 text-xs font-bold hover:text-indigo-800 transition-colors">
                        Ver todas mis compras &rarr;
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase text-xxs font-bold tracking-wider">
                                <th class="py-4 px-6">Código de Orden</th>
                                <th class="py-4 px-6 hidden sm:table-cell">Fecha</th>
                                <th class="py-4 px-6 hidden md:table-cell">Dirección</th>
                                <th class="py-4 px-6 text-right">Monto Total</th>
                                <th class="py-4 px-6 text-center">Entrega</th>
                                <th class="py-4 px-6 text-center">Pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-slate-700">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 hidden sm:table-cell">
                                        {{ $order->created_at->format('d/m/Y h:i A') }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 truncate max-w-xs hidden md:table-cell">
                                        {{ $order->delivery_address }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <span class="font-extrabold text-slate-800 block">{{ number_format($order->total, 2, ',', '.') }} BS</span>
                                        @if($order->exchange_rate && $order->exchange_rate > 0)
                                            <span class="text-indigo-600 font-bold text-xxs block">${{ number_format($order->total / $order->exchange_rate, 2, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($order->status === 'delivered')
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                                Entregado
                                            </span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                En Proceso
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($order->payment_status === 'paid')
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                                Pagado
                                            </span>
                                        @elseif($order->payment_status === 'partial')
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Abonado
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        No has realizado compras todavía. ¡Haz tu primer pedido hoy!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
