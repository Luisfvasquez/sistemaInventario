@extends('admin.layouts.app')

@section('title', 'Cliente: ' . $client->name)

@section('content')
    <div x-data="clientShowPage()" class="space-y-6">

        {{-- Perfil del Cliente --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-md shadow-blue-100">
                        {{ strtoupper(substr($client->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $client->name }}</h1>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-gray-500">
                            <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-semibold">C.I. / RIF: {{ $client->identification }}</span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $client->phone ?? 'Sin teléfono' }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $client->email ?? 'Sin correo' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver al Listado
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 {{ $client->accountsReceivable->isEmpty() ? '' : 'lg:grid-cols-3' }} gap-6">
            
            @if(!$client->accountsReceivable->isEmpty())
                {{-- Panel Izquierdo: Resumen Financiero --}}
                <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Resumen de Cuentas
                    </h2>
                    
                    @php
                        $pendingSum = $client->accountsReceivable->sum('pending_amount');
                        $totalSum = $pendingSum > 0 ? $client->accountsReceivable->sum('total_amount') : 0;
                        $paidSum = $pendingSum > 0 ? $client->accountsReceivable->sum('paid_amount') : 0;
                    @endphp

                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-blue-500 uppercase tracking-wider">Total Deudas</span>
                                <span class="text-xl font-extrabold text-blue-900 font-mono">{{ number_format($totalSum, 2, ',', '.') }}</span>
                            </div>
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-xl border border-green-100 flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-green-500 uppercase tracking-wider">Total Abonado</span>
                                <span class="text-xl font-extrabold text-green-900 font-mono">{{ number_format($paidSum, 2, ',', '.') }}</span>
                            </div>
                            <div class="p-2 bg-green-100 rounded-lg text-green-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-red-500 uppercase tracking-wider">Total Pendiente</span>
                                <span class="text-xl font-extrabold text-red-700 font-mono">{{ number_format($pendingSum, 2, ',', '.') }}</span>
                            </div>
                            <div class="p-2 bg-red-100 rounded-lg text-red-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex justify-between text-sm text-gray-600">
                            <span>Compras Registradas:</span>
                            <span class="font-bold text-gray-800">{{ $client->orders->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Panel Derecho: Listado de Deudas e Historial --}}
            <div class="{{ $client->accountsReceivable->isEmpty() ? 'w-full' : 'lg:col-span-2' }} space-y-6">
                @if(!$client->accountsReceivable->isEmpty())
                    {{-- Deudas (Cuentas por Cobrar) --}}
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Cuentas por Cobrar / Fiados
                        </h2>
                        
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Orden</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Pagado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendiente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($client->accountsReceivable as $account)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-semibold text-gray-700">{{ optional($account->order)->order_number ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right font-mono font-semibold">{{ number_format($account->total_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-right font-mono text-green-600">{{ number_format($account->paid_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-right font-mono text-red-600 font-bold">{{ number_format($account->pending_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-gray-500">{{ optional($account->due_date)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($account->status === 'paid')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-extrabold rounded-full bg-green-100 text-green-800">Pagado</span>
                                                @elseif($account->status === 'partial')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-extrabold rounded-full bg-yellow-100 text-yellow-800">Parcial</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-extrabold rounded-full bg-red-100 text-red-800">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($account->pending_amount > 0)
                                                    <button type="button" 
                                                            @click="openAbono({ id: {{ $account->id }}, orderNumber: '{{ optional($account->order)->order_number ?? $account->id }}', pendingAmount: {{ $account->pending_amount }} })"
                                                            class="inline-flex items-center px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm gap-1 hover:scale-105 transform duration-150">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        Abonar
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400 italic font-medium">Completado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Compras / Pedidos --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Historial de Compras
                    </h2>
                    
                    @if($client->orders->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-sm">No hay compras registradas para este cliente.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Número</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pago</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Verificación</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Detalles</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($client->orders as $order)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-semibold text-blue-600">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="hover:underline flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    {{ $order->order_number ?? $order->id }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono font-semibold text-gray-800">{{ number_format($order->total, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($order->payment_status === 'paid')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Pagado</span>
                                                @elseif($order->payment_status === 'partial')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">Parcial</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($order->verification_status === 'pending')
                                                    <form method="POST" action="{{ route('admin.orders.updateVerification', $order->id) }}" class="inline-block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="verification_status" onchange="this.form.submit()" 
                                                                class="text-xs font-bold rounded-lg border-gray-300 py-1 pl-2 pr-7 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 border-gray-200">
                                                            <option value="pending" selected>Pendiente</option>
                                                            <option value="verified">Verificado</option>
                                                        </select>
                                                    </form>
                                                @elseif($order->verification_status === 'verified')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800">
                                                        Verificado
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">
                                                        Rechazado
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-500">
                                                {{ optional($order->verified_at)->format('Y-m-d') ?? optional($order->created_at)->format('Y-m-d') }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" 
                                                        @click="openOrderDetails({ 
                                                            orderNumber: '{{ $order->order_number ?? $order->id }}', 
                                                            total: {{ $order->total }},
                                                            details: @js($order->details->map(fn($d) => [
                                                                'product_name' => $d->product->name ?? 'Producto',
                                                                'quantity' => $d->quantity,
                                                                'bulk_name' => $d->bulk->name ?? 'Unidad',
                                                                'subtotal' => $d->subtotal
                                                            ])),
                                                            payments: @js($order->payments->map(fn($p) => [
                                                                'method' => $p->paymentMethod->name ?? 'N/A',
                                                                'amount' => $p->amount,
                                                                'reference' => $p->reference ?? '—',
                                                                'date' => optional($p->payment_date)->format('Y-m-d H:i') ?? 'N/A',
                                                                'status' => $p->status ?? 'verified',
                                                                'notes' => $p->notes ?? ''
                                                            ]))
                                                        })"
                                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2.5 py-1 rounded-lg text-xs font-bold transition-colors">
                                                    Ver Pagos
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ===================== MODAL: REGISTRAR ABONO ===================== --}}
        <div x-show="openAbonoModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                
                {{-- Fondo oscuro con transición fade --}}
                <div x-show="openAbonoModal" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75"
                    @click="openAbonoModal = false"></div>

                {{-- Panel con transición scale + fade --}}
                <div x-show="openAbonoModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl w-full sm:max-w-md z-10 border border-gray-100">
                    
                    {{-- Cabecera con Degradado Emerald --}}
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 flex justify-between items-center text-white">
                        <div>
                            <h3 class="text-lg font-bold">Registrar Abono</h3>
                            <p class="text-xs text-emerald-100 mt-0.5">Orden asociada: <span x-text="selectedAccount.orderNumber" class="font-mono font-bold bg-white/20 px-1.5 py-0.5 rounded ml-1"></span></p>
                        </div>
                        <button @click="openAbonoModal = false" class="text-emerald-100 hover:text-white font-bold text-2xl leading-none transition-colors">&times;</button>
                    </div>

                    {{-- Formulario --}}
                    <form method="POST" :action="'/admin/clients/{{ $client->id }}/abonos'">
                        @csrf
                        <input type="hidden" name="account_receivable_id" :value="selectedAccount.id">

                        <div class="p-6 space-y-4">
                            
                            {{-- Info de Deuda Pendiente --}}
                            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 flex justify-between items-center text-sm shadow-inner">
                                <span class="text-emerald-800 font-bold">Saldo Pendiente:</span>
                                <span class="font-mono font-extrabold text-emerald-700 text-lg" x-text="formatCurrency(selectedAccount.pendingAmount)"></span>
                            </div>

                            {{-- Monto del Abono --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Monto del Abono</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-sm">$ / Bs.</span>
                                    </div>
                                    <input type="number" step="0.01" min="0.01" :max="selectedAccount.pendingAmount" 
                                           name="amount" x-model="paymentAmount" required
                                           class="w-full pl-16 rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-bold text-gray-800"
                                           placeholder="0.00">
                                </div>
                                <p class="text-xs text-gray-400 mt-1">El monto no puede superar el saldo pendiente de la deuda.</p>
                            </div>

                            {{-- Método de Pago --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Método de Pago</label>
                                <select name="payment_method_id" x-model="paymentMethodId" required
                                        @change="checkReferenceRequirement()"
                                        class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="" disabled selected>Seleccione método de pago</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" data-requires-reference="{{ $method->requires_reference ? 'true' : 'false' }}">
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Referencia --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                    Referencia de Pago
                                    <span class="text-red-500 font-bold" x-show="requiresReference">*</span>
                                    <span class="text-gray-400 font-normal normal-case" x-show="!requiresReference">(Opcional)</span>
                                </label>
                                <input type="text" name="reference" x-model="reference" :required="requiresReference"
                                       class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                       placeholder="Ej: Código de transferencia, recibo, etc.">
                            </div>

                            {{-- Fecha de Pago --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Fecha de Pago</label>
                                <input type="date" name="payment_date" x-model="paymentDate" required
                                       class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>

                            {{-- Notas --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                    Notas / Observaciones
                                    <span class="text-gray-400 font-normal normal-case">(Opcional)</span>
                                </label>
                                <textarea name="notes" rows="2" x-model="notes"
                                          class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                          placeholder="Ej: Pago parcial, transferencia bancaria..."></textarea>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <button type="submit" x-data="{ enviando: false }" @submit.window="enviando = true"
                                    :disabled="enviando || parseFloat(paymentAmount) <= 0 || parseFloat(paymentAmount) > parseFloat(selectedAccount.pendingAmount)"
                                    :class="enviando ? 'opacity-50 cursor-not-allowed bg-emerald-400' : 'bg-emerald-600 hover:bg-emerald-700'"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 text-white text-sm font-bold rounded-xl transition-colors shadow-md shadow-emerald-100 gap-1.5 hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar Abono
                            </button>
                            <button type="button" @click="openAbonoModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-100 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===================== MODAL: DETALLES DE ORDEN Y PAGOS ===================== --}}
        <div x-show="openOrderDetailsModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                
                {{-- Fondo oscuro con transición fade --}}
                <div x-show="openOrderDetailsModal" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75"
                    @click="openOrderDetailsModal = false"></div>

                {{-- Panel con transición scale + fade --}}
                <div x-show="openOrderDetailsModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl w-full sm:max-w-2xl z-10 border border-gray-100">
                    
                    {{-- Cabecera con Degradado Indigo --}}
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                        <div>
                            <h3 class="text-lg font-bold">Detalle de Compra y Pagos</h3>
                            <p class="text-xs text-indigo-100 mt-0.5">Orden asociada: <span x-text="selectedOrder.orderNumber" class="font-mono font-bold bg-white/20 px-1.5 py-0.5 rounded ml-1"></span></p>
                        </div>
                        <button @click="openOrderDetailsModal = false" class="text-indigo-100 hover:text-white font-bold text-2xl leading-none transition-colors">&times;</button>
                    </div>

                    <div class="p-6 space-y-6">
                        
                        {{-- Productos Comprados --}}
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Detalle de Productos</span>
                            <div class="border border-gray-100 rounded-xl overflow-hidden shadow-inner bg-gray-50/50">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-100 text-gray-600 font-bold">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Producto</th>
                                            <th class="px-4 py-2 text-center">Cant.</th>
                                            <th class="px-4 py-2 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="item in selectedOrder.details" :key="item.product_name + item.subtotal">
                                            <tr class="hover:bg-gray-50/50">
                                                <td class="px-4 py-2.5 font-medium text-gray-800" x-text="item.product_name"></td>
                                                <td class="px-4 py-2.5 text-center text-gray-500 font-bold" x-text="item.quantity + ' (' + item.bulk_name + ')'"></td>
                                                <td class="px-4 py-2.5 text-right font-mono font-bold text-gray-800" x-text="'Bs. ' + parseFloat(item.subtotal).toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-between items-center mt-2 px-1">
                                <span class="text-xs font-bold text-gray-500">Monto Total de Orden:</span>
                                <span class="font-mono font-extrabold text-blue-600" x-text="'Bs. ' + parseFloat(selectedOrder.total).toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </div>

                        {{-- Historial de Pagos --}}
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Historial de Pagos Realizados</span>
                            
                            <template x-if="selectedOrder.payments.length === 0">
                                <div class="bg-gray-50 p-4 rounded-xl text-center text-xs text-gray-400 italic border border-gray-100">
                                    No se han registrado pagos para esta orden todavía.
                                </div>
                            </template>

                            <template x-if="selectedOrder.payments.length > 0">
                                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                    <template x-for="(pay, idx) in selectedOrder.payments" :key="idx">
                                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between hover:border-indigo-100 transition-all duration-200">
                                            <div class="space-y-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="bg-blue-50 text-blue-700 font-extrabold px-2 py-0.5 rounded text-[10px] uppercase tracking-wider" x-text="pay.method"></span>
                                                    <span class="text-xs text-gray-400 font-mono" x-text="pay.date"></span>
                                                </div>
                                                <p class="text-xs text-gray-600 font-medium">
                                                    <strong>Referencia:</strong> <span class="font-mono text-gray-800" x-text="pay.reference"></span>
                                                </p>
                                                <template x-if="pay.notes">
                                                    <p class="text-[11px] text-gray-400 italic" x-text="pay.notes"></p>
                                                </template>
                                            </div>
                                            <div class="mt-2 md:mt-0 text-right">
                                                <span class="text-xs font-semibold block text-gray-400">Abonado</span>
                                                <span class="font-mono font-extrabold text-green-600 text-sm" x-text="'Bs. ' + parseFloat(pay.amount).toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100 rounded-b-2xl">
                        <button type="button" @click="openOrderDetailsModal = false"
                                class="bg-gray-800 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors text-sm shadow-md">
                            Cerrar Detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function clientShowPage() {
            return {
                openAbonoModal: false,
                selectedAccount: {
                    id: null,
                    orderNumber: '',
                    pendingAmount: 0.00,
                },
                paymentAmount: '',
                paymentMethodId: '',
                reference: '',
                paymentDate: '{{ date("Y-m-d") }}',
                notes: '',
                requiresReference: false,

                openOrderDetailsModal: false,
                selectedOrder: {
                    orderNumber: '',
                    total: 0.00,
                    details: [],
                    payments: [],
                },

                openAbono(account) {
                    this.selectedAccount = account;
                    this.paymentAmount = account.pendingAmount;
                    this.paymentMethodId = '';
                    this.reference = '';
                    this.paymentDate = '{{ date("Y-m-d") }}';
                    this.notes = '';
                    this.requiresReference = false;
                    this.openAbonoModal = true;
                },

                openOrderDetails(order) {
                    this.selectedOrder = order;
                    this.openOrderDetailsModal = true;
                },

                checkReferenceRequirement() {
                    this.$nextTick(() => {
                        const select = this.$el.querySelector('select[name="payment_method_id"]');
                        if (select) {
                            const selectedOption = select.options[select.selectedIndex];
                            if (selectedOption) {
                                const requiresRef = selectedOption.getAttribute('data-requires-reference') === 'true';
                                const is6or7 = ['6', '7'].includes(this.paymentMethodId.toString());
                                const text = selectedOption.text.toLowerCase();
                                const isNameMatch = text.includes('transferencia') || text.includes('pago móvil') || text.includes('pago movil');
                                
                                this.requiresReference = requiresRef || is6or7 || isNameMatch;
                            }
                        }
                    });
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('es-VE', {
                        style: 'currency',
                        currency: 'USD',
                        minimumFractionDigits: 2
                    }).format(value).replace('US$', '$');
                }
            }
        }
    </script>
@endsection