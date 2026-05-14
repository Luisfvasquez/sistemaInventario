@extends('admin.layouts.app')

@section('title', 'Productos')

@section('content')
    <div>
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Inventario de Productos
            </h1>
            <a href="{{ route('admin.products.create') }}"
                class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Producto
            </a>
        </div>

        {{-- Tabla de Productos --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Producto / SKU</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoría</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precio Base</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                                Base</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            {{-- Fila controlada por Alpine para manejar sus propios modales --}}
                            <tr x-data="{ openEdit: false, openDelete: false }" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $product->category->name ?? 'Sin categoría' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    ${{ number_format($product->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->inventory->stock ?? 0 }} Unid.</div>
                                    <div class="text-xs text-orange-500">{{ $product->inventory->reserved_stock ?? 0 }}
                                        Reserv.</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($product->status === 'active')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    {{-- Botón Editar --}}
                                    <button @click="openEdit = true"
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md transition-colors">
                                        Editar
                                    </button>
                                    {{-- Botón Eliminar --}}
                                    <button @click="openDelete = true"
                                        class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md transition-colors">
                                        Eliminar
                                    </button>
                                </td>

                                {{-- MODAL DE EDICIÓN (Se teletransporta al body para evitar problemas de z-index con la tabla) --}}
                                <template x-teleport="body">
                                    <div x-show="openEdit" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <div
                                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                                            {{-- Overlay oscuro --}}
                                            <div x-show="openEdit" x-transition.opacity
                                                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                                                @click="openEdit = false"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                                aria-hidden="true">&#8203;</span>

                                            {{-- Contenido del Modal --}}
                                            <div x-show="openEdit" x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                                                <form action="{{ route('admin.products.update', $product->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                        <h3
                                                            class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2">
                                                            Editar Producto: {{ $product->name }}</h3>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Nombre</label>
                                                                <input type="text" name="name"
                                                                    value="{{ $product->name }}"
                                                                    class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                    required>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Categoría</label>
                                                                <select name="category_id"
                                                                    class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                    required>
                                                                    @foreach ($categories as $category)
                                                                        <option value="{{ $category->id }}"
                                                                            {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                                            {{ $category->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">SKU /
                                                                    Cód. Barras</label>
                                                                <input type="text" name="sku_barcode"
                                                                    value="{{ $product->sku_barcode }}"
                                                                    class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                    required>
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-2">
                                                                <div>
                                                                    <label
                                                                        class="block text-sm font-medium text-gray-700">Costo
                                                                        ($)</label>
                                                                    <input type="number" step="0.01" name="cost"
                                                                        value="{{ $product->cost }}"
                                                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                        required>
                                                                </div>
                                                                <div>
                                                                    <label
                                                                        class="block text-sm font-medium text-gray-700">Precio
                                                                        ($)</label>
                                                                    <input type="number" step="0.01" name="price"
                                                                        value="{{ $product->price }}"
                                                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                        required>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Estado</label>
                                                                <select name="status"
                                                                    class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                                                    required>
                                                                    <option value="active"
                                                                        {{ $product->status == 'active' ? 'selected' : '' }}>
                                                                        Activo</option>
                                                                    <option value="inactive"
                                                                        {{ $product->status == 'inactive' ? 'selected' : '' }}>
                                                                        Inactivo</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Nota sobre los bultos --}}
                                                        <p class="mt-4 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                                            Nota: Si necesitas editar o agregar nuevas presentaciones
                                                            (Bultos/Cajas) de este producto o ajustar el stock, hazlo desde
                                                            los módulos de Inventario y Compras.
                                                        </p>
                                                    </div>

                                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                        <button type="submit"
                                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                                            Guardar Cambios
                                                        </button>
                                                        <button type="button" @click="openEdit = false"
                                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- MODAL DE ELIMINACIÓN --}}
                                <template x-teleport="body">
                                    <div x-show="openDelete" style="display: none;"
                                        class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                                        role="dialog" aria-modal="true">
                                        <div
                                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="openDelete" x-transition.opacity
                                                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                                                @click="openDelete = false"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                                aria-hidden="true">&#8203;</span>

                                            <div x-show="openDelete" x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-red-600" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                id="modal-title">Eliminar Producto</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500">¿Estás seguro que deseas
                                                                    eliminar <strong>{{ $product->name }}</strong>? Esta
                                                                    acción lo moverá a la papelera (SoftDelete).</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <form action="{{ route('admin.products.destroy', $product->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                            Sí, Eliminar
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="openDelete = false"
                                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No hay productos registrados en el sistema. <a
                                        href="{{ route('admin.products.create') }}"
                                        class="text-blue-600 hover:underline">Crea el primero.</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
