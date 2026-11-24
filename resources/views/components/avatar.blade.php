@props(['name', 'path' => null, 'size' => 'md'])

@php
    $parts = array_values(array_filter(preg_split('/\s+/u', trim($name)) ?: []));
    $initials = mb_strtoupper(mb_substr($parts[0] ?? 'A', 0, 1).mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
    $sizes = [
        'sm' => 'h-9 w-9 text-xs',
        'md' => 'h-11 w-11 text-sm',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-24 w-24 text-2xl',
    ];
@endphp

@if ($path)
    <img src="{{ route('files.show', ['path' => $path]) }}" alt="Fotografía de {{ $name }}" {{ $attributes->merge(['class' => $sizes[$size].' rounded-xl object-cover bg-white ring-1 ring-slate-200 dark:ring-slate-700']) }}>
@else
    <span {{ $attributes->merge(['class' => $sizes[$size].' inline-flex items-center justify-center rounded-xl bg-brand-600 font-semibold text-white']) }} aria-hidden="true">{{ $initials }}</span>
@endif
