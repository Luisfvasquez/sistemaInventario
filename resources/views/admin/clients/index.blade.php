@extends('admin.layouts.app')

@section('title', 'Listado de Clientes')

@section('content')
    <div x-data="clientsPage()">

        {{-- Flash de éxito --}}
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg flex items-center shadow-sm"
                x-data="{ show: true }" x-show="show" x-transition>
                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-green-800 font-semibold text-sm">{{ session('success') }}</span>
                <button @click="show = false"
                    class="ml-auto text-green-600 hover:text-green-800 font-bold text-lg leading-none">&times;</button>
            </div>
        @endif

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
                            <tr class="hover:bg-gray-50 transition-colors">

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
                                    <a href="{{ route('admin.clients.show', $client->id) }}"
                                        class="text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded-md transition-colors">
                                        Historial
                                    </a>
                                    <button @click="showDetails(@js(['name' => $client->name, 'identification' => $client->identification, 'phone' => $client->phone, 'email' => $client->email ?? '', 'address' => $client->address, 'has_account' => (bool) $client->user_id, 'account_email' => $client->user?->email ?? ($client->email ?? '')]))"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md transition-colors">
                                        Ficha
                                    </button>
                                    <button @click="showEdit(@js(['id' => $client->id, 'name' => $client->name, 'identification' => $client->identification, 'phone' => $client->phone, 'email' => $client->email ?? '', 'address' => $client->address, 'is_active' => (bool) $client->is_active]))"
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md transition-colors">
                                        Editar
                                    </button>
                                </td>
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

        {{-- ===================== MODAL: FICHA DEL CLIENTE ===================== --}}
        {{-- El wrapper exterior solo controla visibilidad (sin transición propia) --}}
        <div x-show="openDetails" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">

                {{-- Fondo oscuro: solo él tiene la transición fade --}}
                <div x-show="openDetails" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75"
                    @click="openDetails = false"></div>

                {{-- Panel: transición scale + fade --}}
                <div x-show="openDetails" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl w-full sm:max-w-lg z-10">

                    <div class="bg-white p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-900">Expediente del Cliente</h3>
                        <button @click="openDetails = false"
                            class="text-gray-400 hover:text-gray-600 font-bold text-2xl leading-none">&times;</button>
                    </div>

                    <div class="p-6 space-y-4 text-sm">
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre o Razón
                                Social</span>
                            <span class="text-base font-bold text-gray-800" x-text="selected.name"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Identificación
                                    Fiscal</span>
                                <span class="font-mono font-semibold text-gray-800"
                                    x-text="selected.identification"></span>
                            </div>
                            <div>
                                <span
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Teléfono</span>
                                <span class="font-semibold text-gray-800" x-text="selected.phone"></span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Dirección
                                Fiscal</span>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 mt-1 whitespace-pre-line"
                                x-text="selected.address"></p>
                        </div>

                        <div class="pt-3 border-t border-gray-100">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Credenciales
                                Digitales</span>
                            <template x-if="selected.has_account">
                                <div
                                    class="bg-purple-50 p-3 rounded-lg border border-purple-100 text-purple-950 space-y-1">
                                    <p><strong>Cuenta Web:</strong> Activa</p>
                                    <p class="text-xs"><strong>Login ID (Email):</strong>
                                        <span x-text="selected.account_email"></span>
                                    </p>
                                    <p class="text-xs text-purple-700">El cliente puede ingresar al catálogo para
                                        consultar apartados utilizando su cédula como clave inicial.</p>
                                </div>
                            </template>
                            <template x-if="!selected.has_account">
                                <div class="bg-gray-50 p-3 rounded-lg text-gray-500 text-xs italic">
                                    Este cliente no posee credenciales para el catálogo web. Sus compras solo se
                                    gestionan en mostrador/caja.
                                </div>
                            </template>
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

        {{-- ===================== MODAL: EDITAR CLIENTE ===================== --}}
        {{-- El wrapper exterior solo controla visibilidad (sin transición propia) --}}
        <div x-show="openEdit" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">

                {{-- Fondo oscuro: solo él tiene la transición fade --}}
                <div x-show="openEdit" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75"
                    @click="openEdit = false"></div>

                {{-- Panel: transición scale + fade --}}
                <div x-show="openEdit" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl w-full sm:max-w-xl z-10">

                    {{-- Cabecera --}}
                    <div
                        class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-white">Editar Cliente</h3>
                            <p class="text-indigo-200 text-xs mt-0.5" x-text="selected.name"></p>
                        </div>
                        <button @click="openEdit = false"
                            class="text-indigo-200 hover:text-white font-bold text-2xl leading-none transition-colors">&times;</button>
                    </div>

                    {{-- Formulario — action dinámico con el ID del cliente seleccionado --}}
                    <form method="POST" :action="'/admin/clients/' + selected.id">
                        @csrf
                        @method('PATCH')

                        <div class="p-6 space-y-4">

                            {{-- Nombre --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nombre
                                    Completo / Razón Social</label>
                                <input type="text" name="name" x-model="selected.name" required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s\-\.\,\']/g, '')"
                                    title="Solo letras, espacios y puntuación básica">
                            </div>

                            {{-- Identificación y Teléfono --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Cédula
                                        o RIF</label>
                                    <input type="text" name="identification" x-model="selected.identification"
                                        required readonly placeholder="Ej: V-12345678"
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono"
                                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '').toUpperCase()"
                                        title="Solo letras, números y guiones">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Teléfono</label>
                                    <input type="tel" name="phone" x-model="selected.phone"
                                        placeholder="Ej: 0412-1234567"
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        oninput="this.value = this.value.replace(/[^0-9\+\-\s\(\)]/g, '')"
                                        title="Solo números, guiones y paréntesis">
                                </div>
                            </div>

                            {{-- Correo --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                    Correo Electrónico
                                    <span class="text-gray-400 font-normal normal-case">(Opcional)</span>
                                </label>
                                <input type="email" name="email" x-model="selected.email"
                                    placeholder="correo@ejemplo.com"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            {{-- Dirección --}}
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Dirección
                                    Fiscal</label>
                                <textarea name="address" rows="3" x-model="selected.address"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                            </div>

                            {{-- Toggle Estado --}}
                            <div
                                class="flex items-center justify-between bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div>
                                    <p class="text-sm font-bold text-gray-700">Estado del Cliente</p>
                                    <p class="text-xs text-gray-500">Desactiva si el cliente no debe generar nuevas
                                        operaciones.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" :checked="selected.is_active"
                                        @change="selected.is_active = $event.target.checked" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-400 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5">
                                    </div>
                                </label>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <button type="submit" x-data="{ enviando: false }" @submit.window="enviando = true"
                                :disabled="enviando"
                                :class="enviando ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar Cambios
                            </button>
                            <button type="button" @click="openEdit = false"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-100 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function clientsPage() {
            return {
                openDetails: false,
                openEdit: false,

                // Objeto que guarda los datos del cliente actualmente seleccionado
                selected: {
                    id: null,
                    name: '',
                    identification: '',
                    phone: '',
                    email: '',
                    address: '',
                    is_active: true,
                    has_account: false,
                    account_email: '',
                },

                showDetails(data) {
                    this.selected = {
                        ...this.selected,
                        ...data
                    };
                    this.openDetails = true;
                },

                showEdit(data) {
                    this.selected = {
                        ...this.selected,
                        ...data
                    };
                    this.openEdit = true;
                },
            }
        }
    </script>
@endsection
