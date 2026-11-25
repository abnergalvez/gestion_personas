@props(['column', 'label', 'default' => 'name', 'defaultDirection' => 'asc', 'class' => ''])

@php
    $sort = request('sort', $default);
    $direction = request('direction', $defaultDirection);
    $direction = $direction === 'desc' ? 'desc' : 'asc';
    $active = $sort === $column;
    $next = $active && $direction === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([
        'sort' => $column,
        'direction' => $next,
        'page' => null,
    ]);
@endphp

<th {{ $attributes->merge(['class' => trim('px-4 py-3 font-semibold '.$class)]) }} @if ($active) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-brand-700 dark:hover:text-blue-200">
        {{ $label }}
        @if ($active)
            <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
        @endif
    </a>
</th>
