<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Pagos pendientes'" />
        <div
            v-if="flash.success || flash.error"
            class="mt-4 space-y-3"
        >
            <div
                v-if="flash.success"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
            >
                {{ flash.error }}
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Pagos pendientes de verificar</h1>
            </div>

            <div class="custom-scrollbar max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pedido</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Cliente</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Método</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Monto</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Comprobante</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Fecha</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Acciones</p></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                <Link :href="`/orders/${payment.order.id}`" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                    #{{ payment.order.id }}
                                </Link>
                            </td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">{{ payment.order.customer_name }}</td>
                            <td class="px-5 py-4 text-theme-sm capitalize sm:px-6">{{ methodLabels[payment.method] ?? payment.method }}</td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                ${{ Number(payment.amount_usd).toFixed(2) }}
                                <span v-if="payment.amount_bs" class="text-slate-400"> ({{ Number(payment.amount_bs).toFixed(2) }} Bs)</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                <a v-if="payment.receipt_path" :href="`/storage/${payment.receipt_path}`" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">
                                    Ver
                                </a>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">{{ payment.paid_at }}</td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="rounded-xl border border-emerald-200 px-3 py-1.5 text-theme-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10"
                                        @click="verify(payment)"
                                    >
                                        Verificar
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                        @click="paymentToReject = payment"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!payments.data.length" class="px-6 py-16 text-center">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No hay pagos pendientes</h3>
            </div>
        </div>

        <div v-if="paymentToReject" class="fixed inset-0 z-99999 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[8px]" @click="paymentToReject = null"></div>
            <div class="relative w-full max-w-sm rounded-[24px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Rechazar pago</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Indica el motivo del rechazo.</p>
                <textarea v-model="rejectionReason" rows="3" class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="paymentToReject = null">
                        Cancelar
                    </button>
                    <button type="button" :disabled="!rejectionReason.trim()" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-60" @click="confirmReject">
                        Rechazar
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface PaymentRow {
    id: number;
    method: string;
    amount_usd: string | number;
    amount_bs: string | number | null;
    receipt_path: string | null;
    paid_at: string;
    order: { id: number; customer_name: string };
}

defineProps<{
    payments: {
        data: PaymentRow[];
    };
}>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const methodLabels: Record<string, string> = {
    pago_movil: 'Pago móvil',
    transferencia: 'Transferencia',
    efectivo: 'Efectivo',
    paypal: 'PayPal',
};

const verify = (payment: PaymentRow) => {
    router.post(`/payments/${payment.id}/verify`, {}, { preserveScroll: true });
};

const paymentToReject = ref<PaymentRow | null>(null);
const rejectionReason = ref('');

const confirmReject = () => {
    if (!paymentToReject.value || !rejectionReason.value.trim()) return;
    router.post(`/payments/${paymentToReject.value.id}/reject`, { rejection_reason: rejectionReason.value }, {
        preserveScroll: true,
        onFinish: () => {
            paymentToReject.value = null;
            rejectionReason.value = '';
        },
    });
};
</script>
