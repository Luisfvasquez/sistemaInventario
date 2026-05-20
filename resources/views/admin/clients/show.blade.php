@extends('admin.layouts.app')

@section('title', 'Cliente: ' . $client->name)

@section('content')
    <div class="space-y-6">

        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $client->name }}</h1>
                    <p class="text-sm text-gray-500">C.I. / RIF: {{ $client->identification }}</p>
                    <p class="text-sm text-gray-500">Tel: {{ $client->phone }}</p>
                    <p class="text-sm text-gray-500">Email: {{ $client->email ?? 'Sin correo' }}</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('admin.clients.index') }}" class="text-gray-600 hover:underline">&larr; Volver</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-lg font-bold mb-2">Resumen</h2>
                <p class="text-sm text-gray-600">Total Deudas: <strong>{{ number_format($client->accountsReceivable->sum('total_amount'), 2, ',', '.') }}</strong></p>
                <p class="text-sm text-gray-600">Total Pagado: <strong>{{ number_format($client->accountsReceivable->sum('paid_amount'), 2, ',', '.') }}</strong></p>
                <p class="text-sm text-gray-600">Total Pendiente: <strong class="text-red-600">{{ number_format($client->accountsReceivable->sum('pending_amount'), 2, ',', '.') }}</strong></p>
                <p class="text-sm text-gray-600">Compras Registradas: <strong>{{ $client->orders->count() }}</strong></p>
            </div>

            <div class="md:col-span-2 space-y-6">

                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-lg font-bold mb-4">Deudas (Cuentas por Cobrar)</h2>
                    @if($client->accountsReceivable->isEmpty())
                        <p class="text-sm text-gray-500">No tiene deudas registradas.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pagado</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($client->accountsReceivable as $account)
                                        <tr>
                                            <td class="px-4 py-2">{{ optional($account->order)->order_number ?? '—' }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($account->total_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($account->paid_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-right text-red-600">{{ number_format($account->pending_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2">{{ optional($account->due_date)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ ucfirst($account->status) }}</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td colspan="6" class="px-4 py-3">
                                                <div class="text-sm text-gray-700">
                                                    <strong>Abonos:</strong>
                                                    @if($account->installments->isEmpty())
                                                        <span class="text-gray-500 ml-2">Sin abonos registrados.</span>
                                                    @else
                                                        <ul class="mt-2 space-y-1">
                                                            @foreach($account->installments as $inst)
                                                                <li class="flex justify-between text-sm">
                                                                    <span>#{{ $inst->installment_number }} — Monto: {{ number_format($inst->amount, 2, ',', '.') }}</span>
                                                                    <span class="text-gray-500">Pagado: {{ number_format($inst->paid_amount, 2, ',', '.') }} — {{ optional($inst->paid_at)->format('Y-m-d H:i') ?? 'Pendiente' }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-lg font-bold mb-4">Compras / Pedidos</h2>
                    @if($client->orders->isEmpty())
                        <p class="text-sm text-gray-500">No hay compras registradas.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pago</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Verificación</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($client->orders as $order)
                                        <tr>
                                            <td class="px-4 py-2"><a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-600 hover:underline">{{ $order->order_number ?? $order->id }}</a></td>
                                            <td class="px-4 py-2 text-right">{{ number_format($order->total, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2">{{ ucfirst($order->payment_status) }}</td>
                                            <td class="px-4 py-2">{{ ucfirst($order->verification_status) }}</td>
                                            <td class="px-4 py-2">{{ optional($order->verified_at)->format('Y-m-d') ?? optional($order->created_at)->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
@endsection