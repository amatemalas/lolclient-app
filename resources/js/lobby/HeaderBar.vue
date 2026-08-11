<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    queue: { type: Object, required: true },
    memberCount: { type: Number, default: 0 },
    canLeave: { type: Boolean, default: false },
    leaving: { type: Boolean, default: false },
});

defineEmits(['changeMode', 'leave']);
</script>

<template>
    <section class=" panel reveal flex flex-wrap items-center gap-5 overflow-visible p-5" style="--reveal-delay:.02s">
        <div class="flex items-center gap-4">
            <div class=" flex h-14 w-14 items-center justify-center bg-gradient-to-br from-gold-bright via-gold to-gold-deep text-obsidian">
                <AppIcon :name="queue.icon || 'aram'" class="h-7 w-7" />
            </div>
            <div>
                <p data-header-mode class="font-display text-xl font-bold uppercase tracking-[0.08em] text-cream">{{ queue.name }}</p>
                <p data-header-subtitle class="mt-0.5 text-[11px] text-mist">{{ queue.map }} · {{ queue.description }}</p>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <button data-mode-toggle class=" border border-line bg-steel px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-cream transition-colors hover:border-gold/50" @click="$emit('changeMode')">Change mode</button>
            <button
                data-action="leave"
                class=" border border-ember/40 px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-ember transition-colors hover:bg-ember/10 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="!canLeave || leaving"
                @click="$emit('leave')"
            >Leave</button>
        </div>
    </section>
</template>
