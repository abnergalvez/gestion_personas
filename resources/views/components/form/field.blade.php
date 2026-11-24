@props(['label', 'name', 'hint' => null])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-sm text-accent-600 dark:text-red-300">{{ $message }}</p>
    @enderror
</div>
