import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import * as ElementPlusIconsVue from '@element-plus/icons-vue'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(ElementPlus);

        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
            app.component(key, component)
        }

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

import RSAService from './Utils/RSAService';

// Funcion para desencriptar
window.decryptMessage = (encryptedMessage, privateKey) => {
    if (!privateKey) {
        console.error("Se requiere la llave privada para desencriptar.");
        return "Error: Llave privada requerida";
    }

    // Desencripta el mensaje
    return RSAService.decrypt(encryptedMessage, privateKey);
};

// Funcion para encriptar
window.encryptMessage = (message, publicKey) => {
    if (!publicKey) {
        console.error("Se requiere la llave pública para encriptar.");
        return "Error: Llave publica requerida";
    }
    // Encripta el mensaje
    return RSAService.encrypt(message, publicKey);
};
