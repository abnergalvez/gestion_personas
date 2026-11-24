@include('partials.context')
@if (collect($employees)->isEmpty() || collect($leaveTypes)->isEmpty())
    <p class="mb-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-300">Para guardar una licencia se necesita un funcionario y un tipo de licencia. Si falta el tipo, lo crea el super administrador en Mantenedores.</p>
@endif
<div class="grid gap-4 sm:grid-cols-2">
    <x-form.select name="employee_id" label="Funcionario" :options="$employees" :selected="$leave->employee_id" required />
    <x-form.select name="leave_type_id" label="Tipo de licencia" :options="$leaveTypes" :selected="$leave->leave_type_id" required />
    <x-form.input name="starts_on" type="date" label="Fecha de inicio" :value="$leave->starts_on?->format('Y-m-d')" required />
    <x-form.input name="ends_on" type="date" label="Fecha de término" :value="$leave->ends_on?->format('Y-m-d')" required />
    <x-form.input name="days_count" type="number" label="Número de días" :value="$leave->days_count" min="1" max="400" required />
    <x-form.file name="document" label="Documento de licencia" :current="$leave->document_path" />
    <x-form.file name="medical_resolution" label="Resolución médica" :current="$leave->medical_resolution_path" />
    <x-form.file name="internal_resolution" label="Resolución interna" :current="$leave->internal_resolution_path" />
</div>
