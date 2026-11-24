@extends('layouts.app')

@section('title', 'Editar contrato')

@section('content')
    <x-page-header title="Editar contrato" />
    <x-card>
        <form method="POST" action="{{ route('contracts.update', $contract) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('contracts._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('contracts.show', $contract)" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('contracts.destroy', $contract)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
