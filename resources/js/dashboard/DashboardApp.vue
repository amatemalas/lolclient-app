<script setup>
import AppSidebar from '../components/AppSidebar.vue';
import AppTopbar from '../components/AppTopbar.vue';
import StatusBanner from './StatusBanner.vue';
import HeroSection from './HeroSection.vue';
import QueueModes from './QueueModes.vue';
import MatchHistorySection from './MatchHistorySection.vue';
import RankCard from './RankCard.vue';
import ProgressCard from './ProgressCard.vue';
import FriendsCard from './FriendsCard.vue';

const props = defineProps({
    initial: { type: Object, required: true },
});

const assetBase = '/api/lcu/assets';
</script>

<template>
    <AppSidebar
        :summoner="initial.summoner"
        :wallet="initial.wallet"
        active-page="dashboard"
        :asset-base="assetBase"
    />

    <div class="flex min-w-0 flex-1 flex-col">
        <AppTopbar
            title="Home"
            :subtitle="initial.summoner.gameName"
            :summoner="initial.summoner"
            :asset-base="assetBase"
            :initial-connected="initial.connected"
            :initial-gameflow="initial.gameflow"
        />

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                <StatusBanner :connected="initial.connected" :error="initial.error" />

                <HeroSection
                    :summoner="initial.summoner"
                    :ranked="initial.ranked"
                    :connected="initial.connected"
                    :asset-base="assetBase"
                />

                <QueueModes :modes="initial.queueModes" :connected="initial.connected" />

                <div class="grid grid-cols-3 gap-5" style="--reveal-delay:.14s">
                    <section class="col-span-2">
                        <MatchHistorySection
                            :matches="initial.matches"
                            :connected="initial.connected"
                            :error="initial.error"
                            :asset-base="assetBase"
                            subtitle="Season 2026 · Split 2"
                        />
                    </section>

                    <section class="flex flex-col gap-5">
                        <RankCard :ranked="initial.ranked" :matches="initial.matches" />
                        <FriendsCard :friends="initial.friends" :connected="initial.connected" :asset-base="assetBase" />
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
