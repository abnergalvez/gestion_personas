@extends('layouts.app')

@section('title', 'Editar tipo de licencia')

@section('content')
    <x-page-header :title="'Editar '.$leaveType->name" />
    <x-card>
        <form method="POST" action="{{ route('leave-types.update', $leaveType) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('leave-types._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('leave-types.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('leave-types.destroy', $leaveType)" /></div>
            </div>
        </form>
    </x-card>
@endsection
