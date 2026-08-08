@extends('layouts.app')

@section('title', 'LoL Client — Lobby')

@section('content')
    @php
        $asset = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);

        $lobbyQueue = $lobby['queue'] ?? [
            'id' => null,
            'name' => 'No game mode',
            'description' => 'Select a mode to start a lobby.',
            'map' => '—',
            'icon' => 'aram',
            'players' => 5,
        ];
        $lobbyPlayerCount = $lobby['playerCount'] ?? 0;
        $lobbySlots = $lobby['slots'] ?? [];

        $profileIconPath = $summoner['profileIconId']
            ? 'v1/profile-icons/'.$summoner['profileIconId'].'.jpg'
            : null;
    @endphp

    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col" data-asset-base="{{ route('api.lcu.asset', ['path' => 'v1/profile-icons']) }}">
        @include('partials.topbar', [
            'topbarTitle' => 'Lobby',
            'topbarSubtitle' => $lobbyQueue['name'],
        ])

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                @include('partials.lobby.status-banner')
                @include('partials.lobby.ready-check')
                @include('partials.lobby.header')

                <div class="grid grid-cols-[minmax(0,1fr)_300px] gap-5">
                    @include('partials.lobby.members')
                    @include('partials.lobby.friends')
                </div>

                @include('partials.lobby.actions')
            </div>
        </main>
    </div>

    @vite(['resources/js/lobby.js'])
@endsection
