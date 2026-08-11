@php
    $hasRank = $ranked['tier'] !== 'UNRANKED';

    $matchCount = count($matches);
    $sumK = $sumD = $sumA = $sumCs = $sumSec = 0;

    foreach ($matches as $m) {
        $sumK += $m['kills'];
        $sumD += $m['deaths'];
        $sumA += $m['assists'];
        $sumCs += $m['cs'];
        $sumSec += $m['durationSeconds'];
    }

    $kda = $sumD > 0 ? number_format(($sumK + $sumA) / $sumD, 2) : ($matchCount > 0 ? 'Perfect' : '—');
    $csPerMin = $sumSec > 0 ? number_format($sumCs / ($sumSec / 60), 1) : '—';

    $stats = [
        ['l' => 'Win Rate', 'v' => $hasRank ? $ranked['winRate'].'%' : '—'],
        ['l' => 'KDA', 'v' => $kda],
        ['l' => 'CS / Min', 'v' => $csPerMin],
        ['l' => 'Games', 'v' => $hasRank ? number_format($ranked['games']) : '—'],
    ];
@endphp

<div class=" panel reveal p-5">
    <div class="flex items-center justify-between">
        <h3 class="label text-gold-deep">Season 2026</h3>
        <span class="font-mono text-[10px] text-mist">Split 2</span>
    </div>

    <div class="mt-4 flex items-center gap-4">
        <div class="relative flex h-20 w-20 shrink-0 items-center justify-center">
            <svg class="absolute inset-0 h-full w-full text-gold drop-shadow-[0_0_14px_rgba(200,170,110,0.35)]" viewBox="0 0 80 80" fill="none">
                <path d="M40 3l32 15v18c0 17-12 30-32 41C20 66 8 53 8 36V18L40 3z" stroke="currentColor" stroke-width="2.5" fill="rgba(200,170,110,0.12)"/>
                <path d="M40 14l22 10.5v12c0 11-8 20-22 27-14-7-22-16-22-27v-12L40 14z" stroke="currentColor" stroke-width="1.5" fill="rgba(10,200,185,0.08)"/>
            </svg>
            <div class="text-center">
                <p class="font-display text-sm font-black leading-none text-gold-bright">{{ $hasRank ? $ranked['division'] : '—' }}</p>
                <p class="mt-0.5 font-display text-[8px] font-bold uppercase tracking-[0.24em] text-gold">{{ $hasRank ? $ranked['tier'] : 'Unranked' }}</p>
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <p class="font-mono text-[13px] font-semibold text-cream">{{ $hasRank ? $ranked['leaguePoints'] : '—' }} <span class="text-arcane-bright">LP</span></p>
            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
                <div class="h-full bg-gradient-to-r from-gold-deep via-gold to-gold-bright" style="width: {{ min(100, $ranked['leaguePoints']) }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-mist">
                @if ($hasRank && $ranked['isProvisional'])
                    <span class="text-arcane-bright">{{ $ranked['provisionalGamesRemaining'] }} placement games left</span>
                @elseif ($hasRank)
                    {{ $ranked['lpToNext'] }} LP to <span class="text-cream">{{ $ranked['tier'] }} {{ $ranked['nextDivision'] }}</span>
                @else
                    Play ranked to unlock
                @endif
            </p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-line/60 pt-4">
        @foreach ($stats as $s)
            <div class="rounded-sm bg-obsidian/70 px-3 py-2 ring-1 ring-line/60">
                <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-mist">{{ $s['l'] }}</p>
                <p class="mt-0.5 font-mono text-[14px] font-semibold text-gold-bright">{{ $s['v'] }}</p>
            </div>
        @endforeach
    </div>
</div>
