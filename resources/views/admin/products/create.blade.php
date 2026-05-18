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
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-red-800 font-bold text-lg">¡No se pudo guardar el producto!</h3>
                </div>
                <ul class="list-disc list-inside text-red-600 text-sm ml-8 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
            x-data="productForm()" @submit="isSubmitting = true">
            @csrf

            {{-- Notificación si no hay tasa de cambio --}}
            @if (!$exchangeRate)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <p class="text-red-700"><strong>¡Atención!</strong> No hay una tasa de cambio activa. Los cálculos en
                        USD no se mostrarán correctamente.</p>
                </div>
            @endif

            {{-- Sección: Imágenes del Producto --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Imágenes del Producto</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subir Imágenes (Formatos: JPG, PNG,
                        WEBP)</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Puedes seleccionar varias imágenes. Se optimizarán a formato WebP.
                        La primera será la principal.</p>
                </div>
            </div>

            {{-- Sección: Información General --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Información General</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <div class="flex gap-2 items-center">
                            <select name="category_id" id="category_select" x-model="categoryId" @change="generateSku"
                                required class="w-full rounded-lg border-gray-300 focus:border-blue-500">
                                <option value="">Seleccione una categoría</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-name="{{ $category->name }}">
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>

                            {{-- Botón para abrir modal --}}
                            <button type="button" @click="showCategoryModal = true"
                                class="p-2.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                                title="Crear nueva categoría">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto</label>
                        <input type="text" name="name" x-model="name" @input.debounce.500ms="generateSku" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Auto-generado / Editable)</label>
                        <input type="text" name="sku" x-model="sku" required
                            class="w-full rounded-lg border-gray-300 bg-yellow-50 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cód. Barras (Pistola escáner)</label>
                        <input type="text" name="sku_barcode" @keydown.enter.prevent="" required
                            class="w-full rounded-lg border-gray-300 bg-blue-50 focus:bg-white transition-colors" autofocus>
                    </div>

                    {{-- Añadidos Marca y Descripción --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sección: Costos en Bs con conversión a USD --}}
            {{-- Sección: Costos en Bs con conversión a USD y Lógica de Pesables --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="text-xl font-semibold text-gray-700">Configuración Base (Bs)</h2>
                    <span class="bg-gray-800 text-white text-xs px-2 py-1 rounded">Tasa Actual: Bs. <span
                            x-text="rate"></span></span>
                </div>

                {{-- Selector de tipo de unidad --}}
                <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <label class="block text-sm font-bold text-blue-800 mb-1">¿Cómo se mide este producto en su mínima
                        expresión?</label>
                    <select x-model="measureType"
                        class="w-full md:w-1/3 rounded-lg border-blue-300 text-sm focus:ring-blue-500 bg-white">
                        <option value="unit">Por Unidad / Pieza (Ej: 1 Refresco, 1 Empaque)</option>
                        <option value="gram">Pesable en Gramos (Ej: Queso, Carne, Vegetales)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">

                    {{-- Costo Visual (Lo que teclea el humano) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Costo Compra <span x-text="measureType === 'gram' ? 'por KILO' : 'por UNIDAD'"
                                class="font-bold text-indigo-600"></span> (Bs)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" x-model="displayCost" @blur="calculatePrice" required
                                class="w-full rounded-lg border-gray-300 pr-20">
                            <span class="absolute right-3 top-2.5 text-gray-500 text-sm font-bold">($<span
                                    x-text="getUsd(displayCost)"></span>)</span>
                        </div>
                    </div>

                    {{-- Precio Visual (Lo que teclea el humano) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Precio Venta <span x-text="measureType === 'gram' ? 'por KILO' : 'por UNIDAD'"
                                class="font-bold text-indigo-600"></span>
                            <span class="text-xs text-green-600 font-bold ml-2">+30% Auto</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" x-model="displayPrice" required
                                class="w-full rounded-lg border-gray-300 pr-20 border-green-300 focus:ring-green-500">
                            <span class="absolute right-3 top-2.5 text-green-700 text-sm font-bold">($<span
                                    x-text="getUsd(displayPrice)"></span>)</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantudad Mínima Alerta</label>
                        <input type="number" name="minimum_stock" value="5" required
                            class="w-full rounded-lg border-gray-300">
                    </div>

                    {{-- LOS CAMPOS OCULTOS (Lo que realmente se guarda en la Base de Datos para Laravel) --}}
                    <input type="hidden" name="cost" :value="realCost()">
                    <input type="hidden" name="price" :value="realPrice()">
                    <input type="hidden" name="unit_type" :value="measureType">

                </div>
            </div>

            {{-- Sección: Presentaciones --}}
            <div class="bg-white p-6 rounded-xl shadow">
                {{-- Sección: Presentaciones Dinámicas desde BD --}}
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-semibold text-gray-700">Presentaciones Especiales</h2>
                        <button type="button" @click="addPresentation()"
                            class="text-sm bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">
                            + Agregar Presentación
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in presentations" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-7 gap-3 p-4 border rounded-xl bg-gray-50 items-end">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Tipo</label>
                                    {{-- Select alimentado por el array dinámico de Alpine --}}
                                    <select :name="`presentations[${index}][bulk_type_id]`" x-model="item.bulk_type_id"
                                        class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                                        <option value="">Seleccione...</option>
                                        <template x-for="type in dbBulkTypes" :key="type.id">
                                            <option :value="type.id" x-text="type.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Nombre Base</label>
                                    <input type="text" :name="`presentations[${index}][name]`"
                                        placeholder="Ej: Harina Pan"
                                        class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Unidades/Gramos</label>
                                    <input type="number" step="0.01" :name="`presentations[${index}][quantity]`"
                                        class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                                </div>

                                {{-- Costos y Precios con conversión dinámica a USD --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Costo (Bs)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01"
                                            :name="`presentations[${index}][purchase_price]`" x-model="item.purchase_price"
                                            @blur="item.sale_price = (item.purchase_price * 1.30).toFixed(2)"
                                            class="w-full mt-1 rounded-lg border-gray-300 text-sm pr-12" required>
                                        <span class="absolute right-2 top-2.5 text-xs text-gray-500 font-bold">($<span
                                                x-text="getUsd(item.purchase_price)"></span>)</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Venta (Bs)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" :name="`presentations[${index}][sale_price]`"
                                            x-model="item.sale_price"
                                            class="w-full mt-1 rounded-lg border-gray-300 text-sm pr-12 border-green-300"
                                            required>
                                        <span class="absolute right-2 top-2.5 text-xs text-green-700 font-bold">($<span
                                                x-text="getUsd(item.sale_price)"></span>)</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500">Cód. Barras</label>
                                    <input type="text" :name="`presentations[${index}][sku_barcode]`"
                                        @keydown.enter.prevent=""
                                        class="w-full mt-1 rounded-lg border-gray-300 text-sm bg-blue-50" required>
                                </div>
                                <div class="text-right">
                                    <button type="button" @click="removePresentation(index)"
                                        class="text-red-600 font-bold text-sm mb-2 hover:text-red-800">X</button>
                                    <input type="hidden" :name="`presentations[${index}][sku]`"
                                        :value="`${sku}-B${index+1}`">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-10">
                <button type="submit" x-bind:disabled="isSubmitting"
                    x-text="isSubmitting ? 'Procesando Producto...' : 'Guardar Producto Completo'"
                    :class="isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg transform transition-all hover:-translate-y-1">
                </button>
            </div>
            {{-- Modal Nueva Categoría --}}
            <div x-show="showCategoryModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    {{-- Fondo oscuro --}}
                    <div x-show="showCategoryModal" x-transition.opacity
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        @click="showCategoryModal = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    {{-- Contenedor del Modal --}}
                    <div x-show="showCategoryModal" x-transition
                        class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">Crear Nueva
                                Categoría</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Categoría</label>
                                <input type="text" x-model="newCategoryName" placeholder="Ej: Lácteos"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500"
                                    @keydown.enter.prevent="saveCategory()">
                                <p x-show="categoryError" x-text="categoryError"
                                    class="text-red-500 text-sm mt-2 font-medium"></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button type="button" @click="saveCategory()" :disabled="isSavingCategory"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!isSavingCategory">Guardar y Seleccionar</span>
                                <span x-show="isSavingCategory">Guardando...</span>
                            </button>
                            <button type="button"
                                @click="showCategoryModal = false; newCategoryName = ''; categoryError = ''"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Lógica Reactiva --}}
        <script>
            function productForm() {
                return {
                    // Recibimos la tasa desde el backend (por defecto 1 si no hay)
                    isSubmitting: false,
                    rate: {{ $exchangeRate ?? 1 }},
                    categoryId: '',
                    name: '',
                    sku: '',
                    cost: 0,
                    costStr: '',
                    price: 0,
                    priceStr: '',
                    presentations: [],

                    measureType: 'unit', // Por defecto es por unidad
                    displayCost: 0, // El número visual (Ej: 9000 Bs el kilo)
                    displayPrice: 0, // El número visual (Ej: 11700 Bs el kilo)

                    showCategoryModal: false,
                    newCategoryName: '',
                    isSavingCategory: false,
                    categoryError: '',

                    dbBulkTypes: @json($bulkTypes),

                    addPresentation() {
                        this.presentations.push({
                            bulk_type_id: '',
                            name: '',
                            quantity: 0,
                            purchase_price: 0,
                            sale_price: 0,
                            sku_barcode: ''
                        });
                    },

                    removePresentation(index) {
                        this.presentations.splice(index, 1);
                    },
                    realCost() {
                        if (this.measureType === 'gram') {
                            return (parseFloat(this.displayCost || 0) / 1000).toFixed(4); // Divide entre 1000
                        }
                        return this.displayCost;
                    },
                    realPrice() {
                        if (this.measureType === 'gram') {
                            return (parseFloat(this.displayPrice || 0) / 1000).toFixed(4); // Divide entre 1000
                        }
                        return this.displayPrice;
                    },
                    generateSku() {
                        if (this.name && this.categoryId) {
                            let selectEl = document.querySelector('select[name="category_id"]');
                            let catName = selectEl.options[selectEl.selectedIndex].getAttribute('data-name') || 'CAT';
                            let catPrefix = catName.substring(0, 3).toUpperCase();
                            let namePrefix = this.name.substring(0, 3).toUpperCase();
                            let randomNum = Math.floor(1000 + Math.random() * 9000);
                            this.sku = `${catPrefix}-${namePrefix}-${randomNum}`;
                        }
                    },
                    updateCostStr() {
                        this.costStr = this.formatCurrency(this.costStr);
                        this.cost = this.parseCurrency(this.costStr);
                    },
                    updatePriceStr() {
                        this.priceStr = this.formatCurrency(this.priceStr);
                        this.price = this.parseCurrency(this.priceStr);
                    },
                    formatCurrency(val) {
                        if (!val) return '';
                        val = val.toString().replace(/[^0-9,]/g, '');
                        let parts = val.split(',');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        return parts.length > 1 ? parts[0] + ',' + parts[1].substring(0, 2) : parts[0];
                    },
                    parseCurrency(val) {
                        if (!val) return 0;
                        return parseFloat(val.toString().replace(/\./g, '').replace(',', '.')) || 0;
                    },
                    calculatePrice() {
                        if (this.displayCost > 0) {
                            this.displayPrice = (parseFloat(this.displayCost) * 1.30).toFixed(2);
                        }
                    },
                    getUsd(bsValue) {
                        if (!bsValue || this.rate <= 0) return '0.00';
                        return (parseFloat(bsValue) / this.rate).toFixed(2);
                    },
                    addPresentation() {
                        this.presentations.push({
                            type: 'bulk',
                            name: '',
                            quantity: 0,
                            purchase_price: 0,
                            purchase_price_str: '',
                            sale_price: 0,
                            sale_price_str: '',
                            sku_barcode: ''
                        });
                    },
                    removePresentation(index) {
                        this.presentations.splice(index, 1);
                    },
                    async saveCategory() {
                        if (!this.newCategoryName.trim()) {
                            this.categoryError = 'El nombre de la categoría es obligatorio.';
                            return;
                        }

                        this.isSavingCategory = true;
                        this.categoryError = '';

                        try {
                            const response = await fetch('{{ route('admin.categories.quickStore') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                        .value, // Toma el token del formulario principal
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    name: this.newCategoryName
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                // Muestra el mensaje de error de Laravel (ej: si ya existe)
                                throw new Error(data.message || data.errors?.name?.[0] || 'Error al guardar.');
                            }

                            // 1. Agregar la nueva opción al elemento <select>
                            const selectEl = document.getElementById('category_select');
                            const newOption = new Option(data.category.name, data.category.id);
                            newOption.setAttribute('data-name', data.category.name);
                            selectEl.add(newOption);

                            // 2. Seleccionar automáticamente la nueva categoría
                            this.categoryId = data.category.id;

                            // 3. Forzar actualización del SKU si ya había un nombre escrito
                            this.generateSku();

                            // 4. Cerrar y limpiar el modal
                            this.showCategoryModal = false;
                            this.newCategoryName = '';

                        } catch (error) {
                            this.categoryError = error.message;
                        } finally {
                            this.isSavingCategory = false;
                        }
                    },

                }
            }
        </script>
    </div>
@endsection
