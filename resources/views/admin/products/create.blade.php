@extends('admin.layouts.app')

@section('title', 'Crear Producto')

@section('content')
    <div>
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Crear Nuevo Producto
            </h1>
            <a href="{{ route('admin.products.index') }}"
                class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Sección: Información General --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Información General</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select name="category_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Código Interno)</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                        <input type="text" name="sku_barcode" value="{{ old('sku_barcode') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sección: Costos e Inventario Base --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Configuración de Unidad Base</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Costo de Compra ($)</label>
                        <input type="number" name="cost" step="0.01" value="{{ old('cost', 0) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio de Venta ($)</label>
                        <input type="number" name="price" step="0.01" value="{{ old('price', 0) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Sección Dinámica: Presentaciones Adicionales (Bultos/Cajas) --}}
            <div class="bg-white p-6 rounded-xl shadow" x-data="handler()">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="text-xl font-semibold text-gray-700">Presentaciones (Bultos/Cajas)</h2>
                    <button type="button" @click="add()"
                        class="text-sm bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition">
                        + Agregar Bulto
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in presentations" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border rounded-xl bg-gray-50 items-end">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Tipo</label>
                                <select :name="`presentations[${index}][type]`"
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                                    <option value="pack">Paquete</option>
                                    <option value="box">Caja</option>
                                    <option value="bulk">Bulto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Nombre</label>
                                <input type="text" :name="`presentations[${index}][name]`" placeholder="Ej: Bulto x24"
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Unidades</label>
                                <input type="number" :name="`presentations[${index}][quantity]`"
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Precio Venta ($)</label>
                                <input type="number" step="0.01" :name="`presentations[${index}][sale_price]`"
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                            </div>
                            <div class="text-right">
                                <button type="button" @click="remove(index)"
                                    class="text-red-600 hover:text-red-800 font-medium text-sm">
                                    Eliminar
                                </button>
                                <input type="hidden" :name="`presentations[${index}][purchase_price]`" value="0">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition-all transform hover:-translate-y-1">
                    Guardar Producto e Inventario
                </button>
            </div>
        </form>
    </div>

    {{-- Script para manejo dinámico --}}
    <script>
        function handler() {
            return {
                presentations: [],
                add() {
                    this.presentations.push({
                        type: 'bulk',
                        name: '',
                        quantity: 0,
                        sale_price: 0
                    });
                },
                remove(index) {
                    this.presentations.splice(index, 1);
                }
            }
        }
    </script>
@endsection
