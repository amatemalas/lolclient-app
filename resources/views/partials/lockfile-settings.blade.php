<section class="panel  reveal w-full max-w-2xl px-8 py-7" style="--reveal-delay:.15s" data-lockfile-settings>
    <div class="flex items-center justify-between gap-4">
        <p class="label text-gold-deep">Client path</p>
        <span class="border border-line bg-steel-2/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-gold-bright" data-lockfile-source>Looking…</span>
    </div>

    <p class="mt-4 text-[12px] leading-relaxed text-mist">
        This app reads live data straight from your local League of Legends installation.
        Normally it finds the client automatically — use this only when your install lives
        somewhere non-standard.
    </p>

    <dl class="mt-5 space-y-2 text-[12px]">
        <div class="flex gap-3">
            <dt class="w-24 shrink-0 font-semibold text-mist">In use</dt>
            <dd class="min-w-0 flex-1 break-all font-mono text-[11px] text-cream" data-lockfile-current>Checking…</dd>
        </div>
        <div class="flex gap-3">
            <dt class="w-24 shrink-0 font-semibold text-mist">Saved</dt>
            <dd class="min-w-0 flex-1 break-all font-mono text-[11px] text-mist" data-lockfile-saved>—</dd>
        </div>
    </dl>

    <form class="mt-6 flex flex-col gap-3" data-lockfile-form>
        <label for="lockfile-path" class="text-[10px] font-semibold uppercase tracking-[0.2em] text-mist">Lockfile path</label>
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="text" id="lockfile-path" name="path" data-lockfile-input
                   placeholder="C:\Riot Games\League of Legends\lockfile"
                   class="min-w-0 flex-1 rounded-sm border border-line bg-obsidian px-4 py-2.5 font-mono text-[12px] text-cream placeholder:text-mist/60 focus:border-gold/70 focus:outline-none">
            <div class="flex gap-2">
                <button type="submit" data-lockfile-save
                        class="bg-gradient-to-br from-gold-bright via-gold to-gold-deep px-5 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-obsidian transition-transform hover:scale-[1.03]">
                    Save
                </button>
                <button type="button" data-lockfile-reset
                        class="border border-line px-4 py-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-mist transition-colors hover:border-gold/60 hover:text-cream">
                    Reset
                </button>
            </div>
        </div>
        <p data-lockfile-message role="status" class="min-h-[1rem] text-[11px] font-semibold"></p>
    </form>

    <details class="mt-4">
        <summary class="cursor-pointer select-none text-[10px] font-semibold uppercase tracking-[0.2em] text-mist hover:text-cream">Known locations</summary>
        <pre class="mt-3 whitespace-pre-wrap break-all font-mono text-[10px] leading-relaxed text-mist" data-lockfile-candidates></pre>
    </details>
</section>

<script>
    (function () {
        const root = document.querySelector('[data-lockfile-settings]');
        if (!root) {
            return;
        }

        const api = '/api/lcu/lockfile';
        const input = root.querySelector('[data-lockfile-input]');
        const form = root.querySelector('[data-lockfile-form]');
        const current = root.querySelector('[data-lockfile-current]');
        const saved = root.querySelector('[data-lockfile-saved]');
        const source = root.querySelector('[data-lockfile-source]');
        const message = root.querySelector('[data-lockfile-message]');
        const resetBtn = root.querySelector('[data-lockfile-reset]');
        const candidates = root.querySelector('[data-lockfile-candidates]');
        const token = document.querySelector('meta[name="csrf-token"]');

        const sourceLabels = { saved: 'Saved', env: 'Configured', detected: 'Auto-detected' };

        function setMessage(text, kind) {
            message.textContent = text || '';
            message.classList.toggle('text-vine', kind === 'ok');
            message.classList.toggle('text-ember', kind === 'error');
        }

        async function load() {
            try {
                const res = await fetch(api, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                current.textContent = data.current || 'Not found — is the launcher running?';
                current.classList.toggle('text-ember', !data.current);
                saved.textContent = data.saved || '—';
                source.textContent = sourceLabels[data.source] || 'Not found';
                if (input && !input.value) {
                    input.value = data.saved || data.current || '';
                }
                if (candidates) {
                    candidates.textContent = (data.candidates || []).join('\n') || 'No known locations.';
                }
            } catch {
                current.textContent = 'Settings unavailable.';
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const path = input.value.trim();
            if (!path) {
                setMessage('Enter the full path to the lockfile.', 'error');
                return;
            }

            try {
                const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
                if (token) {
                    headers['X-CSRF-TOKEN'] = token.content;
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
        });

        resetBtn.addEventListener('click', async () => {
            try {
                const headers = { Accept: 'application/json' };
                if (token) {
                    headers['X-CSRF-TOKEN'] = token.content;
                }

                const res = await fetch(api, { method: 'DELETE', headers });

                if (!res.ok) {
                    setMessage('Could not reset the path.', 'error');
                    return;
                }

                setMessage('Reset. Auto-detection is active again.', 'ok');
                input.value = '';
                await load();
            } catch {
                setMessage('Could not reach the settings API.', 'error');
            }
        });

        load();
    })();
</script>
