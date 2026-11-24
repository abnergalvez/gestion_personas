@props(['name', 'label', 'current' => null, 'hint' => 'PDF o imagen, máximo 8 MB.'])

@php
    $class = 'block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-800 hover:file:bg-brand-100 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-100';
@endphp

<x-form.field :label="$label" :name="$name" :hint="$hint">
    @if ($current)
        <p class="mb-2 text-sm"><x-file-link :path="$current" label="Ver archivo actual" /></p>
        <label class="mb-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remove_{{ $name }}" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600" @checked(old('remove_'.$name))>
            Quitar archivo actual
        </label>
    @endif
    <input type="file" name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => $class]) }}>
</x-form.field>
