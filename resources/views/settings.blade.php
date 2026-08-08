@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">
        @include('partials.topbar')

        <main class="flex-1 overflow-y-auto px-8 py-6">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-5">
                @include('partials.lockfile-settings')
            </div>
        </main>
    </div>
@endsection
