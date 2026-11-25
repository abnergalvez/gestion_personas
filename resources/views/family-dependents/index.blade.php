@extends('layouts.app')

@section('title', 'Cargas familiares')

@section('content')
    <x-report-list
        title="Cargas familiares"
        description="Personas a cargo declaradas por cada funcionario."
        export="family-dependents.export"
        :create="route('family-dependents.create')"
        create-label="Nueva carga"
        :filter-keys="['search', 'relationship', 'education_level', 'birth_from', 'birth_until']"
    >
        <x-slot:filters>
            <x-form.input name="search" label="Nombre o RUT de la carga" :value="request('search')" />
            <x-form.select name="relationship" label="Familiaridad" :options="$relationships" :selected="request('relationship')" placeholder="Todas" />
            <x-form.select name="education_level" label="Escolaridad" :options="$educationLevels" :selected="request('education_level')" placeholder="Todas" />
            <x-form.input name="birth_from" type="date" label="Nacimiento desde" :value="request('birth_from')" />
            <x-form.input name="birth_until" type="date" label="Nacimiento hasta" :value="request('birth_until')" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="name" label="Carga" />
                            <x-sort-heading column="employee" label="Funcionario" />
                            <x-sort-heading column="relationship" label="Familiaridad" class="hidden md:table-cell" />
                            <x-sort-heading column="education" label="Escolaridad" class="hidden lg:table-cell" />
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dependents as $dependent)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-semibold">{{ $dependent->full_name }}<p class="font-normal text-slate-500">{{ $dependent->formatted_rut }}</p></td>
                                <td class="px-4 py-3"><a href="{{ route('employees.show', $dependent->employee) }}" class="text-brand-700 hover:underline dark:text-blue-200">{{ $dependent->employee->full_name }}</a></td>
                                <td class="hidden px-4 py-3 md:table-cell">{{ $dependent->relationship->label() }}</td>
                                <td class="hidden px-4 py-3 lg:table-cell">{{ $dependent->education_level->label() }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('family-dependents.edit', $dependent) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty title="No hay cargas familiares." :action="route('family-dependents.create')" action-label="Registrar carga" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $dependents->links() }}
        </x-card>
    </x-report-list>
@endsection
