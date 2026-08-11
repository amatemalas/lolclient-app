import { createApp } from 'vue';
import SettingsApp from './settings/SettingsApp.vue';

const mount = document.getElementById('settings-app');
const initialEl = document.getElementById('settings-initial');

if (mount && initialEl) {
    let initial = {};

    try {
        initial = JSON.parse(initialEl.textContent) || {};
    } catch {
        // Fall back to an empty seed.
    }

    createApp(SettingsApp, { initial }).mount(mount);
}
