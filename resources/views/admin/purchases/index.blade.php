@extends('admin.layouts.app')

@section('title', 'Historial de Compras')

@section('content')
    <div>
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Historial de Compras
            </h1>
            <a href="{{ route('admin.purchases.create') }}"
                class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Compra
            </a>
        </div>

        {{-- Panel de Métricas / Resumen Financiero Histórico --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Total Facturas Procesadas</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $purchases->count() }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-indigo-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Inversión Total (Bolívares)</h2>
                <p class="text-2xl font-bold text-indigo-600">Bs. {{ number_format($purchases->sum('total'), 2) }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Inversión Total Equivalente</h2>
                <p class="text-2xl font-bold text-green-600">
                    $ {{ number_format($purchases->sum(fn($p) => $p->total / $p->exchange_rate), 2) }}
                </p>
            </div>
        </div>

        {{-- Tabla de Registro Histórico --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código / Factura</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Proveedor</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha
                                de Compra</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tasa Aplicada</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto Total (Bs.)</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto Total (USD)</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($purchases as $purchase)
                            {{-- Fila con estado local Alpine para controlar su propio modal de detalles --}}
                            <tr x-data="{ openDetail: false }" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    #{{ $purchase->purchase_code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $purchase->supplier->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">RIF: {{ $purchase->supplier->rif ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $purchase->purchased_at ? $purchase->purchased_at->format('d/m/Y') : $purchase->created_at->format('d/m/Y') }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-700 bg-gray-50">
                                    Bs. {{ number_format($purchase->exchange_rate, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                    Bs. {{ number_format($purchase->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                                    ${{ number_format($purchase->total / $purchase->exchange_rate, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($purchase->status === 'completed')
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Procesado</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">{{ $purchase->status }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openDetail = true"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md transition-colors">
                                        Ver Detalles
                                    </button>

                                    {{-- MODAL DE INSPECCIÓN DETALLADA DE LA COMPRA --}}
                                    <template x-teleport="body">
                                        <div x-show="openDetail" style="display: none;"
                                            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                            <div
                                                class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                                                {{-- Capa de desenfoque trasera --}}
                                                <div x-show="openDetail" x-transition.opacity
                                                    class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                                                    @click="openDetail = false"></div>
                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                                    aria-hidden="true">&#8203;</span>

                                                {{-- Cuerpo del Modal --}}
                                                <div x-show="openDetail" x-transition:enter="ease-out duration-300"
                                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                    x-transition:leave="ease-in duration-200"
                                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                                                    <div
                                                        class="bg-white p-6 border-b border-gray-100 flex justify-between items-center">
                                                        <div>
                                                            <h3 class="text-xl font-bold text-gray-900">Resumen Documental
                                                                de Compra</h3>
                                                            <p class="text-xs text-gray-500 mt-1">Registrado por:
                                                                {{ $purchase->user->name ?? 'Sistema' }}</p>
                                                        </div>
                                                        <span
                                                            class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-md font-mono font-bold">Factura:
                                                            #{{ $purchase->purchase_code }}</span>
                                                    </div>

                                                    <div class="p-6 space-y-6">
                                                        {{-- Grid Informativo Superior --}}
                                                        <div
                                                            class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border">
                                                            <div>
                                                                <span
                                                                    class="block text-xs font-bold text-gray-400 uppercase">Proveedor</span>
                                                                <span
                                                                    class="text-sm font-semibold text-gray-800">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="block text-xs font-bold text-gray-400 uppercase">Fecha
                                                                    Fiscal</span>
                                                                <span
                                                                    class="text-sm font-semibold text-gray-800">{{ $purchase->purchased_at ? $purchase->purchased_at->format('d/m/Y') : $purchase->created_at->format('d/m/Y') }}</span>
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="block text-xs font-bold text-gray-400 uppercase">Tasa
                                                                    de Cambio Congelada</span>
                                                                <span class="text-sm font-bold text-indigo-600">Bs.
                                                                    {{ number_format($purchase->exchange_rate, 4) }}</span>
                                                            </div>
                                                        </div>

                                                        {{-- Tabla de Ítems Comprados --}}
                                                        <div>
                                                            <h4 class="text-sm font-bold text-gray-700 mb-2">Artículos
                                                                Ingresados al Inventario</h4>
                                                            <div class="border rounded-lg overflow-hidden bg-white">
                                                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                                    <thead class="bg-gray-50 font-medium text-gray-500">
                                                                        <tr>
                                                                            <th class="px-4 py-2 text-left">Producto</th>
                                                                            <th class="px-4 py-2 text-left">Presentación
                                                                            </th>
                                                                            <th class="px-4 py-2 text-center">Cant. Comprada
                                                                            </th>
                                                                            <th class="px-4 py-2 class text-center">Equiv.
                                                                                Unidades Base</th>
                                                                            <th class="px-4 py-2 text-right">Costo Unit.
                                                                                (Bs)
                                                                            </th>
                                                                            <th class="px-4 py-2 text-right">Costo Unit.
                                                                                (USD)</th>
                                                                            <th class="px-4 py-2 text-right">Subtotal (Bs)
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-gray-200 text-gray-700">
                                                                        @foreach ($purchase->details as $detail)
                                                                            <tr>
                                                                                <td
                                                                                    class="px-4 py-2 font-medium text-gray-900">
                                                                                    {{ $detail->product->name ?? 'N/A' }}
                                                                                </td>
                                                                                <td class="px-4 py-2">
                                                                                    <span
                                                                                        class="text-xs bg-gray-100 px-2 py-0.5 rounded font-semibold text-gray-600">
                                                                                        {{ $detail->bulk->name ?? 'Unidad' }}
                                                                                    </span>
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-center font-bold">
                                                                                    {{ number_format($detail->quantity, 2) }}
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-center text-gray-500">
                                                                                    {{ number_format($detail->base_quantity, 2) }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-right">Bs.
                                                                                    {{ number_format($detail->unit_cost, 2) }}
                                                                                </td>
                                                                                {{-- Consumo del Accessor unit_cost_usd del modelo PurchaseDetail --}}
                                                                                <td
                                                                                    class="px-4 py-2 text-right text-green-600 font-semibold">
                                                                                    ${{ number_format($detail->unit_cost_usd, 2) }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-right font-bold">
                                                                                    Bs.
                                                                                    {{ number_format($detail->subtotal, 2) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        {{-- Notas adicionales si existen --}}
                                                        @if ($purchase->notes)
                                                            <div
                                                                class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-xs text-yellow-800">
                                                                <strong>Notas del Registro:</strong> {{ $purchase->notes }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Pie del Modal con Cierre Financiero --}}
                                                    <div
                                                        class="bg-gray-900 px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                                        <div class="text-white">
                                                            <span
                                                                class="text-xs text-gray-400 block uppercase font-bold">Total
                                                                General Compra</span>
                                                            <span class="text-2xl font-black">Bs.
                                                                {{ number_format($purchase->total, 2) }}</span>
                                                            <span
                                                                class="text-green-400 font-bold ml-2">(${{ number_format($purchase->total / $purchase->exchange_rate, 2) }}
                                                                USD)</span>
                                                        </div>
                                                        <button type="button" @click="openDetail = false"
                                                            class="w-full sm:w-auto bg-white text-gray-900 font-bold px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors text-center shadow">
                                                            Cerrar Ventana
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    No se han registrado compras a proveedores todavía. <a
                                        href="{{ route('admin.purchases.create') }}"
                                        class="text-blue-600 hover:underline font-medium">Registra el primer ingreso.</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
