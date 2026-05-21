<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'E-Shop') }} - Tienda Virtual</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind Play CDN (Provides beautiful styling instantly without build steps) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    fontSize: {
                        'xxs': '0.7rem',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        .glass-navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .animate-spin-slow {
            animation: spin 8s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-indigo-500 selection:text-white" x-data="storefrontCart()" x-cloak @keydown.window.escape="cartOpen = false">
    @php
        $safeRate = $exchangeRate ? (float) str_replace(',', '.', $exchangeRate) : 1;
        if ($safeRate <= 0) { $safeRate = 1; }
    @endphp

    <!-- NAVBAR GLASSMORPHIC -->
    <nav class="glass-navbar border-b border-slate-100/80 sticky top-0 z-40 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logotipo / Home -->
                <div class="flex items-center gap-3">
                    <a href="/" class="flex items-center gap-2.5 group">
                        <div class="w-11 h-11 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-500/10 group-hover:scale-105 transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <span class="font-outfit font-black text-xl tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent group-hover:text-indigo-600 transition-colors">
                            {{ config('app.name', 'E-Shop') }}
                        </span>
                    </a>
                </div>

                <!-- Buscador de Escritorio Rápido -->
                <div class="hidden md:flex items-center flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input x-model="searchQuery" type="text" placeholder="Buscar productos, marcas..." class="pl-10 pr-4 py-2.5 w-full bg-slate-100/80 border border-slate-200/60 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm transition-all duration-300 outline-none" />
                    </div>
                </div>

                <!-- Menú y Acciones -->
                <div class="flex items-center gap-4">
                    <!-- Botón del Carrito -->
                    <button @click="cartOpen = true" class="relative p-2.5 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-2xl transition-all duration-300 group">
                        <svg class="w-6.5 h-6.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span x-show="totalItemsCount() > 0" x-text="totalItemsCount()" class="absolute top-1 right-1 bg-indigo-600 text-white font-extrabold text-xxs w-5.5 h-5.5 rounded-full flex items-center justify-center border-2 border-white shadow-md animate-bounce"></span>
                    </button>

                    <!-- Enlaces de Autenticación -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-2">
                            @auth
                                @if(Auth::user()->role === 'client')
                                    <a href="{{ route('client.dashboard') }}" class="hidden sm:inline-flex bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs tracking-wide shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/25 transition-all duration-300 items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Mi Panel
                                    </a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex bg-slate-900 hover:bg-black text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs tracking-wide shadow-md transition-all duration-300 items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                                        Panel Admin
                                    </a>
                                @endif

                                <!-- Formulario de Cerrar Sesión -->
                                <form method="POST" action="{{ route('logout') }}" class="inline" onsubmit="localStorage.removeItem('client_shopping_cart')">
                                    @csrf
                                    <button type="submit" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-2xl transition-all duration-300" title="Cerrar Sesión">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="text-slate-600 hover:text-indigo-600 font-bold px-4 py-2.5 text-xs transition-colors">
                                    Iniciar Sesión
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs tracking-wide shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/25 transition-all duration-300">
                                        Registrarse
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION BANNER -->
    <header class="relative bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 py-16 sm:py-20 text-white overflow-hidden shadow-lg mb-8">
        <!-- Luces Decorativas de Fondo -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl -ml-20 -mb-20"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Info Text -->
                <div class="lg:col-span-8 space-y-6 text-center lg:text-left">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/10 border border-indigo-400/20 rounded-full text-indigo-400 text-xxs font-extrabold uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Tienda Oficial Abierta
                    </span>
                    <h1 class="font-outfit font-black text-4xl sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                        Descubre los Mejores <br />
                        <span class="bg-gradient-to-r from-indigo-400 via-pink-400 to-amber-300 bg-clip-text text-transparent">Productos Premium</span>
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 font-medium">
                        Explora nuestro catálogo completo de existencias en tiempo real. Añade los productos de tu preferencia al carrito y completa tu orden de forma rápida.
                    </p>
                    <div class="pt-4 flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="#catalogo" class="bg-white hover:bg-slate-100 text-slate-950 font-extrabold px-6 py-3.5 rounded-2xl text-xs tracking-wider shadow-md hover:shadow-lg transition-all duration-300">
                            Comenzar a Comprar
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="bg-indigo-600/30 hover:bg-indigo-600/40 text-white font-extrabold px-6 py-3.5 rounded-2xl text-xs tracking-wider border border-white/10 hover:border-white/20 transition-all duration-300">
                                Crear una Cuenta Gratis
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- Tasa de Cambio & Estadísticas Rápidas -->
                <div class="lg:col-span-4 flex justify-center">
                    <div class="bg-white/5 border border-white/10 p-6 sm:p-8 rounded-3xl backdrop-blur-md w-full max-w-sm text-center space-y-6 shadow-2xl relative">
                        <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-wider text-white shadow-md">
                            Tasa del Día
                        </div>
                        
                        @if($exchangeRate)
                            <div>
                                <span class="text-slate-400 text-xxs font-extrabold uppercase tracking-widest block">Tasa de Cambio Oficial</span>
                                <span class="text-3xl sm:text-4xl font-outfit font-black text-white mt-1 block">
                                    {{ number_format($exchangeRate, 2, ',', '.') }} BS
                                </span>
                                <span class="text-emerald-400 text-xs font-bold mt-1 inline-flex items-center gap-1">
                                    <svg class="w-4 h-4 text-emerald-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Equivalencia por cada 1 USD
                                </span>
                            </div>
                        @else
                            <div>
                                <span class="text-slate-400 text-xxs font-extrabold uppercase tracking-widest block">Tasa de Cambio Oficial</span>
                                <span class="text-3xl font-outfit font-black text-white mt-1 block">1,00 BS</span>
                                <span class="text-slate-400 text-xs mt-1 block">Tasa por defecto</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-white/5 pt-5 grid grid-cols-2 gap-4 text-left">
                            <div>
                                <span class="text-slate-400 text-xxs font-bold uppercase tracking-wider">Productos</span>
                                <span class="text-white font-extrabold text-lg block mt-0.5">{{ $products->count() }} Activos</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-xxs font-bold uppercase tracking-wider">Categorías</span>
                                <span class="text-white font-extrabold text-lg block mt-0.5">{{ $categories->count() }} Disponibles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CUERPO PRINCIPAL / TIENDA -->
    <main id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        
        <!-- Notificaciones de Éxito / Error -->
        @if(session('success'))
            <div class="mb-8">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8">
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex flex-col gap-1 shadow-sm">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="font-semibold">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- CONTENEDOR IZQUIERDO: CATÁLOGO Y FILTROS -->
            <div class="flex-1 space-y-8">
                
                <!-- Buscador de Pantalla Pequeña, Búsqueda y Filtros de Categoría -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="font-outfit font-black text-xl text-slate-800 tracking-tight">Catálogo de Productos</h2>
                            <p class="text-xxs text-slate-500 mt-0.5">Explora nuestros artículos, marca tus cantidades y agrégalos al carro.</p>
                        </div>

                        <!-- Buscador Móvil / Duplicado -->
                        <div class="relative w-full md:w-80 md:hidden">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input x-model="searchQuery" type="text" placeholder="Buscar productos..." class="pl-10 pr-4 py-2.5 w-full bg-slate-50 border border-slate-200/60 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                        </div>
                    </div>

                    <!-- Píldoras de Categorías en Carrusel Deslizable -->
                    <div class="border-t border-slate-50 pt-4">
                        <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-2.5">Filtrar por Categoría</span>
                        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none whitespace-nowrap -mx-6 px-6">
                            <button @click="selectedCategory = 'all'" :class="selectedCategory === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-800 border border-slate-100'" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-200 shrink-0 cursor-pointer">
                                Todas las Categorías
                            </button>
                            @foreach($categories as $category)
                                <button @click="selectedCategory = '{{ $category->id }}'" :class="selectedCategory === '{{ $category->id }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-800 border border-slate-100'" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-200 shrink-0 cursor-pointer">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PRODUCT GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @forelse($products as $product)
                        <!-- Tarjeta de Producto -->
                        <div x-show="filterProduct(@js($product))" class="bg-white rounded-3xl shadow-sm border border-slate-100/80 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                            
                            <!-- Cabecera de la Tarjeta (Imagen o Marcador) -->
                            <div class="relative">
                                <div class="h-48 w-full bg-gradient-to-br from-indigo-50 to-slate-100 flex items-center justify-center relative overflow-hidden">
                                    @if($product->images->count() > 0)
                                        <img src="/storage/{{ $product->images->first()->path }}" alt="{{ $product->name }}" class="object-cover h-full w-full group-hover:scale-105 transition-transform duration-500" />
                                    @else
                                        <svg class="w-16 h-16 text-slate-300 group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    @endif
                                    
                                    <!-- Distintivo de Categoría -->
                                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm text-indigo-700 font-extrabold text-[10px] px-3 py-1 rounded-xl shadow-sm border border-indigo-100/50">
                                        {{ $product->category->name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Información y Acciones -->
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-outfit font-extrabold text-slate-800 text-base leading-tight group-hover:text-indigo-600 transition-colors line-clamp-1">
                                            {{ $product->name }}
                                        </h4>
                                        @if($product->brand)
                                            <span class="text-[9px] font-extrabold text-slate-400 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded uppercase tracking-wider shrink-0">
                                                {{ $product->brand }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400 line-clamp-2 h-8">
                                        {{ $product->description ?? 'Sin descripción disponible.' }}
                                    </p>
                                </div>

                                <div class="mt-5 pt-4 border-t border-slate-50 space-y-4">
                                    <!-- Disponibilidad / Existencia -->
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-400 font-medium">Disponibilidad</span>
                                        @if($product->track_inventory)
                                            @php
                                                $availableQty = $product->inventory ? $product->inventory->stock : 0;
                                                // si es pesable se muestra en kilos
                                                $displayQty = $product->unit_type === 'gram' ? ($availableQty / 1000) : $availableQty;
                                                $lbl = $product->unit_type === 'gram' ? ' Kgs' : ' Unds';
                                            @endphp
                                            @if($availableQty > 0)
                                                <span class="font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                                                    {{ number_format($displayQty, $product->unit_type === 'gram' ? 3 : 0, ',', '.') }}{{ $lbl }}
                                                </span>
                                            @else
                                                <span class="font-extrabold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-lg border border-rose-100">
                                                    Agotado
                                                </span>
                                            @endif
                                        @else
                                            <span class="font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                                                Ilimitado
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Bloque de Precios -->
                                    <div class="flex justify-between items-baseline bg-slate-50/50 p-3 rounded-2xl">
                                        <div>
                                            <p class="text-slate-400 text-[9px] font-extrabold uppercase tracking-wider">Precio USD</p>
                                            <span class="text-lg font-black text-slate-800">
                                                ${{ number_format($product->display_price / $safeRate, 2, ',', '.') }}
                                            </span>
                                            <span class="text-slate-500 font-bold text-xxs">{{ $product->unit_label }}</span>
                                        </div>

                                        @if($exchangeRate)
                                            <div class="text-right">
                                                <p class="text-slate-400 text-[9px] font-extrabold uppercase tracking-wider">Precio Local</p>
                                                <span class="text-sm font-extrabold text-indigo-600">
                                                    {{ number_format($product->display_price, 2, ',', '.') }} BS
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Acciones de Carrito -->
                                    @php
                                        $stockMax = $product->track_inventory ? ($product->inventory ? $product->inventory->stock : 0) : 999999;
                                        if ($product->unit_type === 'gram') {
                                            $stockMax = $stockMax / 1000;
                                        }
                                    @endphp
                                    
                                    @if(!$product->track_inventory || ($product->inventory && $product->inventory->stock > 0) || $product->allow_negative_stock)
                                        <div class="flex items-center gap-2" x-data="{ qty: {{ $product->unit_type === 'gram' ? '0.25' : '1' }} }">
                                            <div class="flex items-center border border-slate-200/80 rounded-2xl bg-slate-50 overflow-hidden shrink-0">
                                                <button @click="qty = Math.max({{ $product->unit_type === 'gram' ? '0.05' : '1' }}, qty - {{ $product->unit_type === 'gram' ? '0.05' : '1' }})" class="px-2.5 py-2 hover:bg-slate-200 text-slate-600 font-extrabold text-xs transition-colors cursor-pointer">
                                                    -
                                                </button>
                                                <input type="number" step="{{ $product->unit_type === 'gram' ? '0.01' : '1' }}" min="{{ $product->unit_type === 'gram' ? '0.01' : '1' }}" max="{{ $stockMax }}" x-model.number="qty" class="w-12 text-center bg-transparent border-none text-xs font-bold text-slate-800 p-0 focus:ring-0 outline-none" />
                                                <button @click="qty = Math.min({{ $stockMax }}, qty + {{ $product->unit_type === 'gram' ? '0.05' : '1' }})" class="px-2.5 py-2 hover:bg-slate-200 text-slate-600 font-extrabold text-xs transition-colors cursor-pointer">
                                                    +
                                                </button>
                                            </div>

                                            <button @click="addToCart(@js($product), qty)" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-2 px-3 rounded-2xl shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/25 transition-all duration-300 inline-flex items-center justify-center gap-1.5 text-xs cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                Añadir
                                            </button>
                                        </div>
                                    @else
                                        <button disabled class="w-full bg-slate-100 text-slate-400 font-bold py-3 rounded-2xl text-xs cursor-not-allowed">
                                            Agotado Temporalmente
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center bg-white border border-slate-100 rounded-3xl space-y-4">
                            <svg class="w-16 h-16 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h3 class="font-outfit font-bold text-lg text-slate-700">No hay productos disponibles</h3>
                            <p class="text-sm text-slate-400">Vuelve más tarde o comunícate con soporte.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- CONTENEDOR DERECHO: DETALLES GENERALES / RECOMENDACIONES (DE ESCRITORIO) -->
            <div class="hidden lg:block w-80 space-y-6 shrink-0">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 space-y-4">
                    <h3 class="font-outfit font-black text-base text-slate-800 tracking-tight">¿Cómo comprar?</h3>
                    <ul class="space-y-4 text-xs font-medium text-slate-500">
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 font-extrabold flex items-center justify-center shrink-0">1</span>
                            <span>Añade todos los productos que gustes al carrito.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 font-extrabold flex items-center justify-center shrink-0">2</span>
                            <span>Ingresa tu cuenta o regístrate en minutos para identificarte.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 font-extrabold flex items-center justify-center shrink-0">3</span>
                            <span>Completa los datos de envío y espera la verificación del pago.</span>
                        </li>
                    </ul>
                </div>
                
                @guest
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 rounded-3xl text-white space-y-4 shadow-md shadow-indigo-600/10">
                        <h3 class="font-outfit font-black text-base tracking-tight">¡Crea tu Cuenta!</h3>
                        <p class="text-xs text-indigo-100 font-medium">
                            Regístrate y obtén un panel exclusivo de cliente donde ver tus facturas pendientes, abonos, pagos históricos y estatus de despachos.
                        </p>
                        <a href="{{ route('register') }}" class="block bg-white hover:bg-slate-100 text-indigo-700 font-black text-center py-3 rounded-2xl text-xs transition-colors shadow-sm">
                            Registrarme Ahora
                        </a>
                    </div>
                @endguest
            </div>

        </div>
    </main>

    <!-- BACKDROP DEL CARRITO MÓVIL -->
    <div x-show="cartOpen" @click="cartOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-45"></div>

    <!-- CAJÓN DEL CARRITO DE COMPRAS SLIDE-OVER -->
    <div x-show="cartOpen" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-50 flex flex-col justify-between overflow-hidden">
        
        <!-- Cabecera del Carrito -->
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <div>
                    <h3 class="font-outfit font-black text-slate-800 text-base">Mi Carrito</h3>
                    <p class="text-xxs text-slate-400 font-medium">Revisa y ajusta tus cantidades</p>
                </div>
            </div>
            
            <button @click="cartOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Cuerpo / Lista de Items del Carrito -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 scrollbar-none">
            <template x-if="cart.length === 0">
                <div class="text-center py-20 text-slate-400 flex flex-col items-center justify-center gap-4">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center text-slate-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-extrabold text-slate-700 block">Tu carrito está vacío.</span>
                        <span class="text-xxs text-slate-400 mt-1 block">Añade productos de la lista a la izquierda.</span>
                    </div>
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="item.id">
                <div class="flex gap-4 py-3 items-center justify-between border-b border-slate-50">
                    <div class="flex-1 min-w-0">
                        <span x-text="item.name" class="font-bold text-slate-800 text-sm block truncate"></span>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mt-0.5" x-text="item.category_name"></span>
                        
                        <!-- Cantidades -->
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50 shrink-0">
                                <button @click="changeQtyInCart(item.id, item.unit_type === 'gram' ? -0.05 : -1)" class="px-2 py-0.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xxs transition-colors cursor-pointer">-</button>
                                <span class="w-10 text-center font-bold text-slate-800 text-xs" x-text="formatQuantity(item)"></span>
                                <button @click="changeQtyInCart(item.id, item.unit_type === 'gram' ? 0.05 : 1)" class="px-2 py-0.5 hover:bg-slate-200 text-slate-500 font-extrabold text-xxs transition-colors cursor-pointer">+</button>
                            </div>
                            <span class="text-slate-400 text-xxs font-extrabold" x-text="item.unit_label"></span>
                        </div>
                    </div>

                    <div class="text-right flex flex-col justify-between items-end h-full min-w-[6.5rem]">
                        <button @click="removeFromCart(item.id)" class="text-slate-300 hover:text-rose-600 transition-colors p-1.5 rounded-lg hover:bg-rose-50 mb-2 cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        <div>
                            <span class="font-extrabold text-slate-800 text-sm block" x-text="formatCurrency(calculateItemTotal(item) / {{ $safeRate }})"></span>
                            @if($exchangeRate)
                                <span class="text-indigo-600 font-bold text-[10px] block" x-text="formatCurrency(calculateItemTotal(item), ' BS')"></span>
                            @endif
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pie / Totales e Inicio de Orden -->
        <div class="p-6 border-t border-slate-100 bg-slate-50/50 space-y-4">
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <span>Subtotal</span>
                    <span x-text="formatCurrency(calculateTotal() / {{ $safeRate }})" class="text-slate-800 font-extrabold"></span>
                </div>
                
                <hr class="border-slate-100" />
                
                <div class="flex justify-between items-end">
                    <div>
                        <span class="text-sm font-extrabold text-slate-500 block">Total Estimado</span>
                        @if($exchangeRate)
                            <span x-text="formatCurrency(calculateTotal(), ' BS')" class="text-xs text-indigo-600 font-bold block mt-0.5"></span>
                        @endif
                    </div>
                    <span x-text="formatCurrency(calculateTotal() / {{ $safeRate }})" class="text-2xl font-black text-slate-800 font-outfit"></span>
                </div>
            </div>

            <!-- Botón de Orden (Invitado vs Autenticado) -->
            <button :disabled="cart.length === 0" @click="handleCheckout()" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 disabled:from-slate-100 disabled:to-slate-100 disabled:text-slate-400 text-white font-extrabold py-3.5 px-4 rounded-2xl shadow-lg shadow-indigo-600/10 hover:shadow-indigo-600/20 active:scale-98 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                <span>Proceder a la Orden</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- MODAL DE CHECKOUT (SOLO PARA CLIENTES AUTENTICADOS) -->
    @auth
        @if(Auth::user()->role === 'client' && $client)
            <div x-show="checkoutOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all duration-300" x-transition>
                <div @click.away="checkoutOpen = false" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl border border-slate-100 flex flex-col justify-between">
                    
                    <!-- Cabecera del Modal -->
                    <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center border border-indigo-100 shrink-0">
                                <svg class="w-5.5 h-5.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-outfit font-black text-slate-800 text-base">Registrar Orden</h3>
                                <p class="text-slate-400 text-xxs font-medium">Confirma tu dirección de despacho</p>
                            </div>
                        </div>
                        
                        <button @click="checkoutOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Formulario de Checkout -->
                    <form action="{{ route('client.checkout') }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        
                        <!-- Input oculto del carrito -->
                        <input type="hidden" name="cart_items" :value="JSON.stringify(prepareCartForSubmit())" />

                        <!-- Dirección -->
                        <div class="space-y-2">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Dirección de Entrega</label>
                            <textarea name="delivery_address" rows="3" required placeholder="Ingresa la dirección detallada para el envío..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm transition-all outline-none">{{ old('delivery_address', $client->address) }}</textarea>
                        </div>

                        <!-- Notas -->
                        <div class="space-y-2">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Observaciones (Opcional)</label>
                            <textarea name="notes" rows="2" placeholder="Indicaciones para el despacho..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm transition-all outline-none">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Detalle de Productos a Comprar -->
                        <div class="space-y-2">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Productos a Comprar</label>
                            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-slate-50/50">
                                <div class="max-h-40 overflow-y-auto divide-y divide-slate-100 pr-1">
                                    <template x-for="item in cart" :key="item.id">
                                        <div class="flex items-center justify-between p-3 text-xs hover:bg-slate-100/50 transition-colors">
                                            <div class="min-w-0 flex-1 pr-2">
                                                <span x-text="item.name" class="font-bold text-slate-800 block truncate"></span>
                                                <span class="text-slate-400 text-xxs font-bold">
                                                    <span x-text="formatQuantity(item)"></span>
                                                    x 
                                                    <span x-text="formatCurrency(item.display_price)"></span>
                                                </span>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span x-text="formatCurrency(calculateItemTotal(item) / {{ $safeRate }})" class="font-extrabold text-slate-800 block"></span>
                                                @if($exchangeRate)
                                                    <span x-text="formatCurrency(calculateItemTotal(item), ' BS')" class="text-indigo-600 font-bold text-xxs block"></span>
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Corto -->
                        <div class="bg-indigo-50/50 border border-indigo-100/50 rounded-2xl p-5 space-y-2.5 text-xs text-indigo-900">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-indigo-700">Ítems a ordenar</span>
                                <span x-text="totalItemsCount()" class="font-extrabold"></span>
                            </div>
                            <div class="flex justify-between items-center border-t border-indigo-100/50 pt-2.5 text-sm">
                                <span class="font-extrabold text-slate-700">Total a Pagar</span>
                                <div class="text-right">
                                    <span x-text="formatCurrency(calculateTotal() / {{ $safeRate }})" class="font-black text-slate-900 text-base"></span>
                                    @if($exchangeRate)
                                        <span x-text="formatCurrency(calculateTotal(), ' BS')" class="font-bold text-indigo-700 text-xs block"></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="checkoutOpen = false" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3.5 rounded-xl text-xs transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-emerald-500/15 transition-all duration-300 flex items-center justify-center gap-1.5 text-xs cursor-pointer">
                                Confirmar Orden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <!-- MODAL DE AUTENTICACIÓN REQUERIDA (SOLO INVITADOS) -->
    <div x-show="authPromptOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all duration-300" x-transition>
        <div @click.away="authPromptOpen = false" class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl border border-slate-100 p-8 text-center space-y-6">
            <div class="w-16 h-16 bg-indigo-50 border border-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            
            <div class="space-y-2">
                <h3 class="font-outfit font-black text-slate-800 text-lg">¡Identifícate para Ordenar!</h3>
                <p class="text-xs text-slate-400 font-medium">
                    Para finalizar tu orden y habilitar los detalles de despacho, por favor inicia sesión o crea una cuenta. Tus productos en el carrito se mantendrán perfectamente guardados en este navegador.
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-2">
                <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3.5 rounded-2xl text-xs transition-colors shadow-sm tracking-wide">
                    Iniciar Sesión
                </a>
                <a href="{{ route('register') }}" class="bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-extrabold py-3.5 rounded-2xl text-xs transition-colors tracking-wide">
                    Crear Cuenta de Cliente
                </a>
                <button @click="authPromptOpen = false" class="text-slate-400 hover:text-slate-600 text-xxs font-extrabold tracking-wider uppercase pt-2 cursor-pointer">
                    Seguir Explorando
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT DE ALPINE.JS -->
    <script>
        function storefrontCart() {
            return {
                cartOpen: false,
                checkoutOpen: false,
                authPromptOpen: false,
                searchQuery: '',
                selectedCategory: 'all',
                cart: [],
                isAuthenticated: @js(Auth::check()),
                userRole: @js(Auth::check() ? Auth::user()->role : null),

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
                },

                persistCart() {
                    localStorage.setItem('client_shopping_cart', JSON.stringify(this.cart));
                },

                // Filtro dinámico de productos
                filterProduct(product) {
                    const matchQuery = product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                       (product.brand && product.brand.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    const matchCategory = this.selectedCategory === 'all' || product.category_id.toString() === this.selectedCategory;
                    
                    return matchQuery && matchCategory;
                },

                // Añadir al carrito
                addToCart(product, qty) {
                    const parsedQty = parseFloat(qty);
                    if (isNaN(parsedQty) || parsedQty <= 0) return;

                    const existing = this.cart.find(item => item.id === product.id);

                    // Validar stock disponible
                    if (product.track_inventory && !product.allow_negative_stock) {
                        const availableStock = product.inventory ? parseFloat(product.inventory.stock) : 0;
                        const availableQtyCommercial = product.unit_type === 'gram' ? (availableStock / 1000) : availableStock;
                        const currentInCart = existing ? existing.quantity : 0;

                        if (currentInCart + parsedQty > availableQtyCommercial) {
                            alert("El producto '" + product.name + "' no cuenta con inventario suficiente. Disponible: " + availableQtyCommercial.toLocaleString('es-VE') + " " + (product.unit_type === 'gram' ? 'Kgs' : 'Unds'));
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
                    this.cartOpen = true; // abrir el carrito para retroalimentación
                },

                // Eliminar del carrito
                removeFromCart(id) {
                    this.cart = this.cart.filter(item => item.id !== id);
                    this.persistCart();
                },

                // Ajustar cantidades
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
                            const availableQtyCommercial = item.unit_type === 'gram' ? (item.inventory_stock / 1000) : item.inventory_stock;
                            if (newQty > availableQtyCommercial) {
                                alert("No puedes exceder el stock disponible: " + availableQtyCommercial.toLocaleString('es-VE') + " " + (item.unit_type === 'gram' ? 'Kgs' : 'Unds'));
                                return;
                            }
                        }
                        item.quantity = newQty;
                        this.persistCart();
                    }
                },

                totalItemsCount() {
                    return this.cart.length;
                },

                calculateItemTotal(item) {
                    return item.display_price * item.quantity;
                },

                calculateTotal() {
                    return this.cart.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                },

                formatCurrency(value, suffix = ' $') {
                    return value.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + suffix;
                },

                formatQuantity(item) {
                    if (item.unit_type === 'gram') {
                        return item.quantity.toLocaleString('es-VE', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' Kg';
                    }
                    return item.quantity.toLocaleString('es-VE', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Und';
                },

                prepareCartForSubmit() {
                    return this.cart.map(item => ({
                        id: item.id,
                        quantity: item.quantity
                    }));
                },

                // Controlar redirección o formulario de compra al presionar checkout
                handleCheckout() {
                    if (!this.isAuthenticated) {
                        this.authPromptOpen = true;
                    } else if (this.userRole === 'client') {
                        this.checkoutOpen = true;
                    } else {
                        alert('Tu cuenta tiene rol administrativo. Para realizar pedidos, inicia sesión con una cuenta de cliente.');
                    }
                }
            };
        }
    </script>
</body>
</html>
