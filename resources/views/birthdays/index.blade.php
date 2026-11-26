@extends('layouts.app')

@section('title', 'Cumpleaños')

@section('content')
    <x-page-header title="Cumpleaños" description="La semana va de sábado a viernes. Elija el mes y, si quiere, una sola de sus semanas." />

    <x-stat-row :stats="$stats" />

    <x-card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-form.select name="year" label="Año" :options="$years" :selected="$year" :placeholder="false" />
            <x-form.select name="month" label="Mes" :options="$months" :selected="$month" :placeholder="false" />
            <x-form.select name="week" label="Semana" :options="$weekOptions" :selected="$selectedWeek" placeholder="Todas las semanas del mes" />
            <div class="flex items-end gap-2">
                <x-button>Buscar</x-button>
                <x-button :href="route('birthdays.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>
    </x-card>

    <div class="grid gap-4">
        @foreach ($groups as $group)
            <x-card>
                <h2 class="font-semibold">{{ ucfirst($group['week']->rangeLabel()) }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $group['entries']->count() }} {{ $group['entries']->count() === 1 ? 'funcionario' : 'funcionarios' }}</p>
                <div class="mt-4">
                    <x-birthday-list :entries="$group['entries']" />
                </div>
            </x-card>
        @endforeach
    </div>
@endsection
