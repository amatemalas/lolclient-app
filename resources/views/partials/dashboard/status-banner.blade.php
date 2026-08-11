@if (! $connected)
    <div class="panel-dim  flex items-center gap-3 px-4 py-3" style="--reveal-delay:.01s">
        <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-ember"></span>
        <p class="text-[12px] font-semibold text-cream">League client offline</p>
        <p class="text-[11px] text-mist">{{ $error ?? 'Start League of Legends to see live data.' }}</p>
    </div>
@endif
