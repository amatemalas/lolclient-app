<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>LoL Client — Home</title>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="grain font-sans bg-void text-cream select-none overflow-hidden">
        @php
            $hasRank = $ranked['tier'] !== 'UNRANKED';
            $asset = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);

            $profileIconPath = $summoner['profileIconId']
                ? 'v1/profile-icons/'.$summoner['profileIconId'].'.jpg'
                : null;

            $sumK = $sumD = $sumA = $sumCs = $sumSec = 0;
            foreach ($matches as $m) {
                $sumK += $m['kills'];
                $sumD += $m['deaths'];
                $sumA += $m['assists'];
                $sumCs += $m['cs'];
                $sumSec += $m['durationSeconds'];
            }
            $matchCount = count($matches);
            $kda = $sumD > 0 ? number_format(($sumK + $sumA) / $sumD, 2) : ($matchCount > 0 ? 'Perfect' : '—');
            $csPerMin = $sumSec > 0 ? number_format($sumCs / ($sumSec / 60), 1) : '—';

            $onlineFriends = collect($friends)->reject(fn ($f) => $f['status'] === 'Offline')->count();

            $phaseLabels = [
                'None' => 'Idle',
                'Lobby' => 'Lobby',
                'Matchmaking' => 'Matchmaking',
                'ReadyCheck' => 'Ready check',
                'ChampSelect' => 'Champion select',
                'GameStart' => 'Game start',
                'InProgress' => 'In game',
                'WaitingForStats' => 'End of game',
                'PreEndOfGame' => 'End of game',
                'EndOfGame' => 'End of game',
                'Reconnect' => 'Reconnecting',
                'PlayAgain' => 'Play again',
            ];
            $gameflowLabel = $phaseLabels[$gameflow] ?? $gameflow;

            $champGradients = [
                'from-amber-500/70 via-red-700/70 to-obsidian',
                'from-cerulean via-teal-700/70 to-obsidian',
                'from-gold via-orange-700/70 to-obsidian',
                'from-fuchsia-600/70 via-purple-900/70 to-obsidian',
                'from-emerald-500/70 via-teal-800/70 to-obsidian',
            ];
        @endphp

        {{-- Ambient background glows --}}
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0">
            <div class="glow-pulse absolute -top-32 left-1/3 h-[480px] w-[640px] rounded-full bg-arcane/[0.07] blur-[120px]"></div>
            <div class="absolute -bottom-40 right-0 h-[420px] w-[560px] rounded-full bg-cerulean/[0.06] blur-[120px]"></div>
            <div class="absolute bottom-0 left-0 h-[300px] w-[420px] rounded-full bg-gold/[0.04] blur-[100px]"></div>
        </div>

        <div class="relative z-10 flex h-full overflow-hidden">
            {{-- ================= SIDEBAR ================= --}}
            <aside class="flex w-60 shrink-0 flex-col border-r border-line/60 bg-obsidian/80 backdrop-blur-sm">
                {{-- Brand --}}
                <div class="flex items-center gap-3 border-b border-line/60 px-5 py-5">
                    <div class="clip-corner-sm flex h-9 w-9 items-center justify-center bg-gradient-to-br from-gold-bright via-gold to-gold-deep shadow-[0_0_18px_rgba(200,170,110,0.45)]">
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

                {{-- Nav --}}
                <nav class="mt-4 flex-1 space-y-1 px-3">
                    @php
                        $nav = [
                            ['label' => 'Home', 'active' => true, 'icon' => 'home'],
                            ['label' => 'Play', 'active' => false, 'icon' => 'play'],
                            ['label' => 'Collection', 'active' => false, 'icon' => 'collection'],
                            ['label' => 'ARAM', 'active' => false, 'icon' => 'aram'],
                            ['label' => 'Clash', 'active' => false, 'icon' => 'clash'],
                            ['label' => 'Shop', 'active' => false, 'icon' => 'shop'],
                        ];
                    @endphp
                    @foreach ($nav as $item)
                        <a href="#" class="{{ $item['active'] ? 'border-gold/60 bg-gold/10 text-gold-bright' : 'border-transparent text-mist hover:bg-steel-2/70 hover:text-cream' }} flex items-center gap-3 rounded-sm border px-3 py-2.5 text-[13px] font-semibold transition-colors">
                            <x-lol-icon :name="$item['icon']" class="h-[18px] w-[18px]"/>
                            <span class="uppercase tracking-[0.16em]">{{ $item['label'] }}</span>
                        </a>
                    @endforeach

                    <div class="!mt-6 px-3 pt-5">
                        <p class="label text-gold-deep">Account</p>
                    </div>
                    <a href="#" class="flex items-center gap-3 rounded-sm border border-transparent px-3 py-2.5 text-[13px] font-semibold text-mist transition-colors hover:bg-steel-2/70 hover:text-cream">
                        <x-lol-icon name="settings" class="h-[18px] w-[18px]"/>
                        <span class="uppercase tracking-[0.16em]">Settings</span>
                    </a>
                </nav>

                {{-- Account footer --}}
                <div class="border-t border-line/60 p-4">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <x-summoner-avatar :icon="$profileIconPath ? $asset($profileIconPath) : null" :name="$summoner['gameName']" class="h-11 w-11"/>
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-vine ring-2 ring-obsidian"></span>
                        </div>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-[13px] font-bold text-cream">{{ $summoner['gameName'] }}</p>
                            <p class="text-[11px] text-mist">Level <span class="font-mono text-gold">{{ number_format($summoner['summonerLevel']) }}</span> · #{{ $summoner['tagLine'] }}</p>
                        </div>
                        <span class="clip-corner-sm bg-gold/15 px-2 py-1 font-mono text-[10px] font-semibold text-gold-bright">LVL</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-[11px] font-semibold">
                        <span class="flex items-center gap-1.5 text-gold" title="Blue Essence">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z"/></svg>
                            <span class="font-mono">{{ number_format($wallet['be']) }}</span>
                        </span>
                        <span class="ml-auto flex items-center gap-1.5 text-cerulean" title="Riot Points">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 00-6 6c0 .8.2 1.6.5 2.3C4.7 11.4 3 13.6 3 16v1a5 5 0 005 5h8a5 5 0 005-5v-1c0-2.4-1.7-4.6-3.5-5.7.3-.7.5-1.5.5-2.3a6 6 0 00-6-6z"/></svg>
                            <span class="font-mono">{{ number_format($wallet['rp']) }}</span>
                        </span>
                    </div>
                </div>
            </aside>

            {{-- ================= MAIN ================= --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Top bar --}}
                <header class="flex h-14 shrink-0 items-center gap-4 border-b border-line/60 bg-obsidian/60 px-8 backdrop-blur-sm">
                    <div class="flex items-center gap-3">
                        <h1 class="font-display text-base font-bold uppercase tracking-[0.3em] text-gold-grad">Home</h1>
                        <span class="mt-0.5 h-4 w-px bg-line"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-mist">Summoner's Rift</span>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button class="flex items-center gap-2 rounded-sm border border-line bg-steel px-3 py-1.5 text-[11px] font-semibold text-cream transition-colors hover:border-gold/50">
                            <svg class="h-3.5 w-3.5 text-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M3 6V3h3M21 6V3h-3M3 18v3h3M21 18v3h-3"/></svg>
                            EUW · LAN
                        </button>
                        <span class="flex items-center gap-2 rounded-sm border border-line bg-steel px-3 py-1.5 text-[11px] font-semibold">
                            <span data-live-dot class="h-2 w-2 rounded-full {{ $connected ? 'bg-vine' : 'bg-ember' }}"></span>
                            <span data-live-status class="text-cream">{{ $connected ? 'Connected' : 'Offline' }}</span>
                            <span class="text-line">·</span>
                            <span data-live-gameflow class="text-mist">{{ $connected ? $gameflowLabel : 'Client offline' }}</span>
                        </span>
                        <button class="relative flex h-8 w-8 items-center justify-center rounded-sm border border-line bg-steel text-mist transition-colors hover:text-cream">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
                            <span class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-ember"></span>
                        </button>
                        <button class="flex h-8 w-8 items-center justify-center rounded-sm border border-line bg-steel text-mist transition-colors hover:text-cream">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V21a2 2 0 11-4 0v-.09a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-1.56-1.03H3a2 2 0 110-4h.09A1.7 1.7 0 004.65 8.9a1.7 1.7 0 00-.34-1.88l-.06-.06a2 2 0 112.83-2.83l.06.06a1.7 1.7 0 001.88.34h.08A1.7 1.7 0 0010.13 3V3a2 2 0 114 0v.09a1.7 1.7 0 001.03 1.56h.08a1.7 1.7 0 001.88-.34l.06-.06a2 2 0 112.83 2.83l-.06.06a1.7 1.7 0 00-.34 1.88v.08a1.7 1.7 0 001.56 1.03H21a2 2 0 110 4h-.09a1.7 1.7 0 00-1.56 1.03z"/></svg>
                        </button>
                        <div class="mx-1 h-5 w-px bg-line"></div>
                        <div class="flex items-center gap-2">
                            <x-summoner-avatar :icon="$profileIconPath ? $asset($profileIconPath) : null" :name="$summoner['gameName']" class="h-7 w-7"/>
                            <span class="text-[12px] font-bold text-cream">{{ $summoner['gameName'] }}</span>
                        </div>
                    </div>
                </header>

                {{-- Content --}}
                <main class="flex-1 overflow-y-auto px-8 py-6">
                    <div class="mx-auto flex max-w-[1360px] flex-col gap-5">

                        @if (! $connected)
                            <div class="panel-dim clip-corner flex items-center gap-3 px-4 py-3" style="--reveal-delay:.01s">
                                <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-ember"></span>
                                <p class="text-[12px] font-semibold text-cream">League client offline</p>
                                <p class="text-[11px] text-mist">{{ $error ?? 'Start League of Legends to see live data.' }}</p>
                            </div>
                        @endif

                        {{-- HERO --}}
                        <section class="clip-corner panel reveal overflow-hidden" style="--reveal-delay:.02s">
                            <div class="absolute inset-0 pointer-events-none">
                                <div class="absolute inset-y-0 right-0 w-2/3 bg-[radial-gradient(60%_120%_at_75%_50%,rgba(13,148,206,0.20),transparent_65%)]"></div>
                                <div class="absolute inset-y-0 right-0 w-1/2 bg-[linear-gradient(115deg,transparent_40%,rgba(10,200,185,0.08))]"></div>
                                <div class="absolute right-8 top-1/2 -translate-y-1/2 font-display text-[10rem] font-black leading-none text-gold/[0.06] select-none">R</div>
                            </div>

                            <div class="relative flex flex-wrap items-center gap-8 p-7">
                                <div class="flex items-center gap-5">
                                    <div class="relative">
                                        <x-summoner-avatar :icon="$profileIconPath ? $asset($profileIconPath) : null" :name="$summoner['gameName']" gradient="from-gold-bright via-gold to-gold-deep" class="h-24 w-24 shadow-[0_8px_30px_rgba(200,170,110,0.28)]"/>
                                        <span class="clip-corner-sm absolute -bottom-2 -right-2 bg-obsidian px-2 py-1 font-mono text-[11px] font-semibold text-gold-bright ring-1 ring-gold/50">{{ number_format($summoner['summonerLevel']) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-display text-3xl font-bold uppercase tracking-[0.08em] text-cream">{{ $summoner['gameName'] }}</p>
                                        <p class="mt-1 flex items-center gap-2 text-[12px] font-semibold uppercase tracking-[0.2em] text-mist">
                                            <span class="h-1.5 w-1.5 rotate-45 bg-arcane"></span>
                                            Summoner Level {{ number_format($summoner['summonerLevel']) }} · Season 2026
                                        </p>
                                        <div class="mt-3 flex items-center gap-2">
                                            <span class="clip-corner-sm bg-arcane/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-arcane-bright">Ranked Solo</span>
                                            <span class="clip-corner-sm {{ $hasRank ? 'bg-gold/15 text-gold-bright' : 'bg-ember/15 text-ember' }} px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]">{{ $hasRank ? 'Clash Ready' : 'Unranked' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-auto flex items-center gap-5">
                                    <div class="hidden text-right sm:block">
                                        <p class="label text-gold-deep">Rank</p>
                                        <p class="mt-1 font-display text-2xl font-bold uppercase tracking-[0.1em] text-gold-grad">{{ $hasRank ? $ranked['tier'].' '.$ranked['division'] : 'Unranked' }}</p>
                                        <p class="font-mono text-[12px] text-mist">
                                            @if ($hasRank)
                                                {{ $ranked['leaguePoints'] }} <span class="text-arcane-bright">LP</span>
                                            @else
                                                Play placements
                                            @endif
                                        </p>
                                    </div>
                                    <button class="clip-corner-sm group relative bg-gradient-to-b from-gold-bright via-gold to-gold-deep px-10 py-3.5 font-display text-sm font-black uppercase tracking-[0.3em] text-obsidian shadow-[0_10px_30px_rgba(200,170,110,0.35)] transition-transform hover:-translate-y-0.5" {{ $connected ? '' : 'disabled' }}>
                                        Play
                                        <span class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></span>
                                    </button>
                                    <button class="clip-corner-sm border border-line bg-steel/60 px-8 py-3.5 font-display text-sm font-bold uppercase tracking-[0.3em] text-cream transition-colors hover:border-gold/50 hover:text-gold-bright">
                                        Collection
                                    </button>
                                </div>
                            </div>
                        </section>

                        {{-- QUEUE MODES --}}
                        <section class="grid grid-cols-4 gap-3" style="--reveal-delay:.08s">
                            @php
                                $modes = [
                                    ['name' => 'Ranked Solo', 'queue' => $hasRank ? $ranked['tier'].' '.$ranked['division'] : '5v5 · Ranked', 'featured' => true, 'disabled' => ! $connected],
                                    ['name' => 'Normal', 'queue' => '5v5 · Draft', 'featured' => false, 'disabled' => ! $connected],
                                    ['name' => 'Flex', 'queue' => '5v5 · Flex', 'featured' => false, 'disabled' => ! $connected],
                                    ['name' => 'ARAM', 'queue' => 'Howling Abyss', 'featured' => false, 'disabled' => ! $connected],
                                ];
                            @endphp
                            @foreach ($modes as $mode)
                                <div class="clip-corner-sm panel reveal relative overflow-hidden p-4 transition-colors hover:border-gold/40">
                                    @if ($mode['featured'])
                                        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-gold to-transparent"></div>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="{{ $mode['featured'] ? 'text-gold' : 'text-arcane' }}">
                                            <x-lol-icon :name="$mode['featured'] ? 'ranked' : 'aram'" class="h-6 w-6"/>
                                        </span>
                                        @if ($mode['disabled'])
                                            <span class="clip-corner-sm bg-ember/15 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.2em] text-ember">Offline</span>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full {{ $mode['featured'] ? 'bg-gold' : 'bg-arcane' }}"></span>
                                        @endif
                                    </div>
                                    <p class="mt-3 font-display text-sm font-bold uppercase tracking-[0.12em] {{ $mode['featured'] ? 'text-gold-bright' : 'text-cream' }}">{{ $mode['name'] }}</p>
                                    <p class="mt-0.5 text-[11px] text-mist">{{ $mode['queue'] }}</p>
                                    <p class="{{ $mode['disabled'] ? 'text-ember/80' : 'text-arcane-bright' }} mt-3 text-[10px] font-bold uppercase tracking-[0.22em]">{{ $mode['disabled'] ? 'Offline' : 'Ready' }}</p>
                                </div>
                            @endforeach
                        </section>

                        {{-- BODY GRID --}}
                        <div class="grid grid-cols-3 gap-5" style="--reveal-delay:.14s">
                            {{-- Match history --}}
                            <section class="col-span-2">
                                <div class="mb-3 flex items-center gap-3">
                                    <h2 class="label text-gold-grad">Recent Matches</h2>
                                    <span class="flex-1 border-t border-line/60"></span>
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-mist">Season 2026 · Split 2</span>
                                </div>

                                <div class="panel clip-corner flex flex-col divide-y divide-line/50">
                                    @forelse ($matches as $m)
                                        <div class="group flex items-center gap-4 px-4 py-3 transition-colors hover:bg-steel-2/50">
                                            <div class="flex w-16 items-center gap-2">
                                                <span class="{{ $m['win'] ? 'bg-arcane/15 text-arcane-bright' : 'bg-ember/15 text-ember' }} clip-corner-sm px-2 py-1 text-[9px] font-black uppercase tracking-[0.16em]">
                                                    {{ $m['win'] ? 'Win' : 'Def' }}
                                                </span>
                                            </div>

                                            <div class="clip-corner-sm relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br {{ $champGradients[$m['championId'] % count($champGradients)] }} ring-1 ring-line">
                                                <span class="font-display text-xl font-bold text-cream/90">{{ mb_strtoupper(mb_substr($m['champion'] ?? '?', 0, 1)) }}</span>
                                                <img src="{{ $asset('v1/champion-icons/'.$m['championId'].'.png') }}" alt="{{ $m['champion'] }}" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
                                            </div>

                                            <div class="w-32 shrink-0 leading-tight">
                                                <p class="text-[12px] font-bold {{ $m['win'] ? 'text-arcane-bright' : 'text-ember' }}">{{ $m['mode'] }}</p>
                                                <p class="text-[10px] text-mist">{{ $m['ago'] }} · {{ $m['duration'] }}</p>
                                            </div>

                                            <div class="w-28 shrink-0 leading-tight">
                                                <p class="font-mono text-[12px] font-semibold text-cream">
                                                    <span class="text-gold-bright">{{ $m['kills'] }}</span>
                                                    <span class="text-mist"> / {{ $m['deaths'] }} / {{ $m['assists'] }}</span>
                                                </p>
                                                <p class="text-[10px] text-mist">CS {{ $m['cs'] }} · {{ $m['gold'] }} gold</p>
                                            </div>

                                            <div class="hidden flex-1 items-center gap-1.5 lg:flex">
                                                @for ($s = 0; $s < 6; $s++)
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-[3px] bg-obsidian ring-1 ring-line/70 {{ $s < 5 ? '' : 'opacity-40' }}">
                                                        @if ($s < 5)
                                                            <span class="h-3 w-3 rotate-45 border border-gold/40"></span>
                                                        @endif
                                                    </div>
                                                @endfor
                                            </div>

                                            <div class="ml-auto flex items-center gap-3">
                                                <span class="clip-corner-sm {{ $m['win'] ? 'bg-gold/15 text-gold-bright' : 'bg-ember/15 text-ember' }} px-2 py-0.5 font-display text-[11px] font-bold">Lv {{ $m['champLevel'] }}</span>
                                                <svg class="h-4 w-4 text-mist/50 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-12 text-center">
                                            <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No recent matches</p>
                                            <p class="mt-2 text-[11px] text-mist">{{ $connected ? 'Play a game and it will appear here.' : ($error ?? 'The League client is offline.') }}</p>
                                        </div>
                                    @endforelse

                                    @if (! empty($matches))
                                        <div class="flex items-center justify-center py-3">
                                            <button class="text-[11px] font-bold uppercase tracking-[0.24em] text-mist transition-colors hover:text-gold-bright">
                                                View match history
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            {{-- RIGHT RAIL --}}
                            <section class="flex flex-col gap-5">
                                {{-- Rank --}}
                                <div class="clip-corner panel reveal p-5">
                                    <div class="flex items-center justify-between">
                                        <h3 class="label text-gold-deep">Season 2026</h3>
                                        <span class="font-mono text-[10px] text-mist">Split 2</span>
                                    </div>

                                    <div class="mt-4 flex items-center gap-4">
                                        <div class="relative flex h-20 w-20 shrink-0 items-center justify-center">
                                            <svg class="absolute inset-0 h-full w-full text-gold drop-shadow-[0_0_14px_rgba(200,170,110,0.35)]" viewBox="0 0 80 80" fill="none">
                                                <path d="M40 3l32 15v18c0 17-12 30-32 41C20 66 8 53 8 36V18L40 3z" stroke="currentColor" stroke-width="2.5" fill="rgba(200,170,110,0.12)"/>
                                                <path d="M40 14l22 10.5v12c0 11-8 20-22 27-14-7-22-16-22-27v-12L40 14z" stroke="currentColor" stroke-width="1.5" fill="rgba(10,200,185,0.08)"/>
                                            </svg>
                                            <div class="text-center">
                                                <p class="font-display text-sm font-black leading-none text-gold-bright">{{ $hasRank ? $ranked['division'] : '—' }}</p>
                                                <p class="mt-0.5 font-display text-[8px] font-bold uppercase tracking-[0.24em] text-gold">{{ $hasRank ? $ranked['tier'] : 'Unranked' }}</p>
                                            </div>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="font-mono text-[13px] font-semibold text-cream">{{ $hasRank ? $ranked['leaguePoints'] : '—' }} <span class="text-arcane-bright">LP</span></p>
                                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
                                                <div class="h-full bg-gradient-to-r from-gold-deep via-gold to-gold-bright" style="width: {{ min(100, $ranked['leaguePoints']) }}%"></div>
                                            </div>
                                            <p class="mt-1.5 text-[10px] text-mist">
                                                @if ($hasRank && $ranked['isProvisional'])
                                                    <span class="text-arcane-bright">{{ $ranked['provisionalGamesRemaining'] }} placement games left</span>
                                                @elseif ($hasRank)
                                                    {{ $ranked['lpToNext'] }} LP to <span class="text-cream">{{ $ranked['tier'] }} {{ $ranked['nextDivision'] }}</span>
                                                @else
                                                    Play ranked to unlock
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-line/60 pt-4">
                                        @php
                                            $stats = [
                                                ['l' => 'Win Rate', 'v' => $hasRank ? $ranked['winRate'].'%' : '—'],
                                                ['l' => 'KDA', 'v' => $kda],
                                                ['l' => 'CS / Min', 'v' => $csPerMin],
                                                ['l' => 'Games', 'v' => $hasRank ? number_format($ranked['games']) : '—'],
                                            ];
                                        @endphp
                                        @foreach ($stats as $s)
                                            <div class="rounded-sm bg-obsidian/70 px-3 py-2 ring-1 ring-line/60">
                                                <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-mist">{{ $s['l'] }}</p>
                                                <p class="mt-0.5 font-mono text-[14px] font-semibold text-gold-bright">{{ $s['v'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div class="clip-corner panel reveal p-5" style="--reveal-delay:.06s">
                                    <h3 class="label text-gold-deep">Progress</h3>
                                    <div class="mt-4 flex items-end justify-between">
                                        <p class="font-display text-xl font-bold uppercase text-cream">Level {{ number_format($summoner['summonerLevel']) }}</p>
                                        <p class="font-mono text-[11px] text-mist">{{ number_format($summoner['xpSinceLastLevel']) }} / {{ number_format($summoner['xpUntilNextLevel']) }} <span class="text-gold-bright">XP</span></p>
                                    </div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
                                        <div class="h-full bg-gradient-to-r from-gold-deep via-gold to-gold-bright shadow-[0_0_12px_rgba(200,170,110,0.6)]" style="width: {{ min(100, $summoner['percentCompleteForNextLevel']) }}%"></div>
                                    </div>
                                    <div class="mt-4 space-y-2.5 border-t border-line/60 pt-4">
                                        @forelse ($missions as $q)
                                            <div class="flex items-center gap-3">
                                                <div class="clip-corner-sm {{ $q['done'] >= $q['total'] ? 'bg-vine/20 text-vine' : 'bg-gold/15 text-gold' }} flex h-7 w-7 items-center justify-center">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <p class="truncate text-[12px] font-semibold text-cream">{{ $q['title'] }}</p>
                                                        <p class="font-mono text-[10px] text-mist">{{ $q['done'] }}/{{ $q['total'] }}</p>
                                                    </div>
                                                    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-obsidian ring-1 ring-line/60">
                                                        <div class="h-full bg-gradient-to-r from-gold-deep to-gold-bright" style="width: {{ min(100, $q['done'] / max(1, $q['total']) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="py-2 text-center text-[11px] text-mist">{{ $connected ? 'No active missions' : 'Client offline' }}</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Lobby / Friends --}}
                                <div class="clip-corner panel reveal p-5" style="--reveal-delay:.1s">
                                    <div class="flex items-center justify-between">
                                        <h3 class="label text-gold-deep">Lobby</h3>
                                        <span class="clip-corner-sm bg-arcane/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-arcane-bright">{{ $connected ? $onlineFriends.'/'.count($friends) : '—' }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2.5">
                                        @forelse ($friends as $f)
                                            <div class="flex items-center gap-3 rounded-sm px-2 py-1.5 transition-colors hover:bg-steel-2/60">
                                                <div class="clip-corner-sm relative flex h-8 w-8 items-center justify-center overflow-hidden bg-gradient-to-br from-steel-2 to-obsidian ring-1 ring-line">
                                                    <span class="font-display text-[13px] font-bold text-gold-bright">{{ mb_strtoupper(mb_substr($f['name'], 0, 1)) }}</span>
                                                    @if ($f['icon'])
                                                        <img src="{{ $asset('v1/profile-icons/'.$f['icon'].'.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'" loading="lazy">
                                                    @endif
                                                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full {{ $f['dot'] }} ring-2 ring-obsidian"></span>
                                                </div>
                                                <div class="min-w-0 flex-1 leading-tight">
                                                    <p class="truncate text-[12px] font-semibold text-cream">{{ $f['name'] }}</p>
                                                    <p class="text-[10px] text-mist">{{ $f['status'] }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="py-4 text-center text-[11px] text-mist">{{ $connected ? 'No friends online' : 'Client offline' }}</p>
                                        @endforelse
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
