<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                {{ __('Mis Facturas y Deudas') }}
            </h2>
            <p class="text-xs text-slate-500 mt-1">Monitorea tus saldos pendientes, cuotas asignadas y el historial
                detallado de abonos cargados.</p>
        </div>
    </x-slot>

    <div x-data="invoicesManager()" class="py-8 bg-slate-50 min-h-screen">

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

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Resumen Financiero de Deuda Global -->
            @php
                $globalTotalPending = $accounts->where('status', '!=', 'paid')->sum('pending_amount');
                $globalTotalPaid = $accounts->sum('paid_amount');
                $globalTotalDebt = $accounts->sum('total_amount');
            @endphp
            <div
                class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-8 border border-slate-800 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-8">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <span
                            class="bg-rose-500/20 text-rose-300 border border-rose-500/20 text-xxs font-black tracking-widest uppercase px-3 py-1 rounded-full">
                            Estado Financiero Global
                        </span>
                        <h3 class="text-3xl font-black mt-2 tracking-tight">Balance Pendiente</h3>
                    </div>
                    <div class="flex flex-wrap items-baseline gap-2">
                        <span
                            class="text-4xl font-black text-rose-500">{{ number_format($globalTotalPending, 2, ',', '.') }}
                            BS</span>
                        <span class="text-slate-400 font-bold text-sm">Bolívares pendientes</span>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-8 border-t md:border-t-0 md:border-l border-slate-800 pt-6 md:pt-0 md:pl-8">
                    <div>
                        <span class="text-slate-400 text-xxs font-bold uppercase tracking-wider block">Total Comprado (A
                            Plazos)</span>
                        <span
                            class="text-lg font-extrabold text-slate-200 block">{{ number_format($globalTotalDebt, 2, ',', '.') }}
                            BS</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xxs font-bold uppercase tracking-wider block">Total Amortizado
                            (Abonado)</span>
                        <span
                            class="text-lg font-extrabold text-emerald-400 block">{{ number_format($globalTotalPaid, 2, ',', '.') }}
                            BS</span>
                    </div>
                </div>

                <div class="shrink-0">
                    <button @click="reportPaymentOpen = true"
                        class="w-full md:w-auto bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-500 hover:to-teal-600 text-slate-950 font-black px-6 py-3.5 rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 inline-flex items-center justify-center gap-2 text-sm cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Reportar un Pago
                    </button>
                </div>
            </div>

            <!-- Listado de Cuentas por Cobrar Activas -->
            <div class="space-y-6">
                <h3 class="font-extrabold text-slate-800 text-lg leading-tight">Mis Cuentas de Crédito y Abonos</h3>

                @forelse($accounts as $account)
                    <!-- Tarjeta de Crédito Asociada a una Venta -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-all duration-300 hover:shadow-md">

                        <!-- Cabecera de la deuda -->
                        <div
                            class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/20">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="bg-indigo-50 border border-indigo-100 text-indigo-700 font-extrabold font-mono text-xs px-2.5 py-1 rounded-lg">
                                        Pedido #{{ $account->order->order_number }}
                                    </span>
                                    <span class="text-slate-400 text-xxs font-bold">
                                        Vence: {{ \Carbon\Carbon::parse($account->due_date)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">
                                    Monto Inicial: <span
                                        class="font-bold text-slate-700">{{ number_format($account->total_amount, 2, ',', '.') }}
                                        BS</span>
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t border-slate-100 sm:border-t-0">
                                <!-- Estado de Deuda -->
                                <div class="text-left sm:text-right">
                                    <span class="text-slate-400 text-xxs font-bold uppercase tracking-wider block">Saldo
                                        Restante</span>
                                    <span class="text-base sm:text-lg font-black text-rose-600 block">
                                        {{ number_format($account->pending_amount, 2, ',', '.') }} BS
                                    </span>
                                </div>

                                <!-- Insignia de Estado -->
                                <div>
                                    @if ($account->status === 'paid')
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-green-50 text-green-700 border border-green-200">
                                            Liquidada
                                        </span>
                                    @elseif($account->status === 'cancelled')
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-red-50 text-red-700 border border-red-200">
                                            Rechazada
                                        </span>
                                    @elseif($account->status === 'partial')
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Parcial
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-lg text-xxs font-extrabold bg-gray-50 text-gray-700 border border-gray-200">
                                            Vigente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Detalle de Cuotas / Abonos ya cargados en esta deuda -->
                        <div class="p-6">
                            <span
                                class="block text-slate-400 font-bold text-xxs uppercase tracking-wider mb-4">Historial
                                de Cuotas y Abonos</span>

                            <div class="overflow-x-auto rounded-xl border border-slate-100">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-slate-50/80 text-slate-400 uppercase text-xxs font-bold tracking-wider border-b border-slate-100">
                                            <th class="py-3 px-4 hidden sm:table-cell">Referencia / Banco</th>
                                            <th class="py-3 px-4">Fecha de Reporte</th>
                                            <th class="py-3 px-4 text-right">Monto Reportado</th>
                                            <th class="py-3 px-4 text-center">Estado</th>
                                            <th class="py-3 px-4 hidden md:table-cell">Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @forelse($account->order->payments->sortByDesc('created_at') as $payment)
                                            <tr class="hover:bg-slate-50/10">
                                                <td class="py-3 px-4 hidden sm:table-cell">
                                                    <span
                                                        class="font-bold text-slate-600 block">{{ $payment->reference ?? 'S/R' }}</span>
                                                    <span
                                                        class="text-xxs text-slate-400 font-semibold">{{ $payment->paymentMethod->name ?? 'Método Borrado' }}</span>
                                                </td>
                                                <td class="py-3 px-4 text-slate-600 font-medium">
                                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}
                                                </td>
                                                <td
                                                    class="py-3 px-4 text-right font-extrabold text-slate-700 font-mono">
                                                    {{ number_format($payment->amount, 2, ',', '.') }} BS
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if ($payment->status === 'verified')
                                                        <span
                                                            class="inline-flex px-2 py-0.5 rounded-lg text-xxs font-extrabold bg-green-50 text-green-700 border border-green-200">
                                                            Aprobado
                                                        </span>
                                                    @elseif($payment->status === 'pending')
                                                        <span
                                                            class="inline-flex px-2 py-0.5 rounded-lg text-xxs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 shadow-sm animate-pulse">
                                                            En Revisión
                                                        </span>
                                                    @elseif($payment->status === 'rejected')
                                                        <span
                                                            class="inline-flex px-2 py-0.5 rounded-lg text-xxs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                                            Rechazado
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex px-2 py-0.5 rounded-lg text-xxs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                                            {{ ucfirst($payment->status) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-slate-500 text-xs truncate max-w-[12rem] hidden md:table-cell"
                                                    title="{{ $payment->notes }}">
                                                    {{ $payment->notes ?? 'Sin observaciones' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-slate-400">
                                                    No has reportado ningún comprobante para este pedido.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white rounded-2xl shadow-sm p-12 text-center text-slate-400 border border-slate-100 flex flex-col items-center justify-center gap-3">
                        <svg class="w-16 h-16 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-base font-bold text-slate-600">No posees deudas vigentes.</span>
                        <p class="text-sm">¡Estás al día con tus compromisos financieros! ¡Gracias por tu puntualidad!
                        </p>
                    </div>
                @endforelse
                @if ($accounts->hasPages())
                    {{ $accounts->links() }}
                @endif
            </div>
        </div>

        <div x-cloak x-show="reportPaymentOpen"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all duration-300">
            <div @click.away="reportPaymentOpen = false" x-show="reportPaymentOpen" x-transition.scale.95
                class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-lg max-h-[95vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden">

                <div
                    class="px-5 py-4 sm:px-6 sm:py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center border border-emerald-100 shrink-0">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-sm sm:text-base leading-tight">Reportar
                                Comprobante</h3>
                            <p class="text-slate-400 text-xxs sm:text-xs font-medium">Registra tu comprobante para
                                revisión</p>
                        </div>
                    </div>

                    <button type="button" @click="reportPaymentOpen = false"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('client.invoices.report') }}" method="POST" enctype="multipart/form-data"
                    @submit="setTimeout(() => isSubmitting = true, 50)" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    @csrf

                    @if ($errors->any())
                        <div
                            class="bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-xl text-xs font-bold shadow-sm mb-2">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div
                        class="bg-indigo-50 border border-indigo-100/50 rounded-2xl p-4 text-xs text-indigo-900 flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <span class="font-extrabold block mb-0.5">Nota de Operación</span>
                            <span>Al guardar, el administrador podrá revisar la captura y aplicar formalmente el abono a
                                tu deuda.</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">¿A cuál
                            pedido corresponde?</label>
                        <select name="account_receivable_id" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none font-semibold">
                            <option value="" disabled selected>Selecciona la cuenta...</option>
                            @foreach ($accounts->where('status', '!=', 'paid')->where('status', '!=', 'cancelled') as $acc)
                                <option value="{{ $acc->id }}"
                                    {{ old('account_receivable_id') == $acc->id ? 'selected' : '' }}>
                                    Pedido #{{ $acc->order->order_number }} - Pendiente:
                                    {{ number_format($acc->pending_amount, 2, ',', '.') }} BS
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Método
                                Utilizado</label>
                            <select name="payment_method_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">
                                <option value="" disabled selected>¿A dónde pagaste?</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}"
                                        {{ old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                        {{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Monto
                                Abono (BS)</label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                value="{{ old('amount') }}" required placeholder="0.00"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none font-bold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Ref.
                                Bancaria</label>
                            <input type="text" name="reference" value="{{ old('reference') }}" required
                                placeholder="Ej: 00156942"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none font-bold" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-700 font-extrabold text-xs uppercase tracking-wider">Captura
                                (Obligatorio)</label>
                            <input type="file" name="payment_proof"
                                accept="image/png, image/jpeg, image/jpg, image/webp" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-1.5 focus:bg-white text-xs transition-all outline-none file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-700 hover:file:bg-emerald-200" />
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse sm:flex-row items-center gap-3 pt-4 border-t border-slate-100 shrink-0 mt-4">
                        <button type="button" @click="reportPaymentOpen = false"
                            class="w-full sm:w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3.5 rounded-xl text-sm sm:text-xs transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full sm:flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 disabled:from-slate-300 disabled:to-slate-300 disabled:text-slate-500 text-white font-extrabold py-3.5 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-2 text-sm sm:text-xs cursor-pointer disabled:cursor-not-allowed">
                            <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg x-cloak x-show="isSubmitting" class="w-5 h-5 animate-spin"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Enviando...' : 'Enviar Comprobante'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function invoicesManager() {
            return {
                reportPaymentOpen: {{ $errors->any() ? 'true' : 'false' }},
                isSubmitting: false,
            };
        }
    </script>
</x-app-layout>
