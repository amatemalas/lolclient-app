<section class="clip-corner panel reveal flex flex-wrap items-center gap-5 p-5 overflow-visible" style="--reveal-delay:.02s">
    <div class="flex items-center gap-4">
        <div class="clip-corner-sm flex h-14 w-14 items-center justify-center bg-gradient-to-br from-gold-bright via-gold to-gold-deep text-obsidian">
            @include('partials.lol-icon', ['name' => $lobbyQueue['icon'], 'class' => 'h-7 w-7'])
        </div>
        <div>
            <p data-header-mode class="font-display text-xl font-bold uppercase tracking-[0.08em] text-cream">{{ $lobbyQueue['name'] }}</p>
            <p data-header-subtitle class="mt-0.5 text-[11px] text-mist">{{ $lobbyQueue['map'] }} · {{ $lobbyQueue['description'] }}</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2">
        <button data-mode-toggle class="clip-corner-sm border border-line bg-steel px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-cream transition-colors hover:border-gold/50">
            Change mode
        </button>
        <button data-action="leave" class="clip-corner-sm border border-ember/40 px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-ember transition-colors hover:bg-ember/10">Leave</button>
    </div>
</section>

{{-- Game mode selection popup --}}
<div data-mode-modal role="dialog" aria-modal="true" aria-label="Select game mode"
         class="fixed inset-0 z-40 hidden flex flex-col items-center justify-center px-6">
    <div data-mode-backdrop class="absolute inset-0 bg-void/80 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-sm">
        <div class="mb-3 flex items-center justify-between">
            <p class="label text-gold-deep">Select a game mode</p>
            <button type="button" data-mode-close aria-label="Close"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-line bg-obsidian/80 text-mist transition-colors hover:border-gold/60 hover:text-cream">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <div class="max-h-[70vh] overflow-y-auto">
            <div class="panel clip-corner flex flex-col gap-1 p-2">
                @foreach ($queueModes as $mode)
                    <button data-queue-id="{{ $mode['id'] }}" class="flex items-center justify-between gap-2 rounded-sm px-3 py-2.5 text-left transition-colors hover:bg-steel-2">
                        <span class="text-[12px] font-semibold text-cream">{{ $mode['name'] }}</span>
                        <span class="text-[10px] text-mist">{{ $mode['queue'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
