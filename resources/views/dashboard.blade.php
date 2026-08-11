@extends('layouts.app')

@section('title', 'LoL Client — Home')

@section('content')
    @php
        $seed = [
            'connected'  => $connected ?? false,
            'error'      => $error ?? null,
            'gameflow'   => $gameflow ?? 'None',
            'summoner'   => $summoner ?? [],
            'wallet'     => $wallet ?? ['rp' => 0, 'be' => 0],
            'ranked'     => $ranked ?? [],
            'matches'    => $matches ?? [],
            'friends'    => $friends ?? [],
            'missions'   => $missions ?? [],
            'queueModes' => $queueModes ?? [],
        ];
    @endphp

    <div id="dashboard-app" class="contents"></div>

    <script type="application/json" id="dashboard-initial">@json($seed)</script>

    @vite(['resources/js/dashboard.js'])
@endsection
