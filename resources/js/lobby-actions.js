export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
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
 * Send a request to the LCU api layer and decode the JSON response.
 */
export async function request(path, method = 'GET', body) {
    const headers = { Accept: 'application/json' };

    if (method !== 'GET') {
        headers['X-CSRF-TOKEN'] = csrfToken();
    }

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(path, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    return res.json();
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
