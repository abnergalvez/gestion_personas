<div class="grid gap-4 sm:grid-cols-2">
    <x-form.input name="rut" label="RUT" :value="$employee->formatted_rut ?? null" placeholder="12.345.678-5" required />
    <x-form.input name="birth_date" type="date" label="Fecha de nacimiento" :value="$employee->birth_date?->format('Y-m-d')" required />
    <x-form.input name="first_name" label="Nombres" :value="$employee->first_name" required />
    <x-form.input name="paternal_last_name" label="Apellido paterno" :value="$employee->paternal_last_name" required />
    <x-form.input name="maternal_last_name" label="Apellido materno" :value="$employee->maternal_last_name" required />
    <x-form.input name="profession" label="Profesión" :value="$employee->profession" />
    <x-form.select name="marital_status" label="Estado civil" :options="$maritalStatuses" :selected="$employee->marital_status?->value" required />
    <x-form.select name="employment_quality" label="Calidad" :options="$qualities" :selected="$employee->employment_quality?->value" required />
    <x-form.input name="grade" label="Grado" :value="$employee->grade" />
    <x-form.select name="department_id" label="Departamento" :options="$departments" :selected="$employee->department_id" placeholder="Sin departamento" />
    <x-form.select name="section_id" label="Sección" :options="$sections" :selected="$employee->section_id" placeholder="Sin sección" />
    <x-form.file name="photo" label="Fotografía" :current="$employee->photo_path" hint="JPG, PNG o WebP. Máximo 4 MB." accept="image/*" />
</div>
