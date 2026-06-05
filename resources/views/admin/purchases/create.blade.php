@extends('admin.layouts.app')

@section('title', 'Ingresar Compra')

@section('content')
    <div x-data="purchaseForm()">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Nueva Compra de Mercancía</h1>
            <span class="bg-gray-800 text-white px-4 py-2 rounded-lg font-bold">
                Tasa Activa: Bs. <span x-text="exchangeRate"></span>
            </span>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <ul class="list-disc list-inside text-red-600 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.purchases.store') }}" method="POST" class="space-y-6" @submit="isSubmitting = true">
            @csrf

            {{-- 1. Cabecera de Compra --}}
            <div class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <div class="flex items-center space-x-4 mb-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-blue-600" name="supplier_type" value="existing"
                                :checked="!isNewSupplier" @change="isNewSupplier = false">
                            <span class="ml-2 text-sm text-gray-700">Existente</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-blue-600" name="supplier_type" value="new"
                                :checked="isNewSupplier" @change="isNewSupplier = true">
                            <span class="ml-2 text-sm text-gray-700">Nuevo</span>
                        </label>
                    </div>

                    <div x-show="!isNewSupplier">
                        <select name="supplier_id" class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                            x-bind:required="!isNewSupplier">
                            <option value="">Seleccione Proveedor</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} - {{ $supplier->rif }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="isNewSupplier" class="space-y-2">
                        <input type="text" name="new_supplier_rif" placeholder="RIF (Ej: J-12345678-9)"
                            class="w-full rounded-lg border-gray-300 text-sm" x-bind:required="isNewSupplier"
                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '').toUpperCase()"
                            title="Solo letras, números y guiones (Ej: J12345678-9)">
                        <input type="text" name="new_supplier_name" placeholder="Nombre (Opcional)"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s\-\.]/g, '')"
                            title="Solo letras y espacios">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº Factura / Recibo</label>
                    <input type="text" name="purchase_code" required class="w-full rounded-lg border-gray-300"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-\/]/g, '').toUpperCase()"
                        title="Solo letras, números, guiones y barras (Ej: FAC-0001)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Compra</label>
                    <input type="date" name="purchased_at" value="{{ date('Y-m-d') }}" required
                        class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota (Opcional)</label>
                    <input type="text" name="notes" class="w-full rounded-lg border-gray-300"
                        oninput="this.value = this.value.replace(/[<>{}\[\]]/g, '')"
                        title="Texto libre (sin caracteres especiales como &lt; &gt; { } [ ])">
                </div>
            </div>

            {{-- 2. Filas de Productos Dinámicos --}}
            <div class="bg-white p-6 rounded-xl shadow">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="text-xl font-semibold text-gray-700">Detalle de Productos</h2>
                    <div class="space-x-2">
                        {{-- Botón para agregar producto si no existe (Abre en otra pestaña para no perder la compra) --}}
                        <a href="{{ route('admin.products.create') }}" target="_blank"
                            class="text-sm bg-gray-200 text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-300">
                            Crear Nuevo Producto (Nueva Pestaña)
                        </a>
                    </div>
                </div>

                <!-- Contenedor con Scroll para los productos -->
                <div x-ref="productsContainer" class="max-h-[450px] overflow-y-auto pr-2 space-y-4 mb-4 pb-32">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 border rounded-xl bg-gray-50 items-end relative"
                            :class="row.showDropdown ? 'z-50' : 'z-10'">

                            {{-- Seleccionar Producto con Buscador --}}
                            <div class="md:col-span-3 relative" @click.away="row.showDropdown = false">
                                <label class="block text-xs font-bold text-gray-500">Producto</label>
                                <input type="text" x-model="row.searchQuery" @focus="row.showDropdown = true"
                                    @input="row.showDropdown = true; row.product_id = ''; updateBulks(index)"
                                    placeholder="Buscar producto..."
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                    required>

                                <input type="hidden" :name="`items[${index}][product_id]`" x-model="row.product_id"
                                    required>

                                <div x-show="row.showDropdown"
                                    class="absolute left-0 w-full mt-1 bg-white rounded-lg shadow-xl border border-gray-200 max-h-48 overflow-y-auto z-50"
                                    style="display: none;">
                                    <ul class="py-1 text-sm text-gray-700">
                                        <template x-for="prod in filteredProducts(row.searchQuery)" :key="prod.id">
                                            <li @click="selectProduct(index, prod)"
                                                class="cursor-pointer hover:bg-blue-50 hover:text-blue-900 px-3 py-2 border-b border-gray-100 flex justify-between items-center transition-colors duration-150">
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-800" x-text="prod.name"></span>
                                                    <span class="text-xs text-gray-400 font-mono"
                                                        x-text="'SKU/Cód: ' + (prod.sku_barcode || 'N/A')"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase"
                                                    x-text="prod.unit_type === 'gram' ? 'Kilos' : 'Unidades'"></span>
                                            </li>
                                        </template>
                                        <template x-if="filteredProducts(row.searchQuery).length === 0">
                                            <li class="px-3 py-3 text-gray-500 text-xs text-center font-medium">No se
                                                encontraron productos</li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            {{-- Seleccionar Presentación (Depende del producto) --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500">Presentación comprada</label>
                                <select :name="`items[${index}][bulk_id]`" x-model="row.bulk_id"
                                    @change="updateUnitCost(index)" class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                                    required>
                                    <option value="">Seleccione...</option>
                                    <template x-for="bulk in row.availableBulks" :key="bulk.id">
                                        <option :value="bulk.id"
                                            x-text="bulk.name + ' (Trae ' + bulk.quantity + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Cantidad Comprada --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500">Cantidad (Ej: 5 Kilos)</label>
                                <input type="number" :step="row.unit_type === 'gram' ? '0.01' : '1'"
                                    :min="row.unit_type === 'gram' ? '0.01' : '1'"
                                    :name="`items[${index}][quantity]`" x-model="row.quantity"
                                    class="w-full mt-1 rounded-lg border-gray-300 text-sm" required inputmode="decimal"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                    title="Solo números positivos">
                            </div>

                            {{-- Costo Unitario --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500">Costo Unit. (Bs)</label>
                                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`"
                                    x-model="row.unit_cost" class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                                    required inputmode="decimal"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                    title="Solo números positivos">
                            </div>

                            {{-- Subtotal (Solo visual) --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500">Subtotal</label>
                                <div
                                    class="w-full mt-1 p-2 bg-gray-200 rounded-lg text-sm font-bold text-gray-700 text-right">
                                    Bs. <span x-text="(row.quantity * row.unit_cost).toFixed(2)"></span>
                                </div>
                            </div>

                            {{-- Botón Eliminar Fila --}}
                            <div class="md:col-span-1 text-center">
                                <button type="button" @click="removeRow(index)"
                                    class="text-red-500 hover:text-red-700 font-bold mb-2 p-2">X</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Botón Agregar Fila al pie de la sección, al lado de la lista scrollable -->
                <div class="flex justify-start">
                    <button type="button" @click="addRow()"
                        class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-bold shadow-md transition-colors duration-200">
                        + Agregar Fila
                    </button>
                </div>
            </div>

            {{-- 3. Totales --}}
            <div class="flex justify-end">
                <div class="bg-gray-800 text-white p-6 rounded-xl shadow-lg w-full md:w-1/3">
                    <div class="flex justify-between mb-2">
                        <span>Total Compra (Bs):</span>
                        <span class="font-bold text-xl" x-text="calculateTotalBs()"></span>
                    </div>
                    <div class="flex justify-between text-green-400 border-t border-gray-600 pt-2 mt-2">
                        <span>Total Equivalente ($):</span>
                        <span class="font-bold text-xl" x-text="calculateTotalUsd()"></span>
                    </div>
                    <button type="submit" x-bind:disabled="isSubmitting"
                        x-text="isSubmitting ? 'Procesando Compra...' : 'Procesar Ingreso a Inventario'"
                        :class="isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-500'"
                        class="w-full mt-4 text-white font-bold py-3 rounded-lg transition-colors">
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function purchaseForm() {
            return {
                isSubmitting: false,
                isNewSupplier: false,
                exchangeRate: {{ $exchangeRate ? str_replace(',', '.', $exchangeRate) : 1 }},
                productsList: @json($products),
                rows: [],

                init() {
                    this.addRow(); // Iniciar con una fila vacía
                },


                addRow() {
                    this.rows.push({
                        product_id: '',
                        searchQuery: '',
                        showDropdown: false,
                        bulk_id: '',
                        availableBulks: [],
                        quantity: 1,
                        unit_cost: 0,
                        unit_type: 'unit'
                    });

                    // Esperamos a que Alpine renderice la nueva fila en el DOM
                    this.$nextTick(() => {
                        let container = this.$refs.productsContainer;
                        if (container) {
                            // Hacemos un scroll suave hasta el fondo del contenedor
                            container.scrollTo({
                                top: container.scrollHeight,
                                behavior: 'smooth'
                            });
                        }
                    });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                filteredProducts(query) {
                    if (!query) return this.productsList;
                    let q = query.toLowerCase();
                    return this.productsList.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku_barcode && p.sku_barcode.toLowerCase().includes(q))
                    );
                },

                selectProduct(index, prod) {
                    this.rows[index].product_id = prod.id;
                    this.rows[index].searchQuery = prod.name;
                    this.rows[index].showDropdown = false;
                    this.updateBulks(index);
                },

                // Esta función se ejecuta cuando el usuario selecciona un producto
                updateBulks(index) {
                    let selectedProductId = this.rows[index].product_id;
                    let product = this.productsList.find(p => p.id == selectedProductId);

                    if (product) {
                        // Carga solo las presentaciones de ese producto específico
                        this.rows[index].unit_type = product.unit_type; // Guardamos el tipo para validación
                        this.rows[index].availableBulks = product.bulks;
                        this.rows[index].bulk_id = ''; // Resetea el select secundario
                        this.rows[index].unit_cost = 0; // Se resetea hasta elegir la presentación
                        // Formatear cantidad en caso de cambio
                        this.rows[index].quantity = product.unit_type === 'gram' ? parseFloat(this.rows[index].quantity ||
                            1).toFixed(2) : parseInt(this.rows[index].quantity || 1);
                    } else {
                        this.rows[index].unit_type = 'unit';
                        this.rows[index].availableBulks = [];
                        this.rows[index].bulk_id = '';
                        this.rows[index].unit_cost = 0;
                    }
                },

                // Esta función se ejecuta cuando el usuario selecciona la presentación comprada
                updateUnitCost(index) {
                    let selectedBulkId = this.rows[index].bulk_id;
                    if (selectedBulkId) {
                        let bulk = this.rows[index].availableBulks.find(b => b.id == selectedBulkId);
                        if (bulk) {
                            // Se asigna dinámicamente el precio de compra del bulto o unidad seleccionado
                            this.rows[index].unit_cost = bulk.purchase_price || 0;
                        }
                    } else {
                        this.rows[index].unit_cost = 0;
                    }
                },

                calculateTotalBs() {
                    let total = this.rows.reduce((sum, row) => sum + (row.quantity * row.unit_cost), 0);
                    return total.toFixed(2);
                },

                calculateTotalUsd() {
                    let totalBs = parseFloat(this.calculateTotalBs());
                    if (this.exchangeRate > 0) {
                        return (totalBs / this.exchangeRate).toFixed(2);
                    }
                    return '0.00';
                }
            }
        }
    </script>
@endsection
