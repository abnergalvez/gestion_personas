@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <x-page-header title="Nuevo usuario" description="La cuenta queda habilitada para ingresar de inmediato." />
    <x-card>
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('users._form')
            <div class="flex flex-wrap gap-2">
                <x-button>Guardar</x-button>
                <x-button :href="route('users.index')" variant="ghost">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
