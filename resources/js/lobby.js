import { createApp } from 'vue';
import LobbyApp from './lobby/LobbyApp.vue';

const mount = document.getElementById('lobby-app');
const initialEl = document.getElementById('lobby-initial');

if (mount && initialEl) {
    let initial = {};

    try {
        initial = JSON.parse(initialEl.textContent) || {};
    } catch {
        // Fall back to an empty seed; polling fills in the real state.
    }

    createApp(LobbyApp, {
        initial,
        assetBase: document.querySelector('[data-asset-base]')?.dataset.assetBase || '',
    }).mount(mount);
}
