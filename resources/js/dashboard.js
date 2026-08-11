import { createApp } from 'vue';
import DashboardApp from './dashboard/DashboardApp.vue';

const mount = document.getElementById('dashboard-app');
const initialEl = document.getElementById('dashboard-initial');

if (mount && initialEl) {
    let initial = {};

    try {
        initial = JSON.parse(initialEl.textContent) || {};
    } catch {
        // Fall back to an empty seed; the page will degrade gracefully.
    }

    createApp(DashboardApp, { initial }).mount(mount);
}
