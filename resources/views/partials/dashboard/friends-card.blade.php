@php
    $onlineFriends = collect($friends)->reject(fn ($f) => $f['status'] === 'Offline')->count();
@endphp

<div class="clip-corner panel reveal flex min-h-0 flex-col p-5" style="--reveal-delay:.1s">
    <div class="flex items-center justify-between">
        <h3 class="label text-gold-deep">Friends</h3>
        <span class="clip-corner-sm bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">{{ $connected ? $onlineFriends.'/'.count($friends) : '—' }}</span>
    </div>

    <div class="mt-3 max-h-[380px] min-h-0 flex-1 space-y-1 overflow-y-auto pr-1">
        @forelse ($friends as $f)
            <div class="group flex items-center gap-3 rounded-sm border border-transparent px-2 py-2 transition-colors hover:border-line/60 hover:bg-steel-2/60">
                <div class="clip-corner-sm relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
                    <span class="font-display text-[13px] font-bold text-gold-bright">{{ mb_strtoupper(mb_substr($f['name'], 0, 1)) }}</span>
                    @if ($f['icon'])
                        <img src="{{ $asset('v1/profile-icons/'.$f['icon'].'.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
                    @endif
                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full {{ $f['dot'] }} ring-2 ring-obsidian"></span>
                </div>

                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[12px] font-semibold {{ $f['status'] === 'Offline' ? 'text-mist' : 'text-cream' }}">{{ $f['name'] }}</p>
                </div>

                <span class="clip-corner-sm shrink-0 border px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.14em] {{ $f['pill'] }} {{ $f['text'] }}">{{ $f['status'] }}</span>
            </div>
        @empty
            <p class="py-4 text-center text-[11px] text-mist">{{ $connected ? 'No friends online' : 'Client offline' }}</p>
        @endforelse
    </div>
</div>
