@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
    <x-page-header :title="$employee->full_name" description="Ficha única del funcionario. Desde aquí se consulta y se agrega el resto de su información.">
        <x-slot:actions>
            <x-button :href="route('employees.edit', $employee)">Editar datos</x-button>
            <x-button :href="route('employees.index')" variant="secondary">Volver</x-button>
        </x-slot:actions>
    </x-page-header>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1.5 bg-gradient-to-r from-brand-600 to-accent-500"></div>
        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center">
            <x-avatar :name="$employee->full_name" :path="$employee->photo_path" size="xl" />
            <div class="min-w-0">
                <p class="text-sm text-slate-500">{{ $employee->formatted_rut }}</p>
                @if ($employee->position)
                    <p class="mt-1 text-base font-medium">{{ $employee->position }}</p>
                @endif
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-badge :tone="$employee->employment_quality->tone()">{{ $employee->employment_quality->label() }}</x-badge>
                    @if ($employee->grade)
                        <x-badge>Grado {{ $employee->grade }}</x-badge>
                    @endif
                    @if ($employee->age !== null)
                        <x-badge>{{ $employee->age }} años</x-badge>
                    @endif
                    <x-badge tone="slate">{{ $employee->department?->name ?? 'Sin departamento' }}</x-badge>
                    <x-badge tone="slate">{{ $employee->section?->name ?? 'Sin sección' }}</x-badge>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="font-semibold">Datos personales</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Nacimiento</dt><dd>{{ $employee->birth_date?->format('d-m-Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Edad</dt><dd>{{ $employee->age !== null ? $employee->age.' años' : '—' }}</dd></div>
                <div><dt class="text-slate-500">Sexo</dt><dd>{{ $employee->sex?->label() ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Estado civil</dt><dd>{{ $employee->marital_status?->label() ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Profesión</dt><dd>{{ $employee->profession ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Cargo</dt><dd>{{ $employee->position ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Estamento</dt><dd>{{ $employee->staff_level?->label() ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Calidad vigente</dt><dd>{{ $employee->employment_quality->label() }}</dd></div>
                <div><dt class="text-slate-500">Ingreso</dt><dd>{{ $employee->hired_on?->format('d-m-Y') ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Correo</dt><dd>{{ $employee->email ?: '—' }}</dd></div>
            </dl>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Roles en la organización</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div><dt class="text-slate-500">Jefatura de departamento</dt><dd>{{ $employee->headedDepartments->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Subrogancia de departamento</dt><dd>{{ $employee->deputyDepartments->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Analista de departamento</dt><dd>{{ $employee->analystDepartments->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Jefatura de sección</dt><dd>{{ $employee->headedSections->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Subrogancia de sección</dt><dd>{{ $employee->deputySections->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Analista de sección</dt><dd>{{ $employee->analystSections->pluck('name')->join(', ') ?: '—' }}</dd></div>
            </dl>
        </x-card>
    </div>

    <div class="mt-6 space-y-6">
        <x-record-section title="Cursos y capacitaciones" :create-url="route('training-courses.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Duración</th><th class="px-4 py-3">Certificación</th><th class="px-4 py-3">Vigencia</th><th class="px-4 py-3">Documento</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->trainingCourses as $course)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium">{{ $course->name }}</td>
                                <td class="px-4 py-3">{{ $course->duration }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $course->certified_on->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $course->valid_until?->format('d-m-Y') ?? '—' }}</td>
                                <td class="px-4 py-3"><x-file-link :path="$course->document_path" /></td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('training-courses.edit', [$course, 'employee_id' => $employee->id]) }}">Editar</a><x-delete :action="route('training-courses.destroy', $course)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty title="Sin cursos registrados." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>

        <x-record-section title="Licencias médicas" :create-url="route('medical-leaves.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Inicio</th><th class="px-4 py-3">Término</th><th class="px-4 py-3">Días</th><th class="px-4 py-3">Archivos</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->medicalLeaves as $leave)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">{{ $leave->leaveType->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $leave->starts_on->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $leave->ends_on->format('d-m-Y') }}</td>
                                <td class="px-4 py-3">{{ $leave->days_count }}</td>
                                <td class="px-4 py-3"><div class="flex flex-col gap-1"><x-file-link :path="$leave->document_path" label="Licencia" /><x-file-link :path="$leave->medical_resolution_path" label="Res. médica" /><x-file-link :path="$leave->internal_resolution_path" label="Res. interna" /></div></td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('medical-leaves.edit', [$leave, 'employee_id' => $employee->id]) }}">Editar</a><x-delete :action="route('medical-leaves.destroy', $leave)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty title="Sin licencias médicas." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>

        <x-record-section title="Cargas familiares" :create-url="route('family-dependents.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">RUT</th><th class="px-4 py-3">Parentesco</th><th class="px-4 py-3">Escolaridad</th><th class="px-4 py-3">Archivos</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->familyDependents as $dependent)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium">{{ $dependent->full_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $dependent->formatted_rut }}</td>
                                <td class="px-4 py-3">{{ $dependent->relationship->label() }}</td>
                                <td class="px-4 py-3">{{ $dependent->education_level->label() }}</td>
                                <td class="px-4 py-3"><div class="flex flex-col gap-1"><x-file-link :path="$dependent->birth_certificate_path" label="Cert. nacimiento" /><x-file-link :path="$dependent->resolution_path" label="Resolución" /></div></td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('family-dependents.edit', [$dependent, 'employee_id' => $employee->id]) }}">Editar</a><x-delete :action="route('family-dependents.destroy', $dependent)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty title="Sin cargas familiares." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>

        <x-record-section title="Contratos" :create-url="route('contracts.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Cargo</th><th class="px-4 py-3">Calidad</th><th class="px-4 py-3">Periodo</th><th class="px-4 py-3">Unidad</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->contracts as $contract)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium">{{ $contract->position }}</td>
                                <td class="px-4 py-3"><x-badge :tone="$contract->quality->tone()">{{ $contract->quality->label() }}</x-badge></td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $contract->starts_on->format('d-m-Y') }} — {{ $contract->ends_on?->format('d-m-Y') ?? 'Indefinido' }}</td>
                                <td class="px-4 py-3">{{ $contract->department?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('contracts.show', $contract) }}">Ver</a><x-delete :action="route('contracts.destroy', $contract)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty title="Sin contratos." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>

        <x-record-section title="Permisos" :create-url="route('permits.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Inicio</th><th class="px-4 py-3">Término</th><th class="px-4 py-3">Documento</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->permits as $permit)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">{{ $permit->type->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $permit->starts_at->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $permit->ends_at->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-3"><x-file-link :path="$permit->document_path" /></td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('permits.edit', [$permit, 'employee_id' => $employee->id]) }}">Editar</a><x-delete :action="route('permits.destroy', $permit)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty title="Sin permisos." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>

        <x-record-section title="Calificaciones" :create-url="route('evaluations.create', ['employee_id' => $employee->id])">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase">
                        <tr><th class="px-4 py-3">Periodo</th><th class="px-4 py-3">Proceso</th><th class="px-4 py-3">Nota</th><th class="px-4 py-3">Descripción</th><th class="px-4 py-3">Documento</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->evaluations as $evaluation)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $evaluation->period_start_year }}–{{ $evaluation->period_end_year }}</td>
                                <td class="px-4 py-3"><x-badge :tone="$evaluation->process->tone()">{{ $evaluation->process->label() }}</x-badge></td>
                                <td class="px-4 py-3">{{ $evaluation->score !== null ? number_format((float) $evaluation->score, 1, ',', '.') : '—' }}</td>
                                <td class="px-4 py-3 max-w-xs truncate">{{ $evaluation->notes ?: '—' }}</td>
                                <td class="px-4 py-3"><x-file-link :path="$evaluation->document_path" /></td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-3"><a class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200" href="{{ route('evaluations.edit', [$evaluation, 'employee_id' => $employee->id]) }}">Editar</a><x-delete :action="route('evaluations.destroy', $evaluation)"><input type="hidden" name="context_employee_id" value="{{ $employee->id }}"></x-delete></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty title="Sin calificaciones." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>
    </div>
@endsection
