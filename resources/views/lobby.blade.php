@extends('layouts.app')

@section('title', 'LoL Client — Lobby')

@section('content')
    @php
        $seed = [
            'connected'  => $connected ?? false,
            'error'      => $error ?? null,
            'gameflow'   => $gameflow ?? 'None',
            'lobby'      => $lobby ?? null,
            'friends'    => $friends ?? [],
            'queueModes' => $queueModes ?? [],
            'summoner'   => $summoner ?? [],
            'wallet'     => $wallet ?? ['rp' => 0, 'be' => 0],
            'signature'  => $signature ?? null,
        ];
    @endphp

    <div id="lobby-app" class="contents"></div>

    <script type="application/json" id="lobby-initial">@json($seed)</script>

    @vite(['resources/js/lobby.js'])
@endsection
