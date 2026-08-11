<script setup>
import { computed } from 'vue';

const props = defineProps({
    friend: { type: Object, required: true },
    assetBase: { type: String, default: '' },
    inviting: { type: Boolean, default: false },
});

defineEmits(['invite']);

const initial = computed(() => (props.friend.name?.[0] || '').toUpperCase());
const iconUrl = computed(() => (props.friend.icon ? `${props.assetBase}/${props.friend.icon}.jpg` : null));
const dotClass = computed(() => props.friend.dot || 'bg-mist');

function onIconError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div class="friend-row group flex items-center gap-3 rounded-sm border border-transparent px-2 py-2 transition-colors hover:border-line/60 hover:bg-steel-2/60">
        <div class="relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
            <span class="font-display text-[13px] font-bold text-gold-bright">{{ initial }}</span>
            <img v-if="iconUrl" :src="iconUrl" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" @error="onIconError">
            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-obsidian" :class="dotClass"></span>
        </div>

        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-[12px] font-semibold" :class="friend.status === 'Offline' ? 'text-mist' : 'text-cream'">{{ friend.name }}</p>
            <p class="text-[10px] text-mist">{{ friend.status }}</p>
        </div>

        <button
            v-if="friend.invitable"
            :disabled="inviting"
            class="shrink-0 border border-arcane/40 px-2 py-1 text-[9px] font-bold uppercase tracking-[0.14em] text-arcane-bright transition-colors hover:bg-arcane/10 disabled:cursor-not-allowed disabled:opacity-50"
            @click="$emit('invite', friend)"
        >Invite</button>
    </div>
</template>
