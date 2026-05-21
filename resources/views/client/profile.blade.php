<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                {{ __('Mi Perfil y Preferencias') }}
            </h2>
            <p class="text-xs text-slate-500 mt-1">Mantén al día tus datos de facturación, dirección de despacho y claves de acceso.</p>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        
        <!-- Alertas de éxito y error -->
        @if(session('success'))
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 mb-6">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 mb-6">
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex flex-col gap-1 shadow-sm">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span class="font-semibold">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <!-- Banner de cabecera de perfil -->
                <div class="bg-gradient-to-r from-slate-900 to-indigo-950 px-8 py-8 text-white relative border-b border-slate-800">
                    <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-center text-center sm:text-left gap-4">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 text-2xl font-black text-white shrink-0 shadow-inner">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold tracking-tight">{{ $user->name }}</h3>
                            <p class="text-slate-400 text-xs mt-1">
                                Identificación: <span class="font-bold text-slate-300">{{ $client->identification }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full bg-indigo-500 opacity-20 blur-2xl"></div>
                </div>

                <!-- Formulario de Configuración General -->
                <form action="{{ route('client.profile.update') }}" method="POST" class="p-8 space-y-8">
                    @csrf
                    @method('PATCH')

                    <!-- Sección 1: Datos Personales -->
                    <div class="space-y-6">
                        <h4 class="text-slate-800 font-extrabold text-sm uppercase tracking-wider border-b border-slate-50 pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Datos Personales y de Facturación
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Nombre Completo o Razón Social</label>
                                <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>

                            <!-- Cédula / RIF -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Cédula de Identidad / RIF</label>
                                <input type="text" name="identification" required value="{{ old('identification', $client->identification) }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Correo Electrónico</label>
                                <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>

                            <!-- Teléfono -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Teléfono de Contacto</label>
                                <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" placeholder="Ej: 04121234567" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>
                        </div>

                        <!-- Dirección de Despacho -->
                        <div class="space-y-2">
                            <label class="block text-slate-700 font-extrabold text-xs">Dirección de Despacho Predeterminada</label>
                            <textarea name="address" rows="3" placeholder="Ingresa tu dirección de envío prediseñada..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">{{ old('address', $client->address) }}</textarea>
                        </div>
                    </div>

                    <!-- Sección 2: Seguridad / Clave -->
                    <div class="space-y-6 pt-4 border-t border-slate-100">
                        <h4 class="text-slate-800 font-extrabold text-sm uppercase tracking-wider border-b border-slate-50 pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Seguridad de la Cuenta (Opcional)
                        </h4>

                        <div class="bg-amber-50 border border-amber-100/50 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-2.5">
                            <svg class="w-5.5 h-5.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Completa estos campos únicamente si deseas actualizar tu contraseña actual de acceso. De lo contrario, déjalos en blanco.</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nueva Contraseña -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Nueva Contraseña</label>
                                <input type="password" name="password" placeholder="Mínimo 8 caracteres" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="space-y-2">
                                <label class="block text-slate-700 font-extrabold text-xs">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" placeholder="Repite la contraseña" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Guardar Cambios -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3.5 px-8 rounded-xl shadow-lg shadow-indigo-600/15 hover:shadow-indigo-600/25 transition-all duration-300 inline-flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Guardar Cambios de Cuenta
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>
