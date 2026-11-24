@extends('layouts.app')

@section('title', 'Contrato')

@section('content')
    <x-page-header :title="$contract->position" :description="$contract->employee->full_name">
        <x-slot:actions>
            <x-button :href="route('contracts.edit', $contract)">Editar</x-button>
            <x-button :href="route('employees.show', $contract->employee)" variant="secondary">Ficha del funcionario</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Calidad</dt><dd class="mt-1"><x-badge :tone="$contract->quality->tone()">{{ $contract->quality->label() }}</x-badge></dd></div>
                <div><dt class="text-slate-500">Modalidad</dt><dd>{{ $contract->modality->label() }}</dd></div>
                <div><dt class="text-slate-500">Grado</dt><dd>{{ $contract->grade }}</dd></div>
                <div><dt class="text-slate-500">Inicio</dt><dd>{{ $contract->starts_on->format('d-m-Y') }}</dd></div>
                <div><dt class="text-slate-500">Término</dt><dd>{{ $contract->ends_on?->format('d-m-Y') ?? 'Indefinido' }}</dd></div>
                <div><dt class="text-slate-500">Departamento</dt><dd>{{ $contract->department?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Sección</dt><dd>{{ $contract->section?->name ?? '—' }}</dd></div>
            </dl>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Agregar documento</h2>
            @if ($documentTypes->isEmpty())
                <p class="mt-3 text-sm text-slate-500">{{ auth()->user()->isSuperAdmin() ? 'Cree primero un tipo en Mantenedores → Tipos de documento.' : 'El super administrador debe crear un tipo de documento antes de adjuntar archivos.' }}</p>
            @else
            <form method="POST" action="{{ route('contracts.documents.store', $contract) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <x-form.errors />
                <x-form.select name="contract_document_type_id" label="Tipo de documento" :options="$documentTypes" required />
                <x-form.file name="document" label="Archivo" hint="Obligatorio. PDF o imagen, máximo 8 MB." required />
                <x-button>Adjuntar</x-button>
            </form>
            @endif
        </x-card>
    </div>

    <div class="mt-6">
        <x-record-section title="Documentos del contrato">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs tracking-wide text-slate-500 uppercase"><tr><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Archivo</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody>
                        @forelse ($contract->documents as $document)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">{{ $document->type->name }}</td>
                                <td class="px-4 py-3"><x-file-link :path="$document->document_path" label="Abrir" /></td>
                                <td class="px-4 py-3 text-right"><x-delete :action="route('contracts.documents.destroy', [$contract, $document])" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-empty title="Este contrato aún no tiene documentos." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-record-section>
    </div>
@endsection
