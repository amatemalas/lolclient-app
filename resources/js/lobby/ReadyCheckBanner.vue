<script setup>
import { computed } from 'vue';

const props = defineProps({
    secondsLeft: { type: Number, default: 0 },
    accepting: { type: Boolean, default: false },
    declining: { type: Boolean, default: false },
});

defineEmits(['accept', 'decline']);

const timerText = computed(() => (props.secondsLeft > 0 ? `${props.secondsLeft}s` : 'auto-declining'));
</script>

<template>
    <div class="ready-check clip-corner-sm flex items-center gap-4 border border-arcane/30 bg-arcane/5 px-5 py-4">
        <span class="h-2.5 w-2.5 shrink-0 animate-pulse rounded-full bg-arcane"></span>
        <div class="min-w-0 flex-1">
            <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-arcane-bright">Match found!</p>
            <p class="mt-0.5 text-[11px] text-mist">
                Accept to jump into the game.
                <span class="font-mono text-cream" data-ready-timer>{{ timerText }}</span>
            </p>
        </div>
        <button
            data-action="accept"
            class="clip-corner-sm shrink-0 bg-arcane/20 px-5 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-arcane-bright transition-colors hover:bg-arcane/30 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="accepting"
            @click="$emit('accept')"
        >Accept</button>
        <button
            data-action="decline"
            class="clip-corner-sm shrink-0 border border-line px-5 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-mist transition-colors hover:text-ember disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="declining"
            @click="$emit('decline')"
        >Decline</button>
    </div>
</template>
