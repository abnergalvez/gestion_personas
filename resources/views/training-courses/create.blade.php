@extends('layouts.app')

@section('title', 'Nuevo curso')

@section('content')
    <x-page-header title="Nuevo curso" />
    <x-card>
        <form method="POST" action="{{ route('training-courses.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('training-courses._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('training-courses.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
