import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Expose Pusher to window for Echo
(window as any).Pusher = Pusher;

// Configure and expose Echo instance
(window as any).Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

const getAppSettingsFromInitialPage = () => {
    if (typeof document === 'undefined') return undefined;

    const appEl = document.getElementById('app');
    if (!appEl) return undefined;

    const pageData = appEl.getAttribute('data-page') ?? appEl.dataset.page;
    if (!pageData) return undefined;

    try {
        const parsedPage = JSON.parse(pageData);
        return parsedPage?.props?.app_settings;
    } catch {
        return undefined;
    }
};

const appName =
    (getAppSettingsFromInitialPage()?.app_name as string | undefined) ||
    import.meta.env.VITE_APP_NAME ||
    'Laravel';


// ... existing imports
import { startPageLoading, finishPageLoading, setPageProgress, isPageLoadingStarted, resetPageLoading } from './composables/usePageLoading';
import LoadingPopup from './components/LoadingPopup.vue';
import { router } from '@inertiajs/vue3';

// ... existing code

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h('div', [h(App, props), h(LoadingPopup)]) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: false, // Disable default Inertia progress
});

// Setup Inertia router events for loading state
router.on('start', () => {
    startPageLoading();
});

router.on('progress', (event) => {
    if (isPageLoadingStarted() && event.detail.progress.percentage) {
        setPageProgress(event.detail.progress.percentage);
    }
});

router.on('finish', (event) => {
    finishPageLoading();

    // Handle cancelled/interrupted visits
    if (event.detail.visit.interrupted) {
        resetPageLoading();
    } else if (event.detail.visit.cancelled) {
        finishPageLoading();
    }
});

// This will set light / dark mode on page load...
initializeTheme();
