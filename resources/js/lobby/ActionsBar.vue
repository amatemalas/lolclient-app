<script setup>
import { computed } from 'vue';

const props = defineProps({
    summoner: { type: Object, default: () => ({}) },
    assetBase: { type: String, default: '' },
    playLabel: { type: String, default: 'Find match' },
    playDisabled: { type: Boolean, default: true },
    playing: { type: Boolean, default: false },
});

defineEmits(['inviteFriend', 'play']);

const initial = computed(() => (props.summoner.gameName?.[0] || 'S').toUpperCase());
const avatarUrl = computed(() =>
    props.summoner.profileIconId ? `${props.assetBase}/${props.summoner.profileIconId}.jpg` : null
);

function onAvatarError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <section class="panel clip-corner reveal flex items-center gap-4 p-4" style="--reveal-delay:.16s">
        <div class="flex items-center gap-3">
            <div class="clip-corner-sm relative flex h-10 w-10 items-center justify-center overflow-hidden bg-gradient-to-br from-cerulean via-steel-2 to-obsidian ring-1 ring-line">
                <span class="font-display font-bold text-gold-bright">{{ initial }}</span>
                <img v-if="avatarUrl" :src="avatarUrl" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" @error="onAvatarError">
            </div>
            <div class="leading-tight">
                <p class="text-[12px] font-bold text-cream">{{ summoner.gameName }}</p>
                <p class="text-[10px] text-mist">Invite friends from the panel on the right.</p>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-3">
            <button
                data-action="invite-friend"
                class="clip-corner-sm border border-line bg-steel px-5 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-cream transition-colors hover:border-gold/50"
                @click="$emit('inviteFriend')"
            >Invite friend</button>
            <button
                data-action="play"
                class="clip-corner-sm group relative bg-gradient-to-b from-gold-bright via-gold to-gold-deep px-10 py-3 font-display text-sm font-black uppercase tracking-[0.3em] text-obsidian shadow-[0_10px_30px_rgba(200,170,110,0.35)] transition-transform hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="playDisabled || playing"
                @click="$emit('play')"
            >
                <span data-play-label>{{ playLabel }}</span>
                <span class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></span>
            </button>
        </div>
    </section>
</template>
