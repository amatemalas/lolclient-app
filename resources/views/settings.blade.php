@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    @php
        $seed = [
            'connected' => $connected ?? false,
            'error'     => $error ?? null,
            'gameflow'  => $gameflow ?? 'None',
            'summoner'  => $summoner ?? [],
            'wallet'    => $wallet ?? ['rp' => 0, 'be' => 0],
        ];
    @endphp

    <div id="settings-app" class="contents"></div>

    <script type="application/json" id="settings-initial">@json($seed)</script>

    @vite(['resources/js/settings.js'])
@endsection
