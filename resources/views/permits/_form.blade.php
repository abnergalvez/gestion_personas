@include('partials.context')
<div class="grid gap-4 sm:grid-cols-2">
    <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$permit->employee_id" required />
    <x-form.select name="permit_type_id" label="Tipo de permiso" :options="$permitTypes" :selected="$permit->permit_type_id" required />
    <x-form.input name="starts_at" type="datetime-local" label="Fecha y hora de inicio" :value="$permit->starts_at?->format('Y-m-d\TH:i')" required />
    <x-form.input name="ends_at" type="datetime-local" label="Fecha y hora de término" :value="$permit->ends_at?->format('Y-m-d\TH:i')" required />
    <x-form.file name="document" label="Documento" :current="$permit->document_path" />
</div>
