@props(['path' => null, 'label' => 'Ver'])

@if ($path)
    <a href="{{ route('files.show', ['path' => $path]) }}" target="_blank" rel="noopener" class="font-semibold text-brand-700 underline decoration-brand-200 underline-offset-2 hover:text-brand-800 dark:text-blue-200">{{ $label }}</a>
@else
    <span class="text-slate-400">Sin archivo</span>
@endif
