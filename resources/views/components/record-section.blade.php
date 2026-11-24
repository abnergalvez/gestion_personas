@props(['title', 'createUrl' => null, 'createLabel' => 'Agregar'])

<section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5 dark:border-slate-800">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
        @if ($createUrl)
            <x-button :href="$createUrl" variant="secondary" size="sm">{{ $createLabel }}</x-button>
        @endif
    </div>
    {{ $slot }}
</section>
