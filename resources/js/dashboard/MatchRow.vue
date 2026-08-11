<script setup>
import { computed } from 'vue';
import MatchItems from './MatchItems.vue';

const props = defineProps({
    match: { type: Object, required: true },
    assetBase: { type: String, default: '/api/lcu/assets' },
});

const champGradients = [
    'from-amber-500/70 via-red-700/70 to-obsidian',
    'from-cerulean via-teal-700/70 to-obsidian',
    'from-gold via-orange-700/70 to-obsidian',
    'from-fuchsia-600/70 via-purple-900/70 to-obsidian',
    'from-emerald-500/70 via-teal-800/70 to-obsidian',
];

const champGradient = computed(() => champGradients[props.match.championId % champGradients.length]);
const champInitial = computed(() => (props.match.champion || '?')[0].toUpperCase());
const champIconUrl = computed(() => `${props.assetBase}/v1/champion-icons/${props.match.championId}.png`);

function onIconError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div class="group flex items-center gap-4 px-4 py-3 transition-colors hover:bg-steel-2/50">
        <div class="flex w-16 items-center gap-2">
            <span
                class="px-2 py-1 text-[9px] font-black uppercase tracking-[0.16em]"
                :class="match.win ? 'bg-arcane/15 text-arcane-bright' : 'bg-ember/15 text-ember'"
            >{{ match.win ? 'Win' : 'Def' }}</span>
        </div>

        <div
            class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br ring-1 ring-line"
            :class="champGradient"
        >
            <span class="font-display text-xl font-bold text-cream/90">{{ champInitial }}</span>
            <img
                :src="champIconUrl"
                :alt="match.champion"
                class="absolute inset-0 h-full w-full object-cover"
                loading="lazy"
                @error="onIconError"
            >
        </div>

        <div class="w-32 shrink-0 leading-tight">
            <p class="text-[12px] font-bold" :class="match.win ? 'text-arcane-bright' : 'text-ember'">{{ match.mode }}</p>
            <p class="text-[10px] text-mist">{{ match.ago }} · {{ match.duration }}</p>
        </div>

        <div class="w-28 shrink-0 leading-tight">
            <p class="font-mono text-[12px] font-semibold text-cream">
                <span class="text-gold-bright">{{ match.kills }}</span>
                <span class="text-mist"> / {{ match.deaths }} / {{ match.assists }}</span>
            </p>
            <p class="text-[10px] text-mist">CS {{ match.cs }} · {{ match.gold }} gold</p>
        </div>

        <div class="hidden flex-1 lg:flex">
            <MatchItems :items="match.items ?? []" :asset-base="assetBase" />
        </div>

        <div class="ml-auto flex items-center gap-3">
            <span
                class="px-2 py-0.5 font-display text-[11px] font-bold"
                :class="match.win ? 'bg-gold/15 text-gold-bright' : 'bg-ember/15 text-ember'"
            >Lv {{ match.champLevel }}</span>
            <svg class="h-4 w-4 text-mist/50 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </div>
    </div>
</template>
