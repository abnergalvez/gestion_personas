@props(['padding' => true])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    @if ($padding)
        <div class="p-5 sm:p-6">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</section>
