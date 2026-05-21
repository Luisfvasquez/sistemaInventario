<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                    {{ __('Catálogo de Productos') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Explora nuestras existencias, añade al carrito y realiza tus
                    pedidos en línea.</p>
            </div>

            @if ($exchangeRate)
                <div
                    class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm">
                    <svg class="w-4.5 h-4.5 text-indigo-500 animate-spin-slow" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Tasa de Cambio: <span
                        class="text-slate-800 font-extrabold">{{ number_format($exchangeRate, 2, ',', '.') }} BS /
                        $</span>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Componente Maestro en Alpine.js -->
    <div x-data="storeCart()" class="py-8 bg-slate-50 min-h-screen relative overflow-hidden"
        @keydown.window.escape="cartOpen = false">
        @php
            // Evitamos división por cero y aseguramos formato decimal para JS
            $safeRate = $exchangeRate ? (float) str_replace(',', '.', $exchangeRate) : 1;
            if ($safeRate <= 0) {
                $safeRate = 1;
            }
        @endphp
        <!-- Alertas de éxito y error -->
        @if (session('success'))
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

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
                            <span class="font-semibold">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">

                <!-- Columna Izquierda: Filtros y Productos -->
                <div class="flex-1 space-y-6">
                    <!-- Barra de Búsqueda y Filtros de Categoría -->
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <!-- Campo de Búsqueda -->
                        <div class="relative w-full md:w-80">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input x-model="searchQuery" type="text" placeholder="Buscar por nombre o marca..."
                                class="pl-10 pr-4 py-2.5 w-full bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                        </div>

                        <!-- Filtros de Categoría -->
                        <div
                            class="flex flex-nowrap md:flex-wrap gap-2 justify-start md:justify-end w-full md:w-auto overflow-x-auto pb-2 md:pb-0 whitespace-nowrap scrollbar-none">
                            <button @click="selectedCategory = 'all'"
                                :class="selectedCategory === 'all' ? 'bg-indigo-600 text-white shadow-md' :
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 shrink-0">
                                Todas
                            </button>
                            @foreach ($categories as $category)
                                <button @click="selectedCategory = '{{ $category->id }}'"
                                    :class="selectedCategory === '{{ $category->id }}' ? 'bg-indigo-600 text-white shadow-md' :
                                        'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 shrink-0">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Grid de Tarjetas de Productos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            <!-- Tarjeta de Producto Individual -->
                            <div x-show="filterProduct(@js($product))"
                                class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden transform hover:-translate-y-1 group">
                                <div class="relative">
                                    <!-- Imagen o Marcador de Posición -->
                                    <div
                                        class="h-44 w-full bg-gradient-to-br from-indigo-50 to-slate-100 flex items-center justify-center relative overflow-hidden">
                                        @if ($product->images->count() > 0)
                                            <img src="/storage/{{ $product->images->first()->path }}"
                                                alt="{{ $product->name }}"
                                                class="object-cover h-full w-full group-hover:scale-105 transition-transform duration-500" />
                                        @else
                                            <svg class="w-16 h-16 text-slate-300 group-hover:scale-110 transition-transform duration-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                </path>
                                            </svg>
                                        @endif

                                        <!-- Distintivo de Categoría -->
                                        <span
                                            class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-indigo-700 font-extrabold text-xxs px-2.5 py-1 rounded-lg shadow-sm border border-indigo-100">
                                            {{ $product->category->name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Datos del Producto -->
                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start gap-2">
                                            <h4
                                                class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-indigo-600 transition-colors">
                                                {{ $product->name }}
                                            </h4>
                                            @if ($product->brand)
                                                <span
                                                    class="text-xxs font-bold text-slate-400 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded uppercase">
                                                    {{ $product->brand }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                                            {{ $product->description ?? 'Sin descripción disponible.' }}
                                        </p>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-slate-50">
                                        <!-- Existencia/Stock -->
                                        <div class="flex justify-between items-center text-xs mb-3">
                                            <span class="text-slate-400 font-medium">Disponibilidad:</span>
                                            @if ($product->track_inventory)
                                                @php
                                                    $availableQty = $product->inventory
                                                        ? $product->inventory->stock
                                                        : 0;
                                                    // si es pesable se muestra en kilos
                                                    $displayQty =
                                                        $product->unit_type === 'gram'
                                                            ? $availableQty / 1000
                                                            : $availableQty;
                                                    $lbl = $product->unit_type === 'gram' ? ' Kgs' : ' Unds';
                                                @endphp
                                                @if ($availableQty > 0)
                                                    <span
                                                        class="font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                                                        {{ number_format($displayQty, $product->unit_type === 'gram' ? 3 : 0, ',', '.') }}{{ $lbl }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="font-extrabold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-lg border border-rose-100">
                                                        Sin Stock
                                                    </span>
                                                @endif
                                            @else
                                                <span
                                                    class="font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                                                    Ilimitado
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Precios -->
                                        <div class="flex justify-between items-baseline mb-4">
                                            <div>
                                                <p class="text-slate-400 text-xxs font-bold uppercase tracking-wider">
                                                    Precio USD</p>
                                                <span class="text-xl font-black text-slate-800">
                                                    ${{ number_format($product->display_price / $safeRate, 2, ',', '.') }}
                                                </span>
                                                <span
                                                    class="text-slate-500 font-bold text-xs">{{ $product->unit_label }}</span>
                                            </div>

                                            @if ($exchangeRate)
                                                <div class="text-right">
                                                    <p
                                                        class="text-slate-400 text-xxs font-bold uppercase tracking-wider">
                                                        Precio Local</p>
                                                    <span class="text-sm font-extrabold text-indigo-700">
                                                        {{ number_format($product->display_price, 2, ',', '.') }} BS
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Añadir al Carrito con Alpine.js -->
                                        @php
                                            $stockMax = $product->track_inventory
                                                ? ($product->inventory
                                                    ? $product->inventory->stock
                                                    : 0)
                                                : 999999;
                                            // si es gramo, el stock max en kilos es stockMax / 1000
                                            if ($product->unit_type === 'gram') {
                                                $stockMax = $stockMax / 1000;
                                            }
                                        @endphp

                                        @if (
                                            !$product->track_inventory ||
                                                ($product->inventory && $product->inventory->stock > 0) ||
                                                $product->allow_negative_stock)
                                            <div class="flex items-center gap-2" x-data="{ qty: {{ $product->unit_type === 'gram' ? '0.25' : '1' }} }">
                                                <div
                                                    class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden">
                                                    <button
                                                        @click="qty = Math.max({{ $product->unit_type === 'gram' ? '0.05' : '1' }}, qty - {{ $product->unit_type === 'gram' ? '0.05' : '1' }})"
                                                        class="px-2 py-1.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xs transition-colors">
                                                        -
                                                    </button>
                                                    <input type="number"
                                                        step="{{ $product->unit_type === 'gram' ? '0.01' : '1' }}"
                                                        min="{{ $product->unit_type === 'gram' ? '0.01' : '1' }}"
                                                        max="{{ $stockMax }}" x-model.number="qty"
                                                        class="w-12 text-center bg-transparent border-none text-xs font-bold text-slate-800 p-0 focus:ring-0" />
                                                    <button
                                                        @click="qty = Math.min({{ $stockMax }}, qty + {{ $product->unit_type === 'gram' ? '0.05' : '1' }})"
                                                        class="px-2 py-1.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xs transition-colors">
                                                        +
                                                    </button>
                                                </div>

                                                <button @click="addToCart(@js($product), qty)"
                                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl shadow-sm hover:shadow transition-all duration-300 inline-flex items-center justify-center gap-1.5 text-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                                        </path>
                                                    </svg>
                                                    Agregar
                                                </button>
                                            </div>
                                        @else
                                            <button disabled
                                                class="w-full bg-slate-100 text-slate-400 font-bold py-2 rounded-xl text-xs cursor-not-allowed">
                                                No disponible
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Botón de apertura de carrito flotante (Solo móvil/tablet cuando está cerrado) -->
                <button @click="cartOpen = true"
                    class="fixed bottom-6 right-6 lg:hidden bg-indigo-600 text-white p-4 rounded-full shadow-2xl z-40 hover:bg-indigo-700 transform hover:scale-105 active:scale-95 transition-all duration-200 flex items-center justify-center">
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span x-show="totalItemsCount() > 0" x-text="totalItemsCount()"
                            class="absolute -top-3.5 -right-3.5 bg-rose-600 text-white font-extrabold text-xxs w-5.5 h-5.5 rounded-full flex items-center justify-center border-2 border-indigo-600 shadow animate-pulse"></span>
                    </div>
                </button>

                <!-- Backdrop del carrito móvil con desenfoque de fondo premium -->
                <div x-cloak x-show="cartOpen" @click="cartOpen = false"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-45 lg:hidden"></div>

                <!-- Columna Derecha: Carrito Lateral (Estilo Cajón) -->
                <div x-cloak x-show="cartOpen || isDesktop"
                    :class="cartOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
                    class="fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-50 lg:z-10 lg:static lg:w-80 lg:shadow-sm lg:rounded-2xl border lg:border-slate-100 transition-transform duration-300 ease-in-out flex flex-col justify-between overflow-hidden">

                    <!-- Cabecera del Carrito -->
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <svg class="w-5.5 h-5.5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <h3 class="font-extrabold text-slate-800 text-base">Carrito de Compra</h3>
                        </div>

                        <!-- Botón de cierre en Móvil -->
                        <button @click="cartOpen = false"
                            class="lg:hidden p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Items del Carrito -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 division-y division-slate-100">
                        <template x-if="cart.length === 0">
                            <div
                                class="text-center py-12 text-slate-400 flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="text-sm font-semibold">Tu carrito está vacío.</span>
                                <span class="text-xs">¡Agrega productos desde la izquierda!</span>
                            </div>
                        </template>

                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="flex gap-4 py-3 items-center justify-between border-b border-slate-50">
                                <div class="flex-1 min-w-0">
                                    <span x-text="item.name"
                                        class="font-bold text-slate-800 text-sm block truncate"></span>
                                    <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block"
                                        x-text="item.category_name"></span>

                                    <!-- Cantidades -->
                                    <div class="flex items-center gap-2 mt-2">
                                        <div
                                            class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                                            <button
                                                @click="changeQtyInCart(item.id, item.unit_type === 'gram' ? -0.05 : -1)"
                                                class="px-1.5 py-0.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xxs transition-colors">-</button>
                                            <span class="w-10 text-center font-bold text-slate-800 text-xs"
                                                x-text="formatQuantity(item)"></span>
                                            <button
                                                @click="changeQtyInCart(item.id, item.unit_type === 'gram' ? 0.05 : 1)"
                                                class="px-1.5 py-0.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xxs transition-colors">+</button>
                                        </div>
                                        <span class="text-slate-400 text-xxs font-bold"
                                            x-text="item.unit_label"></span>
                                    </div>
                                </div>

                                <div class="text-right flex flex-col justify-between items-end h-full min-w-24">
                                    <!-- Botón eliminar -->
                                    <button @click="removeFromCart(item.id)"
                                        class="text-slate-300 hover:text-rose-600 transition-colors p-1 rounded-lg hover:bg-rose-50 mb-2">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                    <div>
                                        <span class="font-extrabold text-slate-800 text-sm block"
                                            x-text="formatCurrency(calculateItemTotal(item))"></span>
                                        <div>
                                            <span class="font-extrabold text-slate-800 text-sm block"
                                                x-text="formatCurrency(calculateItemTotal(item) / {{ $safeRate }})"></span>
                                            @if ($exchangeRate)
                                                <span class="text-indigo-600 font-bold text-xxs block"
                                                    x-text="formatCurrency(calculateItemTotal(item), ' BS')"></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Pie del Carrito / Totales -->
                    <div class="p-6 border-t border-slate-100 bg-slate-50/50 space-y-4">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm font-semibold text-slate-500">
                                <span>Subtotal</span>
                                <span x-text="formatCurrency(calculateTotal() / {{ $safeRate }})"
                                    class="text-slate-800"></span>
                            </div>

                            <hr class="border-slate-100" />

                            <div class="flex justify-between items-end">
                                <div>
                                    <span class="text-base font-extrabold text-slate-800 block">Total a Pagar</span>
                                    @if ($exchangeRate)
                                        <span x-text="formatCurrency(calculateTotal(), ' BS')"
                                            class="text-xs text-indigo-600 font-bold block"></span>
                                    @endif
                                </div>
                                <span x-text="formatCurrency(calculateTotal() / {{ $safeRate }})"
                                    class="text-2xl font-black text-slate-800"></span>
                            </div>
                        </div>

                        <!-- Botón de Checkout -->
                        <a :href="cart.length > 0 ? '{{ route('client.checkout.view') }}' : '#'" :class="cart.length === 0 ? 'pointer-events-none from-slate-100 to-slate-100 text-slate-400' : 'from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white cursor-pointer shadow-indigo-600/10 hover:shadow-indigo-600/20 active:scale-98'" class="w-full bg-gradient-to-r font-extrabold py-3.5 px-4 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                            <span>Proceder a Pagar</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
      
    </div>

    <!-- Script de Alpine.js para controlar el Carrito de Compras -->
    <script>
        function storeCart() {
            return {
                cartOpen: false,
                checkoutOpen: false,
                searchQuery: '',
                selectedCategory: 'all',
                cart: [],
                isDesktop: window.innerWidth >= 1024,

                init() {
                    // Cargar el carrito desde localStorage al iniciar
                    const cached = localStorage.getItem('client_shopping_cart');
                    if (cached) {
                        try {
                            this.cart = JSON.parse(cached);
                        } catch (e) {
                            this.cart = [];
                        }
                    }

                    // Escuchar cambios de tamaño de ventana para actualizar isDesktop
                    window.addEventListener('resize', () => {
                        this.isDesktop = window.innerWidth >= 1024;
                    });
                },

                persistCart() {
                    localStorage.setItem('client_shopping_cart', JSON.stringify(this.cart));
                },

                // Filtro dinámico de productos por barra de búsqueda y categoría
                filterProduct(product) {
                    const matchQuery = product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        (product.brand && product.brand.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    const matchCategory = this.selectedCategory === 'all' || product.category_id.toString() === this
                        .selectedCategory;

                    return matchQuery && matchCategory;
                },

                // Añadir un producto al carrito
                addToCart(product, qty) {
                    const parsedQty = parseFloat(qty);
                    if (isNaN(parsedQty) || parsedQty <= 0) return;

                    const existing = this.cart.find(item => item.id === product.id);

                    // Validar stock disponible
                    if (product.track_inventory && !product.allow_negative_stock) {
                        const availableStock = product.inventory ? parseFloat(product.inventory.stock) : 0;
                        const availableQtyCommercial = product.unit_type === 'gram' ? (availableStock / 1000) :
                            availableStock;
                        const currentInCart = existing ? existing.quantity : 0;

                        if (currentInCart + parsedQty > availableQtyCommercial) {
                            alert("El producto '" + product.name + "' no cuenta con inventario suficiente. Disponible: " +
                                availableQtyCommercial.toLocaleString('es-VE') + " " + (product.unit_type === 'gram' ?
                                    'Kgs' : 'Unds'));
                            return;
                        }
                    }

                    if (existing) {
                        existing.quantity = parseFloat((existing.quantity + parsedQty).toFixed(3));
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            category_name: product.category ? product.category.name : 'Varios',
                            unit_type: product.unit_type,
                            unit_label: product.unit_label,
                            display_price: parseFloat(product.display_price),
                            price: parseFloat(product.price),
                            quantity: parsedQty,
                            track_inventory: product.track_inventory,
                            allow_negative_stock: product.allow_negative_stock,
                            inventory_stock: product.inventory ? parseFloat(product.inventory.stock) : 0
                        });
                    }

                    this.persistCart();
                    this.cartOpen = true; // abrir el carrito para retroalimentar
                },

                // Eliminar un producto del carrito
                removeFromCart(id) {
                    this.cart = this.cart.filter(item => item.id !== id);
                    this.persistCart();
                },

                // Modificar cantidad directamente en el carrito
                changeQtyInCart(id, diff) {
                    const item = this.cart.find(item => item.id === id);
                    if (!item) return;

                    let amount = parseFloat(diff);
                    let newQty = parseFloat((item.quantity + amount).toFixed(3));

                    if (newQty <= 0) {
                        this.removeFromCart(id);
                    } else {
                        // Validar stock disponible
                        if (item.track_inventory && !item.allow_negative_stock) {
                            const availableQtyCommercial = item.unit_type === 'gram' ? (item.inventory_stock / 1000) : item
                                .inventory_stock;
                            if (newQty > availableQtyCommercial) {
                                alert("No puedes exceder el stock disponible: " + availableQtyCommercial.toLocaleString(
                                    'es-VE') + " " + (item.unit_type === 'gram' ? 'Kgs' : 'Unds'));
                                return;
                            }
                        }
                        item.quantity = newQty;
                        this.persistCart();
                    }
                },

                // Obtener recuento de productos agregados
                totalItemsCount() {
                    return this.cart.length;
                },

                // Calcular subtotal de cada ítem
                calculateItemTotal(item) {
                    return item.display_price * item.quantity;
                },

                // Calcular total general del carrito
                calculateTotal() {
                    return this.cart.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                },

                // Formatear monedas estéticamente
                formatCurrency(value, suffix = ' $') {
                    return value.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + suffix;
                },

                // Formatear cantidades para legibilidad (gramos vs unidades)
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

                // Mapear los datos limpios del carrito para enviar al servidor
                prepareCartForSubmit() {
                    return this.cart.map(item => ({
                        id: item.id,
                        quantity: item.quantity
                    }));
                }
            };
        }
    </script>

    <style>
        .animate-spin-slow {
            animation: spin 8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
