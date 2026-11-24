@include('partials.context')
@if (collect($employees)->isEmpty())
    <p class="mb-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-300">Registre primero un funcionario. El departamento y la sección pueden quedar vacíos.</p>
@endif
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2"><x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$contract->employee_id" required /></div>
    <x-form.select name="quality" label="Calidad" :options="$qualities" :selected="$contract->quality?->value" required />
    <x-form.select name="modality" label="Modalidad" :options="$modalities" :selected="$contract->modality?->value" required />
    <x-form.input name="position" label="Cargo" :value="$contract->position" required />
    <x-form.input name="grade" label="Grado" :value="$contract->grade" required />
    <x-form.input name="starts_on" type="date" label="Fecha de inicio" :value="$contract->starts_on?->format('Y-m-d')" required />
    <x-form.input name="ends_on" type="date" label="Fecha de término" :value="$contract->ends_on?->format('Y-m-d')" hint="Déjela vacía si el contrato no tiene término." />
    <x-form.select name="department_id" label="Departamento" :options="$departments" :selected="$contract->department_id" placeholder="Sin departamento" />
    <x-form.select name="section_id" label="Sección" :options="$sections" :selected="$contract->section_id" placeholder="Sin sección" />
</div>
