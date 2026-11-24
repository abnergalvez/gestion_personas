@extends('layouts.app')

@section('title', 'Editar sección')

@section('content')
    <x-page-header :title="'Editar '.$section->name" />
    <x-card>
        <form method="POST" action="{{ route('sections.update', $section) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('sections._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('sections.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('sections.destroy', $section)" message="Los funcionarios quedarán sin esta sección. ¿Desea eliminarla?" /></div>
            </div>
        </form>
    </x-card>
@endsection
