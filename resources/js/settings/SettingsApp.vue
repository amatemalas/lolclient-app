<script setup>
import AppSidebar from '../components/AppSidebar.vue';
import AppTopbar from '../components/AppTopbar.vue';
import LockfileSettings from './LockfileSettings.vue';

const props = defineProps({
    initial: { type: Object, required: true },
});

const assetBase = '/api/lcu/assets';
</script>

<template>
    <AppSidebar
        :summoner="initial.summoner"
        :wallet="initial.wallet"
        active-page="settings"
        :asset-base="assetBase"
    />

    <div class="flex min-w-0 flex-1 flex-col">
        <AppTopbar
            title="Settings"
            subtitle="Configuration"
            :summoner="initial.summoner"
            :asset-base="assetBase"
            :initial-connected="initial.connected"
            :initial-gameflow="initial.gameflow"
        />

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                <LockfileSettings />
            </div>
        </main>
    </div>
</template>
