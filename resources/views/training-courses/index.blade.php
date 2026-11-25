@extends('layouts.app')

@section('title', 'Cursos y capacitaciones')

@section('content')
    <x-report-list
        title="Cursos y capacitaciones"
        description="Capacitaciones asociadas a cada funcionario."
        export="training-courses.export"
        :create="route('training-courses.create')"
        create-label="Nuevo curso"
        :filter-keys="['search', 'duration', 'certified_from', 'certified_until', 'valid_from', 'valid_until']"
    >
        <x-slot:filters>
            <x-form.input name="search" label="Nombre del curso" :value="request('search')" />
            <x-form.input name="duration" label="Duración" :value="request('duration')" placeholder="40 horas" />
            <x-form.input name="certified_from" type="date" label="Certificación desde" :value="request('certified_from')" />
            <x-form.input name="certified_until" type="date" label="Certificación hasta" :value="request('certified_until')" />
            <x-form.input name="valid_from" type="date" label="Vigencia desde" :value="request('valid_from')" />
            <x-form.input name="valid_until" type="date" label="Vigencia hasta" :value="request('valid_until')" />
        </x-slot:filters>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                        <tr>
                            <x-sort-heading column="name" label="Curso" default="certified" default-direction="desc" />
                            <x-sort-heading column="employee" label="Funcionario" default="certified" default-direction="desc" />
                            <x-sort-heading column="certified" label="Certificación" default="certified" default-direction="desc" class="hidden md:table-cell" />
                            <x-sort-heading column="valid" label="Vigencia" default="certified" default-direction="desc" class="hidden lg:table-cell" />
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-semibold">{{ $course->name }}<p class="font-normal text-slate-500">{{ $course->duration }}</p></td>
                                <td class="px-4 py-3"><a href="{{ route('employees.show', $course->employee) }}" class="text-brand-700 hover:underline dark:text-blue-200">{{ $course->employee->full_name }}</a></td>
                                <td class="hidden px-4 py-3 md:table-cell">{{ $course->certified_on->format('d-m-Y') }}</td>
                                <td class="hidden px-4 py-3 lg:table-cell">{{ $course->valid_until?->format('d-m-Y') ?? '—' }}</td>
                                <td class="px-4 py-3"><x-file-link :path="$course->document_path" /></td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('training-courses.edit', $course) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty title="No hay cursos." :action="route('training-courses.create')" action-label="Registrar curso" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $courses->links() }}
        </x-card>
    </x-report-list>
@endsection
