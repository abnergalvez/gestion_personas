@include('partials.context')
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2"><x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$dependent->employee_id" required /></div>
    <x-form.input name="full_name" label="Nombre completo" :value="$dependent->full_name" required />
    <x-form.input name="rut" label="RUT" :value="$dependent->formatted_rut ?? null" required />
    <x-form.input name="birth_date" type="date" label="Fecha de nacimiento" :value="$dependent->birth_date?->format('Y-m-d')" required />
    <x-form.select name="relationship" label="Familiaridad" :options="$relationships" :selected="$dependent->relationship?->value" required />
    <x-form.select name="education_level" label="Escolaridad" :options="$educationLevels" :selected="$dependent->education_level?->value" required />
    <x-form.file name="birth_certificate" label="Certificado de nacimiento" :current="$dependent->birth_certificate_path" />
    <x-form.file name="resolution" label="Resolución" :current="$dependent->resolution_path" />
</div>
