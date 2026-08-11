<div class=" panel reveal p-5" style="--reveal-delay:.06s">
    <h3 class="label text-gold-deep">Progress</h3>
    <div class="mt-4 flex items-end justify-between">
        <p class="font-display text-xl font-bold uppercase text-cream">Level {{ number_format($summoner['summonerLevel']) }}</p>
        <p class="font-mono text-[11px] text-mist">{{ number_format($summoner['xpSinceLastLevel']) }} / {{ number_format($summoner['xpUntilNextLevel']) }} <span class="text-gold-bright">XP</span></p>
    </div>
    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
        <div class="h-full bg-gradient-to-r from-gold-deep via-gold to-gold-bright shadow-[0_0_12px_rgba(200,170,110,0.6)]" style="width: {{ min(100, $summoner['percentCompleteForNextLevel']) }}%"></div>
    </div>
    <div class="mt-4 space-y-2.5 border-t border-line/60 pt-4">
        @forelse ($missions as $q)
            <div class="flex items-center gap-3">
                <div class=" {{ $q['done'] >= $q['total'] ? 'bg-vine/20 text-vine' : 'bg-gold/15 text-gold' }} flex h-7 w-7 items-center justify-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="truncate text-[12px] font-semibold text-cream">{{ $q['title'] }}</p>
                        <p class="font-mono text-[10px] text-mist">{{ $q['done'] }}/{{ $q['total'] }}</p>
                    </div>
                    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
                        <div class="h-full bg-gradient-to-r from-gold-deep to-gold-bright" style="width: {{ min(100, $q['done'] / max(1, $q['total']) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        @empty
            <p class="py-2 text-center text-[11px] text-mist">{{ $connected ? 'No active missions' : 'Client offline' }}</p>
        @endforelse
    </div>
</div>
