@props(['label', 'value', 'hint' => null, 'compact' => false])

@if ($compact)
    <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs leading-4 text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="shrink-0 text-lg font-semibold text-slate-900 tabular-nums dark:text-white">{{ $value }}</p>
    </div>
@else
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
@endif
