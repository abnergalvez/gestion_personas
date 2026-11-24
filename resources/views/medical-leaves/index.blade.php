@extends('layouts.app')

@section('title', 'Licencias médicas')

@section('content')
    <x-page-header title="Licencias médicas" description="Periodos, días y respaldos de cada licencia.">
        <x-slot:actions><x-button :href="route('medical-leaves.create')">Nueva licencia</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <x-form.select name="leave_type_id" label="Tipo" :options="$leaveTypes" :selected="request('leave_type_id')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('medical-leaves.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Funcionario</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Periodo</th><th class="hidden px-4 py-3 md:table-cell">Días</th><th class="px-4 py-3"></th></tr></thead>
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
@endsection
