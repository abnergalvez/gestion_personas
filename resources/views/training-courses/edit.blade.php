@extends('layouts.app')

@section('title', 'Editar curso')

@section('content')
    <x-page-header title="Editar curso" />
    <x-card>
        <form method="POST" action="{{ route('training-courses.update', $course) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('training-courses._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('training-courses.index')" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto"><x-delete :action="route('training-courses.destroy', $course)">@include('partials.context')</x-delete></div>
            </div>
        </form>
    </x-card>
@endsection
