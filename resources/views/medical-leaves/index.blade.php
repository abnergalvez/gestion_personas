@extends('layouts.app')

@section('title', 'Licencias médicas')

@section('content')
    <x-report-list
        title="Licencias médicas"
        description="Periodos, días y respaldos de cada licencia."
        export="medical-leaves.export"
        :create="route('medical-leaves.create')"
        create-label="Nueva licencia"
        :filter-keys="['leave_type_id', 'starts_from', 'starts_until', 'ends_from', 'ends_until', 'days_min', 'days_max']"
    >
        <x-slot:filters>
            <x-form.select name="leave_type_id" label="Tipo de licencia" :options="$leaveTypes" :selected="request('leave_type_id')" placeholder="Todos" />
            <x-form.input name="starts_from" type="date" label="Inicio desde" :value="request('starts_from')" />
            <x-form.input name="starts_until" type="date" label="Inicio hasta" :value="request('starts_until')" />
            <x-form.input name="ends_from" type="date" label="Término desde" :value="request('ends_from')" />
            <x-form.input name="ends_until" type="date" label="Término hasta" :value="request('ends_until')" />
            <x-form.input name="days_min" type="number" label="Días desde" :value="request('days_min')" min="1" />
            <x-form.input name="days_max" type="number" label="Días hasta" :value="request('days_max')" min="1" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="employee" label="Funcionario" default="period" default-direction="desc" />
                            <x-sort-heading column="type" label="Tipo" default="period" default-direction="desc" />
                            <x-sort-heading column="period" label="Periodo" default="period" default-direction="desc" />
                            <x-sort-heading column="days" label="Días" default="period" default-direction="desc" class="hidden md:table-cell" />
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $leave)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3"><a href="{{ route('employees.show', $leave->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $leave->employee->full_name }}</a></td>
                                <td class="px-4 py-3">{{ $leave->leaveType->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $leave->starts_on->format('d-m-Y') }} — {{ $leave->ends_on->format('d-m-Y') }}</td>
                                <td class="hidden px-4 py-3 md:table-cell">{{ $leave->days_count }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('medical-leaves.edit', $leave) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty title="No hay licencias." :action="route('medical-leaves.create')" action-label="Registrar licencia" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $leaves->links() }}
        </x-card>
    </x-report-list>
@endsection
