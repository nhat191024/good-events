import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { startPageLoading, finishPageLoading, setPageProgress, isPageLoadingStarted, resetPageLoading } from './composables/usePageLoading';
import LoadingPopup from './components/LoadingPopup.vue';

// Expose Pusher to window for Echo
(window as any).Pusher = Pusher;

// Only initialize Echo if Pusher key is configured
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    (window as any).Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });
}

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

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createSSRApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // Mount LoadingPopup as a separate app so it doesn't interfere with SSR hydration
        const loadingContainer = document.createElement('div');
        document.body.appendChild(loadingContainer);
        createSSRApp(LoadingPopup).mount(loadingContainer);
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
