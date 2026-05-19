<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Bienvenido a tu Panel de Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Banner de Bienvenida -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-8 py-12 text-white relative">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold mb-2">¡Hola, {{ Auth::user()->name }}!</h3>
                        <p class="text-blue-100 text-lg">Nos alegra tenerte de vuelta. Aquí puedes gestionar todas tus actividades.</p>
                    </div>
                    <!-- Decoración de fondo -->
                    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-white opacity-10"></div>
                    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-48 h-48 rounded-full bg-white opacity-10"></div>
                </div>
            </div>

            <!-- Grid de Tarjetas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tarjeta 1 -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mis Compras</h4>
                    <p class="text-gray-500 mb-4 text-sm">Revisa el historial de tus pedidos, su estado actual y facturas de forma rápida.</p>
                    <a href="#" class="text-blue-600 font-semibold hover:text-blue-800 inline-flex items-center text-sm transition-colors">
                        Ver compras <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <!-- Tarjeta 2 -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-2">Favoritos</h4>
                    <p class="text-gray-500 mb-4 text-sm">Accede rápidamente a los productos que más te gustan y agrégalos a tu carrito.</p>
                    <a href="#" class="text-purple-600 font-semibold hover:text-purple-800 inline-flex items-center text-sm transition-colors">
                        Ver favoritos <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <!-- Tarjeta 3 -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mi Perfil</h4>
                    <p class="text-gray-500 mb-4 text-sm">Actualiza tu información personal, dirección de envío y preferencias de cuenta.</p>
                    <a href="{{ route('profile.edit') }}" class="text-green-600 font-semibold hover:text-green-800 inline-flex items-center text-sm transition-colors">
                        Ajustes <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Sección adicional: Ofertas o Novedades -->
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h4 class="text-2xl font-bold text-gray-800 mb-2">¡Descubre nuestras nuevas ofertas!</h4>
                    <p class="text-gray-600">Revisa el catálogo para ver los productos más recientes y descuentos exclusivos.</p>
                </div>
                <div class="mt-6 md:mt-0">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors">
                        Ver Catálogo
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
