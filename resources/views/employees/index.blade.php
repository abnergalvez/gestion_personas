@extends('layouts.app')

@section('title', 'Funcionarios')

@section('content')
    <x-page-header title="Funcionarios" description="Ficha personal, unidad de destino y calidad contractual vigente.">
        <x-slot:actions>
            <x-button :href="route('employees.create')">Nuevo funcionario</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-4">
            <x-form.input name="search" label="Buscar" :value="request('search')" placeholder="Nombre o RUT" />
            <x-form.select name="department_id" label="Departamento" :options="$departments" :selected="request('department_id')" placeholder="Todos" />
            <x-form.select name="employment_quality" label="Calidad" :options="$qualities" :selected="request('employment_quality')" placeholder="Todas" />
            <div class="flex items-end gap-2">
                <x-button>Filtrar</x-button>
                <x-button :href="route('employees.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Funcionario</th>
                        <th class="px-4 py-3 font-semibold">RUT</th>
                        <th class="hidden px-4 py-3 font-semibold md:table-cell">Departamento</th>
                        <th class="hidden px-4 py-3 font-semibold lg:table-cell">Sección</th>
                        <th class="px-4 py-3 font-semibold">Calidad</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3">
                                <a href="{{ route('employees.show', $employee) }}" class="flex items-center gap-3 font-semibold text-brand-800 hover:underline dark:text-blue-100">
                                    <x-avatar :name="$employee->full_name" :path="$employee->photo_path" size="sm" />
                                    <span>
                                        {{ $employee->full_name }}
                                        @if ($employee->position)
                                            <span class="mt-0.5 block text-sm font-normal text-slate-500">{{ $employee->position }}</span>
                                        @endif
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $employee->formatted_rut }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $employee->department?->name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 lg:table-cell">{{ $employee->section?->name ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :tone="$employee->employment_quality->tone()">{{ $employee->employment_quality->label() }}</x-badge></td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('employees.show', $employee) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Ver</a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="text-sm font-semibold text-slate-600 hover:underline dark:text-slate-300">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty title="No hay funcionarios con esos filtros." :action="route('employees.create')" action-label="Registrar funcionario" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $employees->links() }}
    </x-card>
@endsection
