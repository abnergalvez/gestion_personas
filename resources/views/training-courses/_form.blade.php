@include('partials.context')
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$course->employee_id" required />
    </div>
    <div class="sm:col-span-2">
        <x-form.input name="name" label="Nombre" :value="$course->name" required />
    </div>
    <x-form.input name="duration" label="Duración" :value="$course->duration" placeholder="40 horas" required />
    <x-form.input name="certified_on" type="date" label="Fecha de certificación" :value="$course->certified_on?->format('Y-m-d')" required />
    <x-form.input name="valid_until" type="date" label="Vigencia hasta" :value="$course->valid_until?->format('Y-m-d')" />
    <x-form.file name="document" label="Documento" :current="$course->document_path" />
</div>
