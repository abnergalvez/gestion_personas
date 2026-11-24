<div class="grid gap-4">
    <x-form.input name="name" label="Nombre" :value="$type->name" required />
    <x-form.textarea name="description" label="Descripción" :value="$type->description" />
</div>
