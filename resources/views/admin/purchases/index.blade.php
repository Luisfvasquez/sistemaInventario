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

        <livewire:purchase-index />
    </div>
@endsection
