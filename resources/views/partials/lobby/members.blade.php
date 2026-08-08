<section class="panel clip-corner reveal flex min-w-0 flex-col p-5" style="--reveal-delay:.08s">
    <div class="flex items-center justify-between">
        <h2 class="label text-gold-grad">Party</h2>
        <span class="clip-corner-sm bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">
            <span data-member-count>{{ $lobbyPlayerCount }}</span> / {{ $lobbyQueue['players'] }}
        </span>
    </div>

    <div id="members-grid" class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        @forelse ($lobbySlots as $index => $slot)
            @include('partials.lobby.member-slot', ['slot' => $slot, 'index' => $index])
        @empty
            <div class="col-span-full flex flex-col items-center gap-2 py-12 text-center">
                <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No lobby open</p>
                <p class="max-w-xs text-[11px] text-mist">Choose a game mode above to create a lobby and invite your friends.</p>
            </div>
        @endforelse
    </div>
</section>

<template id="member-slot-template">
    @include('partials.lobby.member-slot', ['slot' => null, 'index' => 0])
</template>
