@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <x-page-header :title="'Editar '.$user->name" />
    <x-card>
        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.errors />
            @include('users._form')
            <div class="flex flex-wrap items-center gap-2">
                <x-button>Guardar cambios</x-button>
                <x-button :href="route('users.index')" variant="ghost">Cancelar</x-button>
                @if ($canDelete)
                    <div class="sm:ml-auto">
                        <x-delete :action="route('users.destroy', $user)" message="¿Confirma que desea eliminar esta cuenta? Esta acción no se puede deshacer." />
                    </div>
                @endif
            </div>
        </form>
    </x-card>
@endsection
