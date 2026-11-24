@props(['name', 'label', 'value' => null, 'hint' => null, 'rows' => 4])

@php
    $class = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
@endphp

<x-form.field :label="$label" :name="$name" :hint="$hint">
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => $class]) }}>{{ old($name, $value) }}</textarea>
</x-form.field>
