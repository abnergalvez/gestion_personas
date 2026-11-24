@extends('layouts.app')

@section('title', 'Nuevo tipo de licencia')

@section('content')
    <x-page-header title="Nuevo tipo de licencia" />
    <x-card>
        <form method="POST" action="{{ route('leave-types.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('leave-types._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="route('leave-types.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
