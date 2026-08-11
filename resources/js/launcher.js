import { createApp } from 'vue';
import LauncherRequired from './launcher/LauncherRequired.vue';

const mount = document.getElementById('launcher-app');

if (mount) {
    createApp(LauncherRequired).mount(mount);
}
