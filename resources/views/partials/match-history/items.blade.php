@php
    $items = $items ?? [];
@endphp

<div class="flex items-center gap-1.5">
    @foreach ($items as $iconPath)
        <div class="relative flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-[3px] bg-obsidian ring-1 ring-line/70">
            <span class="h-3 w-3 rotate-45 border {{ $iconPath ? 'border-gold/40' : 'border-mist/30 opacity-40' }}"></span>
            @if ($iconPath)
                <img src="{{ $asset($iconPath) }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
            @endif
        </div>
    @endforeach
</div>
