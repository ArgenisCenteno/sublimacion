import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import ThemeProvider from '@/components/layout/ThemeProvider.vue';
import SidebarProvider from '@/components/layout/SidebarProvider.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue')

        try {
            return resolvePageComponent(`./pages/${name}.vue`, pages)
        } catch {
            // Durante desarrollo, Vite puede no detectar inmediatamente los nuevos archivos.
            // En ese caso, intentamos importar dinámicamente para evitar "Page not found".
            return import(`./pages/${name}.vue`)
        }
    },
    setup({ el, App, props, plugin }) {
        createApp({
            render: () =>
                h(ThemeProvider, () =>
                    h(SidebarProvider, () =>
                        h(App, props)
                    )
                ),
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
