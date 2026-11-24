@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
    <x-page-header title="Permisos" description="Permisos administrativos y otras ausencias autorizadas. No corresponde a permisos de acceso al sistema.">
        <x-slot:actions><x-button :href="route('permits.create')">Nuevo permiso</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <x-form.select name="permit_type_id" label="Tipo" :options="$permitTypes" :selected="request('permit_type_id')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('permits.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Funcionario</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Inicio</th><th class="hidden px-4 py-3 md:table-cell">Término</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($permits as $permit)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3"><a href="{{ route('employees.show', $permit->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $permit->employee->full_name }}</a></td>
                            <td class="px-4 py-3">{{ $permit->type->name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $permit->starts_at->format('d-m-Y H:i') }}</td>
                            <td class="hidden px-4 py-3 whitespace-nowrap md:table-cell">{{ $permit->ends_at->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('permits.edit', $permit) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay permisos." :action="route('permits.create')" action-label="Registrar permiso" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $permits->links() }}
    </x-card>
@endsection
