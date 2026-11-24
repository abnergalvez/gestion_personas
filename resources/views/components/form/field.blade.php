@props(['label', 'name', 'hint' => null])

@php
    $messages = array_values(array_unique(array_merge(
        $errors->get($name),
        $errors->get($name.'.*'),
    )));
    $invalid = $messages !== [];
@endphp

<div @class(['[&_input]:!border-accent-500 [&_select]:!border-accent-500 [&_textarea]:!border-accent-500' => $invalid])>
    <label for="{{ $name }}" @class([
        'mb-1.5 block text-sm font-semibold',
        'text-accent-600 dark:text-red-300' => $invalid,
        'text-slate-700 dark:text-slate-200' => ! $invalid,
    ])>{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @foreach ($messages as $message)
        <p class="mt-1.5 text-sm text-accent-600 dark:text-red-300">{{ $message }}</p>
    @endforeach
</div>
