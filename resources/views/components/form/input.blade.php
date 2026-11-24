@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

@php
    $class = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
@endphp

<x-form.field :label="$label" :name="$name" :hint="$hint">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->merge(['class' => $class]) }}
    >
</x-form.field>
