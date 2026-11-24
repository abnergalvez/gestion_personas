@extends('layouts.app')

@section('title', 'Nueva licencia')

@section('content')
    <x-page-header title="Nueva licencia médica" />
    <x-card>
        <form method="POST" action="{{ route('medical-leaves.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('medical-leaves._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('medical-leaves.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
