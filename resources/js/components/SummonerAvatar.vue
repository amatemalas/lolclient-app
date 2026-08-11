<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: { type: String, default: null },
    name: { type: String, default: 'S' },
    gradient: { type: String, default: 'from-cerulean via-steel-2 to-obsidian' },
});

const initial = computed(() => (props.name?.[0] || 'S').toUpperCase());

function onError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div
        class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br ring-1 ring-line"
        :class="gradient"
    >
        <span class="font-display font-bold text-gold-bright">{{ initial }}</span>
        <img
            v-if="icon"
            :src="icon"
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
            @error="onError"
        >
    </div>
</template>
