<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Sistema')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen relative">

        {{-- Overlay oscuro para móviles cuando el sidebar está abierto --}}
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 lg:hidden" style="display: none;">
        </div>

        {{-- Sidebar --}}
        @include('admin.components.sidebar')

        {{-- Contenedor principal (min-w-0 evita que el contenido rompa el flexbox en móviles) --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Navbar --}}
            @include('admin.components.navbar')
            {{-- COMPONENTE DE ALERTAS  --}}
            @include('admin.components.alerts')

            <main class="p-4 md:p-6">
                @yield('content')
            </main>

        </div>

    </div>
    @livewireScripts
</body>

</html>
