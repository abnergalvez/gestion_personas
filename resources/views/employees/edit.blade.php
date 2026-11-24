@extends('layouts.app')

@section('title', 'Editar funcionario')

@section('content')
    <x-page-header :title="'Editar '.$employee->full_name" />
    <x-card>
        <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('employees._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('employees.show', $employee)" variant="ghost">Cancelar</x-button>
                <div class="sm:ml-auto">
                    <x-delete :action="route('employees.destroy', $employee)" message="Se eliminará el funcionario y toda su información asociada. ¿Desea continuar?" />
                </div>
            </div>
        </form>
    </x-card>
@endsection
