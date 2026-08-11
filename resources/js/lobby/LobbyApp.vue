<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import {
    createLobby,
    fetchLobby,
    fetchFriends,
    leave,
    setMatchmaking,
    acceptReadyCheck,
    declineReadyCheck,
    inviteFriend,
    kickMember,
    showToast,
} from '../lobby-actions';
import HeaderBar from './HeaderBar.vue';
import ModeModal from './ModeModal.vue';
import MembersPanel from './MembersPanel.vue';
import FriendsPanel from './FriendsPanel.vue';
import ActionsBar from './ActionsBar.vue';
import ReadyCheckBanner from './ReadyCheckBanner.vue';

const props = defineProps({
    initial: { type: Object, required: true },
    assetBase: { type: String, default: '' },
});

const emit = defineEmits(['queue-name-changed']);

const state = reactive({
    connected: Boolean(props.initial.connected),
    error: props.initial.error || '',
    gameflow: props.initial.gameflow || 'None',
    lobby: props.initial.lobby || null,
    friends: props.initial.friends || [],
    queueModes: props.initial.queueModes || [],
    summoner: props.initial.summoner || {},
    rev: props.initial.signature || '',
});

const lobbyInterval = 2500;
const friendsInterval = 6000;
const createdGraceMs = 8000;

const modalOpen = ref(false);
const pending = reactive({});
const now = ref(Date.now());
const readyDeadline = ref(0);

let lastCreatedAt = 0;
let lobbyTimer = 0;
let friendsTimer = 0;
let tickTimer = 0;

// ---------------------------------------------------------------------------
// Derived UI state
// ---------------------------------------------------------------------------

const fallbackQueue = {
    id: null,
    name: 'No game mode',
    description: 'Select a mode to start a lobby.',
    map: '—',
    icon: 'aram',
    players: 5,
};

const queue = computed(() => state.lobby?.queue || fallbackQueue);
const memberCount = computed(() => state.lobby?.playerCount ?? 0);
const maxPlayers = computed(() => state.lobby?.maxPlayers ?? 5);

watch(queue, (q) => emit('queue-name-changed', q?.name || 'No game mode'), { immediate: true });
const showReadyCheck = computed(() => Boolean(state.lobby?.readyCheck && state.gameflow === 'ReadyCheck'));
const secondsLeft = computed(() => Math.max(0, Math.ceil((readyDeadline.value - now.value) / 1000)));

const playLabel = computed(() => {
    if (!state.connected) {
        return 'Offline';
    }

    if (!state.lobby) {
        return 'No lobby';
    }

    switch (state.gameflow) {
        case 'Matchmaking':
            return 'Cancel search';
        case 'ReadyCheck':
            return 'Ready check…';
        case 'ChampSelect':
            return 'In champion select';
        default:
            return state.lobby.canStart ? 'Find match' : `Need ${state.lobby.maxPlayers - state.lobby.playerCount} more`;
    }
});

const playDisabled = computed(() => {
    if (!state.connected || !state.lobby) {
        return true;
    }

    if (state.gameflow === 'Matchmaking') {
        return false;
    }

    if (state.gameflow === 'ReadyCheck' || state.gameflow === 'ChampSelect') {
        return true;
    }

    return !state.lobby.canStart;
});

// ---------------------------------------------------------------------------
// State reconciliation
// ---------------------------------------------------------------------------

function applyLobbyData(data) {
    state.connected = Boolean(data.connected);
    state.error = data.error || '';
    state.gameflow = data.gameflow || 'None';

    if (data.changed === false) {
        return;
    }

    // The client can briefly report no lobby right after creation; keep the
    // freshly created lobby on screen instead of blanking the panel.
    if (!data.lobby && state.lobby && Date.now() - lastCreatedAt < createdGraceMs) {
        return;
    }

    state.lobby = data.lobby || null;

    if (data.signature) {
        state.rev = data.signature;
    }
}

function applyLobbyResult(lobby) {
    state.connected = true;
    state.error = '';
    state.gameflow = lobby?.gameflow || 'Lobby';
    state.lobby = lobby || null;
    state.rev = '';
}

// ---------------------------------------------------------------------------
// Polling
// ---------------------------------------------------------------------------

