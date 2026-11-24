@extends('layouts.app')

@section('title', 'Secciones')

@section('content')
    <x-page-header title="Secciones" description="Unidades de trabajo con jefatura, subrogancia y analistas. Pueden depender de un departamento.">
        <x-slot:actions>
            <x-button :href="route('sections.create')">Nueva sección</x-button>
        </x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.input name="search" label="Buscar" :value="request('search')" />
            <x-form.select name="department_id" label="Departamento" :options="$departments" :selected="request('department_id')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('sections.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="hidden px-4 py-3 md:table-cell">Departamento</th>
                        <th class="px-4 py-3">Jefe</th>
                        <th class="hidden px-4 py-3 lg:table-cell">Subrogante</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $section->name }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $section->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $section->head?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 lg:table-cell">{{ $section->deputy?->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('sections.edit', $section) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay secciones." :action="route('sections.create')" action-label="Crear sección" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sections->links() }}
    </x-card>
@endsection
