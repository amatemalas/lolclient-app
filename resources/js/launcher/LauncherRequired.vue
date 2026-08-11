<script setup>
import { ref } from 'vue';
import LockfileSettings from '../settings/LockfileSettings.vue';

const configOpen = ref(false);

function openConfig() {
    configOpen.value = true;
}

function closeConfig() {
    configOpen.value = false;
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        configOpen.value = false;
    }
}
</script>

<template>
    <div
        class="flex min-w-0 flex-1 flex-col items-center justify-center px-8 py-6"
        @keydown="onKeydown"
    >
        <div class="panel reveal w-full max-w-xl px-10 py-10 text-center" style="--reveal-delay:.05s">
            <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-gold-bright via-gold to-gold-deep shadow-[0_0_30px_rgba(200,170,110,0.35)]">
                <svg class="h-7 w-7 text-obsidian" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z" stroke-linejoin="round"/>
                    <path d="M9 11.5l2.2 2.2L15.5 9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <p class="label mb-3 text-gold-deep">Launcher required</p>

            <h1 class="font-display text-xl font-bold leading-snug text-cream">
                Log into the League of Legends launcher to use this app.
            </h1>

            <p class="mx-auto mt-4 max-w-md text-[13px] leading-relaxed text-mist">
                LoL Client reads live data from your local installation. Start the
                launcher, sign in, then reconnect below.
            </p>

            <div class="mt-7 flex flex-wrap items-center justify-center gap-2 text-[10px] font-semibold uppercase tracking-[0.15em] text-mist">
                <span class="border border-line bg-steel-2/70 px-3 py-1.5">1 · Launch the client</span>
                <span class="text-gold/50">→</span>
                <span class="border border-line bg-steel-2/70 px-3 py-1.5">2 · Sign in</span>
                <span class="text-gold/50">→</span>
                <span class="border border-line bg-steel-2/70 px-3 py-1.5">3 · Return here</span>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="/"
                    class="inline-flex items-center gap-3 bg-gradient-to-br from-gold-bright via-gold to-gold-deep px-6 py-3 text-[12px] font-bold uppercase tracking-[0.2em] text-obsidian transition-transform hover:scale-[1.03]"
                >
                    Retry connection
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <button
                    type="button"
                    class="inline-flex items-center gap-3 border border-line px-6 py-3 text-[12px] font-bold uppercase tracking-[0.2em] text-mist transition-colors hover:border-gold/60 hover:text-cream"
                    @click="openConfig"
                >
                    Client settings
                </button>
            </div>
        </div>
    </div>

    <!-- Client settings modal -->
    <Teleport to="body">
        <div
            v-if="configOpen"
            role="dialog"
            aria-modal="true"
            aria-label="Client settings"
            class="fixed inset-0 z-40 flex flex-col items-center justify-center px-6"
        >
            <div class="absolute inset-0 bg-void/80 backdrop-blur-sm" @click="closeConfig"></div>
            <div class="relative w-full max-w-2xl">
                <div class="mb-3 flex items-center justify-between">
                    <p class="label text-gold-deep">Client settings</p>
                    <button
                        type="button"
                        aria-label="Close"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-line bg-obsidian/80 text-mist transition-colors hover:border-gold/60 hover:text-cream"
                        @click="closeConfig"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="max-h-[70vh] overflow-y-auto">
                    <LockfileSettings />
                </div>
            </div>
        </div>
    </Teleport>
</template>
