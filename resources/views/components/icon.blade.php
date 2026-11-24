@props(['name'])

@php
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9.75V20.25h4.5v-5.25h4.5v5.25h4.5V9.75"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5a6 6 0 0 0-6-6H7.5a6 6 0 0 0-6 6"/><circle cx="8.25" cy="7.5" r="2.75"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11.25a4.5 4.5 0 0 1 4.5 4.5"/><circle cx="16.5" cy="7.75" r="2.25"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20.5h16M6 20.5V4.75A1.25 1.25 0 0 1 7.25 3.5h9.5A1.25 1.25 0 0 1 18 4.75v15.75"/><path stroke-linecap="round" d="M9 8h1.5M9 11.5h1.5M9 15h1.5M13.5 8H15M13.5 11.5H15M13.5 15H15"/>',
        'squares' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
        'academic' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4 3.5 8.25 12 12.5l8.5-4.25L12 4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 10.5v4.2c0 .4 2.2 2.3 5 2.3s5-1.9 5-2.3v-4.2"/><path stroke-linecap="round" d="M20.5 8.5V14"/>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5s-6.5-4.1-6.5-8.2A3.3 3.3 0 0 1 12 8.4a3.3 3.3 0 0 1 6.5 2.9c0 4.1-6.5 8.2-6.5 8.2Z"/>',
        'family' => '<circle cx="8" cy="8" r="2.2"/><circle cx="16" cy="8" r="2.2"/><circle cx="12" cy="12.2" r="2"/><path stroke-linecap="round" d="M4.5 18.5a3.6 3.6 0 0 1 3.6-3.6h.4M15.5 14.9h.4a3.6 3.6 0 0 1 3.6 3.6M8.8 18.5a3.3 3.3 0 0 1 6.4 0"/>',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h7l4.5 4.5V20a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 20V5A1.5 1.5 0 0 1 7 3.5Z"/><path stroke-linecap="round" d="M14 3.5V8h4.5M8.5 12.5h7M8.5 16h7"/>',
        'clock' => '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" d="M12 8v4.5l3 2"/>',
        'star' => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 3.8 2.1 4.3 4.7.7-3.4 3.3.8 4.7L12 14.6 7.8 16.8l.8-4.7L5.2 8.8l4.7-.7L12 3.8Z"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12.2 11.8 4.5H19v7.2l-8.2 8.2L4 12.2Z"/><circle cx="15.2" cy="8.3" r="1"/>',
        'folder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8.5A1.5 1.5 0 0 1 5 7h4l2 2h8a1.5 1.5 0 0 1 1.5 1.5v8A1.5 1.5 0 0 1 19 20H5a1.5 1.5 0 0 1-1.5-1.5v-10Z"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 19 6.2v5.3c0 4.2-2.8 7.2-7 8.9-4.2-1.7-7-4.7-7-8.9V6.2L12 3.5Z"/>',
        'list' => '<path stroke-linecap="round" d="M9 7h11M9 12h11M9 17h11"/><path stroke-linecap="round" d="M4.5 7h.01M4.5 12h.01M4.5 17h.01"/>',
        'menu' => '<path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>',
        'sun' => '<circle cx="12" cy="12" r="3.2"/><path stroke-linecap="round" d="M12 3.5v2M12 18.5v2M3.5 12h2M18.5 12h2M5.8 5.8l1.4 1.4M16.8 16.8l1.4 1.4M18.2 5.8l-1.4 1.4M7.2 16.8l-1.4 1.4"/>',
        'moon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 14.5A6.5 6.5 0 0 1 9.2 6 6.2 6.2 0 1 0 16.5 14.5Z"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
    {!! $icons[$name] ?? '<circle cx="12" cy="12" r="3"/>' !!}
</svg>
