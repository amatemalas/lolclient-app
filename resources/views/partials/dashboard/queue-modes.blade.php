@php
    $modes = $queueModes ?? [];

    foreach ($modes as &$mode) {
        $mode['disabled'] = ! $connected;
    }
    unset($mode);
@endphp

<section class="grid grid-cols-4 gap-3" style="--reveal-delay:.08s">
    @foreach ($modes as $mode)
        <button type="button"
                data-queue-id="{{ $mode['id'] }}"
                data-queue-action
                class="clip-corner-sm panel reveal relative overflow-hidden p-4 text-left transition-colors hover:border-gold/40 {{ $mode['disabled'] ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}"
                {{ $mode['disabled'] ? 'disabled' : '' }}>
            @if ($mode['featured'])
                <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-gold to-transparent"></div>
            @endif
            <div class="flex items-center justify-between">
                <span class="{{ $mode['featured'] ? 'text-gold' : 'text-arcane' }}">
                    @include('partials.lol-icon', ['name' => $mode['icon'], 'class' => 'h-6 w-6'])
                </span>
                @if ($mode['disabled'])
                    <span class="clip-corner-sm bg-ember/15 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.2em] text-ember">Offline</span>
                @else
                    <span class="h-1.5 w-1.5 rounded-full {{ $mode['featured'] ? 'bg-gold' : 'bg-arcane' }}"></span>
                @endif
            </div>
            <p class="mt-3 font-display text-sm font-bold uppercase tracking-[0.12em] {{ $mode['featured'] ? 'text-gold-bright' : 'text-cream' }}">{{ $mode['name'] }}</p>
            <p class="mt-0.5 text-[11px] text-mist">{{ $mode['queue'] }}</p>
            <p class="{{ $mode['disabled'] ? 'text-ember/80' : 'text-arcane-bright' }} mt-3 text-[10px] font-bold uppercase tracking-[0.22em]">{{ $mode['disabled'] ? 'Offline' : 'Play' }}</p>
        </button>
    @endforeach
</section>
