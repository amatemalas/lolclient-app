@extends('layouts.app')

@section('title', 'LoL Client — Home')

@section('content')
    @php
        $asset = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);
    @endphp

    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">
        @include('partials.topbar')

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                @include('partials.dashboard.status-banner')

                @include('partials.dashboard.hero')

                @include('partials.dashboard.queue-modes')

                <div class="grid grid-cols-3 gap-5" style="--reveal-delay:.14s">
                    @include('partials.dashboard.match-history')

                    <section class="flex flex-col gap-5">
                        @include('partials.dashboard.rank-card')
                        @include('partials.dashboard.progress-card')
                        @include('partials.dashboard.friends-card')
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
