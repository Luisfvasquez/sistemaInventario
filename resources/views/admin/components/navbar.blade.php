<header class="bg-white shadow px-4 md:px-6 py-4 flex items-center justify-between z-10 relative">

    <div class="flex items-center gap-4">
        {{-- Botón menú hamburguesa (Solo visible en móviles) --}}
        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <h2 class="text-lg md:text-xl font-semibold text-gray-800">
            Panel Administrativo
        </h2>
    </div>

    <div class="flex items-center gap-4">

        {{-- Dropdown de Usuario --}}
        <div class="relative" x-data="{ dropdownOpen: false }">

            {{-- Botón del perfil --}}
            <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false"
                class="flex items-center gap-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                {{-- Icono de usuario SVG --}}
                <div class="bg-gray-200 p-1.5 rounded-full">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <span class="hidden md:block font-medium">
                    {{ auth()->user()->name ?? 'Usuario' }}
                </span>

                {{-- Flecha hacia abajo --}}
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Menú Desplegable --}}
            <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-3 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5"
                style="display: none;">

                {{-- Enlace a Perfil --}}
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        Salir
                    </button>
                </form>
            </div>

        </div>

    </div>

</header>
