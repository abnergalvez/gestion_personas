@php
    $current = (int) request('per_page', \App\Support\Listing::PER_PAGE[0]);
    if (! in_array($current, \App\Support\Listing::PER_PAGE, true)) {
        $current = \App\Support\Listing::PER_PAGE[0];
    }
@endphp

<form method="GET" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
    @foreach (request()->except(['page', 'per_page']) as $name => $value)
        @if (is_scalar($value) && $value !== '')
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach
    <label for="per_page">Registros</label>
    <select id="per_page" name="per_page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" x-on:change="$el.form.submit()">
        @foreach (\App\Support\Listing::PER_PAGE as $size)
            <option value="{{ $size }}" @selected($current === $size)>{{ $size }}</option>
        @endforeach
    </select>
</form>
