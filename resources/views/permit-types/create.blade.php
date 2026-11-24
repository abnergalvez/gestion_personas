@extends('layouts.app')

@section('title', 'Nuevo tipo de permiso')

@section('content')
    <x-page-header title="Nuevo tipo de permiso" />
    <x-card>
        <form method="POST" action="{{ route('permit-types.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('permit-types._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="route('permit-types.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
