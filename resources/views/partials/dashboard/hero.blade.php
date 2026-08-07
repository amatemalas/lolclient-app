@php
    $hasRank = $ranked['tier'] !== 'UNRANKED';
    $profileIconPath = $summoner['profileIconId']
        ? 'v1/profile-icons/'.$summoner['profileIconId'].'.jpg'
        : null;
@endphp

<section class="clip-corner panel reveal overflow-hidden" style="--reveal-delay:.02s">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-y-0 right-0 w-2/3 bg-[radial-gradient(60%_120%_at_75%_50%,rgba(13,148,206,0.20),transparent_65%)]"></div>
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[linear-gradient(115deg,transparent_40%,rgba(10,200,185,0.08))]"></div>
        <div class="absolute right-8 top-1/2 -translate-y-1/2 font-display text-[10rem] font-black leading-none text-gold/[0.06] select-none">R</div>
    </div>

    <div class="relative flex flex-wrap items-center gap-8 p-7">
        <div class="flex items-center gap-5">
            <div class="relative">
                @include('partials.summoner-avatar', [
                    'icon' => $profileIconPath && $asset ? $asset($profileIconPath) : null,
                    'name' => $summoner['gameName'],
                    'gradient' => 'from-gold-bright via-gold to-gold-deep',
                    'class' => 'h-24 w-24 shadow-[0_8px_30px_rgba(200,170,110,0.28)]',
                ])
                <span class="clip-corner-sm absolute -bottom-2 -right-2 bg-obsidian px-2 py-1 font-mono text-[11px] font-semibold text-gold-bright ring-1 ring-gold/50">{{ number_format($summoner['summonerLevel']) }}</span>
            </div>
            <div>
                <p class="font-display text-3xl font-bold uppercase tracking-[0.08em] text-cream">{{ $summoner['gameName'] }}</p>
                <p class="mt-1 flex items-center gap-2 text-[12px] font-semibold uppercase tracking-[0.2em] text-mist">
                    <span class="h-1.5 w-1.5 rotate-45 bg-arcane"></span>
                    Summoner Level {{ number_format($summoner['summonerLevel']) }} · Season 2026
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <span class="clip-corner-sm bg-arcane/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-arcane-bright">Ranked Solo</span>
                    <span class="clip-corner-sm {{ $hasRank ? 'bg-gold/15 text-gold-bright' : 'bg-ember/15 text-ember' }} px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]">{{ $hasRank ? 'Clash Ready' : 'Unranked' }}</span>
                </div>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-5">
            <div class="hidden text-right sm:block">
                <p class="label text-gold-deep">Rank</p>
                <p class="mt-1 font-display text-2xl font-bold uppercase tracking-[0.1em] text-gold-grad">{{ $hasRank ? $ranked['tier'].' '.$ranked['division'] : 'Unranked' }}</p>
                <p class="font-mono text-[12px] text-mist">
                    @if ($hasRank)
                        {{ $ranked['leaguePoints'] }} <span class="text-arcane-bright">LP</span>
                    @else
                        Play placements
                    @endif
                </p>
            </div>
            <button class="clip-corner-sm group relative bg-gradient-to-b from-gold-bright via-gold to-gold-deep px-10 py-3.5 font-display text-sm font-black uppercase tracking-[0.3em] text-obsidian shadow-[0_10px_30px_rgba(200,170,110,0.35)] transition-transform hover:-translate-y-0.5" {{ $connected ? '' : 'disabled' }}>
                Play
                <span class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></span>
            </button>
            <button class="clip-corner-sm border border-line bg-steel/60 px-8 py-3.5 font-display text-sm font-bold uppercase tracking-[0.3em] text-cream transition-colors hover:border-gold/50 hover:text-gold-bright">
                Collection
            </button>
        </div>
    </div>
</section>
