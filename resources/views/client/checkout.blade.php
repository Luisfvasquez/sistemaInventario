<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.products') }}"
                class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                    {{ __('Finalizar Compra') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Verifica tus productos, elige tu método de entrega y reporta tu
                    pago.</p>
            </div>
        </div>
    </x-slot>

    @php
        $safeRate = isset($exchangeRate) && $exchangeRate > 0 ? (float) str_replace(',', '.', $exchangeRate) : 1;
    @endphp

    <div x-data="checkoutManager()" x-init="init()" class="py-8 bg-slate-50 min-h-screen">

        @if ($errors->any())
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
                <div
                    class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex flex-col gap-1 shadow-sm">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <span class="font-semibold text-sm">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('client.checkout') }}" method="POST" enctype="multipart/form-data"
                @submit="setTimeout(() => isSubmitting = true, 50)" class="flex flex-col lg:flex-row gap-8">

                @csrf

                <input type="hidden" name="cart_items" :value="JSON.stringify(prepareCartForSubmit())" />
                <div class="flex-1 space-y-6">

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div
                                class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                                <span class="font-extrabold">1</span>
                            </div>
                            <h3 class="font-extrabold text-slate-800 text-lg">Método de Entrega</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-400 focus:outline-none"
                                :class="{ 'ring-2 ring-indigo-600 border-indigo-600 bg-indigo-50/30': deliveryType === 'store_pickup' }">
                                <input type="radio" name="delivery_type" value="store_pickup" x-model="deliveryType"
                                    class="sr-only">
                                <div class="flex flex-1 items-center justify-between">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-bold text-slate-900">Retiro en Tienda</span>
                                        <span class="mt-1 flex items-center text-xs text-slate-500">Busca tu pedido sin
                                            costo adicional.</span>
                                    </div>
                                    <svg x-show="deliveryType === 'store_pickup'" class="h-5 w-5 text-indigo-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>

                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-400 focus:outline-none"
                                :class="{ 'ring-2 ring-indigo-600 border-indigo-600 bg-indigo-50/30': deliveryType === 'delivery' }">
                                <input type="radio" name="delivery_type" value="delivery" x-model="deliveryType"
                                    class="sr-only">
                                <div class="flex flex-1 items-center justify-between">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-bold text-slate-900">Delivery</span>
                                        <span class="mt-1 flex items-center text-xs text-slate-500">Recibe en tu
                                            dirección.</span>
                                    </div>
                                    <svg x-show="deliveryType === 'delivery'" class="h-5 w-5 text-indigo-600"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
                        </div>

                        <div x-show="deliveryType === 'delivery'" x-transition class="space-y-2 pt-2">
                            <label
                                class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Dirección
                                Detallada</label>
                            <textarea name="delivery_address" rows="2" placeholder="Ej: Av. Principal, Urb. Centro, Casa Nro 45..."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">{{ old('delivery_address', $client->address) }}</textarea>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div
                                class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                                <span class="font-extrabold">2</span>
                            </div>
                            <h3 class="font-extrabold text-slate-800 text-lg">Reporte de Pago</h3>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">¿A dónde
                                transferiste?</label>
                            <select name="payment_method_id" x-model="selectedMethod"
                                @change="updateMethodDescription()" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-semibold text-slate-700 transition-all outline-none">
                                <option value="" disabled selected>Selecciona una cuenta receptora...</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" data-desc="{{ $pm->description }}">
                                        {{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="methodDescription !== ''" x-transition
                            class="bg-indigo-50 border border-indigo-100/50 rounded-xl p-4 text-xs text-indigo-900 whitespace-pre-wrap font-mono"
                            x-text="methodDescription"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="space-y-2">
                                <label
                                    class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Número
                                    de Referencia</label>
                                <input type="text" name="reference" required value="{{ old('reference') }}" maxlength="50"
                                    placeholder="Ej: 001456228"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold transition-all outline-none" />
                            </div>

                            <div class="space-y-2">
                                <label
                                    class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Comprobante
                                    (Imagen)</label>
                                <input type="file" name="payment_proof"
                                    accept="image/png, image/jpeg, image/jpg, image/webp" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:bg-white text-xs font-semibold text-slate-500 transition-all outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200" />
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Notas
                                Adicionales (Opcional)</label>
                            <textarea name="notes" rows="2" placeholder="Alguna indicación sobre tu pago o pedido..."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-96 shrink-0">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 sticky top-6">
                        <h3 class="font-extrabold text-slate-800 text-lg mb-4 border-b border-slate-100 pb-3">Resumen
                            de Compra</h3>

                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 pr-2 mb-4 scrollbar-thin">
                            <template x-for="item in cart" :key="item.id">
                                <div class="py-3 flex justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <span x-text="item.name"
                                            class="text-sm font-bold text-slate-800 block truncate"></span>
                                        <span class="text-xs font-semibold text-slate-400">
                                            <span x-text="formatQuantity(item)"></span> x <span
                                                x-text="formatCurrency(item.display_price)"></span> BS
                                        </span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span x-text="formatCurrency(calculateItemTotal(item)) + ' BS'"
                                            class="font-extrabold text-slate-800 text-sm block"></span>
                                        @if ($exchangeRate)
                                            <span
                                                x-text="'$' + formatCurrency(calculateItemTotal(item) / {{ $safeRate }})"
                                                class="text-indigo-600 font-bold text-xxs block"></span>
                                        @endif
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="bg-indigo-50/50 border border-indigo-100/50 rounded-xl p-4 space-y-3">
                            <div class="flex justify-between items-center text-sm font-semibold text-indigo-900/70">
                                <span>Cant. de Ítems</span>
                                <span x-text="cart.length"></span>
                            </div>

                            <hr class="border-indigo-100/50" />

                            <div class="flex justify-between items-end">
                                <span class="text-sm font-extrabold text-slate-800 block">Total a Pagar</span>
                                <div class="text-right">
                                    <span x-text="formatCurrency(calculateTotal()) + ' BS'"
                                        class="text-2xl font-black text-slate-900 block leading-none"></span>
                                    @if ($exchangeRate)
                                        <span
                                            x-text="'≈ $' + formatCurrency(calculateTotal() / {{ $safeRate }}) + ' USD'"
                                            class="font-bold text-indigo-700 text-xs block mt-1"></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <button type="submit" :disabled="cart.length === 0 || isSubmitting"
                            class="w-full mt-6 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">

                            <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>

                            <svg x-cloak x-show="isSubmitting" class="w-5 h-5 animate-spin"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Procesando envío...' : 'Confirmar Pago y Pedido'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function checkoutManager() {
            return {
                cart: [],
                deliveryType: 'store_pickup',
                selectedMethod: '{{ old('payment_method_id') }}',
                methodDescription: '',
                isSubmitting: false,

                init() {
                    // Cargar carrito desde localstorage
                    const cached = localStorage.getItem('client_shopping_cart');
                    if (cached) {
                        try {
                            this.cart = JSON.parse(cached);
                        } catch (e) {
                            this.cart = [];
                        }
                    }

                    // Si el carrito está vacío, regresar a tienda
                    if (this.cart.length === 0) {
                        window.location.href = "{{ route('client.products') }}";
                    }

                    // Inicializar descripción de método si hubo un error de validación y regresó con old()
                    this.$nextTick(() => {
                        this.updateMethodDescription();
                    });
                },

                updateMethodDescription() {
                    if (!this.selectedMethod) {
                        this.methodDescription = '';
                        return;
                    }
                    // Buscar el option seleccionado y extraer su data-desc
                    const select = document.querySelector('select[name="payment_method_id"]');
                    const option = select.options[select.selectedIndex];
                    this.methodDescription = option.getAttribute('data-desc') ||
                        'No hay instrucciones adicionales para este método.';
                },

                calculateItemTotal(item) {
                    return item.display_price * item.quantity;
                },

                calculateTotal() {
                    return this.cart.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                },

                formatCurrency(value) {
                    return parseFloat(value).toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                formatQuantity(item) {
                    if (item.unit_type === 'gram') {
                        return item.quantity.toLocaleString('es-VE', {
                            minimumFractionDigits: 3,
                            maximumFractionDigits: 3
                        }) + ' Kg';
                    }
                    return item.quantity.toLocaleString('es-VE', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }) + ' Und';
                },

                prepareCartForSubmit() {
                    return this.cart.map(item => ({
                        id: item.id,
                        quantity: item.quantity
                    }));
                }
            };
        }
    </script>
</x-app-layout>
