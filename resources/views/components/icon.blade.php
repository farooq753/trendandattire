@props(['name'])
@php
    $paths = [
        'shield' => '<path d="M12 3.5l7 2.5v5.5c0 4.2-2.7 7.2-7 8.5-4.3-1.3-7-4.3-7-8.5V6l7-2.5z"/><path d="M9 12l2 2 4-4"/>',
        'pulse' => '<path d="M4 12h3l2-4 3 8 2-4h6"/><path d="M12 4v2M12 18v2"/>',
        'scissors' => '<circle cx="6" cy="6" r="2.2"/><circle cx="6" cy="18" r="2.2"/><path d="M8 7.5L20 18M8 16.5L20 6"/>',
        'needle' => '<path d="M15 4l5 5-9 9-3 1 1-3 6-12z"/><path d="M14 6l4 4"/>',
        'box' => '<path d="M3 8l9-4 9 4-9 4-9-4z"/><path d="M3 8v8l9 4 9-4V8"/><path d="M12 12v8"/>',
        'truck' => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'layers' => '<path d="M12 4l8 4-8 4-8-4 8-4z"/><path d="M4 12l8 4 8-4"/><path d="M4 16l8 4 8-4"/>',
        'wind' => '<path d="M4 8h9a2 2 0 1 0-2-2"/><path d="M4 12h13a2 2 0 1 1-2 2"/><path d="M4 16h7a2 2 0 1 0-2 2"/>',
        'crease' => '<path d="M4 8c2.2 2 4.2 2 6.4 0s4.2-2 6.4 0 4.2 2 6.4 0"/><path d="M4 13c2.2 2 4.2 2 6.4 0s4.2-2 6.4 0 4.2 2 6.4 0"/><path d="M4 18c2.2 2 4.2 2 6.4 0s4.2-2 6.4 0 4.2 2 6.4 0"/>',
        'droplet' => '<path d="M12 3s6 7 6 11a6 6 0 0 1-12 0c0-4 6-11 6-11z"/>',
        'facebook' => '<path d="M14 8h2V5h-2c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h2.2l.8-3H13V9c0-.6.4-1 1-1z" fill="currentColor" stroke="none"/>',
        'instagram' => '<rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="M16 16l4 4"/>',
        'person' => '<circle cx="12" cy="8" r="3"/><path d="M5 19c1.5-3 3.8-4.5 7-4.5S17.5 16 19 19"/>',
        'cart' => '<circle cx="9" cy="19" r="1.3"/><circle cx="17" cy="19" r="1.3"/><path d="M4 5h2l2.2 9h9.3l1.8-6H8"/>',
        'phone' => '<path d="M7 4h3l1 4-2 1a12 12 0 0 0 6 6l1-2 4 1v3c0 1-1 2-2 2C9 19 5 15 5 6c0-1 1-2 2-2z"/>',
        'mail' => '<rect x="4" y="6" width="16" height="12" rx="2"/><path d="M4 8l8 6 8-6"/>',
        'pin' => '<path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10z"/><circle cx="12" cy="11" r="2"/>',
        'clock' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/>',
        'star' => '<path d="M12 3.5l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 16.8 7.2 18.4l.9-5.4L4.2 9.2l5.4-.8L12 3.5z" fill="currentColor" stroke="none"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'ta-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] !!}</svg>
