@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <x-page-header title="Inicio" description="Resumen de la dotación y accesos directos para la gestión diaria." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Funcionarios" :value="$cards['employees']" />
        <x-stat label="Departamentos" :value="$cards['departments']" />
        <x-stat label="Secciones" :value="$cards['sections']" />
        <x-stat label="Cargas familiares" :value="$cards['dependents']" />
        <x-stat label="Licencias vigentes" :value="$cards['active_leaves']" hint="Con fecha de término hoy o posterior" />
        <x-stat label="Contratos por vencer" :value="$cards['expiring_contracts']" hint="Dentro de los próximos 60 días" />
        <x-stat label="Permisos del mes" :value="$cards['permits_this_month']" />
        <x-stat label="Promedio de notas" :value="$cards['average_score'] !== null ? number_format((float) $cards['average_score'], 1, ',', '.') : '—'" hint="Escala de 1 a 7" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-card>
            <h2 class="font-semibold">Funcionarios por calidad</h2>
            <div class="mt-4 h-64"><canvas id="chart-quality"></canvas></div>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Contratos por calidad</h2>
            <div class="mt-4 h-64"><canvas id="chart-contracts"></canvas></div>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Funcionarios por departamento</h2>
            <div class="mt-4 h-64"><canvas id="chart-departments"></canvas></div>
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
                    <li class="py-6 text-sm text-slate-500">No hay contratos por vencer en 60 días.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <script id="dashboard-charts" type="application/json">@json($charts)</script>
@endsection

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush
