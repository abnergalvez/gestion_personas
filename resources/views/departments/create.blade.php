@extends('layouts.app')

@section('title', 'Nuevo departamento')

@section('content')
    <x-page-header title="Nuevo departamento" description="Puede crear el departamento ahora y asignar funcionarios cuando ya existan." />
    <x-card>
        <form method="POST" action="{{ route('departments.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('departments._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="route('departments.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
