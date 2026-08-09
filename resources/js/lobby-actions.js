export const DEFAULT_TIMEOUT = 8000;

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Send a request to the LCU api layer and decode the JSON response. Each
 * request is aborted after `timeout` ms so a hung client can never leave the
 * UI waiting indefinitely.
 */
export async function request(path, method = 'GET', body, timeout = DEFAULT_TIMEOUT) {
    const headers = { Accept: 'application/json' };

    if (method !== 'GET') {
        headers['X-CSRF-TOKEN'] = csrfToken();
    }

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeout);

    try {
        const res = await fetch(path, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
            signal: controller.signal,
        });

        return await res.json();
    } catch (error) {
        if (error?.name === 'AbortError') {
            throw new Error('The lobby API timed out.');
        }

        throw error;
    } finally {
        clearTimeout(timer);
    }
}

/**
 * Create (or switch) the lobby for the given queue id.
 */
export async function createLobby(queueId, token = csrfToken()) {
    const res = await fetch('/api/lcu/lobby', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ queue_id: queueId }),
    });

    try {
        return await res.json();
    } catch {
        throw new Error(`Lobby API responded with HTTP ${res.status}.`);
    }
}

/**
 * Fetch the current lobby payload. When `rev` matches the server's current
 * state the response collapses to `{ changed: false }` and no lobby data is
 * included.
 */
export async function fetchLobby(rev) {
    const query = rev ? `?rev=${encodeURIComponent(rev)}` : '';

    return request(`/api/lcu/lobby${query}`);
}

export async function fetchFriends() {
    return request('/api/lcu/friends');
}

export async function leave() {
    return request('/api/lcu/lobby', 'DELETE');
}

export async function setMatchmaking(on) {
    return request('/api/lcu/lobby/matchmaking/search', on ? 'POST' : 'DELETE');
}

export async function acceptReadyCheck() {
    return request('/api/lcu/lobby/ready-check/accept', 'POST');
}

export async function declineReadyCheck() {
    return request('/api/lcu/lobby/ready-check/decline', 'POST');
}

export async function inviteFriend(summonerId) {
    return request(`/api/lcu/lobby/members/${summonerId}/invite`, 'POST');
}

export async function kickMember(summonerId) {
    return request(`/api/lcu/lobby/members/${summonerId}`, 'DELETE');
}

/**
 * Push a toast into the global toast stack. No-ops when the page has no
 * toast container (e.g. the dashboard).
 */
export function showToast(message, kind = 'gold') {
    const container = document.querySelector('[data-toasts]');
    const template = document.getElementById('toast-template');

    if (!container || !template || !message) {
        return;
    }

    const node = template.content.firstElementChild.cloneNode(true);
    const dot = node.querySelector('.toast-dot');
    const text = node.querySelector('.toast-text');

    text.textContent = message;

    dot.classList.remove('bg-gold', 'bg-vine', 'bg-ember', 'bg-arcane');
    dot.classList.add({ gold: 'bg-gold', ok: 'bg-vine', error: 'bg-ember', info: 'bg-arcane' }[kind] || 'bg-gold');

    container.appendChild(node);

    setTimeout(() => {
        node.style.transition = 'all .35s ease';
        node.style.opacity = '0';
        node.style.transform = 'translateY(8px)';
        setTimeout(() => node.remove(), 350);
    }, 4200);
}
