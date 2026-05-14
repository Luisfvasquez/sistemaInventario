@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div>

        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            Dashboard
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-gray-500 text-sm">
                    Productos
                </h2>

                <p class="text-3xl font-bold mt-2">
                    {{ $products->count() }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-gray-500 text-sm">
                    Compras
                </h2>

                <p class="text-3xl font-bold mt-2">
                    {{ $purchases->count() }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-gray-500 text-sm">
                    Pedidos
                </h2>

                <p class="text-3xl font-bold mt-2">
                    {{ $orders->count() }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-gray-500 text-sm">
                    Clientes
                </h2>

                <p class="text-3xl font-bold mt-2">
                    {{ $clients->count() }}
                </p>
            </div>

        </div>

    </div>

@endsection
