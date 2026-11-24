@extends('layouts.app')

@section('title', 'Contratos')

@section('content')
    <x-page-header title="Contratos" description="Historial contractual. Los respaldos se adjuntan dentro de cada contrato.">
        <x-slot:actions><x-button :href="route('contracts.create')">Nuevo contrato</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <x-form.select name="quality" label="Calidad" :options="$qualities" :selected="request('quality')" placeholder="Todas" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('contracts.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Funcionario</th><th class="px-4 py-3">Cargo</th><th class="hidden px-4 py-3 md:table-cell">Calidad</th><th class="px-4 py-3">Término</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3"><a href="{{ route('employees.show', $contract->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $contract->employee->full_name }}</a></td>
                            <td class="px-4 py-3">{{ $contract->position }}</td>
                            <td class="hidden px-4 py-3 md:table-cell"><x-badge :tone="$contract->quality->tone()">{{ $contract->quality->label() }}</x-badge></td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $contract->ends_on?->format('d-m-Y') ?? 'Indefinido' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('contracts.show', $contract) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay contratos." :action="route('contracts.create')" action-label="Registrar contrato" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $contracts->links() }}
    </x-card>
@endsection
