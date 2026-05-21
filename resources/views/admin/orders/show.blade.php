@extends('admin.layouts.app')

@section('title', 'Detalle de Orden ' . $order->order_number)

@section('content')
<div class="space-y-6">
    
    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm">
            <p class="font-bold">{{ session('success') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm">
            <p class="font-bold">{{ $errors->first() }}</p>
        </div>
    @endif

    {{-- Encabezado con Badges --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-3xl font-black text-gray-800">{{ $order->order_number }}</h1>
            </div>
            <p class="text-sm text-gray-500 mt-1 ml-9">Creada el {{ $order->created_at->format('d/m/Y h:i A') }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $order->status == 'completed' ? 'bg-green-100 text-green-700' : ($order->status == 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                Estado: {{ ucfirst($order->status) }}
            </span>
            <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $order->payment_status == 'paid' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                Pago: {{ ucfirst($order->payment_status) }}
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
                    <p class="font-bold text-gray-800 text-lg">{{ $order->client_name ?? ($order->client->name ?? 'Consumidor Final') }}</p>
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
                            @foreach($order->details as $detail)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        {{ $detail->product->name }}
                                        @if($detail->bulk)
                                            <span class="block text-xs text-gray-400 font-normal">Presentación: {{ $detail->bulk->name }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-600">
                                        {{ $detail->product->unit_type === 'gram' ? number_format($detail->quantity, 3) . ' Kg' : number_format($detail->quantity, 0) . ' Und' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-500">Bs. {{ number_format($detail->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-800">Bs. {{ number_format($detail->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-gray-50 flex justify-end">
                    <div class="w-full md:w-1/2 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span>Bs. {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-black text-gray-900 border-t border-gray-200 pt-2">
                            <span>Total</span>
                            <span>Bs. {{ number_format($order->total, 2) }}</span>
                        </div>
                        @if($order->exchange_rate)
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
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800">Verificación de Pago</h3>
                </div>

                <div class="p-6 space-y-6">
                    @forelse($order->paymentProofs as $proof)
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50 relative">
                            {{-- Badge de estado del comprobante --}}
                            <span class="absolute top-3 right-3 text-xs font-bold px-2 py-1 rounded {{ $proof->status == 'verified' ? 'bg-green-100 text-green-700' : ($proof->status == 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($proof->status) }}
                            </span>
                            
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Referencia</p>
                            <p class="font-mono font-bold text-lg text-gray-800 mb-3">{{ $proof->reference }}</p>

                            @if($proof->images->count() > 0)
                                @php $imagePath = $proof->images->first()->path; @endphp
                                <div class="mt-2 aspect-[3/4] bg-gray-200 rounded-lg overflow-hidden border border-gray-300">
                                    <a href="{{ asset('storage/' . $imagePath) }}" target="_blank" title="Clic para ampliar">
                                        <img src="{{ asset('storage/' . $imagePath) }}" alt="Comprobante" class="w-full h-full object-cover hover:scale-105 transition-transform">
                                    </a>
                                </div>
                                <p class="text-center text-xs text-gray-400 mt-2">Haz clic en la imagen para ampliar</p>
                            @else
                                <div class="bg-gray-100 text-gray-400 text-xs p-4 rounded text-center">Sin imagen adjunta</div>
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
            @if($order->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 space-y-3">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Acciones de Verificación</h3>
                    
                    {{-- Botón Aprobar --}}
                    <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de APROBAR esta orden? Se procesará como pagada y completada.');">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow transition-colors flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Aprobar y Completar
                        </button>
                    </form>

                    <div class="relative py-2">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center"><span class="bg-white px-2 text-xs text-gray-400">Ó</span></div>
                    </div>

                    {{-- Formulario Rechazar --}}
                    <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" onsubmit="return confirm('ATENCIÓN: ¿Seguro que deseas RECHAZAR esta orden? Se cancelará la compra y el stock será devuelto al inventario automáticamente.');">
                        @csrf
                        <div class="space-y-2 mb-3">
                            <label class="text-xs font-bold text-gray-500">Motivo del rechazo (Opcional):</label>
                            <input type="text" name="notes" placeholder="Ej: Pago no recibido, referencia inválida..." class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200">
                        </div>
                        <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-3 px-4 rounded-xl border border-red-200 hover:border-transparent shadow-sm transition-colors flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Rechazar y Devolver Stock
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection