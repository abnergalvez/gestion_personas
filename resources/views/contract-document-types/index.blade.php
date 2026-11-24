@extends('layouts.app')

@section('title', 'Tipos de documento')

@section('content')
    <x-page-header title="Tipos de documento de contrato" description="Clasifican los archivos que se adjuntan a un contrato.">
        <x-slot:actions><x-button :href="route('contract-document-types.create')">Nuevo tipo</x-button></x-slot:actions>
    </x-page-header>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Nombre</th><th class="hidden px-4 py-3 md:table-cell">Descripción</th><th class="px-4 py-3">Uso</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $type->name }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $type->description ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $type->documents_count }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('contract-document-types.edit', $type) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay tipos de documento." :action="route('contract-document-types.create')" action-label="Crear tipo" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $types->links() }}
    </x-card>
@endsection
