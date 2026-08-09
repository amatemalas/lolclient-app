@extends('layouts.app')

@section('title', 'LoL Client — Lobby')

@section('content')
    @php
        $seed = [
            'connected' => $connected ?? false,
            'error' => $error ?? null,
            'gameflow' => $gameflow ?? 'None',
            'lobby' => $lobby ?? null,
            'friends' => $friends ?? [],
            'queueModes' => $queueModes ?? [],
            'summoner' => $summoner ?? [],
            'signature' => $signature ?? null,
        ];
    @endphp

    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">
        @include('partials.topbar', [
            'topbarTitle' => 'Lobby',
            'topbarSubtitle' => $lobby['queue']['name'] ?? 'No game mode',
        ])

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5" data-asset-base="{{ route('api.lcu.asset', ['path' => 'v1/profile-icons']) }}">
                <div id="lobby-app"></div>
            </div>
        </main>
    </div>

    <script type="application/json" id="lobby-initial">@json($seed)</script>

    @vite(['resources/js/lobby.js'])
@endsection
