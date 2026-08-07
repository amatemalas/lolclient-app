@props([
    'icon' => null,
    'name' => 'S',
    'gradient' => 'from-cerulean via-steel-2 to-obsidian',
])

@php
    $initial = mb_strtoupper(mb_substr($name, 0, 1)) ?: 'S';
@endphp

<div {{ $attributes->merge(['class' => 'clip-corner-sm relative flex items-center justify-center overflow-hidden bg-gradient-to-br ring-1 ring-line '.$gradient]) }}>
    <span class="font-display font-bold text-gold-bright">{{ $initial }}</span>
    @if ($icon)
        <img src="{{ $icon }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
    @endif
</div>
