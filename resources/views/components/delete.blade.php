@props(['action', 'message' => '¿Confirma que desea eliminar este registro? Esta acción no se puede deshacer.'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($message))">
    @csrf
    @method('DELETE')
    {{ $slot }}
    <button type="submit" class="text-sm font-semibold text-accent-600 hover:underline dark:text-red-300">Eliminar</button>
</form>
