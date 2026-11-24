@extends('layouts.app')

@section('title', 'Editar departamento')

@section('content')
    <x-page-header :title="'Editar '.$department->name" />
    <x-card>
        <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('departments._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('departments.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('departments.destroy', $department)" message="Los funcionarios quedarán sin este departamento. ¿Desea eliminarlo?" /></div>
            </div>
        </form>
    </x-card>
@endsection
