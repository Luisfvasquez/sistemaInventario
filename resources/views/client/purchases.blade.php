<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                {{ __('Mis Compras') }}
            </h2>
            <p class="text-xs text-slate-500 mt-1">Lleva el rastreo de tus órdenes, estados de entrega y pagos registrados en el sistema.</p>
        </div>
    </x-slot>

    <div x-data="purchasesManager()" class="py-8 bg-slate-50 min-h-screen">
        
        <!-- Mensajes de éxito -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
            @if(Str::contains(session('success'), 'Orden de compra') || Str::contains(session('success'), 'ORD-'))
                <script>
                    localStorage.removeItem('client_shopping_cart');
                </script>
            @endif
        @endif

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Barra de Filtros -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="font-extrabold text-slate-800 text-base">Filtro de Órdenes</h3>
                
                <div class="flex flex-wrap gap-2 justify-center overflow-x-auto py-1">
                    <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200">
                        Todas
                    </button>
                    <button @click="statusFilter = 'pending'" :class="statusFilter === 'pending' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200">
                        En Proceso / Pendientes
                    </button>
                    <button @click="statusFilter = 'delivered'" :class="statusFilter === 'verified' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200">
                        Entregadas
                    </button>
                </div>
            </div>

            <!-- Listado de Compras -->
            <div class="space-y-4">
                @forelse($orders as $order)
                    <!-- Tarjeta de Compra / Orden individual -->
                    <div x-show="filterOrder(@js($order))" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-all duration-300 hover:shadow-md" x-data="{ expanded: false }">
                        
                        <!-- Cabecera de la orden -->
                        <div @click="expanded = !expanded" class="p-6 cursor-pointer hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <div :class="expanded ? 'rotate-180' : 'rotate-0'" class="flex w-8 h-8 bg-slate-50 hover:bg-slate-100 rounded-xl items-center justify-center text-slate-500 transition-transform duration-200 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <span class="font-mono font-bold text-slate-800 text-sm sm:text-base">{{ $order->order_number }}</span>
                                        <span class="text-xxs text-slate-400 font-semibold">{{ $order->created_at->format('d/m/Y h:i A') }}</span>
                                    </div>
                                    <p class="text-xs text-slate-400 truncate mt-1">
                                        Despacho: {{ $order->delivery_address }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t border-slate-100 sm:border-t-0">
                                <!-- Precios / Montos -->
                                <div class="text-left sm:text-right">
                                    <span class="text-base sm:text-lg font-black text-slate-800 block">{{ number_format($order->total, 2, ',', '.') }} BS</span>
                                    @if($order->exchange_rate && $order->exchange_rate > 0)
                                        <span class="text-indigo-600 font-bold text-xxs sm:text-xs block">
                                            Ref. ${{ number_format($order->total / $order->exchange_rate, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Insignias de Estado -->
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Entrega -->
                                    @if($order->status === 'verified' || $order->status === 'delivered')
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-green-50 text-green-700 border border-green-200">
                                            Entregado
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            En Proceso
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif

                                    <!-- Pagos -->
                                    @if($order->payment_status === 'paid')
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-green-50 text-green-700 border border-green-200">
                                            Pagado
                                        </span>
                                    @elseif($order->payment_status === 'partial')
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Abonado
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            Sin Pagar
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Sección Detallada (Desplegable) -->
                        <div x-cloak x-show="expanded" x-collapse class="border-t border-slate-100 bg-slate-50/30 p-6 space-y-6">
                            
                            <!-- Tabla de Detalles de Productos -->
                            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-400 uppercase text-xxs font-bold tracking-wider border-b border-slate-100">
                                            <th class="py-3 px-6">Producto</th>
                                            <th class="py-3 px-6 text-center">Cantidad</th>
                                            <th class="py-3 px-4 text-right hidden sm:table-cell">Precio Unitario</th>
                                            <th class="py-3 px-6 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @foreach($order->details as $detail)
                                            <tr class="hover:bg-slate-50/20">
                                                <td class="py-3 px-6">
                                                    <span class="font-bold text-slate-800">{{ $detail->product->name }}</span>
                                                    @if($detail->product->brand)
                                                        <span class="text-xxs bg-slate-100 text-slate-500 font-bold px-1.5 py-0.5 rounded uppercase ml-1">
                                                            {{ $detail->product->brand }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-6 text-center font-bold text-slate-600">
                                                    {{ $detail->product->unit_type === 'gram' ? number_format($detail->quantity, 3, ',', '.') : number_format($detail->quantity, 0) }} 
                                                    {{ $detail->product->unit_type === 'gram' ? 'Kg' : 'Und' }}
                                                </td>
                                                <td class="py-3 px-4 text-right text-slate-500 font-mono hidden sm:table-cell">
                                                    {{ number_format($detail->unit_price, 2, ',', '.') }} BS
                                                </td>
                                                <td class="py-3 px-6 text-right font-extrabold text-slate-700 font-mono">
                                                    {{ number_format($detail->subtotal, 2, ',', '.') }} BS
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bloque de Notas e Info General -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Datos de despacho y notas -->
                                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4">
                                    <div>
                                        <span class="block text-slate-400 font-bold text-xxs uppercase tracking-wider">Dirección de Despacho</span>
                                        <p class="text-slate-700 text-sm font-semibold mt-1">
                                            {{ $order->delivery_address }}
                                        </p>
                                    </div>
                                    @if($order->notes)
                                        <div>
                                            <span class="block text-slate-400 font-bold text-xxs uppercase tracking-wider">Observaciones</span>
                                            <p class="text-slate-600 text-sm italic mt-1">
                                                "{{ $order->notes }}"
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Resumen Financiero Completo -->
                                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-3 text-sm">
                                    <span class="block text-slate-400 font-bold text-xxs uppercase tracking-wider border-b border-slate-50 pb-2 mb-2">Resumen Financiero</span>
                                    
                                    <div class="flex justify-between items-center text-slate-500">
                                        <span>Subtotal</span>
                                        <span class="font-bold text-slate-700">{{ number_format($order->subtotal, 2, ',', '.') }} BS</span>
                                    </div>
                                    
                                    @if($order->discount > 0)
                                        <div class="flex justify-between items-center text-rose-500 font-semibold">
                                            <span>Descuento</span>
                                            <span>-{{ number_format($order->discount, 2, ',', '.') }} BS</span>
                                        </div>
                                    @endif

                                    @if($order->tax > 0)
                                        <div class="flex justify-between items-center text-slate-500">
                                            <span>Impuesto</span>
                                            <span>+{{ number_format($order->tax, 2, ',', '.') }} BS</span>
                                        </div>
                                    @endif

                                    <hr class="border-slate-100" />

                                    <div class="flex justify-between items-end">
                                        <span class="font-extrabold text-slate-800 text-base">Total Pedido</span>
                                        <div class="text-right">
                                            <span class="text-xl font-black text-slate-900">{{ number_format($order->total, 2, ',', '.') }} BS</span>
                                            @if($order->exchange_rate && $order->exchange_rate > 0)
                                                <span class="block text-indigo-700 font-extrabold text-xs">
                                                    Ref. ${{ number_format($order->total / $order->exchange_rate, 2, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-slate-400 border border-slate-100 flex flex-col items-center justify-center gap-3">
                        <svg class="w-16 h-16 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span class="text-base font-bold text-slate-600">No tienes historial de compras.</span>
                        <p class="text-sm">¡Comienza a comprar navegando en el catálogo!</p>
                        <a href="{{ route('client.products') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl mt-2 inline-block transition-colors">
                            Ir al Catálogo
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function purchasesManager() {
            return {
                statusFilter: 'all',

                filterOrder(order) {
                    if (this.statusFilter === 'all') return true;
                    if (this.statusFilter === 'pending') return order.status === 'pending';
                    if (this.statusFilter === 'delivered') return order.status === 'delivered';
                    return true;
                }
            };
        }
    </script>
</x-app-layout>
