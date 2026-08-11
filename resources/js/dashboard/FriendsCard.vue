<script setup>
import { computed } from 'vue';

const props = defineProps({
    friends: { type: Array, default: () => [] },
    connected: { type: Boolean, default: false },
    assetBase: { type: String, default: '/api/lcu/assets' },
});

const onlineCount = computed(() =>
    props.friends.filter((f) => f.status !== 'Offline').length
);

const countLabel = computed(() =>
    props.connected ? `${onlineCount.value}/${props.friends.length}` : '—'
);

function iconUrl(friend) {
    return friend.icon
        ? `${props.assetBase}/v1/profile-icons/${friend.icon}.jpg`
        : null;
}

function initial(name) {
    return (name?.[0] || '').toUpperCase();
}

function onError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div class="panel reveal flex min-h-0 flex-col p-5" style="--reveal-delay:.1s">
        <div class="flex items-center justify-between">
            <h3 class="label text-gold-deep">Friends</h3>
            <span class="bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">{{ countLabel }}</span>
        </div>

        <div class="mt-3 max-h-[380px] min-h-0 flex-1 space-y-1 overflow-y-auto pr-1">
            <template v-if="friends.length">
                <div
                    v-for="f in friends"
                    :key="f.summonerId"
                    class="group flex items-center gap-3 rounded-sm border border-transparent px-2 py-2 transition-colors hover:border-line/60 hover:bg-steel-2/60"
                >
                    <div class="relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
                        <span class="font-display text-[13px] font-bold text-gold-bright">{{ initial(f.name) }}</span>
                        <img
                            v-if="iconUrl(f)"
                            :src="iconUrl(f)"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                            @error="onError"
                        >
                        <span
                            class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-obsidian"
                            :class="f.dot"
                        ></span>
                    </div>

                    <div class="min-w-0 flex-1 leading-tight">
                        <p
                            class="truncate text-[12px] font-semibold"
                            :class="f.status === 'Offline' ? 'text-mist' : 'text-cream'"
                        >{{ f.name }}</p>
                    </div>

                    <span
                        class="shrink-0 border px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.14em]"
                        :class="[f.pill, f.text]"
                    >{{ f.status }}</span>
                </div>
            </template>
            <p v-else class="py-4 text-center text-[11px] text-mist">
                {{ connected ? 'No friends online' : 'Client offline' }}
            </p>
        </div>
    </div>
</template>
