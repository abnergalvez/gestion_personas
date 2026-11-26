@extends('layouts.app')

@section('title', 'Calificaciones')

@section('content')
    <x-report-list
        title="Calificaciones"
        description="Proceso de evaluación del periodo: inicial, en evaluación, precalificación, apelación o final."
        export="evaluations.export"
        :create="route('evaluations.create')"
        create-label="Nueva calificación"
        :stats="$stats"
        :filter-keys="['process', 'period_start_year', 'period_end_year', 'score_min', 'score_max', 'notes']"
    >
        <x-slot:filters>
            <x-form.select name="process" label="Proceso" :options="$processes" :selected="request('process')" placeholder="Todos" />
            <x-form.input name="period_start_year" type="number" label="Año de inicio del periodo" :value="request('period_start_year')" min="1980" />
            <x-form.input name="period_end_year" type="number" label="Año de término del periodo" :value="request('period_end_year')" min="1980" />
            <x-form.input name="score_min" type="number" label="Nota desde" :value="request('score_min')" min="1" max="7" step="0.1" />
            <x-form.input name="score_max" type="number" label="Nota hasta" :value="request('score_max')" min="1" max="7" step="0.1" />
            <x-form.input name="notes" label="Descripción" :value="request('notes')" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="employee" label="Funcionario" default="period" default-direction="desc" />
                            <x-sort-heading column="period" label="Periodo" default="period" default-direction="desc" />
                            <x-sort-heading column="process" label="Proceso" default="period" default-direction="desc" />
                            <x-sort-heading column="score" label="Nota" default="period" default-direction="desc" class="hidden md:table-cell" />
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluations as $evaluation)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3"><a href="{{ route('employees.show', $evaluation->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $evaluation->employee->full_name }}</a></td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $evaluation->period_start_year }}–{{ $evaluation->period_end_year }}</td>
                                <td class="px-4 py-3"><x-badge :tone="$evaluation->process->tone()">{{ $evaluation->process->label() }}</x-badge></td>
                                <td class="hidden px-4 py-3 md:table-cell">{{ $evaluation->score !== null ? number_format((float) $evaluation->score, 1, ',', '.') : '—' }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('evaluations.edit', $evaluation) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty title="No hay calificaciones." :action="route('evaluations.create')" action-label="Registrar calificación" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $evaluations->links() }}
        </x-card>
    </x-report-list>
@endsection
