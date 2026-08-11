<script setup>
import { ref, computed, watch } from 'vue';
import AppSidebar from '../components/AppSidebar.vue';
import AppTopbar from '../components/AppTopbar.vue';
import LobbyApp from './LobbyApp.vue';

const props = defineProps({
    initial: { type: Object, required: true },
});

const assetBase = '/api/lcu/assets';
// LobbyApp children expect the profile-icons specific base.
const lobbyAssetBase = `${assetBase}/v1/profile-icons`;

const topbarSubtitle = ref(props.initial.lobby?.queue?.name || 'No game mode');
</script>

<template>
    <AppSidebar
        :summoner="initial.summoner"
        :wallet="initial.wallet ?? { rp: 0, be: 0 }"
        active-page="lobby"
        :asset-base="assetBase"
    />

    <div class="flex min-w-0 flex-1 flex-col">
        <AppTopbar
            title="Lobby"
            :subtitle="topbarSubtitle"
            :summoner="initial.summoner"
            :asset-base="assetBase"
            :initial-connected="initial.connected"
            :initial-gameflow="initial.gameflow"
        />

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                <LobbyApp
                    :initial="initial"
                    :asset-base="lobbyAssetBase"
                    @queue-name-changed="topbarSubtitle = $event"
                />
            </div>
        </main>
    </div>
</template>
