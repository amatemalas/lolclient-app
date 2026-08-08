@php
    $friend = $friend ?? null;
@endphp

<div class="friend-row group flex items-center gap-3 rounded-sm border border-transparent px-2 py-2 transition-colors hover:border-line/60 hover:bg-steel-2/60" data-summoner-id="{{ $friend['summonerId'] ?? '' }}" data-availability="{{ $friend['availability'] ?? 'offline' }}">
    <div class="clip-corner-sm relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
        <span data-field="initial" class="font-display text-[13px] font-bold text-gold-bright">{{ $friend ? mb_strtoupper(mb_substr($friend['name'], 0, 1)) : '' }}</span>
        @if ($friend && ($friend['icon'] ?? null))
            <img data-field="icon" src="{{ $asset('v1/profile-icons/'.$friend['icon'].'.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
        @else
            <img data-field="icon" class="absolute inset-0 hidden h-full w-full object-cover" alt="" onerror="this.style.display='none'" loading="lazy">
        @endif
        <span data-field="dot" class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-mist ring-2 ring-obsidian"></span>
    </div>

    <div class="min-w-0 flex-1 leading-tight">
        <p data-field="name" class="truncate text-[12px] font-semibold {{ $friend && $friend['status'] === 'Offline' ? 'text-mist' : 'text-cream' }}">{{ $friend ? $friend['name'] : '' }}</p>
        <p data-field="status" class="text-[10px] text-mist">{{ $friend ? $friend['status'] : '' }}</p>
    </div>

    <button data-action="invite"
        class="{{ $friend && $friend['invitable'] ? '' : 'hidden ' }}shrink-0 clip-corner-sm border px-2 py-1 text-[9px] font-bold uppercase tracking-[0.14em] {{ $friend && $friend['invitable'] ? 'border-arcane/40 text-arcane-bright hover:bg-arcane/10' : 'border-line text-mist/40' }}"
        {{ $friend && ! $friend['invitable'] ? 'disabled' : '' }}>Invite</button>
</div>
