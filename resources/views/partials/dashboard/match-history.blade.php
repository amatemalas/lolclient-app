<section class="col-span-2">
    <div class="mb-3 flex items-center gap-3">
        <h2 class="label text-gold-grad">Recent Matches</h2>
        <span class="flex-1 border-t border-line/60"></span>
        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-mist">Season 2026 · Split 2</span>
    </div>

    <div class="panel clip-corner flex flex-col divide-y divide-line/50">
        @forelse ($matches as $m)
            @include('partials.dashboard.match-row', ['match' => $m])
        @empty
            <div class="px-4 py-12 text-center">
                <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No recent matches</p>
                <p class="mt-2 text-[11px] text-mist">{{ $connected ? 'Play a game and it will appear here.' : ($error ?? 'The League client is offline.') }}</p>
            </div>
        @endforelse

        @if (! empty($matches))
            <div class="flex items-center justify-center py-3">
                <button class="text-[11px] font-bold uppercase tracking-[0.24em] text-mist transition-colors hover:text-gold-bright">
                    View match history
                </button>
            </div>
        @endif
    </div>
</section>
