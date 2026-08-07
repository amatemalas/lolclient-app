@php
    $onlineFriends = collect($friends)->reject(fn ($f) => $f['status'] === 'Offline')->count();
@endphp

<div class="clip-corner panel reveal p-5" style="--reveal-delay:.1s">
    <div class="flex items-center justify-between">
        <h3 class="label text-gold-deep">Lobby</h3>
        <span class="clip-corner-sm bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">{{ $connected ? $onlineFriends.'/'.count($friends) : '—' }}</span>
    </div>
    <div class="mt-3 space-y-2.5">
        @forelse ($friends as $f)
            <div class="flex items-center gap-3 rounded-sm px-2 py-1.5 transition-colors hover:bg-steel-2/60">
                <div class="clip-corner-sm relative flex h-8 w-8 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
                    <span class="font-display text-[13px] font-bold text-gold-bright">{{ mb_strtoupper(mb_substr($f['name'], 0, 1)) }}</span>
                    @if ($f['icon'])
                        <img src="{{ $asset('v1/profile-icons/'.$f['icon'].'.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
                    @endif
                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full {{ $f['dot'] }} ring-2 ring-obsidian"></span>
                </div>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[12px] font-semibold text-cream">{{ $f['name'] }}</p>
                    <p class="text-[10px] text-mist">{{ $f['status'] }}</p>
                </div>
            </div>
        @empty
            <p class="py-4 text-center text-[11px] text-mist">{{ $connected ? 'No friends online' : 'Client offline' }}</p>
        @endforelse
    </div>
</div>
