@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <x-page-header title="Usuarios" description="Cuentas que entran al sistema. El administrador opera la gestión diaria; el super administrador también los mantenedores.">
        <x-slot:actions>
            <x-button :href="route('users.create')">Nuevo usuario</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-4">
            <x-form.input name="search" label="Buscar" :value="request('search')" placeholder="Nombre o correo" />
            <x-form.select name="role" label="Tipo" :options="$roles" :selected="request('role')" placeholder="Todos" />
            <div class="flex items-end gap-2 md:col-span-2">
                <x-button>Filtrar</x-button>
                <x-button :href="route('users.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="hidden px-4 py-3 md:table-cell">Correo</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $account)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$account->name" :path="$account->avatar_path" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-semibold">{{ $account->name }}</p>
                                        <p class="truncate text-slate-500 md:hidden">{{ $account->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $account->email }}</td>
                            <td class="px-4 py-3"><x-badge :tone="$account->role->tone()">{{ $account->role->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('users.edit', $account) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay usuarios con ese criterio." :action="route('users.create')" action-label="Crear usuario" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </x-card>
@endsection
