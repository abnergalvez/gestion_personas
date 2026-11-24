@extends('layouts.app')

@section('title', 'Calificaciones')

@section('content')
    <x-page-header title="Calificaciones" description="Proceso de evaluación del periodo: inicial, en evaluación, precalificación, apelación o final.">
        <x-slot:actions><x-button :href="route('evaluations.create')">Nueva calificación</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <x-form.select name="process" label="Proceso" :options="$processes" :selected="request('process')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('evaluations.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Funcionario</th><th class="px-4 py-3">Periodo</th><th class="px-4 py-3">Proceso</th><th class="hidden px-4 py-3 md:table-cell">Nota</th><th class="px-4 py-3"></th></tr></thead>
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
@endsection
