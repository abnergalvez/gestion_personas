@extends('layouts.app')

@section('title', 'Nueva sección')

@section('content')
    <x-page-header title="Nueva sección" />
    <x-card>
        <form method="POST" action="{{ route('sections.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('sections._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="route('sections.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
