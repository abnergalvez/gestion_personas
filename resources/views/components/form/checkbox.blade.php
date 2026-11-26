@props(['name', 'label', 'checked' => false, 'hint' => null])

<div>
    <input type="hidden" name="{{ $name }}" value="0">
    <label for="{{ $name }}" class="flex items-start gap-3">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            @checked(filter_var(old($name, $checked), FILTER_VALIDATE_BOOLEAN))
            {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-slate-300']) }}
        >
        <span>
            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</span>
            @if ($hint)
                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>
            @endif
        </span>
    </label>
</div>
