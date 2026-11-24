@extends('layouts.app')

@section('title', 'Nuevo tipo de documento')

@section('content')
    <x-page-header title="Nuevo tipo de documento" />
    <x-card>
        <form method="POST" action="{{ route('contract-document-types.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('contract-document-types._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="route('contract-document-types.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
