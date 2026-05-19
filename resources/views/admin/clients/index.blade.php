@extends('admin.layouts.app')

@section('title', 'Listado de Clientes')

@section('content')
    <div>
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Gestión de Clientes
            </h1>
            <a href="{{ route('admin.clients.create') }}"
                class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Cliente
            </a>
        </div>

        {{-- Panel de Métricas Rápidas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Total Clientes</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $clients->count() }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Con Acceso Web</h2>
                <p class="text-2xl font-bold text-purple-600">{{ $clients->whereNotNull('user_id')->count() }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
                <h2 class="text-gray-500 text-sm font-medium uppercase">Clientes Activos</h2>
                <p class="text-2xl font-bold text-green-600">{{ $clients->where('is_active', true)->count() }}</p>
            </div>
        </div>

        {{-- Tabla de Clientes --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente / Identificación</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contacto</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acceso Web</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($clients as $client)
                            <tr x-data="{ openDetails: false }" class="hover:bg-gray-50 transition-colors">
                                {{-- Nombre e Identificación --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $client->name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">C.I. / RIF: {{ $client->identification }}
                                    </div>
                                </td>

                                {{-- Teléfono y Correo --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <svg class="w-3.5 h-3.5 text-gray-400 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ $client->phone }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $client->email ?? 'Sin correo registrado' }}
                                    </div>
                                </td>

                                {{-- Badge de Acceso Web --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($client->user_id)
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-purple-100 text-purple-800 items-center justify-center">
                                            <span
                                                class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-1.5 animate-pulse"></span>
                                            Habilitado
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full bg-gray-100 text-gray-400">
                                            Solo Facturación
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado del Cliente --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($client->is_active)
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">
                                    <button @click="openDetails = true"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md transition-colors">
                                        Ficha
                                    </button>
                                    <a href="#"
                                        class="text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1 rounded-md transition-colors inline-block">
                                        Editar
                                    </a>
                                </td>

                                {{-- MODAL DE FICHA COMPLETA DEL CLIENTE --}}
                                <template x-teleport="body">
                                    <div x-show="openDetails" style="display: none;"
                                        class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                        <div
                                            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                                            <div x-show="openDetails" x-transition.opacity
                                                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                                                @click="openDetails = false"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                                aria-hidden="true">&#8203;</span>

                                            <div x-show="openDetails" x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                                                <div
                                                    class="bg-white p-6 border-b border-gray-100 flex justify-between items-center">
                                                    <h3 class="text-xl font-bold text-gray-900">Expediente del Cliente</h3>
                                                    <button @click="openDetails = false"
                                                        class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
                                                </div>

                                                <div class="p-6 space-y-4 text-sm">
                                                    <div>
                                                        <span
                                                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre
                                                            o Razón Social</span>
                                                        <span
                                                            class="text-base font-bold text-gray-800">{{ $client->name }}</span>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <span
                                                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Identificación
                                                                Fiscal</span>
                                                            <span
                                                                class="font-mono font-semibold text-gray-800">{{ $client->identification }}</span>
                                                        </div>
                                                        <div>
                                                            <span
                                                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Teléfono</span>
                                                            <span
                                                                class="font-semibold text-gray-800">{{ $client->phone }}</span>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Dirección
                                                            Fiscal de Entrega</span>
                                                        <p
                                                            class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 mt-1 whitespace-pre-line">
                                                            {{ $client->address }}</p>
                                                    </div>

                                                    <div class="pt-3 border-t border-gray-100">
                                                        <span
                                                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Credenciales
                                                            Digitales</span>
                                                        @if ($client->user_id)
                                                            <div
                                                                class="bg-purple-50 p-3 rounded-lg border border-purple-100 text-purple-950 space-y-1">
                                                                <p><strong>Cuenta Web:</strong> Activa</p>
                                                                <p class="text-xs"><strong>Login ID (Email):</strong>
                                                                    {{ $client->user->email ?? $client->email }}</p>
                                                                <p class="text-xs text-purple-700">El cliente puede
                                                                    ingresar al catálogo para consultar apartados utilizando
                                                                    su cédula como clave inicial.</p>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="bg-gray-50 p-3 rounded-lg text-gray-500 text-xs italic">
                                                                Este cliente no posee credenciales para el catálogo web. Sus
                                                                compras solo se gestionan en mostrador/caja.
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                                                    <button type="button" @click="openDetails = false"
                                                        class="bg-gray-800 text-white font-bold px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                                        Cerrar Ficha
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No hay clientes registrados en el sistema administrativo todavía. <a
                                        href="{{ route('admin.clients.create') }}"
                                        class="text-blue-600 hover:underline font-medium">Registra el primero aquí.</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
