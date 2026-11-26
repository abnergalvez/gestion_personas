@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <x-page-header title="Inicio" description="Resumen de la dotación y accesos directos para la gestión diaria." />

    <section class="mb-6 rounded-2xl border border-brand-100 bg-brand-50 p-5 sm:p-6 dark:border-brand-800 dark:bg-brand-600/20" aria-labelledby="birthday-week-title">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-brand-800 dark:text-blue-100">
                    <x-icon name="cake" class="h-5 w-5 shrink-0" />
                    <h2 id="birthday-week-title" class="font-semibold">Cumpleaños de la semana</h2>
                </div>
                <p class="mt-2 text-sm text-brand-900 dark:text-blue-100">
                    Del {{ $birthdayWeek->rangeLabel() }}.
                    Este aviso se muestra desde el viernes {{ $birthdayWeek->opensOn()->format('d-m-Y') }} a las 12:00
                    hasta el viernes {{ $birthdayWeek->closesOn()->format('d-m-Y') }} a las 12:00.
                </p>
            </div>
            <x-button
                :href="route('birthdays.index', ['year' => $birthdayWeek->startsOn->year, 'month' => $birthdayWeek->startsOn->month, 'week' => $birthdayWeek->startsOn->toDateString()])"
                variant="secondary"
                class="shrink-0"
            >Buscar por mes</x-button>
        </div>
        <div class="mt-4">
            <x-birthday-list :entries="$birthdays" />
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Funcionarios" :value="$cards['employees']" />
        <x-stat label="Departamentos" :value="$cards['departments']" />
        <x-stat label="Secciones" :value="$cards['sections']" />
        <x-stat label="Cargas familiares" :value="$cards['dependents']" />
        <x-stat label="Licencias vigentes" :value="$cards['active_leaves']" hint="Con fecha de término hoy o posterior" />
        <x-stat label="Contratos por vencer" :value="$cards['expiring_contracts']" :hint="'Dentro de los próximos '.$cards['contract_notice_days'].' días'" />
        <x-stat label="Permisos del mes" :value="$cards['permits_this_month']" />
        <x-stat label="Promedio de notas" :value="$cards['average_score'] !== null ? number_format((float) $cards['average_score'], 1, ',', '.') : '—'" hint="Escala de 1 a 7" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-card>
            <h2 class="font-semibold">Funcionarios por calidad</h2>
            @if (collect($charts['quality']['values'])->sum() > 0)
                <div class="mt-4 h-64"><canvas id="chart-quality"></canvas></div>
            @else
                <p class="mt-8 text-sm text-slate-500">Aún no hay funcionarios para graficar.</p>
            @endif
        </x-card>
        <x-card>
            <h2 class="font-semibold">Contratos por calidad</h2>
            @if (collect($charts['contracts']['values'])->sum() > 0)
                <div class="mt-4 h-64"><canvas id="chart-contracts"></canvas></div>
            @else
                <p class="mt-8 text-sm text-slate-500">Aún no hay contratos para graficar.</p>
            @endif
        </x-card>
        <x-card>
            <h2 class="font-semibold">Funcionarios por departamento</h2>
            @if (collect($charts['departments']['values'])->sum() > 0)
                <div class="mt-4 h-64"><canvas id="chart-departments"></canvas></div>
            @else
                <p class="mt-8 text-sm text-slate-500">Aún no hay funcionarios asignados a un departamento.</p>
            @endif
        </x-card>
        <x-card>
            <h2 class="font-semibold">Licencias médicas, últimos 6 meses</h2>
            <div class="mt-4 h-64"><canvas id="chart-leaves"></canvas></div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        <x-card class="xl:col-span-1">
            <h2 class="font-semibold">Acciones rápidas</h2>
            <div class="mt-4 grid gap-2">
                <x-button :href="route('employees.create')" variant="secondary" class="justify-start">Registrar funcionario</x-button>
                <x-button :href="route('contracts.create')" variant="secondary" class="justify-start">Registrar contrato</x-button>
                <x-button :href="route('medical-leaves.create')" variant="secondary" class="justify-start">Registrar licencia médica</x-button>
                <x-button :href="route('permits.create')" variant="secondary" class="justify-start">Registrar permiso</x-button>
                <x-button :href="route('training-courses.create')" variant="secondary" class="justify-start">Registrar curso</x-button>
                <x-button :href="route('evaluations.create')" variant="secondary" class="justify-start">Registrar calificación</x-button>
            </div>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Licencias vigentes</h2>
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($activeLeaves as $leave)
                    <li class="py-3">
                        <a href="{{ route('employees.show', $leave->employee) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $leave->employee->full_name }}</a>
                        <p class="text-sm text-slate-500">{{ $leave->leaveType->name }} · hasta {{ $leave->ends_on->format('d-m-Y') }}</p>
                    </li>
                @empty
                    <li class="py-6 text-sm text-slate-500">No hay licencias vigentes.</li>
                @endforelse
            </ul>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Contratos que vencen pronto</h2>
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($expiringContracts as $contract)
                    <li class="py-3">
                        <a href="{{ route('contracts.show', $contract) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $contract->employee->full_name }}</a>
                        <p class="text-sm text-slate-500">{{ $contract->position }} · {{ $contract->ends_on->format('d-m-Y') }}</p>
                    </li>
                @empty
                    <li class="py-6 text-sm text-slate-500">No hay contratos por vencer en {{ $cards['contract_notice_days'] }} días.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <script id="dashboard-charts" type="application/json">@json($charts)</script>
@endsection

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush
