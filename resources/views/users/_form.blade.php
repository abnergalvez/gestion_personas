@php
    $passwordIsRequired = ! $user->exists;
    $passwordHint = $passwordIsRequired
        ? 'Mínimo 8 caracteres, con mayúsculas, minúsculas y un número.'
        : 'Déjela en blanco para mantener la actual. Mínimo 8 caracteres, con mayúsculas, minúsculas y un número.';
@endphp
<div class="grid gap-4">
    <x-form.input name="name" label="Nombre" :value="$user->name" required />
    <x-form.input name="email" type="email" label="Correo" :value="$user->email" required />
    <x-form.select name="role" label="Tipo de usuario" :options="$roles" :selected="$user->role?->value" :placeholder="false" hint="El administrador no entra a los mantenedores." required />
    <x-form.file name="avatar" label="Avatar" :current="$user->avatar_path" hint="JPG, PNG o WebP. Máximo 4 MB." accept="image/*" />
    <x-form.input name="password" type="password" label="Contraseña" autocomplete="new-password" :hint="$passwordHint" :required="$passwordIsRequired" />
    <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" autocomplete="new-password" :required="$passwordIsRequired" />
</div>
