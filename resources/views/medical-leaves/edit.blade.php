@extends('layouts.app')

@section('title', 'Editar licencia')

@section('content')
    <x-page-header title="Editar licencia médica" />
    <x-card>
        <form method="POST" action="{{ route('medical-leaves.update', $leave) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('medical-leaves._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('medical-leaves.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('medical-leaves.destroy', $leave)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
