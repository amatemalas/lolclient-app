<script setup>
import { computed } from 'vue';
import AppIcon from '../lobby/AppIcon.vue';
import SummonerAvatar from './SummonerAvatar.vue';

const props = defineProps({
    summoner: { type: Object, required: true },
    wallet: { type: Object, default: () => ({ rp: 0, be: 0 }) },
    activePage: { type: String, default: '' },
    assetBase: { type: String, default: '/api/lcu/assets' },
});

const nav = [
    { label: 'Home',       page: 'dashboard',  icon: 'home',       href: '/' },
    { label: 'Play',       page: 'lobby',      icon: 'play',       href: '/lobby' },
    { label: 'Collection', page: 'collection', icon: 'collection', href: '#' },
    { label: 'ARAM',       page: 'aram',       icon: 'aram',       href: '#' },
    { label: 'Clash',      page: 'clash',      icon: 'clash',      href: '#' },
    { label: 'Shop',       page: 'shop',       icon: 'shop',       href: '#' },
];

const profileIconUrl = computed(() =>
    props.summoner.profileIconId
        ? `${props.assetBase}/v1/profile-icons/${props.summoner.profileIconId}.jpg`
        : null
);

function fmt(n) {
    return Number(n ?? 0).toLocaleString();
}
</script>

<template>
    <aside class="flex w-60 shrink-0 flex-col border-r border-line/60 bg-obsidian/80 backdrop-blur-sm">
        <!-- Brand -->
        <div class="flex items-center gap-3 border-b border-line/60 px-5 py-5">
            <div class="flex h-9 w-9 items-center justify-center bg-gradient-to-br from-gold-bright via-gold to-gold-deep shadow-[0_0_18px_rgba(200,170,110,0.45)]">
                <svg class="h-5 w-5 text-obsidian" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z" stroke-linejoin="round"/>
                    <path d="M9 11.5l2.2 2.2L15.5 9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="leading-tight">
                <p class="font-display text-sm font-bold uppercase tracking-[0.22em] text-cream">LoL</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.34em] text-gold">Client</p>
            </div>
        </div>

        <!-- Nav -->
        <nav class="mt-4 flex-1 space-y-1 px-3">
            <a
                v-for="item in nav"
                :key="item.page"
                :href="item.href"
                class="flex items-center gap-3 rounded-sm border px-3 py-2.5 text-[13px] font-semibold transition-colors"
                :class="activePage === item.page
                    ? 'border-gold/60 bg-gold/10 text-gold-bright'
                    : 'border-transparent text-mist hover:bg-steel-2/70 hover:text-cream'"
            >
                <AppIcon :name="item.icon" class="h-[18px] w-[18px]" />
                <span class="uppercase tracking-[0.16em]">{{ item.label }}</span>
            </a>

            <div class="!mt-6 px-3 pt-5">
                <p class="label text-gold-deep">Account</p>
            </div>
            <a
                href="/settings"
                class="flex items-center gap-3 rounded-sm border px-3 py-2.5 text-[13px] font-semibold transition-colors"
                :class="activePage === 'settings'
                    ? 'border-gold/60 bg-gold/10 text-gold-bright'
                    : 'border-transparent text-mist hover:bg-steel-2/70 hover:text-cream'"
            >
                <AppIcon name="settings" class="h-[18px] w-[18px]" />
                <span class="uppercase tracking-[0.16em]">Settings</span>
            </a>
        </nav>

        <!-- Account footer -->
        <div class="border-t border-line/60 p-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <SummonerAvatar
                        :icon="profileIconUrl"
                        :name="summoner.gameName"
                        gradient="from-cerulean via-steel-2 to-obsidian"
                        class="h-11 w-11"
                    />
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-vine ring-2 ring-obsidian"></span>
                </div>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[13px] font-bold text-cream">{{ summoner.gameName }}</p>
                    <p class="text-[11px] text-mist">
                        Level <span class="font-mono text-gold">{{ fmt(summoner.summonerLevel) }}</span>
                        · #{{ summoner.tagLine }}
                    </p>
                </div>
                <span class="bg-gold/15 px-2 py-1 font-mono text-[10px] font-semibold text-gold-bright">LVL</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] font-semibold">
                <span class="flex items-center gap-1.5 text-gold" title="Blue Essence">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z"/>
                    </svg>
                    <span class="font-mono">{{ fmt(wallet.be) }}</span>
                </span>
                <span class="ml-auto flex items-center gap-1.5 text-cerulean" title="Riot Points">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2a6 6 0 00-6 6c0 .8.2 1.6.5 2.3C4.7 11.4 3 13.6 3 16v1a5 5 0 005 5h8a5 5 0 005-5v-1c0-2.4-1.7-4.6-3.5-5.7.3-.7.5-1.5.5-2.3a6 6 0 00-6-6z"/>
                    </svg>
                    <span class="font-mono">{{ fmt(wallet.rp) }}</span>
                </span>
            </div>
        </div>
    </aside>
</template>
