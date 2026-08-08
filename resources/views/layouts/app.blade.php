<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'LoL Client')</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="grain font-sans bg-void text-cream select-none overflow-hidden">
        {{-- Ambient background glows --}}
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0">
            <div class="glow-pulse absolute -top-32 left-1/3 h-[480px] w-[640px] rounded-full bg-arcane/[0.07] blur-[120px]"></div>
            <div class="absolute -bottom-40 right-0 h-[420px] w-[560px] rounded-full bg-cerulean/[0.06] blur-[120px]"></div>
            <div class="absolute bottom-0 left-0 h-[300px] w-[420px] rounded-full bg-gold/[0.04] blur-[100px]"></div>
        </div>

        <div class="relative z-10 flex h-full overflow-hidden">
            @yield('content')
        </div>

        <template id="toast-template">
            <div class="toast clip-corner-sm pointer-events-auto flex items-center gap-3 border border-line bg-obsidian px-4 py-3 shadow-2xl">
                <span class="toast-dot h-2 w-2 shrink-0 rounded-full bg-gold"></span>
                <p class="toast-text min-w-0 text-[11px] font-semibold text-cream"></p>
            </div>
        </template>

        <div class="pointer-events-none fixed bottom-6 right-6 z-50 flex flex-col gap-2" data-toasts></div>
    </body>
</html>
