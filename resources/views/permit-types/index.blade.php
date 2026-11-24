@extends('layouts.app')

@section('title', 'Tipos de permiso')

@section('content')
    <x-page-header title="Tipos de permiso" description="Catálogo para los permisos administrativos del personal.">
        <x-slot:actions><x-button :href="route('permit-types.create')">Nuevo tipo</x-button></x-slot:actions>
    </x-page-header>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Nombre</th><th class="hidden px-4 py-3 md:table-cell">Descripción</th><th class="px-4 py-3">Uso</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($permitTypes as $permitType)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $permitType->name }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $permitType->description ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $permitType->permits_count }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('permit-types.edit', $permitType) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay tipos de permiso." :action="route('permit-types.create')" action-label="Crear tipo" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $permitTypes->links() }}
    </x-card>
@endsection
