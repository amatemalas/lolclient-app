<script setup>
import { computed } from 'vue';
import MemberSlot from './MemberSlot.vue';

const props = defineProps({
    lobby: { type: Object, default: null },
    gameflow: { type: String, default: 'None' },
    assetBase: { type: String, default: '' },
    memberCount: { type: Number, default: 0 },
    maxPlayers: { type: Number, default: 5 },
    pending: { type: Object, default: () => ({}) },
});

defineEmits(['kick']);

const slots = computed(() => props.lobby?.slots || []);
const showReady = computed(() => props.gameflow === 'ReadyCheck' && Boolean(props.lobby?.readyCheck));

function canKick(slot) {
    const member = slot?.member;

    return Boolean(member && props.lobby?.local?.isOwner && !member.isLocal && props.gameflow === 'Lobby');
}

function isKicking(slot) {
    const member = slot?.member;

    return Boolean(member && props.pending[`kick:${member.summonerId}`]);
}
</script>

<template>
    <section class="panel  reveal flex min-w-0 flex-col p-5" style="--reveal-delay:.08s">
        <div class="flex items-center justify-between">
            <h2 class="label text-gold-grad">Party</h2>
            <span class=" bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">
                <span data-member-count>{{ memberCount }}</span> / {{ maxPlayers }}
            </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
            <template v-if="slots.length">
                <MemberSlot
                    v-for="(slot, index) in slots"
                    :key="index"
                    :slot="slot"
                    :asset-base="assetBase"
                    :can-kick="canKick(slot)"
                    :kicking="isKicking(slot)"
                    :show-ready="showReady"
                    @kick="$emit('kick', $event)"
                />
            </template>
            <div v-else class="col-span-full flex flex-col items-center gap-2 py-12 text-center">
                <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No lobby open</p>
                <p class="max-w-xs text-[11px] text-mist">Choose a game mode above to create a lobby and invite your friends.</p>
            </div>
        </div>
    </section>
</template>
