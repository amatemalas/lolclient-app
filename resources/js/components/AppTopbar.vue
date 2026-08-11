<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import SummonerAvatar from './SummonerAvatar.vue';

const props = defineProps({
    title: { type: String, default: 'Home' },
    subtitle: { type: String, default: "Summoner's Rift" },
    summoner: { type: Object, required: true },
    assetBase: { type: String, default: '/api/lcu/assets' },
    initialConnected: { type: Boolean, default: false },
    initialGameflow: { type: String, default: 'None' },
});

const connected = ref(props.initialConnected);
const gameflow = ref(props.initialGameflow);

const phaseLabels = {
    None: 'Idle',
    Lobby: 'Lobby',
    Matchmaking: 'Matchmaking',
    ReadyCheck: 'Ready check',
    ChampSelect: 'Champion select',
    GameStart: 'Game start',
    InProgress: 'In game',
    WaitingForStats: 'End of game',
    PreEndOfGame: 'End of game',
    EndOfGame: 'End of game',
    Reconnect: 'Reconnecting',
    PlayAgain: 'Play again',
};

const gameflowLabel = computed(() =>
    connected.value ? (phaseLabels[gameflow.value] ?? gameflow.value) : 'Client offline'
);

const profileIconUrl = computed(() =>
    props.summoner.profileIconId
        ? `${props.assetBase}/v1/profile-icons/${props.summoner.profileIconId}.jpg`
        : null
);

let pollTimer = 0;

async function pollStatus() {
    try {
        const res = await fetch('/api/lcu/status', { headers: { Accept: 'application/json' } });
        const data = await res.json();
        connected.value = Boolean(data.connected);
        gameflow.value = data.gameflow || 'None';
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

function handleRefetch() {
    window.location.reload();
}

onMounted(() => {
    pollStatus();
    pollTimer = setInterval(pollStatus, 10000);
});

onUnmounted(() => {
    clearInterval(pollTimer);
});
</script>

<template>
    <header class="flex h-14 shrink-0 items-center gap-4 border-b border-line/60 bg-obsidian/60 px-8 backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <h1 class="font-display text-base font-bold uppercase tracking-[0.3em] text-gold-grad">{{ title }}</h1>
            <span class="mt-0.5 h-4 w-px bg-line"></span>
            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-mist">{{ subtitle }}</span>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <button class="flex items-center gap-2 rounded-sm border border-line bg-steel px-3 py-1.5 text-[11px] font-semibold text-cream transition-colors hover:border-gold/50">
                <svg class="h-3.5 w-3.5 text-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="8"/>
                    <path d="M3 6V3h3M21 6V3h-3M3 18v3h3M21 18v3h-3"/>
                </svg>
                EUW · LAN
            </button>

            <span class="flex items-center gap-2 rounded-sm border border-line bg-steel px-3 py-1.5 text-[11px] font-semibold">
                <span class="h-2 w-2 rounded-full" :class="connected ? 'bg-vine' : 'bg-ember'"></span>
                <span class="text-cream">{{ connected ? 'Connected' : 'Offline' }}</span>
                <span class="text-line">·</span>
                <span class="text-mist">{{ gameflowLabel }}</span>
            </span>

            <button
                class="flex h-8 w-8 items-center justify-center rounded-sm border border-line bg-steel text-mist transition-colors hover:text-cream"
                title="Refetch data"
                aria-label="Refetch data"
                @click="handleRefetch"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12a9 9 0 11-2.64-6.36"/>
                    <path d="M21 3v6h-6"/>
                </svg>
            </button>

            <button class="relative flex h-8 w-8 items-center justify-center rounded-sm border border-line bg-steel text-mist transition-colors hover:text-cream">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.7 21a2 2 0 01-3.4 0"/>
                </svg>
                <span class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-ember"></span>
            </button>

            <a
                href="/settings"
                class="flex h-8 w-8 items-center justify-center rounded-sm border border-line bg-steel text-mist transition-colors hover:text-cream"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V21a2 2 0 11-4 0v-.09a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-1.56-1.03H3a2 2 0 110-4h.09A1.7 1.7 0 004.65 8.9a1.7 1.7 0 00-.34-1.88l-.06-.06a2 2 0 112.83-2.83l.06.06a1.7 1.7 0 001.88.34h.08A1.7 1.7 0 0010.13 3V3a2 2 0 114 0v.09a1.7 1.7 0 001.03 1.56h.08a1.7 1.7 0 001.88-.34l.06-.06a2 2 0 112.83 2.83l-.06.06a1.7 1.7 0 00-.34 1.88v.08a1.7 1.7 0 001.56 1.03H21a2 2 0 110 4h-.09a1.7 1.7 0 00-1.56 1.03z"/>
                </svg>
            </a>

            <div class="mx-1 h-5 w-px bg-line"></div>

            <div class="flex items-center gap-2">
                <SummonerAvatar
                    :icon="profileIconUrl"
                    :name="summoner.gameName"
                    gradient="from-cerulean via-steel-2 to-obsidian"
                    class="h-7 w-7"
                />
                <span class="text-[12px] font-bold text-cream">{{ summoner.gameName }}</span>
            </div>
        </div>
    </header>
</template>
