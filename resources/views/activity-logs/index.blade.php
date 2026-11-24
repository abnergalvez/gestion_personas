@extends('layouts.app')

@section('title', 'Registro de acciones')

@section('content')
    <x-page-header title="Registro de acciones" description="Consultas, cambios, descargas, ingresos e intentos de acceso. El registro no se edita ni se elimina." />

    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <x-form.input name="search" label="Buscar" :value="request('search')" placeholder="Descripción, correo o IP" />
            <x-form.select name="action" label="Acción" :options="$actions" :selected="request('action')" placeholder="Todas" />
            <x-form.select name="user_id" label="Usuario" :options="$users" :selected="request('user_id')" placeholder="Todos" />
            <x-form.input name="from" type="date" label="Desde" :value="request('from')" />
            <x-form.input name="to" type="date" label="Hasta" :value="request('to')" />
            <div class="flex items-end gap-2">
                <x-button>Filtrar</x-button>
                <x-button :href="route('activity-logs.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="hidden px-4 py-3 md:table-cell">Acción</th>
                        <th class="px-4 py-3">Detalle</th>
                        <th class="hidden px-4 py-3 lg:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $log->displayName() }}</p>
                                <p class="text-slate-500 md:hidden">{{ $log->action->label() }}</p>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell"><x-badge :tone="$log->action->tone()">{{ $log->action->label() }}</x-badge></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('activity-logs.show', $log) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ $log->description }}</a>
                            </td>
                            <td class="hidden px-4 py-3 lg:table-cell">{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="No hay acciones con ese criterio." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </x-card>
@endsection
