@php
    $name ??= '';
    $class ??= '';

    $icons = [
        'home' => ['path' => '<path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>'],
        'play' => ['path' => '<path d="M6 4l14 8-14 8V4z"/>'],
        'collection' => ['path' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
        'aram' => ['path' => '<path d="M12 2l2.5 7.5H22l-6 4.5 2.5 8L12 17l-6.5 5 2.5-8-6-4.5h7.5L12 2z"/>'],
        'clash' => ['path' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/>'],
        'shop' => ['path' => '<path d="M6 7l1.5-4h9L18 7"/><path d="M4 7h16v3a3 3 0 01-6 0 3 3 0 01-6 0 3 3 0 01-6 0V7z"/><path d="M5 11v9h14v-9"/>'],
        'settings' => ['path' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V21a2 2 0 11-4 0v-.09a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-1.56-1.03H3a2 2 0 110-4h.09A1.7 1.7 0 004.65 8.9a1.7 1.7 0 00-.34-1.88l-.06-.06a2 2 0 112.83-2.83l.06.06a1.7 1.7 0 001.88.34h.08A1.7 1.7 0 0010.13 3V3a2 2 0 114 0v.09a1.7 1.7 0 001.03 1.56h.08a1.7 1.7 0 001.88-.34l.06-.06a2 2 0 112.83 2.83l-.06.06a1.7 1.7 0 00-.34 1.88v.08a1.7 1.7 0 001.56 1.03H21a2 2 0 110 4h-.09a1.7 1.7 0 00-1.56 1.03z"/>'],
        'ranked' => ['path' => '<path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z"/><path d="M12 8l1.5 3 3 .3-2.2 2.1.6 3-2.9-1.6-2.9 1.6.6-3L7.5 11.3l3-.3L12 8z"/>'],
    ];
    $icon = $icons[$name] ?? ['path' => '<circle cx="12" cy="12" r="9"/>'];
@endphp

<svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $icon['path'] !!}
</svg>
