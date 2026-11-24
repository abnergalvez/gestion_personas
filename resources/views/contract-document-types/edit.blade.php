@extends('layouts.app')

@section('title', 'Editar tipo de documento')

@section('content')
    <x-page-header :title="'Editar '.$type->name" />
    <x-card>
        <form method="POST" action="{{ route('contract-document-types.update', $type) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('contract-document-types._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('contract-document-types.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('contract-document-types.destroy', $type)" /></div>
            </div>
        </form>
    </x-card>
@endsection
