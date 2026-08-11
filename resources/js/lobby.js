import { createApp } from 'vue';
import LobbyPage from './lobby/LobbyPage.vue';

const mount = document.getElementById('lobby-app');
const initialEl = document.getElementById('lobby-initial');

if (mount && initialEl) {
    let initial = {};

    try {
        initial = JSON.parse(initialEl.textContent) || {};
    } catch {
        // Fall back to an empty seed; polling fills in the real state.
    }

    createApp(LobbyPage, { initial }).mount(mount);
}
