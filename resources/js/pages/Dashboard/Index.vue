<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Dashboard" />

        <div class="mt-5 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Vendido este mes</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">${{ kpis.total_vendido_mes.toFixed(2) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pedidos este mes</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ kpis.pedidos_mes }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pedidos hoy</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ kpis.pedidos_hoy }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pendientes de pago</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ kpis.pendientes_pago }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Pedidos recientes</h2>
                    </div>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        <li v-for="order in recentOrders" :key="order.id" class="px-6 py-3">
                            <Link :href="`/orders/${order.id}`" class="flex items-center justify-between text-sm hover:text-indigo-600 dark:hover:text-indigo-400">
                                <span>
                                    <span class="font-medium text-slate-900 dark:text-white">#{{ order.id }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> — {{ order.customer_name }}</span>
                                </span>
                                <span class="text-slate-500 dark:text-slate-400">${{ Number(order.total_usd).toFixed(2) }}</span>
                            </Link>
                        </li>
                    </ul>
                    <p v-if="!recentOrders.length" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Sin pedidos todavía.</p>
                </div>

                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Pagos pendientes de verificar</h2>
                    </div>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        <li v-for="payment in pendingPayments" :key="payment.id" class="px-6 py-3">
                            <Link href="/payments" class="flex items-center justify-between text-sm hover:text-indigo-600 dark:hover:text-indigo-400">
                                <span>
                                    <span class="font-medium text-slate-900 dark:text-white">Pedido #{{ payment.order.id }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> — {{ payment.order.customer_name }}</span>
                                </span>
                                <span class="text-slate-500 dark:text-slate-400">${{ Number(payment.amount_usd).toFixed(2) }}</span>
                            </Link>
                        </li>
                    </ul>
                    <p v-if="!pendingPayments.length" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Sin pagos pendientes.</p>
                </div>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Accesos rápidos</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="link in visibleQuickLinks"
                        :key="link.href"
                        :href="link.href"
                        class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        {{ link.label }}
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface OrderRow {
    id: number;
    customer_name: string;
    total_usd: string | number;
}

interface PaymentRow {
    id: number;
    amount_usd: string | number;
    order: { id: number; customer_name: string };
}

defineProps<{
    kpis: {
        total_vendido_mes: number;
        pedidos_mes: number;
        pedidos_hoy: number;
        pendientes_pago: number;
    };
    recentOrders: OrderRow[];
    pendingPayments: PaymentRow[];
}>();

const page = usePage<{ auth: { permissions: string[] } }>();
const permissions = computed(() => page.props.auth?.permissions ?? []);

const quickLinks = [
    { label: 'Pedidos', href: '/orders', permission: 'orders.view' },
    { label: 'Pagos pendientes', href: '/payments', permission: 'payments.view' },
    { label: 'Punto de venta', href: '/pos', permission: 'pos.vender' },
    { label: 'Reportes', href: '/reportes', permission: 'reportes.ver' },
    { label: 'Productos', href: '/products', permission: 'products.view' },
    { label: 'Depósitos bancarios', href: '/bank-deposits', permission: 'bank_deposits.view' },
    { label: 'Usuarios', href: '/users', permission: 'users.view' },
    { label: 'Activity log', href: '/activity-log', permission: 'activity_log.view' },
];

const visibleQuickLinks = computed(() => quickLinks.filter((link) => permissions.value.includes(link.permission)));
</script>
