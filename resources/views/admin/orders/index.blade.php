@extends('admin.layouts.app')

@section('title', 'Gestión de Órdenes')

@section('content')
    @php
        $statusTranslations = [
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'ready_for_pickup' => 'Listo para retirar',
            'completed' => 'Completada',
            'delivered' => 'Entregada',
            'cancelled' => 'Cancelada',
        ];

        $paymentTranslations = [
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
            'rejected' => 'Rechazado',
        ];
    @endphp

    <div>
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Órdenes de Venta</h1>
            <a href="{{ route('admin.orders.create') }}"
                class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-transform hover:-translate-y-1">
                + Nueva Venta
            </a>
        </div>

        <livewire:order-index />
    </div>
@endsection
