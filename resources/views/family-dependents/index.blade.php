@extends('layouts.app')

@section('title', 'Cargas familiares')

@section('content')
    <x-page-header title="Cargas familiares" description="Personas a cargo declaradas por cada funcionario.">
        <x-slot:actions><x-button :href="route('family-dependents.create')">Nueva carga</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.input name="search" label="Nombre o RUT" :value="request('search')" />
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('family-dependents.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Carga</th><th class="px-4 py-3">Funcionario</th><th class="hidden px-4 py-3 md:table-cell">Parentesco</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($dependents as $dependent)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $dependent->full_name }}<p class="font-normal text-slate-500">{{ $dependent->formatted_rut }}</p></td>
                            <td class="px-4 py-3"><a href="{{ route('employees.show', $dependent->employee) }}" class="text-brand-700 hover:underline dark:text-blue-200">{{ $dependent->employee->full_name }}</a></td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $dependent->relationship->label() }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('family-dependents.edit', $dependent) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay cargas familiares." :action="route('family-dependents.create')" action-label="Registrar carga" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $dependents->links() }}
    </x-card>
@endsection
