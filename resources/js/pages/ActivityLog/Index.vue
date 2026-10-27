<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Activity log" />

        <div class="mt-5 space-y-6">
            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-[1fr_1fr_160px_160px]">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Usuario</label>
                    <select v-model="causerId" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Todos</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Acción</label>
                    <select v-model="logName" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Todas</option>
                        <option v-for="name in logNames" :key="name" :value="name">{{ logNameLabels[name] ?? name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Desde</label>
                    <input v-model="from" type="date" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Hasta</label>
                    <input v-model="to" type="date" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Fecha</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Usuario</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Acción</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Descripción</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <template v-for="activity in activities.data" :key="activity.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDateTime(activity.created_at) }}</td>
                                    <td class="px-5 py-4 text-theme-sm sm:px-6">{{ activity.causer?.name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-theme-sm sm:px-6">{{ logNameLabels[activity.log_name ?? ''] ?? activity.log_name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-theme-sm sm:px-6">{{ activity.description }}</td>
                                    <td class="px-5 py-4 text-theme-sm sm:px-6">
                                        <button
                                            v-if="hasProperties(activity)"
                                            type="button"
                                            class="text-indigo-600 hover:underline dark:text-indigo-400"
                                            @click="toggleExpanded(activity.id)"
                                        >
                                            {{ expanded.has(activity.id) ? 'Ocultar' : 'Ver' }}
                                        </button>
                                        <span v-else class="text-slate-400">—</span>
                                    </td>
                                </tr>
                                <tr v-if="expanded.has(activity.id)">
                                    <td colspan="5" class="bg-slate-50 px-5 py-4 text-theme-xs sm:px-6 dark:bg-slate-800/40">
                                        <pre class="whitespace-pre-wrap text-slate-600 dark:text-slate-300">{{ JSON.stringify(detailFor(activity), null, 2) }}</pre>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div v-if="!activities.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Sin actividad registrada</h3>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ activities.from }} a {{ activities.to }} de {{ activities.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link v-if="activities.prev_page_url" :href="activities.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Anterior</Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ activities.current_page }} de {{ activities.last_page }}</span>
                        <Link v-if="activities.next_page_url" :href="activities.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Siguiente</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface ActivityRow {
    id: number;
    log_name: string | null;
    description: string;
    created_at: string;
    causer: { id: number; name: string } | null;
    properties: Record<string, unknown> | unknown[];
    attribute_changes: Record<string, unknown> | unknown[] | null;
}

const { activities, filters, users, logNames } = defineProps<{
    activities: {
        data: ActivityRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: { causer_id: number | null; log_name: string; from: string; to: string };
    users: { id: number; name: string }[];
    logNames: string[];
}>();

const causerId = ref(filters.causer_id ?? '');
const logName = ref(filters.log_name ?? '');
const from = ref(filters.from ?? '');
const to = ref(filters.to ?? '');

const logNameLabels: Record<string, string> = {
    producto: 'Producto',
    pago: 'Pago',
    pedido: 'Pedido',
    recibo: 'Recibo',
    'configuración de tienda': 'Configuración de tienda',
    'impersonación': 'Impersonación',
};

const submit = () => {
    const params: Record<string, string> = {};
    if (causerId.value) params.causer_id = String(causerId.value);
    if (logName.value) params.log_name = logName.value;
    if (from.value) params.from = from.value;
    if (to.value) params.to = to.value;

    router.get('/activity-log', params, { preserveState: true, preserveScroll: true, replace: true });
};

watch([causerId, logName, from, to], submit);

const expanded = ref<Set<number>>(new Set());

const toggleExpanded = (id: number) => {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
};

const hasProperties = (activity: ActivityRow): boolean =>
    (!!activity.properties && Object.keys(activity.properties).length > 0) ||
    (!!activity.attribute_changes && Object.keys(activity.attribute_changes).length > 0);

const detailFor = (activity: ActivityRow) => ({
    ...(hasEntries(activity.attribute_changes) ? { cambios: activity.attribute_changes } : {}),
    ...(hasEntries(activity.properties) ? { propiedades: activity.properties } : {}),
});

const hasEntries = (value: Record<string, unknown> | unknown[] | null | undefined): boolean =>
    !!value && Object.keys(value).length > 0;

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>
