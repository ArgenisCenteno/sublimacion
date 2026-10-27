<template>
    <FullScreenLayout>
        <div class="mx-auto max-w-5xl space-y-6 p-4 md:p-6">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Mi cuenta</h1>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Saldo pendiente</p>
                    <p class="mt-2 text-2xl font-semibold" :class="balanceDue > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'">
                        ${{ balanceDue.toFixed(2) }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pedidos</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ orders.total }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Mis pedidos</h2>
                </div>
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Pedido</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Estado</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Total</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Fecha</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4 text-theme-sm sm:px-6">#{{ order.id }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusBadgeClass(order.status)">
                                        {{ statusLabels[order.status] ?? order.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">${{ Number(order.total_usd).toFixed(2) }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDate(order.created_at) }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <Link :href="`/pedidos/${order.id}`" class="text-indigo-600 hover:underline dark:text-indigo-400">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="!orders.data.length" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Todavía no tienes pedidos.</p>
                <div v-if="orders.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="text-sm font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-300">Anterior</Link>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Página {{ orders.current_page }} de {{ orders.last_page }}</span>
                    <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="text-sm font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-300">Siguiente</Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Mis recibos</h2>
                </div>
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Recibo</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Monto</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Fecha</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ receipt.formatted_number }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">${{ Number(receipt.amount_usd).toFixed(2) }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDate(receipt.issued_at) }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <a :href="`/receipts/${receipt.id}/pdf`" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">Descargar PDF</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="!receipts.data.length" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Todavía no tienes recibos.</p>
                <div v-if="receipts.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <Link v-if="receipts.prev_page_url" :href="receipts.prev_page_url" class="text-sm font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-300">Anterior</Link>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Página {{ receipts.current_page }} de {{ receipts.last_page }}</span>
                    <Link v-if="receipts.next_page_url" :href="receipts.next_page_url" class="text-sm font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-300">Siguiente</Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Notificaciones</h2>
                </div>
                <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                    <li v-for="notification in notifications.items" :key="notification.id" class="px-6 py-3 text-sm">
                        <p class="font-medium text-slate-900 dark:text-white">{{ notification.title }}</p>
                        <p class="text-slate-500 dark:text-slate-400">{{ notification.message }}</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ notification.time }}</p>
                    </li>
                </ul>
                <p v-if="!notifications.items.length" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin notificaciones por ahora.</p>
            </div>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';

interface OrderRow {
    id: number;
    status: string;
    total_usd: string | number;
    created_at: string;
}

interface ReceiptRow {
    id: number;
    formatted_number: string;
    amount_usd: string | number;
    issued_at: string;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url?: string;
    next_page_url?: string;
}

defineProps<{
    orders: Paginated<OrderRow>;
    balanceDue: number;
    receipts: Paginated<ReceiptRow>;
}>();

const page = usePage<{ notifications: { count: number; items: { id: string; title: string; message: string; time: string }[] } }>();
const notifications = computed(() => page.props.notifications ?? { count: 0, items: [] });

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
