@extends('layouts.app')

@section('title', 'Contratos')

@section('content')
    <x-report-list
        title="Contratos"
        description="Historial contractual. Los respaldos se adjuntan dentro de cada contrato."
        export="contracts.export"
        :create="route('contracts.create')"
        create-label="Nuevo contrato"
        :stats="$stats"
        :filter-keys="['quality', 'modality', 'position', 'grade', 'starts_from', 'starts_until', 'ends_from', 'ends_until', 'department_id', 'section_id', 'term']"
    >
        <x-slot:filters>
            <x-form.select name="quality" label="Calidad del contrato" :options="$contractQualities" :selected="request('quality')" placeholder="Todas" />
            <x-form.select name="modality" label="Modalidad" :options="$modalities" :selected="request('modality')" placeholder="Todas" />
            <x-form.input name="position" label="Cargo del contrato" :value="request('position')" />
            <x-form.input name="grade" label="Grado" :value="request('grade')" />
            <x-form.input name="starts_from" type="date" label="Inicio desde" :value="request('starts_from')" />
            <x-form.input name="starts_until" type="date" label="Inicio hasta" :value="request('starts_until')" />
            <x-form.input name="ends_from" type="date" label="Término desde" :value="request('ends_from')" />
            <x-form.input name="ends_until" type="date" label="Término hasta" :value="request('ends_until')" />
            <x-form.select name="department_id" label="Departamento del contrato" :options="$contractDepartments" :selected="request('department_id')" placeholder="Todos" />
            <x-form.select name="section_id" label="Sección del contrato" :options="$contractSections" :selected="request('section_id')" placeholder="Todas" />
            <x-form.select name="term" label="Término" :options="['open' => 'Sin fecha de término', 'closed' => 'Con fecha de término']" :selected="request('term')" placeholder="Todos" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="employee" label="Funcionario" default="start" default-direction="desc" />
                            <x-sort-heading column="position" label="Cargo" default="start" default-direction="desc" />
                            <x-sort-heading column="quality" label="Calidad" default="start" default-direction="desc" class="hidden md:table-cell" />
                            <x-sort-heading column="modality" label="Modalidad" default="start" default-direction="desc" class="hidden lg:table-cell" />
                            <x-sort-heading column="start" label="Inicio" default="start" default-direction="desc" class="hidden md:table-cell" />
                            <x-sort-heading column="end" label="Término" default="start" default-direction="desc" />
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3"><a href="{{ route('employees.show', $contract->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $contract->employee->full_name }}</a></td>
                                <td class="px-4 py-3">{{ $contract->position }}</td>
                                <td class="hidden px-4 py-3 md:table-cell"><x-badge :tone="$contract->quality->tone()">{{ $contract->quality->label() }}</x-badge></td>
                                <td class="hidden px-4 py-3 lg:table-cell">{{ $contract->modality->label() }}</td>
                                <td class="hidden px-4 py-3 whitespace-nowrap md:table-cell">{{ $contract->starts_on->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $contract->ends_on?->format('d-m-Y') ?? 'Sin término' }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('contracts.show', $contract) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Ver</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-empty title="No hay contratos." :action="route('contracts.create')" action-label="Registrar contrato" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $contracts->links() }}
        </x-card>
    </x-report-list>
@endsection
