<script setup>
defineProps({
    open: { type: Boolean, default: false },
    queueModes: { type: Array, default: () => [] },
});

defineEmits(['close', 'select']);
</script>

<template>
    <div v-if="open" data-mode-modal role="dialog" aria-modal="true" aria-label="Select game mode" class="fixed inset-0 z-40 flex flex-col items-center justify-center px-6">
        <div class="absolute inset-0 bg-void/80 backdrop-blur-sm" @click="$emit('close')"></div>
        <div class="relative w-full max-w-sm">
            <div class="mb-3 flex items-center justify-between">
                <p class="label text-gold-deep">Select a game mode</p>
                <button type="button" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full border border-line bg-obsidian/80 text-mist transition-colors hover:border-gold/60 hover:text-cream" @click="$emit('close')">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto">
                <div class="panel clip-corner flex flex-col gap-1 p-2">
                    <button
                        v-for="mode in queueModes"
                        :key="mode.id"
                        data-queue-id
                        class="flex items-center justify-between gap-2 rounded-sm px-3 py-2.5 text-left transition-colors hover:bg-steel-2"
                        @click="$emit('select', mode.id)"
                    >
                        <span class="text-[12px] font-semibold text-cream">{{ mode.name }}</span>
                        <span class="text-[10px] text-mist">{{ mode.queue }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
