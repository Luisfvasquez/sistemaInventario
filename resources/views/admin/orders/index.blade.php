@extends('admin.layouts.app')

@section('title', 'Gestión de Órdenes')

@section('content')
    <div>
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Órdenes de Venta</h1>
            <a href="{{ route('admin.orders.create') }}"
                class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-transform hover:-translate-y-1">
                + Nueva Venta
            </a>
        </div>

        {{-- Tabla de Órdenes --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Orden</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Pago</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total (Bs.)</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr x-data="{ openModal: false }" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-indigo-600">
                                    {{ $order->order_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $order->client->name ?? 'Invitado' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $order->client->identification ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{-- Lógica de colores según estado --}}
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-bold
                                        {{ $order->status == 'completed'
                                            ? 'bg-green-100 text-green-700'
                                            : ($order->status == 'pending'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-bold
                                        {{ $order->payment_status == 'paid'
                                            ? 'bg-blue-100 text-blue-700'
                                            : ($order->payment_status == 'partial'
                                                ? 'bg-orange-100 text-orange-700'
                                                : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900">
                                    Bs. {{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openModal = true"
                                        class="text-indigo-600 hover:text-indigo-900 font-bold">Detalle</button>
                                </td>

                                {{-- Modal de Detalle (Teleport para evitar problemas de z-index) --}}
                                <template x-teleport="body">
                                    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto"
                                        style="display: none;">
                                        <div class="flex items-center justify-center min-h-screen px-4">
                                            <div @click="openModal = false" class="fixed inset-0 bg-black opacity-50"></div>
                                            <div class="bg-white rounded-2xl p-8 max-w-2xl w-full shadow-2xl relative z-10">
                                                <h2 class="text-2xl font-black mb-4">Detalle Orden
                                                    {{ $order->order_number }}</h2>

                                                <div class="border-t border-b py-4 my-4 space-y-2">
                                                    @foreach ($order->details as $detail)
                                                        <div class="flex justify-between">
                                                            <span>{{ $detail->quantity }}x {{ $detail->product->name }}
                                                                ({{ $detail->bulk->name }})</span>
                                                            <span class="font-bold">Bs.
                                                                {{ number_format($detail->subtotal, 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="flex justify-between text-xl font-black">
                                                    <span>Total:</span>
                                                    <span>Bs. {{ number_format($order->total, 2) }}</span>
                                                </div>

                                                <button @click="openModal = false"
                                                    class="mt-6 w-full bg-gray-800 text-white py-3 rounded-xl font-bold">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">No hay órdenes
                                    registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
