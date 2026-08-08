import { createLobby } from './lobby-actions';

const statusUrl = '/api/lcu/status';
const pollInterval = 10000;

const phaseLabels = {
    None: 'Idle',
    Lobby: 'Lobby',
    Matchmaking: 'Matchmaking',
    ReadyCheck: 'Ready check',
    ChampSelect: 'Champion select',
    GameStart: 'Game start',
    InProgress: 'In game',
    WaitingForStats: 'End of game',
    PreEndOfGame: 'End of game',
    EndOfGame: 'End of game',
    Reconnect: 'Reconnecting',
    PlayAgain: 'Play again',
};

function setLiveState(connected, phase) {
    document.querySelectorAll('[data-live-dot]').forEach((el) => {
        el.classList.remove('bg-vine', 'bg-ember');
        el.classList.add(connected ? 'bg-vine' : 'bg-ember');
    });

    const status = document.querySelector('[data-live-status]');
    if (status) {
        status.textContent = connected ? 'Connected' : 'Offline';
    }

    const gameflow = document.querySelector('[data-live-gameflow]');
    if (gameflow) {
        gameflow.textContent = connected ? (phaseLabels[phase] ?? phase) : 'Client offline';
    }
}

async function poll() {
    try {
        const res = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        setLiveState(Boolean(data.connected), data.gameflow);
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

if (document.querySelector('[data-live-dot]')) {
    poll();
    setInterval(poll, pollInterval);
}

// Refetch: reload the page so all server-rendered data is re-fetched.
document.querySelectorAll('[data-refetch]').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.disabled = true;
        btn.querySelector('svg')?.classList.add('refetch-spin');
        setTimeout(() => window.location.reload(), 350);
    });
});

// Gamemode selection: create the matching lobby, then open it.
document.querySelectorAll('[data-queue-action]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const queueId = Number(btn.dataset.queueId);

        if (!queueId || btn.disabled) {
            return;
        }

        btn.disabled = true;

        try {
            const result = await createLobby(queueId);

            if (result.ok) {
                window.location.href = '/lobby';
                return;
            }

            btn.disabled = false;
        } catch {
            btn.disabled = false;
        }
    });
});
