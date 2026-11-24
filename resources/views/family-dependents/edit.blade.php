@extends('layouts.app')

@section('title', 'Editar carga familiar')

@section('content')
    <x-page-header title="Editar carga familiar" />
    <x-card>
        <form method="POST" action="{{ route('family-dependents.update', $dependent) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('family-dependents._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('family-dependents.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('family-dependents.destroy', $dependent)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
