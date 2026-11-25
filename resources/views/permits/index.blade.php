@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
    <x-report-list
        title="Permisos"
        description="Permisos administrativos y otras ausencias autorizadas. No corresponde a permisos de acceso al sistema."
        export="permits.export"
        :create="route('permits.create')"
        create-label="Nuevo permiso"
        :filter-keys="['permit_type_id', 'starts_from', 'starts_until', 'ends_from', 'ends_until']"
    >
        <x-slot:filters>
            <x-form.select name="permit_type_id" label="Tipo de permiso" :options="$permitTypes" :selected="request('permit_type_id')" placeholder="Todos" />
            <x-form.input name="starts_from" type="date" label="Inicio desde" :value="request('starts_from')" />
            <x-form.input name="starts_until" type="date" label="Inicio hasta" :value="request('starts_until')" />
            <x-form.input name="ends_from" type="date" label="Término desde" :value="request('ends_from')" />
            <x-form.input name="ends_until" type="date" label="Término hasta" :value="request('ends_until')" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="employee" label="Funcionario" default="start" default-direction="desc" />
                            <x-sort-heading column="type" label="Tipo" default="start" default-direction="desc" />
                            <x-sort-heading column="start" label="Inicio" default="start" default-direction="desc" />
                            <x-sort-heading column="end" label="Término" default="start" default-direction="desc" class="hidden md:table-cell" />
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
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
    </x-report-list>
@endsection
