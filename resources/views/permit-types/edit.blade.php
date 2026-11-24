@extends('layouts.app')

@section('title', 'Editar tipo de permiso')

@section('content')
    <x-page-header :title="'Editar '.$permitType->name" />
    <x-card>
        <form method="POST" action="{{ route('permit-types.update', $permitType) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('permit-types._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('permit-types.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('permit-types.destroy', $permitType)" /></div>
            </div>
        </form>
    </x-card>
@endsection
