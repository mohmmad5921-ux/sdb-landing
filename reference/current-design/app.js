import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'SDB Wallet';
// Force Safari to correctly reload Inertia pages from back-forward cache
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        // If Safari loads raw JSON from BFCache, or if layout glitches, force reload
        if (document.documentElement.textContent.includes('component":')) {
            window.location.reload();
        }
    }
});


createInertiaApp({
    id: 'sw-app',
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
