@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navegación de Paginación" class="flex items-center justify-between">
            
            {{-- Vista para Teléfonos Móviles (Solo botones Anterior / Siguiente) --}}
            <div class="flex justify-between flex-1 sm:hidden gap-3">
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-xl">
                        Anterior
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                        Anterior
                    </button>
                @endif

                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                        Siguiente
                    </button>
                @else
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-xl">
                        Siguiente
                    </span>
                @endif
            </div>

            {{-- Vista para Computadoras / Pantallas Grandes --}}
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                
                {{-- Texto de Resultados --}}
                <div>
                    <p class="text-sm text-slate-500 font-medium">
                        Mostrando del
                        <span class="font-black text-slate-800">{{ $paginator->firstItem() }}</span>
                        al
                        <span class="font-black text-slate-800">{{ $paginator->lastItem() }}</span>
                        de
                        <span class="font-black text-slate-800">{{ $paginator->total() }}</span>
                        resultados
                    </p>
                </div>

                {{-- Botones Numerados --}}
                <div>
                    <span class="relative z-0 inline-flex shadow-sm rounded-xl gap-1.5">
                        
                        {{-- Botón Anterior --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="Anterior">
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-lg" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @else
                            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm" aria-label="Anterior">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Elementos de Paginación (Números y Puntos) --}}
                        @foreach ($elements as $element)
                            {{-- Separador de 3 puntos "..." --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 bg-transparent cursor-default">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Arreglo de Links Numerados --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                {{-- ESTILO DEL BOTÓN ACTIVO --}}
                                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-black text-white bg-indigo-600 border border-indigo-600 rounded-lg shadow-md shadow-indigo-600/20">{{ $page }}</span>
                                            </span>
                                        @else
                                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Botón Siguiente --}}
                        @if ($paginator->hasMorePages())
                            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm" aria-label="Siguiente">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="Siguiente">
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-lg" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
