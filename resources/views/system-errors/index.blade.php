@extends('layouts.app')

@section('title', 'Errores del sistema')

@section('content')
    <x-page-header title="Errores del sistema" description="Excepciones y respuestas de error, con el mensaje, el archivo y el rastro. Un error grave sigue en la campanita hasta marcarlo como visto o solucionado." />

    <x-card class="mb-4">
        <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <x-form.input name="search" label="Buscar" :value="request('search')" placeholder="Mensaje, excepción, archivo o URL" />
            <x-form.select name="severity" label="Gravedad" :options="$severities" :selected="request('severity')" placeholder="Todas" />
            <x-form.select name="review" label="Revisión" :options="$reviews" :selected="request('review')" placeholder="Todas" />
            <div class="flex items-end gap-2">
                <x-button>Filtrar</x-button>
                <x-button :href="route('system-errors.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase dark:bg-slate-950/50">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Error</th>
                        <th class="hidden px-4 py-3 md:table-cell">Estado</th>
                        <th class="hidden px-4 py-3 lg:table-cell">Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($errors as $error)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $error->created_at?->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('system-errors.show', $error) }}" class="font-semibold text-brand-700 hover:underline dark:text-blue-200">{{ Str::limit($error->summary(), 140) }}</a>
                                <p class="mt-1 text-slate-500 md:hidden">{{ $error->status_code }} · {{ $error->review?->label() ?? 'Pendiente' }}</p>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    <x-badge :tone="$error->severity->tone()">{{ $error->severity->label() }}</x-badge>
                                    @if ($error->review)
                                        <x-badge :tone="$error->review->tone()">{{ $error->review->label() }}</x-badge>
                                    @else
                                        <x-badge>Pendiente</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 lg:table-cell">{{ $error->file ? $error->file.':'.$error->line : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty title="No hay errores con ese criterio." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $errors->links() }}
    </x-card>
@endsection
