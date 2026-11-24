@extends('layouts.app')

@section('title', 'Nueva calificación')

@section('content')
    <x-page-header title="Nueva calificación" />
    <x-card>
        <form method="POST" action="{{ route('evaluations.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('evaluations._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('evaluations.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
