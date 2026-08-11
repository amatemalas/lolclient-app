<script setup>
defineProps({
    items: { type: Array, default: () => [] },
    assetBase: { type: String, default: '/api/lcu/assets' },
});

function onError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div class="flex items-center gap-1.5">
        <div
            v-for="(iconPath, i) in items"
            :key="i"
            class="relative flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-[3px] bg-obsidian ring-1 ring-line/70"
        >
            <span class="h-3 w-3 rotate-45 border" :class="iconPath ? 'border-gold/40' : 'border-mist/30 opacity-40'"></span>
            <img
                v-if="iconPath"
                :src="`${assetBase}/${iconPath}`"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
                loading="lazy"
                @error="onError"
            >
        </div>
    </div>
</template>
