@php
    $slot ??= ['position' => 'FILL', 'member' => null];
    $member = $slot['member'];
    $position = $slot['position'];
    $index ??= 0;
@endphp

<div class="member-slot clip-corner-sm relative flex min-h-[210px] flex-col items-center justify-between border p-4 transition-colors {{ $member ? 'border-line bg-steel/70' : 'border-dashed border-line/40 bg-obsidian/40' }} {{ $member && ($member['isLocal'] ?? false) ? 'ring-1 ring-gold/50' : '' }}" data-index="{{ $index }}" data-summoner-id="{{ $member['summonerId'] ?? '' }}">
    <span class="label {{ $member ? 'text-gold-deep' : 'text-mist/50' }}">{{ $position }}</span>

    <div class="relative">
        <div class="clip-corner-sm relative flex h-16 w-16 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
            <span data-field="initial" class="font-display text-lg font-bold {{ $member ? 'text-gold-bright' : 'text-mist/40' }}">{{ $member ? mb_strtoupper(mb_substr($member['gameName'], 0, 1)) : '?' }}</span>
            @if ($member && ($member['icon'] ?? null))
                <img data-field="icon" src="{{ $asset('v1/profile-icons/'.$member['icon'].'.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
            @else
                <img data-field="icon" class="absolute inset-0 hidden h-full w-full object-cover" alt="" onerror="this.style.display='none'" loading="lazy">
            @endif
        </div>
        @if ($member && ($member['isOwner'] ?? false))
            <span data-field="owner" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-black text-obsidian" title="Lobby owner">★</span>
        @endif
    </div>

    <div class="w-full text-center leading-tight">
        <p data-field="name" class="truncate text-[12px] font-bold {{ $member ? 'text-cream' : 'text-mist/50' }}">{{ $member ? $member['gameName'] : 'Empty slot' }}</p>
        <p data-field="meta" class="mt-0.5 text-[10px] text-mist">
            @if ($member)
                {{ $member['isLocal'] ? 'You' : ($member['level'] ? 'Level '.$member['level'] : '') }}
            @else
                Invite a friend
            @endif
        </p>
    </div>

    <div class="flex h-6 items-center gap-2">
        <span data-field="ready" class="hidden clip-corner-sm px-2 py-0.5 text-[9px] font-black uppercase tracking-[0.16em] bg-vine/15 text-vine"></span>
        <button data-action="kick" class="hidden clip-corner-sm border border-ember/40 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.16em] text-ember transition-colors hover:bg-ember/10">Kick</button>
    </div>
</div>
