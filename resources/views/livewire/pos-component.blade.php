<div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative">

        @if (session()->has('success'))
            <div
                class="lg:col-span-12 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm font-bold text-lg flex justify-between items-center">
                {{ session('success') }}
                <button wire:click="$refresh"
                    class="bg-green-600 text-white px-4 py-1 rounded text-sm hover:bg-green-700">Nueva Venta</button>
            </div>
        @endif
        @if (session()->has('error'))
            <div
                class="lg:col-span-12 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        {{-- LADO IZQUIERDO: BUSCADOR Y CARRITO (8 Columnas) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Buscador Unificado (Pistola Lector / Teclado) --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-indigo-500 relative">
                <label class="block text-sm font-bold text-gray-700 mb-2">Escanea el Código de Barras o Escribe el
                    Nombre</label>
                <div class="relative">
                    <input type="text" wire:model="search_query" wire:keydown.enter="scanProduct" autofocus
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-lg shadow-sm pl-4 py-3"
                        placeholder="Dispara la lectora aquí o busca...">

                    <div wire:loading wire:target="scanProduct"
                        class="absolute right-3 top-3 text-indigo-500 font-bold text-sm">Buscando...</div>
                </div>

                {{-- Resultados desplegables si se buscó por nombre --}}
                @if (!empty($search_results))
                    <div
                        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-2xl border border-gray-200 max-h-60 overflow-y-auto left-0">
                        <ul class="py-1">
                            @foreach ($search_results as $prod)
                                @foreach ($prod->bulks as $bulk)
                                    <li wire:click="addToCart({{ $prod->id }}, {{ $bulk->id }})"
                                        class="cursor-pointer hover:bg-indigo-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                        <div>
                                            <span class="font-bold text-gray-800 text-lg">{{ $prod->name }}</span>
                                            <span
                                                class="text-sm text-indigo-600 font-bold ml-2">({{ $bulk->name }})</span>
                                            <div class="text-xs text-gray-500">Stock: {{ $prod->inventory->stock ?? 0 }}
                                                Und base</div>
                                        </div>
                                        <span class="font-black text-green-600 text-lg">Bs.
                                            {{ number_format($bulk->sale_price, 2) }}</span>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
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
                        @forelse($cart as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900">{{ $item['name'] }}</div>
                                    <div class="text-xs text-gray-500 font-bold">{{ $item['presentation'] }} (A Bs.
                                        {{ number_format($item['price'], 2) }})</div>
                                    @if (!$item['allow_negative'] && $item['current_stock'] < $item['quantity'] * $item['conversion_factor'])
                                        <span
                                            class="text-[10px] text-white bg-red-500 px-2 py-0.5 rounded font-bold">Sin
                                            Stock Físico</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 w-32">
                                    <input type="number" step="0.01" min="0.01"
                                        wire:model.live.debounce.300ms="cart.{{ $index }}.quantity"
                                        wire:change="calculateTotals"
                                        class="w-full text-center rounded-lg border-gray-300 font-bold text-lg">
                                </td>
                                <td class="px-4 py-3 text-right font-black text-gray-900 text-lg">
                                    Bs. {{ number_format($item['subtotal'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="removeItem({{ $index }})"
                                        class="text-red-500 hover:text-red-700 font-black text-xl px-2">×</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-16 text-center text-gray-400 font-medium">
                                    Escanea un código de barras para añadir productos a la factura.
                                </td>
                            </tr>
                        @endforelse
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
                    <input type="text" wire:model="client_identification" wire:keydown.enter="searchClient"
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 font-mono text-center font-bold"
                        placeholder="V-12345678">
                    <button wire:click="searchClient"
                        class="bg-blue-100 text-blue-700 px-3 rounded-lg hover:bg-blue-200 font-bold">OK</button>
                </div>

                @if ($client_name)
                    <div
                        class="mt-3 p-2 bg-green-50 text-green-800 rounded-lg text-sm font-bold flex justify-between items-center border border-green-200">
                        <span>👤 {{ $client_name }}</span>
                    </div>
                @endif
            </div>

            {{-- Caja Pagos (Múltiples) --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border-t-4 border-emerald-500">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-bold text-gray-700 uppercase">Pagos Realizados</h3>
                    <button wire:click="addPaymentLine"
                        class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-gray-300 font-bold">+ Dividir
                        Pago</button>
                </div>

                <div class="space-y-3">
                    @foreach ($payments as $index => $payment)
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 relative">
                            @if (count($payments) > 1)
                                <button wire:click="removePaymentLine({{ $index }})"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow font-bold">X</button>
                            @endif

                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <select wire:model="payments.{{ $index }}.payment_method_id"
                                    class="w-full text-xs rounded border-gray-300 font-bold text-gray-700 py-2">
                                    @foreach ($available_payment_methods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01"
                                    wire:model.live.debounce.300ms="payments.{{ $index }}.amount"
                                    wire:change="calculateTotals" placeholder="Monto Bs."
                                    class="w-full text-sm rounded border-gray-300 text-right font-bold text-emerald-700 py-2">
                            </div>
                            <input type="text" wire:model="payments.{{ $index }}.reference"
                                placeholder="Ref. Bancaria (Solo si aplica)"
                                class="w-full text-xs rounded border-gray-300 py-1.5">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pantalla de Totales y Procesamiento --}}
            <div class="bg-gray-900 text-white p-6 rounded-xl shadow-xl">
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-gray-300">
                        <span class="text-sm">Total a Facturar:</span>
                        <span class="font-black text-xl">Bs.
                            {{ number_format(collect($cart)->sum('subtotal'), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-300">
                        <span class="text-sm">Entregado en Caja:</span>
                        <span class="font-bold text-green-400 text-lg">Bs.
                            {{ number_format($amount_received, 2) }}</span>
                    </div>

                    {{-- Detección de FIADO (Deuda) --}}
                    @if ($amount_pending > 0)
                        <div
                            class="flex justify-between items-center bg-red-500/20 p-2 rounded border border-red-500 mt-2">
                            <span class="text-red-300 text-xs font-bold uppercase tracking-wider">Monto Fiado
                                (Deuda)</span>
                            <span class="font-black text-red-400">Bs. {{ number_format($amount_pending, 2) }}</span>
                        </div>
                    @elseif($amount_pending < 0)
                        <div
                            class="flex justify-between items-center bg-blue-500/20 p-2 rounded border border-blue-500 mt-2">
                            <span class="text-blue-300 text-xs font-bold uppercase tracking-wider">Cambio /
                                Vuelto</span>
                            <span class="font-black text-blue-400">Bs.
                                {{ number_format(abs($amount_pending), 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-gray-700 mt-3 pt-3 text-right">
                        <span class="text-xs text-gray-500">Tasa Dólar: Bs. {{ $exchange_rate }}</span>
                    </div>
                </div>

                {{-- Bloqueo automático del botón si no hay cliente o productos --}}
                <button wire:click="processOrder" wire:loading.attr="disabled"
                    @if (empty($client_id) || empty($cart)) disabled @endif
                    class="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-gray-900 font-black py-4 rounded-xl transition-all uppercase text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="processOrder">Generar Venta</span>
                    <span wire:loading wire:target="processOrder">Facturando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