async function pollLobby() {
    try {
        applyLobbyData(await fetchLobby(state.rev));
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

async function pollFriends() {
    try {
        state.friends = await fetchFriends();
    } catch {
        // Ignore transient failures; the next poll will retry.
    }
}

// ---------------------------------------------------------------------------
// Join / leave / online announcements
// ---------------------------------------------------------------------------

let prevMemberIds = new Set();
let prevMembers = new Map();
let prevFriends = new Map();

watch(
    () => state.lobby?.members,
    (members) => {
        const list = members || [];
        const ids = new Set(list.map((member) => member.summonerId));

        list.forEach((member) => {
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
        prevMembers = new Map(list.filter((member) => !member.isLocal).map((member) => [member.summonerId, member.gameName]));
    },
    { immediate: true }
);

watch(
    () => state.friends,
    (friends) => {
        const online = (availability) => ['chat', 'mobile', 'away', 'dnd'].includes(availability);
        const nowMap = new Map(friends.map((friend) => [friend.summonerId, friend]));

        nowMap.forEach((friend, id) => {
            const previous = prevFriends.get(id);

            if (previous && previous.availability === 'offline' && online(friend.availability)) {
                showToast(`${friend.name} is now online`, 'ok');
            }
        });

        prevFriends = nowMap;
    },
    { immediate: true }
);

watch(
    () => state.lobby?.readyCheck,
    (readyCheck) => {
        if (readyCheck && state.gameflow === 'ReadyCheck') {
            readyDeadline.value = Date.now() + (readyCheck.timeLimitMs || 30000);
        }
    }
);

// ---------------------------------------------------------------------------
// Actions (each returns a Promise; the triggering control stays disabled
// until it settles)
// ---------------------------------------------------------------------------

async function handleLeave() {
    pending.leave = true;

    try {
        await leave();
        showToast('Left the lobby.', 'info');
        lastCreatedAt = 0;
        await pollLobby();
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending.leave = false;
    }
}

async function handlePlay() {
    pending.play = true;

    try {
        const searching = !state.lobby?.searching;

        if (state.lobby) {
            state.lobby.searching = searching;
        }

        await setMatchmaking(searching);
        await pollLobby();
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending.play = false;
    }
}

async function handleAccept() {
    pending.accept = true;

    try {
        await acceptReadyCheck();
        await pollLobby();
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending.accept = false;
    }
}

async function handleDecline() {
    pending.decline = true;

    try {
        await declineReadyCheck();
        await pollLobby();
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending.decline = false;
    }
}

async function handleInvite(friend) {
    const key = `invite:${friend.summonerId}`;
    pending[key] = true;

    try {
        const result = await inviteFriend(friend.summonerId);

        if (result.ok) {
            showToast('Invitation sent.', 'ok');
        } else {
            showToast(result.error || 'Could not send the invitation.', 'error');
        }
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending[key] = false;
    }
}

async function handleKick(member) {
    const key = `kick:${member.summonerId}`;
    pending[key] = true;

    try {
        await kickMember(member.summonerId);
        await pollLobby();
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    } finally {
        pending[key] = false;
    }
}

function handleInviteFriend() {
    document.querySelector('#friends-list')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function handleSwitchMode(queueId) {
    modalOpen.value = false;

    try {
        const result = await createLobbyWithRetry(queueId);

        if (!result.ok) {
            showToast(result.error || 'Could not switch game mode.', 'error');
            return;
        }

        lastCreatedAt = Date.now();

        // Render the lobby returned by the API immediately; the client may not
        // have registered the new gameflow phase yet, so polling would show
        // "No lobby open" until the next tick.
        applyLobbyResult(result.lobby);
        showToast('Lobby updated.', 'ok');
    } catch (error) {
        showToast(error?.message || 'Could not reach the lobby API.', 'error');
    }

    // Reconcile against the authoritative state once the client settles.
    await reconcileLobby();
}

async function createLobbyWithRetry(queueId) {
    for (let attempt = 0; attempt < 2; attempt++) {
        try {
            return await createLobby(queueId);
        } catch (error) {
            if (attempt === 1) {
                throw error;
            }

            // The client can be busy right after a transition; retry once.
            await sleep(700);
        }
    }
}

async function reconcileLobby() {
    for (let i = 0; i < 10; i++) {
        await sleep(500);

        try {
            const data = await fetchLobby(state.rev);

            if (data.lobby) {
                applyLobbyData(data);
                return;
            }
        } catch {
            // Keep retrying; the interval poll continues in the background.
        }
    }
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

// ---------------------------------------------------------------------------
// Lifecycle
// ---------------------------------------------------------------------------

function onKeydown(event) {
    if (event.key === 'Escape') {
        modalOpen.value = false;
    }
}

onMounted(() => {
    pollLobby();
    lobbyTimer = setInterval(pollLobby, lobbyInterval);
    pollFriends();
    friendsTimer = setInterval(pollFriends, friendsInterval);
    tickTimer = setInterval(() => {
        now.value = Date.now();
    }, 1000);

    document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    clearInterval(lobbyTimer);
    clearInterval(friendsTimer);
    clearInterval(tickTimer);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div class="flex flex-col gap-5">
        <div v-if="!state.connected" class="panel-dim  flex items-center gap-3 px-4 py-3" style="--reveal-delay:.01s">
            <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-ember"></span>
            <p class="text-[12px] font-semibold text-cream">League client offline</p>
            <p class="text-[11px] text-mist">{{ state.error || 'Start League of Legends to manage your lobby.' }}</p>
        </div>

        <ReadyCheckBanner
            v-if="showReadyCheck"
            :seconds-left="secondsLeft"
            :accepting="pending.accept"
            :declining="pending.decline"
            @accept="handleAccept"
            @decline="handleDecline"
        />

        <HeaderBar
            :queue="queue"
            :member-count="memberCount"
            :can-leave="Boolean(state.lobby)"
            :leaving="pending.leave"
            @change-mode="modalOpen = true"
            @leave="handleLeave"
        />

        <div class="grid grid-cols-[minmax(0,1fr)_300px] gap-5">
            <MembersPanel
                :lobby="state.lobby"
                :gameflow="state.gameflow"
                :asset-base="assetBase"
                :member-count="memberCount"
                :max-players="maxPlayers"
                :pending="pending"
                @kick="handleKick"
            />
            <FriendsPanel
                :friends="state.friends"
                :asset-base="assetBase"
                :pending="pending"
                @invite="handleInvite"
            />
        </div>

        <ActionsBar
            :summoner="state.summoner"
            :asset-base="assetBase"
            :play-label="playLabel"
            :play-disabled="playDisabled"
            :playing="pending.play"
            @invite-friend="handleInviteFriend"
            @play="handlePlay"
        />

        <ModeModal
            :open="modalOpen"
            :queue-modes="state.queueModes"
            @close="modalOpen = false"
            @select="handleSwitchMode"
        />
    </div>
</template>
