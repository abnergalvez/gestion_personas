@extends('layouts.app')

@section('title', 'Editar permiso')

@section('content')
    <x-page-header title="Editar permiso" />
    <x-card>
        <form method="POST" action="{{ route('permits.update', $permit) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('permits._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('permits.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('permits.destroy', $permit)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
