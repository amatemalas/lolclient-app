<script setup>
import FriendRow from './FriendRow.vue';

const props = defineProps({
    friends: { type: Array, default: () => [] },
    assetBase: { type: String, default: '' },
    pending: { type: Object, default: () => ({}) },
});

defineEmits(['invite']);

function isInviting(friend) {
    return Boolean(friend && props.pending[`invite:${friend.summonerId}`]);
}
</script>

<template>
    <aside id="friends-list" class="panel  reveal flex min-h-0 flex-col p-4" style="--reveal-delay:.12s">
        <div class="flex items-center justify-between">
            <h3 class="label text-gold-deep">Invite Friends</h3>
            <span class="bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright" data-friend-count>{{ friends.length }}</span>
        </div>
        <p class="mt-1 text-[10px] text-mist">Availability and invites update live.</p>

        <div class="mt-3 max-h-[560px] min-h-0 flex-1 space-y-1 overflow-y-auto pr-1">
            <FriendRow
                v-for="friend in friends"
                :key="friend.summonerId"
                :friend="friend"
                :asset-base="assetBase"
                :inviting="isInviting(friend)"
                @invite="$emit('invite', $event)"
            />
        </div>
    </aside>
</template>
