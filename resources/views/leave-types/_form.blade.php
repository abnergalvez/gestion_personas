<div class="grid gap-4">
    <x-form.input name="name" label="Nombre" :value="$leaveType->name" required />
    <x-form.input name="internal_code" label="Código interno" :value="$leaveType->internal_code" hint="Letras, números, guion o guion bajo." required />
    <x-form.textarea name="description" label="Descripción" :value="$leaveType->description" />
</div>
