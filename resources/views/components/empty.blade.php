@props(['title' => 'No hay registros para mostrar.', 'action' => null, 'actionLabel' => 'Crear'])

<div class="px-4 py-12 text-center">
    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $title }}</p>
    @if ($action)
        <div class="mt-4">
            <x-button :href="$action" variant="secondary">{{ $actionLabel }}</x-button>
        </div>
    @endif
</div>
