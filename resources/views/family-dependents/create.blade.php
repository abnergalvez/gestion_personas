@extends('layouts.app')

@section('title', 'Nueva carga familiar')

@section('content')
    <x-page-header title="Nueva carga familiar" />
    <x-card>
        <form method="POST" action="{{ route('family-dependents.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('family-dependents._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('family-dependents.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
