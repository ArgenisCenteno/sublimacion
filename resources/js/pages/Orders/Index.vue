<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Pedidos'" />
        <div
            v-if="flash.success || flash.error"
            class="mt-4 space-y-3"
        >
            <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ flash.error }}
            </div>
        </div>

        <div class="mt-5 space-y-6">
            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-[1fr_200px_180px]">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Buscar</label>
                    <input v-model="search" type="text" placeholder="Nombre o email del cliente..." class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Estado</label>
                    <select v-model="status" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Todos</option>
                        <option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Canal</label>
                    <select v-model="canal" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Todos</option>
                        <option value="online">Online</option>
                        <option value="tienda_fisica">Tienda física</option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Pedido</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Cliente</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Canal</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Estado</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Total</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Fecha</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4 text-theme-sm sm:px-6">#{{ order.id }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ order.customer_name }}
                                    <p class="text-xs text-slate-400">{{ order.customer_email }}</p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm capitalize sm:px-6">{{ order.canal === 'online' ? 'Online' : 'Tienda física' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusBadgeClass(order.status)">
                                        {{ statusLabels[order.status] ?? order.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">${{ Number(order.total_usd).toFixed(2) }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDate(order.created_at) }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <Link :href="`/orders/${order.id}`" class="rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!orders.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No encontramos pedidos</h3>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ orders.from }} a {{ orders.to }} de {{ orders.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Anterior</Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ orders.current_page }} de {{ orders.last_page }}</span>
                        <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Siguiente</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface OrderRow {
    id: number;
    customer_name: string;
    customer_email: string;
    canal: string;
    status: string;
    total_usd: string | number;
    created_at: string;
}

interface Props {
    orders: {
        data: OrderRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: { status?: string; canal?: string; search?: string };
}

const { orders, filters } = defineProps<Props>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const search = ref(filters.search ?? '');
const status = ref(filters.status ?? '');
const canal = ref(filters.canal ?? '');

let debounceTimeout: number | null = null;

const submitFilters = () => {
    const params: Record<string, string> = {};
    if (search.value.trim().length > 1) params.search = search.value.trim();
    if (status.value) params.status = status.value;
    if (canal.value) params.canal = canal.value;
    router.get('/orders', params, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, (value) => {
    const trimmed = value.trim();
    if (trimmed.length > 1 || trimmed === '') {
        if (debounceTimeout !== null) window.clearTimeout(debounceTimeout);
        debounceTimeout = window.setTimeout(submitFilters, 320);
    }
});

watch([status, canal], submitFilters);

const statusLabels: Record<string, string> = {
    pendiente_pago: 'Pendiente de pago',
    pago_parcial: 'Pago parcial',
    pago_confirmado: 'Pago confirmado',
    en_preparacion: 'En preparación',
    enviado: 'Enviado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
};

const statusBadgeClass = (status: string) => {
    if (status === 'entregado' || status === 'pago_confirmado') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
    if (status === 'cancelado') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300';
    if (status === 'enviado' || status === 'en_preparacion') return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300';
    return 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
};

const formatDate = (value: string) => new Date(value).toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>
