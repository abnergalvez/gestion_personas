@props([
    'title',
    'description',
    'export',
    'create',
    'createLabel',
    'filterKeys' => [],
    'stats' => [],
])

@php
    $activeFilters = collect(array_merge(\App\Support\ReportFilters::FIELDS, $filterKeys))
        ->filter(fn (string $field) => filled(request($field)))
        ->count();
@endphp

<div x-data="{ filters: false }">
    <x-page-header :title="$title" :description="$description">
        <x-slot:actions>
            <x-per-page />
            <x-button type="button" variant="secondary" x-on:click="filters = ! filters">
                Filtros{{ $activeFilters > 0 ? ' ('.$activeFilters.')' : '' }}
            </x-button>
            <x-button variant="secondary" :href="route($export, request()->except('page'))">Exportar Excel</x-button>
            <x-button :href="$create">{{ $createLabel }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($stats !== [])
        <x-stat-row :stats="$stats" />
    @endif

    <div x-show="filters" x-cloak class="mb-4">
        <x-card>
            <form method="GET" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach (['sort', 'direction', 'per_page'] as $kept)
                    @if (filled(request($kept)))
                        <input type="hidden" name="{{ $kept }}" value="{{ request($kept) }}">
                    @endif
                @endforeach
                @include('partials.employee-filters', \App\Support\ReportFilters::viewData())
                @isset($filters)
                    {{ $filters }}
                @endisset
                <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-3">
                    <x-button>Filtrar</x-button>
                    <x-button :href="url()->current()" variant="ghost">Limpiar</x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{ $slot }}
</div>
