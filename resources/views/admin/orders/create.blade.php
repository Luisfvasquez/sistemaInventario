@extends('admin.layouts.app')

@section('title', 'Punto de Venta')

@section('content')
    <div x-data="posSystem()" class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative">

        {{-- Mensajes de Sistema --}}
        <div x-show="alert.show" x-transition
            :class="alert.type === 'success' ? 'bg-green-100 border-green-500 text-green-700' :
                'bg-red-100 border-red-500 text-red-700'"
            class="lg:col-span-12 border-l-4 p-4 rounded shadow-sm font-bold text-lg" style="display: none;">
            <span x-text="alert.message"></span>
        </div>

        {{-- LADO IZQUIERDO: BUSCADOR Y CARRITO (8 Columnas) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Buscador Unificado --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-indigo-500 relative">
                <label class="block text-sm font-bold text-gray-700 mb-2">Escanea el Código de Barras o Escribe el
                    Nombre</label>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchProduct" autofocus
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 text-lg shadow-sm pl-4 py-3"
                        placeholder="Dispara la lectora aquí o busca...">
                </div>

                {{-- Resultados desplegables --}}
                <div x-show="searchResults.length > 0" @click.away="searchResults = []" style="display: none;"
                    class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-2xl border border-gray-200 max-h-60 overflow-y-auto left-0">
                    <ul class="py-1">
                        <template x-for="prod in searchResults" :key="prod.id">
                            <template x-for="bulk in prod.bulks" :key="bulk.id">
                                <li @click="addToCart(prod, bulk)"
                                    class="cursor-pointer hover:bg-indigo-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-gray-800 text-lg" x-text="prod.name"></span>
                                        <span class="text-sm text-indigo-600 font-bold ml-2"
                                            x-text="`(${bulk.name})`"></span>
                                        <div class="text-xs text-gray-500">Stock: <span
                                                x-text="prod.inventory?.stock || 0"></span> Und base</div>
                                    </div>
                                    <span class="font-black text-green-600 text-lg">Bs. <span
                                            x-text="parseFloat(bulk.sale_price).toFixed(2)"></span></span>
                                </li>
                            </template>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Carrito de Compras --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden min-h-[300px]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase">Producto</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase">Cant.</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(item, index) in cart" :key="index">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900" x-text="item.name"></div>
                                    <div class="text-xs text-gray-500 font-bold">
                                        <span x-text="item.presentation"></span> (A Bs. <span
                                            x-text="parseFloat(item.price).toFixed(2)"></span>)
                                    </div>
                                    <template
                                        x-if="!item.allow_negative && (item.current_stock < (item.quantity * item.conversion_factor))">
                                        <span class="text-[10px] text-white bg-red-500 px-2 py-0.5 rounded font-bold">Sin
                                            Stock Físico</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 w-32">
                                    <input type="number" :step="item.unit_type === 'gram' ? '0.01' : '1'"
                                        :min="item.unit_type === 'gram' ? '0.01' : '1'" x-model="item.quantity"
                                        @input="updateTotals"
                                        class="w-full text-center rounded-lg border-gray-300 font-bold text-lg"
                                        inputmode="decimal"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                        title="Solo números positivos">
                                </td>
                                <td class="px-4 py-3 text-right font-black text-gray-900 text-lg">
                                    Bs. <span x-text="item.subtotal.toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="removeItem(index)"
                                        class="text-red-500 hover:text-red-700 font-black text-xl px-2">×</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cart.length === 0">
                            <td colspan="4" class="px-4 py-16 text-center text-gray-400 font-medium">
                                Escanea un código de barras para añadir productos a la factura.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- LADO DERECHO: CLIENTE Y PAGOS (4 Columnas) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Caja Cliente --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border-t-4 border-blue-500">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cédula o RIF del Cliente</label>
                <div class="flex space-x-2">
                    <input type="text" x-model="clientIdentification" @keydown.enter.prevent="searchClient"
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 font-mono text-center font-bold"
                        placeholder="V-12345678"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '').toUpperCase()"
                        title="Solo letras, números y guiones (Ej: V-12345678)">
                    <button @click.prevent="searchClient"
                        class="bg-blue-100 text-blue-700 px-3 rounded-lg hover:bg-blue-200 font-bold">OK</button>
                </div>

                {{-- Muestra si el cliente existe --}}
                <div x-show="clientId"
                    class="mt-3 p-2 bg-green-50 text-green-800 rounded-lg text-sm font-bold flex border border-green-200"
                    style="display: none;">
                    👤 <span x-text="clientName" class="ml-2"></span>
                </div>

                {{-- Muestra botón de crear si no existe --}}
                <div x-show="!clientId && clientIdentification && clientSearched" class="mt-3" style="display: none;">
                    <p class="text-xs text-red-500 mb-2 font-bold">Cliente no encontrado.</p>
                    <button @click.prevent="quickCreateClient"
                        class="w-full bg-indigo-100 text-indigo-700 py-2 rounded-lg hover:bg-indigo-200 font-bold text-sm">
                        + Registrar rápidamente (<span x-text="clientIdentification"></span>)
                    </button>
                </div>
            </div>

            {{-- Caja Pagos (Múltiples) --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border-t-4 border-emerald-500">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center space-x-2">
                        <h3 class="text-sm font-bold text-gray-700 uppercase">Pagos Recibidos</h3>
                        <label class="inline-flex items-center text-xs font-bold ml-3">
                            <input type="checkbox" class="form-checkbox h-4 w-4" x-model="isCreditSale" @change="toggleCreditSale">
                            <span class="ml-2">Vender a Fiado</span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <button x-show="!isCreditSale" @click.prevent="addPaymentLine"
                            class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-gray-300 font-bold">+ Dividir</button>
                        <span x-show="isCreditSale" class="text-xs text-yellow-300 font-bold ml-2">Fiado activo</span>
                    </div>
                </div>

                <div class="space-y-3" x-show="!isCreditSale" style="display: none;">
                    <template x-for="(payment, index) in payments" :key="index">
                        <div class="bg-gray-50 p-3 rounded-lg border relative">
                            <button x-show="payments.length > 1" @click.prevent="removePaymentLine(index)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow font-bold">X</button>

                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <select x-model="payment.payment_method_id"
                                    class="w-full text-xs rounded border-gray-300 font-bold text-gray-700">
                                    <template x-for="pm in availablePaymentMethods" :key="pm.id">
                                        <option :value="pm.id" x-text="pm.name"></option>
                                    </template>
                                </select>
                                <input type="number" step="0.01" min="0" x-model="payment.amount"
                                    @input="onPaymentInput(index)" placeholder="Monto Bs."
                                    class="w-full text-sm rounded border-gray-300 text-right font-bold text-emerald-700"
                                    inputmode="decimal"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                    title="Solo números positivos">
                            </div>
                            <input type="text" x-model="payment.reference"
                                placeholder="Ref. Bancaria (Solo si aplica)"
                                class="w-full text-xs rounded border-gray-300 py-1.5"
                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-\s]/g, '').toUpperCase()"
                                title="Solo letras, números, guiones y espacios">
                        </div>
                    </template>
                </div>
            </div>

            {{-- Pantalla de Totales --}}
            <div class="bg-gray-900 text-white p-6 rounded-xl shadow-xl">
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-gray-300">
                        <span class="text-sm">Total Orden:</span>
                        <span class="font-black text-xl">Bs. <span x-text="totalOrder.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-gray-300">
                        <span class="text-sm">Pagado en Caja:</span>
                        <span class="font-bold text-green-400 text-lg">Bs. <span
                                x-text="amountReceived.toFixed(2)"></span></span>
                    </div>

                    {{-- Fiado / Vuelto --}}
                    <div x-show="amountPending > 0"
                        class="flex justify-between items-center bg-red-500/20 p-2 rounded border border-red-500 mt-2"
                        style="display: none;">
                        <span class="text-red-300 text-xs font-bold uppercase">Fiado (Deuda)</span>
                        <span class="font-black text-red-400">Bs. <span x-text="amountPending.toFixed(2)"></span></span>
                    </div>
                    <div x-show="amountPending < 0"
                        class="flex justify-between items-center bg-blue-500/20 p-2 rounded border border-blue-500 mt-2"
                        style="display: none;">
                        <span class="text-blue-300 text-xs font-bold uppercase">Cambio / Vuelto</span>
                        <span class="font-black text-blue-400">Bs. <span
                                x-text="Math.abs(amountPending).toFixed(2)"></span></span>
                    </div>

                    <div class="border-t border-gray-700 mt-3 pt-3 text-right">
                        <span class="text-xs text-gray-500">Tasa Dólar: Bs. {{ $exchangeRate ?? 1 }}</span>
                    </div>
                </div>

                <button @click.prevent="processOrder" :disabled="isSubmitting || !clientId || cart.length === 0"
                    :class="isSubmitting || !clientId || cart.length === 0 ? 'bg-gray-600 text-gray-400 cursor-not-allowed' :
                        'bg-emerald-500 text-gray-900 hover:bg-emerald-400 shadow-[0_0_15px_rgba(34,197,94,0.4)]'"
                    class="w-full mt-4 font-black py-4 rounded-xl transition-all uppercase text-lg">
                    <span x-text="isSubmitting ? 'Facturando...' : 'Generar Venta'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- SCRIPTS ALPINE JS --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                isSubmitting: false,
                alert: {
                    show: false,
                    type: '',
                    message: ''
                },

                // Configuración
                exchangeRate: {{ $exchangeRate ? $exchangeRate : 1 }},
                availablePaymentMethods: @json($paymentMethods),

                // Cliente
                clientIdentification: '',
                clientId: null,
                clientName: '',
                clientSearched: false,

                // Modalidad de venta
                isCreditSale: false,

                // Buscador Productos
                searchQuery: '',
                searchResults: [],

                // Carrito
                cart: [],
                payments: [],
                totalOrder: 0,
                amountReceived: 0,
                amountPending: 0,

                init() {
                    if (!this.isCreditSale && this.availablePaymentMethods.length > 0) {
                        this.addPaymentLine();
                    }
                },

                // ====== 1. BÚSQUEDA DE PRODUCTOS (AJAX) ======
                async searchProduct() {
                    if (!this.searchQuery) return;

                    try {
                        let response = await fetch(
                            `{{ route('admin.pos.products.search') }}?q=${this.searchQuery}`);
                        let result = await response.json();

                        if (result.exact) {
                            // Coincidencia exacta (Ej: Lector de Código de barras)
                            let prod = result.data;
                            let defaultBulk = prod.bulks.find(b => b.is_default) || prod.bulks[0];
                            this.addToCart(prod, defaultBulk);
                        } else {
                            // Búsqueda por nombre
                            this.searchResults = result.data;
                        }
                    } catch (error) {
                        console.error('Error buscando producto:', error);
                    }
                },

                addToCart(prod, bulk) {
                    let existingItem = this.cart.find(item => item.product_id === prod.id && item
                        .bulk_id === bulk.id);

                    if (existingItem) {
                        // Forzar a Number antes de incrementar (x-model devuelve strings)
                        existingItem.quantity = Number(existingItem.quantity) + 1;
                    } else {
                        this.cart.push({
                            product_id: prod.id,
                            bulk_id: bulk.id,
                            name: prod.name,
                            presentation: bulk.name,
                            price: bulk.sale_price,
                            quantity: 1,
                            conversion_factor: bulk.quantity,
                            unit_type: prod.unit_type, // 'unit' o 'gram'
                            allow_negative: prod.allow_negative_stock,
                            current_stock: prod.inventory ? prod.inventory.stock : 0,
                            subtotal: 0
                        });
                    }

                    this.searchQuery = '';
                    this.searchResults = [];
                    this.updateTotals();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.updateTotals();
                },

                // ====== 2. CLIENTES (AJAX) ======
                async searchClient() {
                    this.clientSearched = true;
                    if (!this.clientIdentification) {
                        this.clientId = null;
                        return;
                    }

                    try {
                        let response = await fetch(
                            `{{ route('admin.pos.clients.search') }}?q=${this.clientIdentification}`
                        );
                        let result = await response.json();

                        if (result.client) {
                            this.clientId = result.client.id;
                            this.clientName = result.client.name || 'Cliente Registrado';
                        } else {
                            this.clientId = null; // Muestra el botón de crear rápido
                        }
                    } catch (error) {
                        console.error(error);
                    }
                },

                async quickCreateClient() {
                    try {
                        let response = await fetch(`{{ route('admin.pos.clients.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                identification: this.clientIdentification
                            })
                        });

                        let result = await response.json();
                        if (result.success) {
                            this.clientId = result.client.id;
                            this.clientName = result.client.name;
                        }
                    } catch (error) {
                        alert('Error al crear el cliente.');
                    }
                },

                // ====== 3. PAGOS Y TOTALES ======

                // Elige el siguiente método de pago disponible diferente al anterior
                _nextPaymentMethodId(currentId) {
                    let others = this.availablePaymentMethods.filter(pm => pm.id !== currentId);
                    return others.length > 0 ? others[0].id : (this.availablePaymentMethods[0]?.id ||
                        '');
                },

                addPaymentLine() {
                    if (this.isCreditSale) return;
                    // Calcular lo que ya está cubierto por las líneas actuales
                    let alreadyAssigned = this.payments.reduce((sum, p) => sum + (parseFloat(p
                        .amount) || 0), 0);
                    let remaining = Math.max(0, this.totalOrder - alreadyAssigned);

                    // El nuevo método será distinto al último usado
                    let lastMethodId = this.payments.length > 0 ?
                        this.payments[this.payments.length - 1].payment_method_id :
                        null;

                    this.payments.push({
                        payment_method_id: this._nextPaymentMethodId(lastMethodId),
                        amount: remaining > 0 ? parseFloat(remaining.toFixed(2)) : '',
                        reference: ''
                    });

                    this.updateTotals();
                },

                removePaymentLine(index) {
                    this.payments.splice(index, 1);
                    this.redistributeRemainder();
                    this.updateTotals();
                },

                toggleCreditSale() {
                    if (this.isCreditSale) {
                        // activar fiado: limpiar pagos y recalcular
                        this.payments = [];
                        this.amountReceived = 0;
                        this.amountPending = parseFloat((this.totalOrder - this.amountReceived).toFixed(2));
                    } else {
                        // desactivar fiado: asegurar al menos una línea de pago
                        if (this.payments.length === 0 && this.availablePaymentMethods.length > 0) {
                            this.addPaymentLine();
                        }
                        this.updateTotals();
                    }
                },

                // Asigna el monto restante automáticamente al último método de pago
                redistributeRemainder() {
                    if (this.payments.length === 0) return;

                    // Sumar todo menos la última línea
                    let sumExceptLast = this.payments
                        .slice(0, -1)
                        .reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);

                    let remainder = parseFloat((this.totalOrder - sumExceptLast).toFixed(2));
                    this.payments[this.payments.length - 1].amount = remainder > 0 ? remainder : 0;
                },

                // Cuando el usuario edita manualmente el monto de un pago
                onPaymentInput(index) {
                    let isLast = index === this.payments.length - 1;

                    if (!isLast) {
                        // Si editó una línea que NO es la última → redistribuir restante al último
                        this.redistributeRemainder();
                    }
                    // Si editó la última línea, no sobreescribimos lo que escribió

                    // Recalcular totales finales
                    this.amountReceived = this.payments.reduce((sum, p) => sum + (parseFloat(p
                        .amount) || 0), 0);
                    this.amountPending = parseFloat((this.totalOrder - this.amountReceived).toFixed(2));
                },

                updateTotals() {
                    // 1. Recalcular subtotales del carrito
                    this.cart.forEach(item => {
                        item.quantity = Number(item.quantity) || 0;
                        item.subtotal = item.quantity * parseFloat(item.price);
                    });

                    this.totalOrder = this.cart.reduce((sum, item) => sum + item.subtotal, 0);

                    // 2. Si hay pagos, redistribuir el restante al último método
                    if (this.payments.length > 0) {
                        this.redistributeRemainder();
                    }

                    // 3. Recalcular totales de pago
                    this.amountReceived = this.payments.reduce((sum, p) => sum + (parseFloat(p
                        .amount) || 0), 0);
                    this.amountPending = parseFloat((this.totalOrder - this.amountReceived).toFixed(2));
                },

                // ====== 4. PROCESAR VENTA (AJAX FINAL) ======
                async processOrder() {
                    this.isSubmitting = true;

                    let payload = {
                        client_id: this.clientId,
                        cart: this.cart,
                        payments: this.payments,
                        exchange_rate: this.exchangeRate
                    };

                    try {
                        let response = await fetch(`{{ route('admin.orders.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        let result = await response.json();

                        if (result.success) {
                            this.alert = {
                                show: true,
                                type: 'success',
                                message: result.message
                            };
                            // Resetear para próxima venta
                            this.cart = [];
                            this.payments = [];
                            this.addPaymentLine();
                            this.clientIdentification = '';
                            this.clientId = null;
                            this.clientSearched = false;
                            this.updateTotals();

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        } else {
                            this.alert = {
                                show: true,
                                type: 'error',
                                message: result.message || 'Error en validación.'
                            };
                        }
                    } catch (error) {
                        this.alert = {
                            show: true,
                            type: 'error',
                            message: 'Error de conexión con el servidor.'
                        };
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }));
        });
    </script>
@endsection
