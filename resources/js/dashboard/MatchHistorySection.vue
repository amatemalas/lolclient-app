<script setup>
import MatchRow from './MatchRow.vue';

defineProps({
    matches: { type: Array, default: () => [] },
    connected: { type: Boolean, default: false },
    error: { type: String, default: '' },
    title: { type: String, default: 'Recent Matches' },
    subtitle: { type: String, default: null },
    assetBase: { type: String, default: '/api/lcu/assets' },
});
</script>

<template>
    <div>
        <div class="mb-3 flex items-center gap-3">
            <h2 class="label text-gold-grad">{{ title }}</h2>
            <span class="flex-1 border-t border-line/60"></span>
            <span v-if="subtitle" class="text-[11px] font-semibold uppercase tracking-[0.2em] text-mist">{{ subtitle }}</span>
        </div>

        <div class="panel flex flex-col divide-y divide-line/50">
            <template v-if="matches.length">
                <MatchRow
                    v-for="(match, i) in matches"
                    :key="i"
                    :match="match"
                    :asset-base="assetBase"
                />
                <div class="flex items-center justify-center py-3">
                    <button class="text-[11px] font-bold uppercase tracking-[0.24em] text-mist transition-colors hover:text-gold-bright">
                        View match history
                    </button>
                </div>
            </template>
            <div v-else class="px-4 py-12 text-center">
                <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No recent matches</p>
                <p class="mt-2 text-[11px] text-mist">
                    {{ connected ? 'Play a game and it will appear here.' : (error || 'The League client is offline.') }}
                </p>
            </div>
        </div>
    </div>
</template>
