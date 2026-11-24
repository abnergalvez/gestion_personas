@extends('layouts.app')

@section('title', 'Tipos de licencia')

@section('content')
    <x-page-header title="Tipos de licencia" description="Mantenedor usado por las licencias médicas.">
        <x-slot:actions><x-button :href="route('leave-types.create')">Nuevo tipo</x-button></x-slot:actions>
    </x-page-header>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50"><tr><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Código</th><th class="hidden px-4 py-3 md:table-cell">Uso</th><th class="px-4 py-3"></th></tr></thead>
                <tbody>
                    @forelse ($leaveTypes as $leaveType)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold">{{ $leaveType->name }}<p class="font-normal text-slate-500">{{ $leaveType->description }}</p></td>
                            <td class="px-4 py-3">{{ $leaveType->internal_code }}</td>
                            <td class="hidden px-4 py-3 md:table-cell">{{ $leaveType->medical_leaves_count }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('leave-types.edit', $leaveType) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-blue-200">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay tipos de licencia." :action="route('leave-types.create')" action-label="Crear tipo" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leaveTypes->links() }}
    </x-card>
@endsection
