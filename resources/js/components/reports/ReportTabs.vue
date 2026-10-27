<template>
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        <Link
            v-for="tab in visibleTabs"
            :key="tab.href"
            :href="tab.href"
            class="rounded-xl px-4 py-2 text-sm font-medium transition"
            :class="tab.href === current ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const { current } = defineProps<{ current: string }>();

const page = usePage<{ auth: { permissions: string[] } }>();
const permissions = computed(() => page.props.auth?.permissions ?? []);

const tabs = [
    { href: '/reportes', label: 'Dashboard' },
    { href: '/reportes/ventas', label: 'Ventas' },
    { href: '/reportes/financieros', label: 'Financieros', permission: 'reportes.financieros.ver' },
    { href: '/reportes/clientes', label: 'Clientes' },
    { href: '/reportes/inventario', label: 'Inventario' },
    { href: '/reportes/envios', label: 'Envíos' },
];

const visibleTabs = computed(() => tabs.filter((tab) => !tab.permission || permissions.value.includes(tab.permission)));
</script>
