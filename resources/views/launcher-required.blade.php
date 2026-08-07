@extends('layouts.app')

@section('title', 'Launcher Required')

@section('content')
    <div class="flex min-w-0 flex-1 flex-col items-center justify-center px-8 py-6">
        <div class="panel clip-corner reveal w-full max-w-xl px-10 py-14 text-center" style="--reveal-delay:.05s">
            <div class="mx-auto mb-10 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-gold-bright via-gold to-gold-deep shadow-[0_0_30px_rgba(200,170,110,0.35)]">
                <svg class="h-8 w-8 text-obsidian" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M12 2l7 4v5c0 5-3 8.5-7 11-4-2.5-7-6-7-11V6l7-4z" stroke-linejoin="round"/>
                    <path d="M9 11.5l2.2 2.2L15.5 9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <p class="label mb-4 text-gold-deep">Launcher required</p>

            <h1 class="font-display text-2xl font-bold leading-snug text-cream">
                You must log into the native launcher in order to use this app.
            </h1>

            <p class="mx-auto mt-6 max-w-md text-[13px] leading-relaxed text-mist">
                LoL Client reads live data straight from your local League of Legends
                installation. Until the launcher is running and signed in, there is
                nothing to show.
            </p>

            <div class="mt-12 grid grid-cols-3 gap-3 text-left">
                <div class="panel-dim clip-corner-sm px-4 py-5">
                    <p class="font-display text-sm font-bold text-gold-bright">1</p>
                    <p class="mt-3 text-[11px] font-semibold leading-snug text-cream">Launch the client</p>
                    <p class="mt-2 text-[10px] leading-relaxed text-mist">Open the League of Legends launcher.</p>
                </div>
                <div class="panel-dim clip-corner-sm px-4 py-5">
                    <p class="font-display text-sm font-bold text-gold-bright">2</p>
                    <p class="mt-3 text-[11px] font-semibold leading-snug text-cream">Sign in</p>
                    <p class="mt-2 text-[10px] leading-relaxed text-mist">Log in with your account in the launcher.</p>
                </div>
                <div class="panel-dim clip-corner-sm px-4 py-5">
                    <p class="font-display text-sm font-bold text-gold-bright">3</p>
                    <p class="mt-3 text-[11px] font-semibold leading-snug text-cream">Return here</p>
                    <p class="mt-2 text-[10px] leading-relaxed text-mist">Reopen the app and your data will load.</p>
                </div>
            </div>

            <a href="{{ url('/') }}"
               class="clip-corner-sm mt-10 inline-flex items-center gap-3 bg-gradient-to-br from-gold-bright via-gold to-gold-deep px-6 py-3 text-[12px] font-bold uppercase tracking-[0.2em] text-obsidian transition-transform hover:scale-[1.03]">
                Retry connection
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>
@endsection
