@extends('layouts.app')

@section('title', 'Mi ficha')

@section('content')
    <x-page-header title="Mi ficha" description="Nombre, correo, avatar y contraseña de su cuenta. El tipo de usuario lo cambia el super administrador." />

    <div class="grid gap-6 xl:grid-cols-2">
        <x-card>
            <div class="mb-5 flex items-center gap-4">
                <x-avatar :name="$user->name" :path="$user->avatar_path" size="lg" />
                <div>
                    <p class="font-semibold">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    <p class="mt-2"><x-badge :tone="$user->role->tone()">{{ $user->role->label() }}</x-badge></p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form.errors />
                <x-form.input name="name" label="Nombre" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Correo" :value="$user->email" required />
                <x-form.file name="avatar" label="Avatar" :current="$user->avatar_path" hint="JPG, PNG o WebP. Máximo 4 MB." accept="image/*" />
                <x-button>Guardar ficha</x-button>
            </form>
        </x-card>

        <x-card id="contrasena">
            <h2 class="text-lg font-semibold">Cambiar contraseña</h2>
            <p class="mt-1 text-sm text-slate-500">Use al menos 8 caracteres, con mayúsculas, minúsculas y un número.</p>
            <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <x-form.input name="current_password" type="password" label="Contraseña actual" autocomplete="current-password" required />
                <x-form.input name="password" type="password" label="Nueva contraseña" autocomplete="new-password" required />
                <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" autocomplete="new-password" required />
                <x-button>Actualizar contraseña</x-button>
            </form>
        </x-card>
    </div>
@endsection
