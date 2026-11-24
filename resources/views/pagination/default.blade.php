@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
        <p class="text-slate-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="rounded-lg px-3 py-1.5 text-slate-400">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg px-3 py-1.5 font-semibold text-brand-700 hover:bg-brand-50 dark:text-blue-200 dark:hover:bg-slate-800">Anterior</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg px-3 py-1.5 font-semibold text-brand-700 hover:bg-brand-50 dark:text-blue-200 dark:hover:bg-slate-800">Siguiente</a>
            @else
                <span class="rounded-lg px-3 py-1.5 text-slate-400">Siguiente</span>
            @endif
        </div>
    </div>
@endif
