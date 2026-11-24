@include('partials.context')
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2"><x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$evaluation->employee_id" required /></div>
    <x-form.input name="period_start_year" type="number" label="Año de inicio del periodo" :value="$evaluation->period_start_year" min="1980" required />
    <x-form.input name="period_end_year" type="number" label="Año de término del periodo" :value="$evaluation->period_end_year" min="1980" required />
    <x-form.select name="process" label="Proceso" :options="$processes" :selected="$evaluation->process?->value" required />
    <x-form.input name="score" type="number" label="Nota numérica" :value="$evaluation->score" min="1" max="7" step="0.1" hint="Opcional si escribe una descripción. Escala de 1 a 7." />
    <div class="sm:col-span-2"><x-form.textarea name="notes" label="Nota / descripción" :value="$evaluation->notes" /></div>
    <x-form.file name="document" label="Documento" :current="$evaluation->document_path" />
</div>
