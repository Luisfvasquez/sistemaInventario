@extends('admin.layouts.app')

@section('title', 'Asignación de Roles')

@section('content')
    <div x-data="{
        showModal: false,
        userName: '',
        userId: '',
        selectedRoles: [],
    
        openAssign(user) {
            this.userId = user.id;
            this.userName = user.name + ' ' + (user.last_name || '');
            this.selectedRoles = user.roles.map(r => r.name);
            this.showModal = true;
        },
        getFormAction() {
            return `/admin/config/users-roles/${this.userId}`;
        }
    }">

        <!-- Cabecera -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Asignación de Roles</h1>
                <p class="text-gray-500 text-sm">Gestiona y asigna roles a los diferentes usuarios registrados en el sistema</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            <form action="{{ route('admin.users-roles.index') }}" method="GET" class="w-full md:w-96 flex gap-2 m-0">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por DNI, nombre o correo..."
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Buscar
                </button>
                @if($search)
                    <a href="{{ route('admin.users-roles.index') }}" class="px-4 py-2 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg text-sm font-medium transition-colors flex items-center">
                        Limpiar
                    </a>
                @endif
            </form>
            <div class="text-xs text-gray-400">
                Mostrando {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-xs font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4">DNI / Identificación</th>
                            <th class="px-6 py-4">Teléfono</th>
                            <th class="px-6 py-4">Roles Actuales</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <!-- Info Usuario -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold border border-blue-100 shadow-inner">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-bold text-gray-800">{{ $user->name }} {{ $user->last_name }}</span>
                                            <span class="block text-xs text-gray-400">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- DNI -->
                                <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                    {{ $user->dni ?? 'No especificado' }}
                                </td>

                                <!-- Teléfono -->
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $user->phone_number ?? 'No disponible' }}
                                </td>

                                <!-- Badges de Roles -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($user->roles as $role)
                                            @php
                                                $badgeColors = [
                                                    'admin' => 'bg-indigo-50 text-indigo-700 border-indigo-150',
                                                    'client' => 'bg-green-50 text-green-700 border-green-150',
                                                    'seller' => 'bg-amber-50 text-amber-700 border-amber-150',
                                                    'warehouse' => 'bg-blue-50 text-blue-700 border-blue-150',
                                                ];
                                                $color = $badgeColors[$role->name] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                                            @endphp
                                            <span class="px-2 py-0.5 rounded text-xs font-bold capitalize border {{ $color }}">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-50 text-gray-400 border border-dashed border-gray-200">
                                                Sin Rol Asignado
                                            </span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="openAssign({{ json_encode($user) }})"
                                        class="bg-white hover:bg-gray-50 text-blue-600 border border-gray-200 hover:border-gray-300 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 ml-auto">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        <span>Asignar Roles</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="font-medium text-sm">No se encontraron usuarios coincidentes</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL DE ASIGNACIÓN (Alpine.js) -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-60"
            style="display: none;" x-transition:opacity>

            <div @click.away="showModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all"
                x-transition:scale>

                <!-- Cabecera del Modal -->
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Gestionar Roles</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="'Usuario: ' + userName"></p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 bg-white p-1 rounded-full shadow-sm border border-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="p-6">
                    <form :action="getFormAction()" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Roles Disponibles</span>
                            
                            <div class="space-y-2.5">
                                @foreach ($roles as $role)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 hover:border-blue-200 hover:bg-blue-50/30 cursor-pointer select-none transition-all">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            x-model="selectedRoles"
                                            class="rounded border-gray-300 text-blue-600 shadow-xs focus:ring-blue-500">
                                        <div>
                                            <span class="block text-sm font-bold text-gray-700 capitalize">{{ $role->name }}</span>
                                            <span class="block text-[11px] text-gray-400">
                                                @if($role->name === 'admin')
                                                    Acceso total e irrestricto a todo el sistema.
                                                @elseif($role->name === 'client')
                                                    Acceso restringido al portal de clientes y sus facturas.
                                                @elseif($role->name === 'seller')
                                                    Permisos para gestionar ventas, cotizaciones y clientes.
                                                @elseif($role->name === 'warehouse')
                                                    Acceso a inventarios, compras y productos.
                                                @else
                                                    Rol personalizado con permisos específicos.
                                                @endif
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Advertencias y Mensajes -->
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-[11px] leading-relaxed mb-6" x-show="selectedRoles.length === 0">
                            <strong>Atención:</strong> Desmarcar todos los roles quitará por completo el acceso administrativo de este usuario.
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 bg-white">Cancelar</button>
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-xs">
                                Sincronizar Roles
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
