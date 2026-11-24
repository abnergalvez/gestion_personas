@extends('layouts.app')

@section('title', 'Cursos y capacitaciones')

@section('content')
    <x-page-header title="Cursos y capacitaciones" description="Capacitaciones asociadas a cada funcionario.">
        <x-slot:actions><x-button :href="route('training-courses.create')">Nuevo curso</x-button></x-slot:actions>
    </x-page-header>
    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-form.input name="search" label="Nombre del curso" :value="request('search')" />
            <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="request('employee_id')" placeholder="Todos" />
            <div class="flex items-end gap-2"><x-button>Filtrar</x-button><x-button :href="route('training-courses.index')" variant="ghost">Limpiar</x-button></div>
        </form>
    </x-card>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Curso</th><th class="px-4 py-3">Funcionario</th><th class="hidden px-4 py-3 md:table-cell">Certificación</th><th class="px-4 py-3">Documento</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $course->name }}<p class="font-normal text-slate-500">{{ $course->duration }}</p></td>
                            <td class="px-4 py-3"><a href="{{ route('employees.show', $course->employee) }}" class="text-brand-700 hover:underline dark:text-blue-200">{{ $course->employee->full_name }}</a></td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $course->certified_on->format('d-m-Y') }}</td>
                            <td class="px-4 py-3"><x-file-link :path="$course->document_path" /></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('training-courses.edit', $course) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay cursos." :action="route('training-courses.create')" action-label="Registrar curso" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $courses->links() }}
    </x-card>
@endsection
