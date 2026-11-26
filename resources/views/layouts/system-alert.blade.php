<div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button
        type="button"
        class="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
        x-on:click="open = !open"
        x-bind:aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-label="{{ $systemAlertCount > 0 ? 'Errores del sistema, '.$systemAlertCount.' graves sin revisar' : 'Errores del sistema' }}"
    >
        <x-icon name="bell" />
        @if ($systemAlertCount > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent-500 px-1 text-xs font-semibold text-white">{{ $systemAlertCount > 9 ? '9+' : $systemAlertCount }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak x-on:click.outside="open = false" class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <p class="font-semibold">Errores del sistema</p>
            @if ($systemAlertCount > 0)
                <p class="text-sm text-slate-500">{{ $systemAlertCount }} {{ $systemAlertCount === 1 ? 'error grave sin revisar' : 'errores graves sin revisar' }}</p>
            @endif
        </div>
        @if ($systemAlert)
            <div class="px-4 py-3">
                <p class="truncate text-sm font-semibold text-accent-600 dark:text-red-300" title="{{ $systemAlert->summary() }}">{{ $systemAlert->summary() }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $systemAlert->created_at?->format('d-m-Y H:i') }}</p>
                <form method="POST" action="{{ route('system-errors.review', $systemAlert) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <x-button name="review" value="seen" variant="secondary" size="sm">Visto</x-button>
                    <x-button name="review" value="resolved" size="sm">Solucionado</x-button>
                </form>
            </div>
        @else
            <p class="px-4 py-3 text-sm text-slate-500">No hay errores graves pendientes.</p>
        @endif
        <a href="{{ route('system-errors.index', $systemAlert ? ['severity' => 'grave', 'review' => 'pending'] : []) }}" class="block border-t border-slate-200 px-4 py-2.5 text-sm font-semibold text-brand-700 hover:bg-slate-50 dark:border-slate-700 dark:text-blue-200 dark:hover:bg-slate-800">Ver el registro</a>
    </div>
</div>
