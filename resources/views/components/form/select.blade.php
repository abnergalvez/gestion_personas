@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => 'Seleccione', 'hint' => null])

@php
    $class = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    $current = old($name, $selected);
@endphp

<x-form.field :label="$label" :name="$name" :hint="$hint">
    <select name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => $class]) }}>
        @if ($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
</x-form.field>
