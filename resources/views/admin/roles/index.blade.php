@extends('admin.layouts.app')

@section('title', 'Gestión de Roles y Permisos')

@section('content')
    <div x-data="{
        showModal: false,
        isEdit: false,
        modalTitle: '',
        roleId: '',
        roleName: '',
        selectedPermissions: [],
    
        openCreate() {
            this.isEdit = false;
            this.roleId = '';
            this.roleName = '';
            this.selectedPermissions = [];
            this.modalTitle = 'Crear Nuevo Rol';
            this.showModal = true;
        },
        openEdit(role) {
            this.isEdit = true;
            this.roleId = role.id;
            this.roleName = role.name;
            this.selectedPermissions = role.permissions.map(p => p.name);
            this.modalTitle = 'Editar Rol: ' + role.name;
            this.showModal = true;
        },
        getFormAction() {
            return this.isEdit ? `/admin/config/roles/${this.roleId}` : '/admin/config/roles';
        },
        toggleGroup(permsList, selectAll) {
            permsList.forEach(pName => {
                const index = this.selectedPermissions.indexOf(pName);
                if (selectAll) {
                    if (index === -1) {
                        this.selectedPermissions.push(pName);
                    }
                } else {
                    if (index !== -1) {
                        this.selectedPermissions.splice(index, 1);
                    }
                }
            });
        },
        isGroupAllSelected(permsList) {
            if (permsList.length === 0) return false;
            return permsList.every(pName => this.selectedPermissions.includes(pName));
        }
    }">

        <!-- Cabecera -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Roles y Permisos</h1>
                <p class="text-gray-500 text-sm">Define las capacidades y privilegios para cada puesto de trabajo</p>
            </div>

            <button @click="openCreate()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Rol</span>
            </button>
        </div>

        <!-- Grid de Roles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach ($roles as $role)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500 transition-colors {{ $role->name === 'admin' ? 'bg-indigo-600' : ($role->name === 'client' ? 'bg-green-500' : ($role->name === 'seller' ? 'bg-amber-500' : 'bg-blue-500')) }}"></div>
                    
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-xl font-bold text-gray-800 capitalize">{{ $role->name }}</h2>
                            @if(in_array($role->name, ['admin', 'client', 'seller', 'warehouse']))
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-xs font-semibold uppercase tracking-wider">Sistema</span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-xs font-semibold uppercase tracking-wider">Personalizado</span>
                            @endif
                        </div>

                        <!-- Estadísticas Rápidas -->
                        <div class="space-y-2 mb-6">
                            <div class="flex items-center text-sm text-gray-500 gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span><strong>{{ $role->users_count }}</strong> usuarios asociados</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500 gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span><strong>{{ $role->permissions_count }}</strong> permisos asignados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-gray-50 flex items-center justify-between mt-auto">
                        <button type="button" @click="openEdit({{ json_encode($role) }})"
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Configurar</span>
                        </button>

                        @if(!in_array($role->name, ['admin', 'client', 'seller', 'warehouse']))
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                class="inline-block m-0"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este rol? Se desvinculará de todos los usuarios.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 hover:text-red-700 font-medium text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- MODAL DE ROLES (Alpine.js) -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-60"
            style="display: none;" x-transition:opacity>

            <div @click.away="showModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[92vh] flex flex-col transform transition-all"
                x-transition:scale>

                <!-- Cabecera del Modal -->
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
                        <p class="text-xs text-gray-500 mt-1">Configura el nombre del rol y marca cada permiso que tendrá concedido</p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 bg-white p-1 rounded-full shadow-sm border border-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal (Scrollable) -->
                <div class="p-6 overflow-y-auto flex-1 bg-gray-50/50">
                    <form :action="getFormAction()" method="POST" id="role-form">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Nombre del Rol -->
                        <div class="mb-6 bg-white p-5 rounded-xl border border-gray-200/60 shadow-xs">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Rol</label>
                            <input type="text" name="name" x-model="roleName" required placeholder="Ej. Supervisor, Auditor, Cajero"
                                :readonly="roleName === 'admin'"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                :class="roleName === 'admin' ? 'bg-gray-50 border-gray-200 text-gray-500' : ''">
                            <p class="text-xs text-gray-400 mt-1.5" x-show="roleName === 'admin'">
                                El nombre del administrador principal no puede ser modificado, pero puedes personalizar todos sus permisos.
                            </p>
                        </div>

                        <!-- Panel de Permisos -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-md font-bold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    <span>Listado de Permisos por Componente</span>
                                </h4>
                                
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="selectedPermissions = []"
                                        class="text-xs text-red-500 hover:text-red-700 font-semibold transition-colors">
                                        Desmarcar Todo
                                    </button>
                                </div>
                            </div>

                            <!-- Listado de Categorías -->
                            <div class="space-y-6">
                                @foreach ($groupedPermissions as $groupName => $perms)
                                    @php
                                        // Generate a list of permission names in this group to use in AlpineJS
                                        $permsNames = collect($perms)->pluck('name')->toArray();
                                    @endphp
                                    @if(count($perms) > 0)
                                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden" 
                                             x-data="{ 
                                                groupPerms: {{ json_encode($permsNames) }},
                                                get isAllChecked() {
                                                    return this.groupPerms.every(p => selectedPermissions.includes(p));
                                                }
                                             }">
                                            
                                            <!-- Encabezado de la Categoría -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-5 py-3 flex justify-between items-center">
                                                <span class="font-bold text-gray-700 text-sm flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full {{ $groupName === 'General / Dashboard' ? 'bg-indigo-500' : ($groupName === 'Productos' ? 'bg-blue-500' : ($groupName === 'Ventas y Órdenes' ? 'bg-amber-500' : 'bg-emerald-500')) }}"></span>
                                                    {{ $groupName }}
                                                </span>

                                                <button type="button" @click="toggleGroup(groupPerms, !isAllChecked)"
                                                    class="text-xs font-semibold px-2.5 py-1 rounded bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-all select-none"
                                                    x-text="isAllChecked ? 'Desmarcar Grupo' : 'Marcar Grupo'">
                                                </button>
                                            </div>

                                            <!-- Grid de Permisos -->
                                            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                                @foreach ($perms as $perm)
                                                    <label class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-gray-50 cursor-pointer select-none transition-colors border border-transparent hover:border-gray-100">
                                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                                            x-model="selectedPermissions"
                                                            class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-xs focus:ring-blue-500 transition-colors">
                                                        <div>
                                                            <!-- Clean display name by stripping the prefix 'admin.' -->
                                                            <span class="block text-xs font-semibold text-gray-700 tracking-tight">
                                                                {{ str_replace(['admin.', 'destroyImage', 'forzarActualizacionDolar', 'registerAbono', 'updateVerification', 'quickStore'], [' ', 'Eliminar Imagen', 'Actualizar Dólar', 'Registrar Abono', 'Verificar Orden', 'Rápido Registro'], $perm->name) }}
                                                            </span>
                                                            <span class="block text-[10px] text-gray-400 font-mono select-all">
                                                                {{ $perm->name }}
                                                            </span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Footer del Modal -->
                        <div class="pt-6 mt-8 border-t border-gray-200 flex justify-end gap-3 bg-white -mx-6 -mb-6 p-6">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium border border-gray-200 bg-white">Cancelar</button>
                            <button type="submit" x-data="{ enviando: false }" @submit.window="enviando = true"
                                :disabled="enviando"
                                :class="enviando ? 'opacity-50 cursor-not-allowed bg-blue-400' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-5 py-2 text-white rounded-lg transition-colors font-medium flex items-center gap-2 shadow-xs">
                                
                                <svg x-show="enviando" class="animate-spin h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                
                                <span x-text="enviando ? 'Guardando...' : 'Guardar Cambios'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
