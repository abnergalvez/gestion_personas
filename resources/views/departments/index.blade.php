@extends('layouts.app')

@section('title', 'Departamentos')

@section('content')
    <x-page-header title="Departamentos" description="Cada departamento tiene un jefe, un subrogante y puede tener varios analistas.">
        <x-slot:actions>
            <x-button :href="route('departments.create')">Nuevo departamento</x-button>
        </x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <x-form.input name="search" label="Buscar" :value="request('search')" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('departments.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Jefe</th>
                        <th class="hidden px-4 py-3 md:table-cell">Subrogante</th>
                        <th class="px-4 py-3">Dotación</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $department->name }}</td>
                            <td class="px-4 py-3">{{ $department->head?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $department->deputy?->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $department->employees_count }} funcionarios · {{ $department->analysts_count }} analistas</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('departments.edit', $department) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay departamentos." :action="route('departments.create')" action-label="Crear departamento" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $departments->links() }}
    </x-card>
@endsection
