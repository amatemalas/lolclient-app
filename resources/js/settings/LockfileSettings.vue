<script setup>
import { ref, onMounted } from 'vue';

const api = '/api/lcu/lockfile';

const currentPath = ref('Checking…');
const savedPath = ref('—');
const sourceLabel = ref('Looking…');
const candidates = ref('');
const pathInput = ref('');
const message = ref('');
const messageKind = ref('');
const showCandidates = ref(false);

const sourceLabels = { saved: 'Saved', env: 'Configured', detected: 'Auto-detected' };

function setMessage(text, kind) {
    message.value = text || '';
    messageKind.value = kind || '';
}

async function load() {
    try {
        const res = await fetch(api, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        currentPath.value = data.current || 'Not found — is the launcher running?';
        savedPath.value = data.saved || '—';
        sourceLabel.value = sourceLabels[data.source] || 'Not found';
        if (!pathInput.value) {
            pathInput.value = data.saved || data.current || '';
        }
        candidates.value = (data.candidates || []).join('\n') || 'No known locations.';
    } catch {
        currentPath.value = 'Settings unavailable.';
    }
}

async function handleSave() {
    const path = pathInput.value.trim();

    if (!path) {
        setMessage('Enter the full path to the lockfile.', 'error');
        return;
    }

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };

        if (token) {
            headers['X-CSRF-TOKEN'] = token;
        }

        const res = await fetch(api, {
            method: 'POST',
            headers,
            body: JSON.stringify({ path }),
        });
        const data = await res.json();

        if (!res.ok) {
            setMessage(data.error || 'Could not save that path.', 'error');
            return;
        }

        setMessage('Saved. Retry the connection to load your data.', 'ok');
        await load();
    } catch {
        setMessage('Could not reach the settings API.', 'error');
    }
}

async function handleReset() {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const headers = { Accept: 'application/json' };

        if (token) {
            headers['X-CSRF-TOKEN'] = token;
        }

        const res = await fetch(api, { method: 'DELETE', headers });

        if (!res.ok) {
            setMessage('Could not reset the path.', 'error');
            return;
        }

        setMessage('Reset. Auto-detection is active again.', 'ok');
        pathInput.value = '';
        await load();
    } catch {
        setMessage('Could not reach the settings API.', 'error');
    }
}

onMounted(load);
</script>

<template>
    <section class="panel reveal w-full max-w-2xl px-8 py-7" style="--reveal-delay:.15s">
        <div class="flex items-center justify-between gap-4">
            <p class="label text-gold-deep">Client path</p>
            <span class="border border-line bg-steel-2/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-gold-bright">
                {{ sourceLabel }}
            </span>
        </div>

        <p class="mt-4 text-[12px] leading-relaxed text-mist">
            This app reads live data straight from your local League of Legends installation.
            Normally it finds the client automatically — use this only when your install lives
            somewhere non-standard.
        </p>

        <dl class="mt-5 space-y-2 text-[12px]">
            <div class="flex gap-3">
                <dt class="w-24 shrink-0 font-semibold text-mist">In use</dt>
                <dd
                    class="min-w-0 flex-1 break-all font-mono text-[11px]"
                    :class="currentPath.startsWith('Not found') ? 'text-ember' : 'text-cream'"
                >{{ currentPath }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-24 shrink-0 font-semibold text-mist">Saved</dt>
                <dd class="min-w-0 flex-1 break-all font-mono text-[11px] text-mist">{{ savedPath }}</dd>
            </div>
        </dl>

        <form class="mt-6 flex flex-col gap-3" @submit.prevent="handleSave">
            <label for="lockfile-path" class="text-[10px] font-semibold uppercase tracking-[0.2em] text-mist">Lockfile path</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    id="lockfile-path"
                    v-model="pathInput"
                    type="text"
                    name="path"
                    placeholder="C:\Riot Games\League of Legends\lockfile"
                    class="min-w-0 flex-1 rounded-sm border border-line bg-obsidian px-4 py-2.5 font-mono text-[12px] text-cream placeholder:text-mist/60 focus:border-gold/70 focus:outline-none"
                >
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="bg-gradient-to-br from-gold-bright via-gold to-gold-deep px-5 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-obsidian transition-transform hover:scale-[1.03]"
                    >Save</button>
                    <button
                        type="button"
                        class="border border-line px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-mist transition-colors hover:border-gold/60 hover:text-cream"
                        @click="handleReset"
                    >Reset</button>
                </div>
            </div>
            <p
                role="status"
                class="min-h-[1rem] text-[11px] font-semibold"
                :class="messageKind === 'ok' ? 'text-vine' : messageKind === 'error' ? 'text-ember' : ''"
            >{{ message }}</p>
        </form>

        <details class="mt-4" :open="showCandidates">
            <summary
                class="cursor-pointer select-none text-[10px] font-semibold uppercase tracking-[0.2em] text-mist hover:text-cream"
                @click.prevent="showCandidates = !showCandidates"
            >Known locations</summary>
            <pre v-if="showCandidates" class="mt-3 whitespace-pre-wrap break-all font-mono text-[10px] leading-relaxed text-mist">{{ candidates }}</pre>
        </details>
    </section>
</template>
