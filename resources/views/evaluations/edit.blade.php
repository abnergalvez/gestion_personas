@extends('layouts.app')

@section('title', 'Editar calificación')

@section('content')
    <x-page-header title="Editar calificación" />
    <x-card>
        <form method="POST" action="{{ route('evaluations.update', $evaluation) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('evaluations._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('evaluations.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('evaluations.destroy', $evaluation)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
