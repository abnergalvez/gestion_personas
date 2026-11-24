@extends('layouts.app')

@section('title', 'Nuevo contrato')

@section('content')
    <x-page-header title="Nuevo contrato" description="Después de guardarlo podrá adjuntar los documentos." />
    <x-card>
        <form method="POST" action="{{ route('contracts.store') }}" class="space-y-6">
            @csrf
            <x-form.errors />
            @include('contracts._form')
            <div class="flex gap-2"><x-button>Guardar</x-button><x-button :href="$contextEmployeeId ? route('employees.show', $contextEmployeeId) : route('contracts.index')" variant="ghost">Cancelar</x-button></div>
        </form>
    </x-card>
@endsection
