import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: true,
        allowedHosts: ['sukientot.test'],
        hmr: {
            host: 'sukientot.test',
            clientPort: 443,
            protocol: 'wss', // Use secure WebSocket
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/js/calendar.js', 'resources/css/filament/partner/theme.css', 'resources/css/filament/admin/theme.css'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
