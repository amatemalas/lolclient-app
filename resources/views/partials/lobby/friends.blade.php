<aside class="panel clip-corner reveal flex min-h-0 flex-col p-4" style="--reveal-delay:.12s">
    <div class="flex items-center justify-between">
        <h3 class="label text-gold-deep">Invite Friends</h3>
        <span class="clip-corner-sm bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright" data-friend-count>{{ count($friends) }}</span>
    </div>
    <p class="mt-1 text-[10px] text-mist">Availability and invites update live.</p>

    <div id="friends-list" class="mt-3 max-h-[560px] min-h-0 flex-1 space-y-1 overflow-y-auto pr-1">
        @foreach ($friends as $friend)
            @include('partials.lobby.friend-row', ['friend' => $friend])
        @endforeach
    </div>
</aside>

<template id="friend-row-template">
    @include('partials.lobby.friend-row', ['friend' => null])
</template>
