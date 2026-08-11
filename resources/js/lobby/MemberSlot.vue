<script setup>
import { computed } from 'vue';

const props = defineProps({
    slot: { type: Object, required: true },
    assetBase: { type: String, default: '' },
    canKick: { type: Boolean, default: false },
    kicking: { type: Boolean, default: false },
    showReady: { type: Boolean, default: false },
});

defineEmits(['kick']);

const member = computed(() => props.slot?.member || null);
const hasMember = computed(() => Boolean(member.value));
const initial = computed(() => (member.value?.gameName || '?')[0].toUpperCase());
const iconUrl = computed(() => (member.value?.icon ? `${props.assetBase}/${member.value.icon}.jpg` : null));
const meta = computed(() => {
    if (!member.value) {
        return 'Invite a friend';
    }

    return member.value.isLocal ? 'You' : member.value.level ? `Level ${member.value.level}` : '';
});

function onIconError(event) {
    event.target.style.display = 'none';
}
</script>

<template>
    <div
        class="member-slot relative flex min-h-[210px] flex-col items-center justify-between border p-4 transition-colors"
        :class="[
            hasMember ? 'border-line bg-steel/70' : 'border-dashed border-line/40 bg-obsidian/40',
            member?.isLocal ? 'ring-1 ring-gold/50' : '',
        ]"
    >
        <span class="label" :class="hasMember ? 'text-gold-deep' : 'text-mist/50'">{{ slot?.position || 'FILL' }}</span>

        <div class="relative">
            <div class="relative flex h-16 w-16 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
                <span class="font-display text-lg font-bold" :class="hasMember ? 'text-gold-bright' : 'text-mist/40'">{{ initial }}</span>
                <img v-if="iconUrl" :src="iconUrl" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" @error="onIconError">
            </div>
            <span v-if="member?.isOwner" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-black text-obsidian" title="Lobby owner">★</span>
        </div>

        <div class="w-full text-center leading-tight">
            <p class="truncate text-[12px] font-bold" :class="hasMember ? 'text-cream' : 'text-mist/50'">{{ member?.gameName || 'Empty slot' }}</p>
            <p class="mt-0.5 text-[10px] text-mist">{{ meta }}</p>
        </div>

        <div class="flex h-6 items-center gap-2">
            <span v-if="showReady" class="px-2 py-0.5 text-[9px] font-black uppercase tracking-[0.16em] bg-vine/15 text-vine">{{ member?.ready ? 'Ready' : 'Not ready' }}</span>
            <button v-if="canKick" :disabled="kicking" class="border border-ember/40 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.16em] text-ember transition-colors hover:bg-ember/10 disabled:cursor-not-allowed disabled:opacity-50" @click="$emit('kick', member)">Kick</button>
        </div>
    </div>
</template>
