<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Control de Almacén
        </h1>
        <div class="flex space-x-2">
            <button
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                Historial de Movimientos
            </button>
        </div>
    </div>

    {{-- Resumen de Alertas (Corregido con variables globales) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Productos Agotados</h2>
            <p class="text-2xl font-bold text-red-600">{{ $totalOutOfStock }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-orange-500">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Bajo Stock Mínimo</h2>
            <p class="text-2xl font-bold text-orange-600">{{ $totalLowStock }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Total Unidades en Almacén</h2>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalStock, 2) }}</p>
        </div>
    </div>

    {{-- Buscador en Tiempo Real --}}
    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre de producto o SKU..."
            class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">

        <div wire:loading wire:target="search" class="text-sm text-gray-500 ml-2">
            Buscando...
        </div>
    </div>

    {{-- Tabla de Inventario --}}
    <div class="bg-white rounded-xl shadow overflow-hidden relative">
        {{-- Loader para la paginación --}}
        <div wire:loading.delay class="absolute inset-0 bg-white bg-opacity-70 z-10 flex items-center justify-center">
            <span class="text-gray-600 font-semibold">Cargando datos...</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock Físico</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reservado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Disponible</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventories as $inventory)
                        <tr x-data="{ openAdjust: false }" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $inventory->product->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $inventory->product->category->name ?? 'General' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inventory->product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-gray-700">
                                {{ number_format($inventory->stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-orange-600 font-medium">
                                {{ number_format($inventory->reserved_stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="text-lg font-bold {{ $inventory->stock - $inventory->reserved_stock <= $inventory->minimum_stock ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($inventory->stock - $inventory->reserved_stock, 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($inventory->stock <= 0)
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">Agotado</span>
                                @elseif($inventory->stock <= $inventory->minimum_stock)
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800">Stock
                                        Bajo</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">Óptimo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button @click="openAdjust = true"
                                    class="bg-gray-800 text-white px-3 py-1 rounded-md hover:bg-gray-700 transition">
                                    Ajustar
                                </button>

                                {{-- MODAL DE AJUSTE MANUAL CON ALPINE --}}
                                <template x-teleport="body">
                                    <div x-show="openAdjust" class="fixed inset-0 z-50 overflow-y-auto"
                                        style="display: none;">
                                        <div class="flex items-center justify-center min-h-screen px-4">
                                            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                                                @click="openAdjust = false"></div>

                                            <div
                                                class="bg-white rounded-xl shadow-xl overflow-hidden transform transition-all sm:max-w-lg sm:w-full z-50">
                                                <form action="{{ route('admin.inventories.update', $inventory->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="p-6 text-left">
                                                        <h3 class="text-xl font-bold text-gray-900 mb-4">Ajuste de
                                                            Stock: {{ $inventory->product->name }}</h3>
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Tipo
                                                                    de Ajuste</label>
                                                                <select name="adjustment_type"
                                                                    class="mt-1 w-full rounded-lg border-gray-300">
                                                                    <option value="addition">➕ Sumar (Entrada)</option>
                                                                    <option value="subtraction">➖ Restar (Salida/Merma)
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Cantidad</label>
                                                                <input type="number" name="quantity" min="1"
                                                                    step="0.01"
                                                                    class="mt-1 w-full rounded-lg border-gray-300"
                                                                    required>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Motivo</label>
                                                                <textarea name="reason" rows="2" class="mt-1 w-full rounded-lg border-gray-300" required></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                                                        <button type="button" @click="openAdjust = false"
                                                            class="text-gray-700 font-medium">Cancelar</button>
                                                        <button type="submit"
                                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700">Procesar
                                                            Ajuste</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">No hay existencias
                                registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>
