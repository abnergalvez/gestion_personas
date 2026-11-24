@extends('layouts.app')

@section('title', 'Detalle de la acción')

@section('content')
    <x-page-header title="Detalle de la acción" :description="$log->description">
        <x-slot:actions>
            <x-button :href="route('activity-logs.index')" variant="secondary">Volver al registro</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4"><x-badge :tone="$log->action->tone()">{{ $log->action->label() }}</x-badge></div>
        <dl class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($log->details() as $label => $value)
                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="text-sm break-words whitespace-pre-wrap sm:col-span-2">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>
@endsection
