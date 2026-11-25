@props(['entries'])

@if ($entries->isEmpty())
    <p class="text-sm text-slate-500 dark:text-slate-400">Nadie cumple años en esta semana.</p>
@else
    <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($entries as $birthday)
            <li>
                <a href="{{ route('employees.show', $birthday->employee) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 hover:border-brand-600 dark:border-slate-700 dark:bg-slate-950">
                    <x-avatar :name="$birthday->employee->full_name" :path="$birthday->employee->photo_path" size="sm" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-slate-900 dark:text-white">{{ $birthday->employee->full_name }}</span>
                        <span class="block truncate text-sm text-slate-500">
                            {{ ucfirst($birthday->whenLabel()) }} · {{ $birthday->ageLabel() }}
                            @if ($birthday->employee->department)
                                · {{ $birthday->employee->department->name }}
                            @endif
                        </span>
                    </span>
                    <x-badge :tone="$birthday->tone()">{{ $birthday->timingLabel() }}</x-badge>
                </a>
            </li>
        @endforeach
    </ul>
@endif
