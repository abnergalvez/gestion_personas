@extends('layouts.app')

@section('title', 'Nuevo funcionario')

@section('content')
    <x-page-header title="Nuevo funcionario" description="Los datos de cursos, licencias, contratos y permisos se agregan desde la ficha." />
    <x-card>
        <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('employees._form')
            <div class="flex flex-wrap gap-2">
                <x-button>Guardar</x-button>
                <x-button :href="route('employees.index')" variant="ghost">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
