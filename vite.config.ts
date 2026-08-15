import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
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
        wayfinder({
            formVariants: true,
            throwOnError: false,
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(import.meta.dirname, 'resources/js'),
        },
    },
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-vue': ['vue', '@inertiajs/vue3'],
                    'vendor-apexcharts': ['apexcharts', 'vue3-apexcharts'],
                    'vendor-fullcalendar': [
                        '@fullcalendar/core',
                        '@fullcalendar/daygrid',
                        '@fullcalendar/timegrid',
                        '@fullcalendar/list',
                        '@fullcalendar/interaction',
                        '@fullcalendar/vue3',
                    ],
                },
            },
        },
    },
    server: {
        hmr: {
            overlay: false,
        },
    },
});
