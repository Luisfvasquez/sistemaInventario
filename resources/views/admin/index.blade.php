@extends('admin.layouts.app')

@section('title', 'Panel de Administración')

@section('content')
    <div x-data="{
        // Si hay un modal_type guardado en old(), ábrelo, sino por defecto 'administradores'
        activeTab: '{{ old('modal_type', '') }}',
    
        // Si Laravel devuelve errores, abre el modal automáticamente
        showModal: {{ $errors->any() ? 'true' : 'false' }},
    
        modalTitle: '{{ $errors->any() ? (old('_method') == 'PUT' ? 'Editar Registro' : 'Nuevo Registro') : '' }}',
        modalType: '{{ old('modal_type', 'admins') }}',
        isEdit: {{ old('_method') == 'PUT' ? 'true' : 'false' }},
        itemId: '{{ old('item_id', '') }}',
    
        // Rellenamos formData con los valores old() de Laravel para evitar perder datos
        formData: {
            name: '{{ old('name', '') }}',
            last_name: '{{ old('last_name', '') }}',
            email: '{{ old('email', '') }}',
            phone_number: '{{ old('phone_number', '') }}',
            address: '{{ old('address', '') }}',
            rif: '{{ old('rif', '') }}',
            contact_person: '{{ old('contact_person', '') }}',
            slug: '{{ old('slug', '') }}',
            description: '{{ old('description', '') }}',
            is_active: {{ old('is_active', '1') ? 'true' : 'false' }},
            requires_reference: {{ old('requires_reference', '0') ? 'true' : 'false' }},
            show_in_checkout: {{ old('show_in_checkout', '0') ? 'true' : 'false' }}
        },
    
        openCreate() {
            this.isEdit = false;
            this.itemId = null;
            this.modalType = this.activeTab;
            this.modalTitle = 'Nuevo Registro';
    
            // Limpiamos los datos dependiendo del tipo de dato
            Object.keys(this.formData).forEach(key => {
                if (key === 'is_active') {
                    this.formData[key] = true;
                } else if (key === 'requires_reference' || key === 'show_in_checkout') {
                    this.formData[key] = false;
                } else {
                    this.formData[key] = '';
                }
            });
            this.showModal = true;
        },
    
        openEdit(type, item) {
            this.isEdit = true;
            this.modalType = type;
            this.itemId = item.id;
            this.modalTitle = 'Editar Registro';
    
            // Asignamos directamente los valores booleanos basándonos en los datos del backend
            Object.assign(this.formData, item);
            this.formData.requires_reference = !!item.requires_reference;
            this.formData.show_in_checkout = !!item.show_in_checkout;
            this.formData.is_active = !!item.is_active;
    
            this.showModal = true;
        },
    
        getFormAction() {
            const baseUrl = `/admin/${this.modalType}`;
            return this.isEdit ? `${baseUrl}/${this.itemId}` : baseUrl;
        }
    }">

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Panel de Administración</h1>
                <p class="text-gray-500 text-sm">Gestiona los accesos y configuraciones del sistema</p>
            </div>

            {{-- Botón Dinámico --}}
            <button x-show="activeTab !== ''" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100" @click="openCreate()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors flex items-center gap-2 shadow-sm"
                style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Registro</span>
            </button>
        </div>

        {{-- Selector de Sección --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach ([['id' => 'admins', 'label' => 'Administradores', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'], ['id' => 'payment_methods', 'label' => 'Métodos de Pago', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'], ['id' => 'categories', 'label' => 'Categorías', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'], ['id' => 'suppliers', 'label' => 'Proveedores', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4']] as $item)
                <button @click="activeTab = '{{ $item['id'] }}'"
                    :class="activeTab === '{{ $item['id'] }}' ? 'border-blue-500 ring-2 ring-blue-200 bg-blue-50' :
                        'border-gray-200 bg-white hover:border-gray-300'"
                    class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left">
                    <div :class="activeTab === '{{ $item['id'] }}' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500'"
                        class="p-3 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $item['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <span class="block font-bold text-gray-800">{{ $item['label'] }}</span>
                        <span class="text-xs text-gray-500 uppercase tracking-wider">Gestionar</span>
                    </div>
                </button>
            @endforeach
        </div>

        {{-- TABLAS DE DATOS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Tabla admins --}}
            <div x-show="activeTab === 'admins'" style="display: none;">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">DNI/Identificación</th>
                            <th class="px-6 py-4 font-semibold">Nombre</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Estado</th>
                            <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($admins as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $item->dni ?? 'No disponible' }}</td>
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $item->email }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Activo</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="openEdit('admins', @js($item))"
                                        class="text-blue-600 hover:text-blue-800 mr-2 rounded-lg">Editar</button>
                                    <form action="{{ url('/admin/admins/' . $item->id) }}" method="POST"
                                        class="inline-block m-0"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar a {{ $item->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium transition-colors flex items-center gap-1 inline-flex">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tabla Métodos de Pago --}}
            <div x-show="activeTab === 'payment_methods'" style="display: none;">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Método</th>
                            <th class="px-6 py-4 font-semibold">Ref. Requerida</th>
                            <th class="px-6 py-4 font-semibold">Mostrar en Checkout</th>
                            <th class="px-6 py-4 font-semibold">Estado</th>
                            <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($payment_methods ?? [] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $item->name }}</td>
                                <td class="px-6 py-4">
                                    @if ($item->requires_reference)
                                        <span
                                            class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold">Sí</span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->show_in_checkout)
                                        <span
                                            class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">Sí</span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->is_active)
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Activo</span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                        @click="openEdit('payment_methods', @js($item))"
                                        class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tabla Categorías --}}
            <div x-show="activeTab === 'categories'" style="display: none;">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Categoría</th>
                            <th class="px-6 py-4 font-semibold">Slug</th>
                            <th class="px-6 py-4 font-semibold">Estado</th>
                            <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($categories as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ $item->slug }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Activo</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="openEdit('categories', @js($item))"
                                        class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                                    <form action="{{ url('/admin/categories/' . $item->id) }}" method="POST"
                                        class="inline-block m-0"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar a {{ $item->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium transition-colors flex items-center gap-1 inline-flex">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tabla Proveedores (suppliers) --}}
            <div x-show="activeTab === 'suppliers'" style="display: none;">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">RIF</th>
                            <th class="px-6 py-4 font-semibold">Razón Social</th>
                            <th class="px-6 py-4 font-semibold">Persona de Contacto</th>
                            <th class="px-6 py-4 font-semibold">Correo Electrónico</th>
                            <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($suppliers as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $item->rif }}</td>
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ $item->contact_person }}</td>
                                <td class="px-6 py-4">{{ $item->email }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="openEdit('suppliers', @js($item))"
                                        class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                                    <form action="{{ url('/admin/suppliers/' . $item->id) }}" method="POST"
                                        class="inline-block m-0"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar a {{ $item->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium transition-colors flex items-center gap-1 inline-flex">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        {{-- MODAL DINÁMICO --}}
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-60"
            style="display: none;" x-transition:opacity>

            <div @click.away="showModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col"
                x-transition:scale>

                {{-- Cabecera del Modal --}}
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo del Modal (Scrollable) --}}
                <div class="p-6 overflow-y-auto">
                    <form :action="getFormAction()" method="POST">
                        @csrf
                        {{-- Campos ocultos para recuperar el estado si hay errores --}}
                        <input type="hidden" name="modal_type" x-model="modalType">
                        <input type="hidden" name="item_id" x-model="itemId">

                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- ERRORES DE VALIDACIÓN --}}
                        @if ($errors->any())
                            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Por favor, corrige los siguientes
                                            errores:</h3>
                                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- FORMULARIO: admins --}}
                        <template x-if="modalType === 'admins'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="block text-sm text-gray-700">Nombre</label><input type="text"
                                        required name="name" x-model="formData.name"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required></div>
                                <div><label class="block text-sm text-gray-700">Cedula/Dni</label><input type="text"
                                        required name="dni" x-model="formData.dni"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required></div>
                                <div><label class="block text-sm text-gray-700">Apellido</label><input type="text"
                                        required name="last_name" x-model="formData.last_name"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div><label class="block text-sm text-gray-700">Teléfono</label><input type="text"
                                        required name="phone_number" x-model="formData.phone_number"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div><label class="block text-sm text-gray-700">Correo (Email)</label><input
                                        type="email" name="email" x-model="formData.email"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required></div>
                                <div class="col-span-1 md:col-span-1">
                                    <label class="block text-sm text-gray-700">Contraseña</label>
                                    <input type="password" name="password" x-model="formData.password"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                    <span class="text-xs text-gray-500" x-show="isEdit">Dejar en blanco si no deseas
                                        cambiarla.</span>
                                </div>
                                <div class="flex items-center mt-2 col-span-1 md:col-span-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm">
                                    <label class="ml-2 text-sm text-gray-700">Registro Activo</label>
                                </div>
                            </div>
                        </template>

                        {{-- FORMULARIO: Métodos de Pago (NUEVO) --}}
                        <template x-if="modalType === 'payment_methods'">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-700">Nombre del Método (Ej. Zelle, Pago Móvil,
                                        Efectivo)</label>
                                    <input type="text" name="name" x-model="formData.name"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Descripción (Opcional - Instrucciones para
                                        el cliente)</label>
                                    <textarea name="description" rows="3" x-model="formData.description"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>

                                <div class="flex flex-col gap-3 mt-2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="hidden" name="requires_reference" value="0">
                                        <input type="checkbox" name="requires_reference" value="1"
                                            x-model="formData.requires_reference"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Requiere número de referencia
                                            al pagar</span>
                                    </label>

                                    <label class="flex items-center cursor-pointer">
                                        <input type="hidden" name="show_in_checkout" value="0">
                                        <input type="checkbox" name="show_in_checkout" value="1"
                                            x-model="formData.show_in_checkout"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Mostrar opción en la pantalla
                                            de Checkout</span>
                                    </label>

                                    <label class="flex items-center cursor-pointer">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1"
                                            x-model="formData.is_active"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Método de pago Activo</span>
                                    </label>
                                </div>
                            </div>
                        </template>

                        {{-- FORMULARIO: categories --}}
                        <template x-if="modalType === 'categories'">
                            <div class="grid grid-cols-1 gap-4">
                                <div><label class="block text-sm text-gray-700">Nombre de Categoría</label><input
                                        type="text" name="name" x-model="formData.name"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required></div>
                                <div><label class="block text-sm text-gray-700">Slug (URL amigable)</label><input
                                        type="text" name="slug" x-model="formData.slug"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div><label class="block text-sm text-gray-700">Descripción</label>
                                    <textarea name="description" rows="3" x-model="formData.description"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm">
                                    <label class="ml-2 text-sm text-gray-700">Registro Activo</label>
                                </div>
                            </div>
                        </template>

                        {{-- FORMULARIO: suppliers --}}
                        <template x-if="modalType === 'suppliers'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="block text-sm text-gray-700">RIF</label><input type="text"
                                        name="rif" x-model="formData.rif" readonly
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div><label class="block text-sm text-gray-700">Razón Social / Nombre</label><input
                                        type="text" name="name" x-model="formData.name"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required></div>
                                <div><label class="block text-sm text-gray-700">Persona de Contacto</label><input
                                        type="text" name="contact_person" x-model="formData.contact_person"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div><label class="block text-sm text-gray-700">Teléfono</label><input type="text"
                                        name="phone_number" x-model="formData.phone_number"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div class="col-span-1 md:col-span-2"><label class="block text-sm text-gray-700">Correo
                                        Electrónico</label><input type="email" name="email" x-model="formData.email"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                <div class="col-span-1 md:col-span-2"><label
                                        class="block text-sm text-gray-700">Dirección</label>
                                    <textarea name="address" rows="2" x-model="formData.address"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                                <div class="flex items-center mt-2 col-span-1 md:col-span-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm">
                                    <label class="ml-2 text-sm text-gray-700">Registro Activo</label>
                                </div>
                            </div>
                        </template>

                        {{-- Footer del Modal --}}
                        <div class="pt-6 mt-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">Cancelar</button>
                            <button type="submit" x-data="{ enviando: false }" @submit.window="enviando = true"
                                :disabled="enviando"
                                :class="enviando ? 'opacity-50 cursor-not-allowed bg-blue-400' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-white rounded-lg transition-colors font-medium flex items-center gap-2">

                                <!-- Icono de carga opcional que aparece al enviar -->
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
