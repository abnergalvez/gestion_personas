@extends('layouts.app')

@section('title', 'Nuevo permiso')

@section('content')
    <x-page-header title="Nuevo permiso" />
    <x-card>
        <form method="POST" action="{{ route('permits.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('permits._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('permits.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
