@props(['tone' => 'slate'])

@php
    $tones = [
        'blue' => 'bg-brand-50 text-brand-800 dark:bg-brand-600/20 dark:text-blue-100',
        'red' => 'bg-accent-50 text-accent-600 dark:bg-accent-500/15 dark:text-red-100',
        'green' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-100',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-100',
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.$tones[$tone]]) }}>{{ $slot }}</span>
