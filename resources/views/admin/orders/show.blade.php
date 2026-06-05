@extends('admin.layouts.app')

@section('title', 'Detalle de Orden ' . $order->order_number)

@section('content')
    <div class="space-y-6" x-data="{ showUploadModal: false }">

        {{-- Alertas --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm">
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm">
                <p class="font-bold">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Encabezado con Badges --}}
        <div
            class="bg-white rounded-xl shadow-sm p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-black text-gray-800">{{ $order->order_number }}</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1 ml-9">Creada el {{ $order->created_at->format('d/m/Y h:i A') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @php
                    // Diccionarios de traducción
                    $statusTranslations = [
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'ready_for_pickup' => 'Listo para retirar',
                        'completed' => 'Completada',
                        'delivered' => 'Entregada',
                        'cancelled' => 'Cancelada',
                    ];

                    $paymentTranslations = [
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        'rejected' => 'Rechazado',
                    ];
                @endphp

                <span
                    class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider 
        {{ $order->status == 'completed'
            ? 'bg-green-100 text-green-700'
            : ($order->status == 'cancelled'
                ? 'bg-red-100 text-red-700'
                : 'bg-yellow-100 text-yellow-700') }}">
                    Estado: {{ $statusTranslations[$order->status] ?? ucfirst($order->status) }}
                </span>

                <span
                    class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider 
        {{ $order->payment_status == 'paid' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                    Pago: {{ $paymentTranslations[$order->payment_status] ?? ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLUMNA IZQUIERDA: Info General y Productos --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Datos del Cliente y Envío --}}
                <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Datos del Cliente</h3>
                        <p class="font-bold text-gray-800 text-lg">
                            {{ $order->client_name ?? ($order->client->name ?? 'Consumidor Final') }}</p>
                        <p class="text-sm text-gray-600">{{ $order->client->identification ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">{{ $order->client_phone ?? ($order->client->phone ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Detalles de Entrega</h3>
                        <p class="font-bold text-gray-800">
                            {{ $order->order_type === 'pickup' ? 'Retiro en Tienda' : 'Delivery' }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">{{ $order->delivery_address ?? 'No especificada' }}</p>
                    </div>
                </div>

                {{-- Detalle de Productos --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Productos Solicitados</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold">
                                <tr>
                                    <th class="px-6 py-3 text-left">Producto</th>
                                    <th class="px-6 py-3 text-center">Cant.</th>
                                    <th class="px-6 py-3 text-right">P. Unitario</th>
                                    <th class="px-6 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                {{-- Modificamos la iteración aquí --}}
                                @foreach ($details as $detail)
                                    <tr>
                                        <td class="px-6 py-4 font-semibold text-gray-800">
                                            {{ $detail->product->name }}
                                            @if ($detail->bulk)
                                                <span class="block text-xs text-gray-400 font-normal">Presentación:
                                                    {{ $detail->bulk->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-600">
                                            {{ $detail->product->unit_type === 'gram' ? number_format($detail->quantity, 3) . ' Kg' : number_format($detail->quantity, 0) . ' Und' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-500">Bs.
                                            {{ number_format($detail->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-800">Bs.
                                            {{ number_format($detail->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Enlaces de paginación con padding para que respire el diseño --}}
                    @if ($details->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $details->links() }}
                        </div>
                    @endif

                    <div class="p-6 bg-gray-50 flex justify-end">
                        <div class="w-full md:w-1/2 space-y-2">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal</span>
                                {{-- Estos totales seguirán funcionando porque provienen del objeto Order principal --}}
                                <span>Bs. {{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div
                                class="flex justify-between text-xl font-black text-gray-900 border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span>Bs. {{ number_format($order->total, 2) }}</span>
                            </div>
                            @if ($order->exchange_rate)
                                <div class="flex justify-end text-sm font-bold text-indigo-600">
                                    ≈ ${{ number_format($order->total / $order->exchange_rate, 2) }} USD
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA: Comprobantes y Acciones --}}
            <div class="space-y-6">

                {{-- Panel de Validación de Pagos --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Verificación de Pago</h3>

                        {{-- Solo se muestra si la orden está pendiente Y la colección de comprobantes está vacía --}}
                        @if ($order->payment_status === 'pending' && $order->paymentProofs->isEmpty())
                            <button @click="showUploadModal = true"
                                class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors border border-indigo-100 shadow-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Subir Manualmente
                            </button>
                        @endif
                    </div>

                    <div class="p-6 space-y-6">
                        @forelse($order->paymentProofs as $proof)
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50 relative">
                                {{-- Badge de estado del comprobante --}}
                                <span
                                    class="absolute top-3 right-3 text-xs font-bold px-2 py-1 rounded {{ $proof->status == 'verified' ? 'bg-green-100 text-green-700' : ($proof->status == 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst($proof->status) }}
                                </span>

                                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Referencia</p>
                                <p class="font-mono font-bold text-lg text-gray-800 mb-3">{{ $proof->reference }}</p>

                                @if ($proof->images->count() > 0)
                                    @php $imagePath = $proof->images->first()->path; @endphp
                                    <div
                                        class="mt-2 aspect-[3/4] bg-gray-200 rounded-lg overflow-hidden border border-gray-300">
                                        <a href="{{ asset('storage/' . $imagePath) }}" target="_blank"
                                            title="Clic para ampliar">
                                            <img src="{{ asset('storage/' . $imagePath) }}" alt="Comprobante"
                                                class="w-full h-full object-cover hover:scale-105 transition-transform">
                                        </a>
                                    </div>
                                    <p class="text-center text-xs text-gray-400 mt-2">Haz clic en la imagen para ampliar</p>
                                @else
                                    <div class="bg-gray-100 text-gray-400 text-xs p-4 rounded text-center">Sin imagen
                                        adjunta</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center p-4 text-gray-500 text-sm italic">
                                El cliente no ha subido comprobantes para esta orden.
                            </div>
                        @endforelse
                    </div>
                </div>
                {{-- Botones de Acción (Aprobar/Rechazar) SOLO si está Pendiente --}}
                @if ($order->payment_status === 'pending' or $order->status === 'ready_for_pickup')
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 space-y-3">
                        <h3 class="font-bold text-gray-800 text-sm mb-4">Acciones de Verificación</h3>

                        {{-- Botón Aprobar --}}
                        <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de APROBAR esta orden? Se procesará como pagada y completada.');">
                            @csrf

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Acción a
                                    realizar</label>
                                <select name="status" id="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required>
                                    <option value="" disabled selected>Seleccione una opción...</option>

                                    {{-- Solo mostramos "Lista para entrega" si NO es una venta de tienda (fiado) --}}
                                    @if ($order->order_type !== 'store' && $order->status !== 'ready_for_pickup')
                                        <option value="ready_for_pickup">Pago verificado - Lista para entrega</option>
                                    @endif

                                    <option value="completed">Pago verificado - Marcar como Completada/Entregada</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow transition-colors flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Aprobar
                            </button>
                        </form>

                        <div class="relative py-2">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center"><span
                                    class="bg-white px-2 text-xs text-gray-400">Ó</span></div>
                        </div>

                        {{-- Formulario Rechazar --}}
                        <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST"
                            onsubmit="return confirm('ATENCIÓN: ¿Seguro que deseas RECHAZAR esta orden? Se cancelará la compra y el stock será devuelto al inventario automáticamente.');">
                            @csrf
                            <div class="space-y-2 mb-3">
                                <label class="text-xs font-bold text-gray-500">Motivo del rechazo (Opcional):</label>
                                <input type="text" name="notes"
                                    placeholder="Ej: Pago no recibido, referencia inválida..."
                                    class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200">
                            </div>
                            <button type="submit"
                                class="w-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-3 px-4 rounded-xl border border-red-200 hover:border-transparent shadow-sm transition-colors flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Rechazar y Devolver Stock
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
        <div x-cloak x-show="showUploadModal"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.away="showUploadModal = false" x-show="showUploadModal" x-transition.scale.95
                class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col">

                <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center border border-indigo-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-gray-800 text-sm">Cargar Comprobante Manual</h3>
                            <p class="text-[10px] font-bold text-gray-400">Archiva recibos de WhatsApp o similares</p>
                        </div>
                    </div>
                    <button type="button" @click="showUploadModal = false"
                        class="text-gray-400 hover:text-gray-600 bg-gray-100 p-1.5 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.orders.proof', $order->id) }}" method="POST"
                    enctype="multipart/form-data" x-data="{ isSubmitting: false }"
                    @submit="setTimeout(() => isSubmitting = true, 50)" class="p-5 space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">¿Dónde se recibió el
                            dinero?</label>
                        <select name="payment_method_id" required
                            class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 font-semibold">
                            <option value="" disabled selected>Selecciona el método de pago...</option>
                            @foreach ($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Monto
                                Total</label>
                            <input type="number" step="0.01" name="amount" value="{{ $order->total }}" required
                                class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 font-black text-indigo-600">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Nro.
                                Referencia</label>
                            <input type="text" name="reference" required placeholder="Ej: 001452"
                                class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Foto del Recibo
                            (Obligatorio)</label>
                        <input type="file" name="payment_proof" accept="image/png, image/jpeg, image/jpg, image/webp"
                            required
                            class="w-full text-xs rounded-xl border border-gray-200 p-1.5 bg-gray-50 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700 file:font-bold file:px-3 file:py-1.5 file:cursor-pointer">
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="button" @click="showUploadModal = false"
                            class="w-1/3 bg-gray-100 text-gray-600 font-bold py-2.5 rounded-xl hover:bg-gray-200 transition-colors text-xs">Cancelar</button>

                        <button type="submit" :disabled="isSubmitting"
                            class="flex-1 bg-indigo-600 disabled:bg-indigo-400 text-white font-bold py-2.5 rounded-xl hover:bg-indigo-700 shadow-sm transition-colors text-xs flex justify-center items-center gap-2">
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <svg x-cloak x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="isSubmitting ? 'Guardando...' : 'Cargar Comprobante'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
