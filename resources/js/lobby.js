import { createLobby, request, showToast } from './lobby-actions';

const lobbyInterval = 2500;
const friendsInterval = 6000;

const assetBase = document.querySelector('[data-asset-base]')?.dataset.assetBase || '';
const toasts = document.querySelector('[data-toasts]');

const state = {
    connected: true,
    gameflow: 'None',
    lobby: null,
};

let prevMemberIds = new Set();
let prevMembers = new Map();
let prevFriends = new Map();
let readyDeadline = 0;
let lastCreatedAt = 0;

// ---------------------------------------------------------------------------
// Members
// ---------------------------------------------------------------------------

function renderSlots(slots) {
    const grid = document.getElementById('members-grid');
    if (!grid) {
        return;
    }

    if (!slots || slots.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full flex flex-col items-center gap-2 py-12 text-center">
                <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-mist">No lobby open</p>
                <p class="max-w-xs text-[11px] text-mist">Choose a game mode above to create a lobby and invite your friends.</p>
            </div>`;
        return;
    }

    const fragment = document.createDocumentFragment();
    slots.forEach((slot, index) => fragment.appendChild(renderSlot(slot, index)));
    grid.replaceChildren(fragment);
}

function renderSlot(slot, index) {
    const template = document.getElementById('member-slot-template');
    const node = template.content.firstElementChild.cloneNode(true);
    const $ = (sel) => node.querySelector(sel);
    const member = slot.member;

    node.dataset.index = String(index);
    node.dataset.summonerId = member ? member.summonerId : '';

    const position = node.querySelector('.label');
    position.textContent = slot.position;
    position.classList.toggle('text-gold-deep', Boolean(member));
    position.classList.toggle('text-mist/50', !member);

    if (member) {
        node.classList.remove('border-dashed', 'border-line/40', 'bg-obsidian/40');
        node.classList.add('border-line', 'bg-steel/70');

        if (member.isLocal) {
            node.classList.add('ring-1', 'ring-gold/50');
        }

        $('[data-field="initial"]').textContent = (member.gameName[0] || '?').toUpperCase();
        $('[data-field="name"]').textContent = member.gameName;
        $('[data-field="name"]').classList.add('text-cream');
        $('[data-field="meta"]').textContent = member.isLocal ? 'You' : member.level ? `Level ${member.level}` : '';

        if (member.icon) {
            const img = $('[data-field="icon"]');
            img.src = `${assetBase}/${member.icon}.jpg`;
            img.classList.remove('hidden');
        }

        if (member.isOwner) {
            $('[data-field="owner"]').classList.remove('hidden');
        }

        if (state.gameflow === 'ReadyCheck' && state.lobby?.readyCheck) {
            const ready = $('[data-field="ready"]');
            ready.classList.remove('hidden');
            ready.textContent = member.ready ? 'Ready' : 'Not ready';
        }

        const canKick = Boolean(state.lobby?.local?.isOwner && !member.isLocal && state.gameflow === 'Lobby');
        const kick = $('[data-action="kick"]');
        if (canKick) {
            kick.classList.remove('hidden');
        }
    }

    return node;
}

function announceMemberChanges() {
    const members = state.lobby?.members || [];
    const ids = new Set(members.map((m) => m.summonerId));

    members.forEach((member) => {
        if (!prevMemberIds.has(member.summonerId) && !member.isLocal) {
            showToast(`${member.gameName} joined the lobby`, 'ok');
        }
    });

    prevMemberIds.forEach((id) => {
        if (!ids.has(id)) {
            const name = prevMembers.get(id);
            if (name) {
                showToast(`${name} left the lobby`, 'info');
            }
        }
    });

    prevMemberIds = ids;
    prevMembers = new Map(members.filter((m) => !m.isLocal).map((m) => [m.summonerId, m.gameName]));
}

// ---------------------------------------------------------------------------
// Friends
// ---------------------------------------------------------------------------

function renderFriends(friends) {
    const list = document.getElementById('friends-list');
    if (!list) {
        return;
    }

    const count = document.querySelector('[data-friend-count]');
    if (count) {
        count.textContent = friends.length;
    }

    const fragment = document.createDocumentFragment();
    friends.forEach((friend) => fragment.appendChild(renderFriend(friend)));
    list.replaceChildren(fragment);
}

function renderFriend(friend) {
    const template = document.getElementById('friend-row-template');
    const node = template.content.firstElementChild.cloneNode(true);
    const $ = (sel) => node.querySelector(sel);

    node.dataset.summonerId = friend.summonerId;
    node.dataset.availability = friend.availability;

    $('[data-field="initial"]').textContent = (friend.name[0] || '').toUpperCase();
    $('[data-field="name"]').textContent = friend.name;
    $('[data-field="name"]').classList.toggle('text-mist', friend.status === 'Offline');
    $('[data-field="status"]').textContent = friend.status;

    const dot = $('[data-field="dot"]');
    dot.classList.remove('bg-vine', 'bg-ember', 'bg-cerulean', 'bg-gold', 'bg-arcane', 'bg-mist');
    dot.classList.add(friend.dot);

    if (friend.icon) {
        const img = $('[data-field="icon"]');
        img.src = `${assetBase}/${friend.icon}.jpg`;
        img.classList.remove('hidden');
    }

    const invite = $('[data-action="invite"]');
    invite.classList.remove('hidden');
    invite.disabled = !friend.invitable;
    invite.classList.toggle('border-arcane/40', friend.invitable);
    invite.classList.toggle('text-arcane-bright', friend.invitable);
    invite.classList.toggle('hover:bg-arcane/10', friend.invitable);
    invite.classList.toggle('border-line', !friend.invitable);
    invite.classList.toggle('text-mist/40', !friend.invitable);

    return node;
}

function announceFriendChanges(friends) {
    const now = new Map(friends.map((f) => [f.summonerId, f]));

    now.forEach((friend, id) => {
        const previous = prevFriends.get(id);
        const online = ['chat', 'mobile', 'away', 'dnd'].includes(friend.availability);

        if (previous && previous.availability === 'offline' && online) {
            showToast(`${friend.name} is now online`, 'ok');
        }
    });

    prevFriends = now;
}

// ---------------------------------------------------------------------------
// Header / controls
// ---------------------------------------------------------------------------

function updateHeader() {
    const lobby = state.lobby;
    const mode = document.querySelector('[data-header-mode]');
    const subtitle = document.querySelector('[data-header-subtitle]');
    const count = document.querySelector('[data-member-count]');

    if (lobby) {
        if (mode) mode.textContent = lobby.queue.name;
        if (subtitle) subtitle.textContent = `${lobby.queue.map} · ${lobby.queue.description}`;
        if (count) count.textContent = lobby.playerCount;
    }
}

function updateControls() {
    const play = document.querySelector('[data-action="play"]');
    const leave = document.querySelector('[data-action="leave"]');
    const label = document.querySelector('[data-play-label]');
    const lobby = state.lobby;

    if (leave) {
        leave.disabled = !lobby;
    }

    if (!play || !label) {
        return;
    }

    if (!state.connected) {
        play.disabled = true;
        label.textContent = 'Offline';
        return;
    }

    if (!lobby) {
        play.disabled = true;
        label.textContent = 'No lobby';
        return;
    }

    switch (state.gameflow) {
        case 'Matchmaking':
            play.disabled = false;
            label.textContent = 'Cancel search';
            break;
        case 'ReadyCheck':
            play.disabled = true;
            label.textContent = 'Ready check…';
            break;
        case 'ChampSelect':
            play.disabled = true;
            label.textContent = 'In champion select';
            break;
        default:
            play.disabled = !lobby.canStart;
            label.textContent = lobby.canStart ? 'Find match' : `Need ${lobby.maxPlayers - lobby.playerCount} more`;
    }
}

function updateReadyCheck() {
    const banner = document.querySelector('[data-ready-check]');
    if (!banner) {
        return;
    }

    const readyCheck = state.lobby?.readyCheck;

    if (!readyCheck || state.gameflow !== 'ReadyCheck') {
        banner.classList.add('hidden');
        return;
    }

    banner.classList.remove('hidden');
    readyDeadline = Date.now() + readyCheck.timeLimitMs;
}

// ---------------------------------------------------------------------------
// Polling
// ---------------------------------------------------------------------------

function applyLobbyData(data) {
    state.connected = Boolean(data.connected);
    state.gameflow = data.gameflow || 'None';
    state.lobby = data.lobby || null;

    renderSlots(state.lobby?.slots || []);
    announceMemberChanges();
    updateHeader();
    updateControls();
    updateReadyCheck();
}

async function pollLobby() {
    try {
        const data = await request('/api/lcu/lobby');
        state.connected = Boolean(data.connected);
        state.gameflow = data.gameflow || 'None';

        // The client can briefly report no lobby right after creation; keep the
        // freshly created lobby on screen instead of blanking the panel.
        if (!data.lobby && state.lobby && Date.now() - lastCreatedAt < 8000) {
            return;
        }

        applyLobbyData(data);
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

async function pollFriends() {
    try {
        const friends = await request('/api/lcu/friends');
        renderFriends(friends);
        announceFriendChanges(friends);
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

// ---------------------------------------------------------------------------
// Actions
// ---------------------------------------------------------------------------

document.addEventListener('click', async (event) => {
    const modeItem = event.target.closest('[data-queue-id]');
    if (modeItem) {
        await switchMode(Number(modeItem.dataset.queueId));
        return;
    }

    const modeToggle = event.target.closest('[data-mode-toggle]');
    if (modeToggle) {
        document.querySelector('[data-mode-modal]')?.classList.remove('hidden');
        return;
    }

    const modeClose = event.target.closest('[data-mode-close], [data-mode-backdrop]');
    if (modeClose) {
        document.querySelector('[data-mode-modal]')?.classList.add('hidden');
        return;
    }

    const actionEl = event.target.closest('[data-action]');
    if (!actionEl) {
        return;
    }

    const action = actionEl.dataset.action;
    actionEl.disabled = true;

    try {
        switch (action) {
            case 'leave':
                lastCreatedAt = 0;
                await request('/api/lcu/lobby', 'DELETE');
                showToast('Left the lobby.', 'info');
                await pollLobby();
                break;

            case 'play':
                await request('/api/lcu/lobby/matchmaking/search', state.lobby?.searching ? 'DELETE' : 'POST');
                await pollLobby();
                break;

            case 'accept':
                await request('/api/lcu/lobby/ready-check/accept', 'POST');
                await pollLobby();
                break;

            case 'decline':
                await request('/api/lcu/lobby/ready-check/decline', 'POST');
                await pollLobby();
                break;

            case 'invite': {
                const id = actionEl.closest('.friend-row')?.dataset.summonerId;
                const result = await request(`/api/lcu/lobby/members/${id}/invite`, 'POST');
                if (result.ok) {
                    showToast('Invitation sent.', 'ok');
                } else {
                    showToast(result.error || 'Could not send the invitation.', 'error');
                }
                break;
            }

            case 'kick': {
                const id = actionEl.closest('.member-slot')?.dataset.summonerId;
                await request(`/api/lcu/lobby/members/${id}`, 'DELETE');
                await pollLobby();
                break;
            }

            case 'invite-friend':
                document.querySelector('#friends-list')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                break;
        }
    } finally {
        actionEl.disabled = false;
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelector('[data-mode-modal]')?.classList.add('hidden');
    }
});

async function switchMode(queueId) {
    document.querySelector('[data-mode-modal]')?.classList.add('hidden');

    try {
        const result = await createLobby(queueId);

        if (!result.ok) {
            showToast(result.error || 'Could not switch game mode.', 'error');
            return;
        }

        lastCreatedAt = Date.now();

        // Render the lobby returned by the API immediately; the client may not
        // have registered the new gameflow phase yet, so polling would show
        // "No lobby open" until the next tick.
        state.connected = true;
        state.lobby = result.lobby || null;
        state.gameflow = state.lobby?.gameflow || 'Lobby';

        renderSlots(state.lobby?.slots || []);
        announceMemberChanges();
        updateHeader();
        updateControls();
        updateReadyCheck();

        showToast('Lobby updated.', 'ok');
    } catch {
        showToast('Could not reach the lobby API.', 'error');
    }

    // Reconcile against the authoritative state once the client settles.
    await reconcileLobby();
}

async function reconcileLobby() {
    for (let i = 0; i < 10; i++) {
        await new Promise((resolve) => setTimeout(resolve, 500));

        try {
            const data = await request('/api/lcu/lobby');

            if (data.lobby) {
                applyLobbyData(data);
                return;
            }
        } catch {
            // Keep retrying; the interval poll continues in the background.
        }
    }
}

// ---------------------------------------------------------------------------
// Countdown for the ready-check timer
// ---------------------------------------------------------------------------

setInterval(() => {
    const timer = document.querySelector('[data-ready-timer]');
    if (!timer || state.gameflow !== 'ReadyCheck' || !readyDeadline) {
        return;
    }

    const seconds = Math.max(0, Math.ceil((readyDeadline - Date.now()) / 1000));
    timer.textContent = seconds > 0 ? `${seconds}s` : 'auto-declining';
}, 1000);

// ---------------------------------------------------------------------------
// Boot
// ---------------------------------------------------------------------------

if (toasts) {
    pollLobby();
    setInterval(pollLobby, lobbyInterval);
    pollFriends();
    setInterval(pollFriends, friendsInterval);
}
