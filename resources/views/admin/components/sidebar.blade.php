<aside
    class="bg-gray-900 text-white w-64 min-h-screen fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-30 lg:translate-x-0 lg:static lg:inset-0"
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">

    <div class="p-5 border-b border-gray-700 flex justify-between items-center">
        <h1 class="text-xl md:text-2xl font-bold">
            Inventario App
        </h1>

        {{-- Botón para cerrar en móviles --}}
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="p-4 space-y-2 overflow-y-auto">

        <a href="{{ route('dashboard') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Dashboard
        </a>

        <a href="{{ route('admin.products.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Productos
        </a>

        <a href="{{ route('admin.inventories.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.inventories.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Inventario
        </a>

        <a href="{{ route('admin.purchases.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.purchases.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Compras
        </a>

        <a href="{{ route('admin.orders.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Pedidos
        </a>

        <a href="{{ route('admin.clients.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.clients.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Clientes
        </a>

        <a href="{{ route('admin.index') }}"
            class="block px-4 py-2 rounded transition-colors {{ request()->routeIs('admin.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            Administración
        </a>

    </nav>

</aside>
