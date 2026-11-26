@extends('layouts.app')

@section('title', 'Detalle del error')

@section('content')
    <x-page-header title="Detalle del error" :description="$error->summary()">
        <x-slot:actions>
            <x-button :href="route('system-errors.index')" variant="secondary">Volver al registro</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-badge :tone="$error->severity->tone()">{{ $error->severity->label() }}</x-badge>
            @if ($error->review)
                <x-badge :tone="$error->review->tone()">{{ $error->review->label() }}</x-badge>
            @else
                <x-badge>Pendiente</x-badge>
                <form method="POST" action="{{ route('system-errors.review', $error) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <x-button name="review" value="seen" variant="secondary" size="sm">Marcar como visto</x-button>
                    <x-button name="review" value="resolved" size="sm">Marcar como solucionado</x-button>
                </form>
            @endif
        </div>
        <dl class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($error->details() as $label => $value)
                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="text-sm break-words whitespace-pre-wrap sm:col-span-2">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
        @if ($error->trace)
            <h2 class="mt-6 font-semibold">Rastro</h2>
            <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs whitespace-pre text-slate-100">{{ $error->trace }}</pre>
        @endif
    </x-card>
@endsection
