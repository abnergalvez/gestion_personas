@php
    $selectedAnalysts = old('analyst_ids', $section->exists ? $section->analysts->pluck('id')->all() : []);
    $class = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
@endphp
<div class="grid gap-4">
    <x-form.input name="name" label="Nombre" :value="$section->name" required />
    <x-form.select name="department_id" label="Departamento" :options="$departments" :selected="$section->department_id" placeholder="Sin departamento" hint="Una sección puede pertenecer a un departamento." />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-form.select name="head_employee_id" label="Funcionario jefe" :options="$employees" :selected="$section->head_employee_id" placeholder="Sin jefe" />
        <x-form.select name="deputy_employee_id" label="Funcionario subrogante" :options="$employees" :selected="$section->deputy_employee_id" placeholder="Sin subrogante" />
    </div>
    <x-form.field name="analyst_ids" label="Funcionarios analistas" hint="Puede elegir varios. En computador, mantenga presionada Ctrl.">
        <select name="analyst_ids[]" id="analyst_ids" multiple size="8" class="{{ $class }}">
            @foreach ($employees as $id => $label)
                <option value="{{ $id }}" @selected(collect($selectedAnalysts)->contains(fn ($value) => (string) $value === (string) $id))>{{ $label }}</option>
            @endforeach
        </select>
    </x-form.field>
</div>
