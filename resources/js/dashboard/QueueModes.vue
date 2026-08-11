<script setup>
import { ref } from 'vue';
import AppIcon from '../lobby/AppIcon.vue';
import { createLobby } from '../lobby-actions';

const props = defineProps({
    modes: { type: Array, default: () => [] },
    connected: { type: Boolean, default: false },
});

const pending = ref(new Set());

async function handleClick(mode) {
    if (!props.connected || pending.value.has(mode.id)) {
        return;
    }

    pending.value.add(mode.id);

    try {
        const result = await createLobby(mode.id);

        if (result.ok) {
            window.location.href = '/lobby';
            return;
        }
    } catch {
        // Ignore; re-enable button below.
    } finally {
        pending.value.delete(mode.id);
    }
}
</script>

<template>
    <section class="grid grid-cols-4 gap-3" style="--reveal-delay:.08s">
        <button
            v-for="mode in modes"
            :key="mode.id"
            type="button"
            class="panel reveal relative overflow-hidden p-4 text-left transition-colors hover:border-gold/40"
            :class="(!connected || pending.has(mode.id)) ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'"
            :disabled="!connected || pending.has(mode.id)"
            @click="handleClick(mode)"
        >
            <div v-if="mode.featured" class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-gold to-transparent"></div>
            <div class="flex items-center justify-between">
                <span :class="mode.featured ? 'text-gold' : 'text-arcane'">
                    <AppIcon :name="mode.icon" class="h-6 w-6" />
                </span>
                <span v-if="!connected" class="bg-ember/15 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.2em] text-ember">Offline</span>
                <span v-else class="h-1.5 w-1.5 rounded-full" :class="mode.featured ? 'bg-gold' : 'bg-arcane'"></span>
            </div>
            <p class="mt-3 font-display text-sm font-bold uppercase tracking-[0.12em]" :class="mode.featured ? 'text-gold-bright' : 'text-cream'">{{ mode.name }}</p>
            <p class="mt-0.5 text-[11px] text-mist">{{ mode.queue }}</p>
            <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.22em]" :class="!connected ? 'text-ember/80' : 'text-arcane-bright'">
                {{ !connected ? 'Offline' : 'Play' }}
            </p>
        </button>
    </section>
</template>
