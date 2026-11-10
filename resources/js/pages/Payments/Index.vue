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
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                {{ methodLabels[payment.method] ?? payment.method }}
                                <span v-if="payment.reference" class="block text-theme-xs text-slate-400">Ref. {{ payment.reference }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                ${{ Number(payment.amount_usd).toFixed(2) }}
                                <span v-if="payment.amount_bs" class="text-slate-400"> ({{ Number(payment.amount_bs).toFixed(2) }} Bs)</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                <button
                                    v-if="payment.receipt_path"
                                    type="button"
                                    class="group flex items-center gap-2 text-left"
                                    title="Ver comprobante"
                                    @click="receiptToShow = payment"
                                >
                                    <img
                                        :src="receiptUrl(payment)"
                                        alt="Comprobante"
                                        class="h-10 w-10 rounded-lg border border-slate-200 object-cover dark:border-slate-700"
                                        @error="($event.target as HTMLImageElement).style.display = 'none'"
                                    />
                                    <span class="text-indigo-600 group-hover:underline dark:text-indigo-400">Ver comprobante</span>
                                </button>
                                <span v-else class="text-slate-400">Sin comprobante</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-theme-sm sm:px-6">{{ formatDateTime(payment.created_at) }}</td>
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

        <!-- Vista previa del comprobante -->
        <div v-if="receiptToShow" class="fixed inset-0 z-99999 flex items-center justify-center p-4" @keydown.esc="receiptToShow = null">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-[6px]" @click="receiptToShow = null"></div>
            <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-[24px] border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-3 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Comprobante · Pedido #{{ receiptToShow.order.id }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ receiptToShow.order.customer_name }} · {{ methodLabels[receiptToShow.method] ?? receiptToShow.method }} ·
                            ${{ Number(receiptToShow.amount_usd).toFixed(2) }}<span v-if="receiptToShow.amount_bs"> ({{ Number(receiptToShow.amount_bs).toFixed(2) }} Bs)</span>
                            <span v-if="receiptToShow.reference"> · Ref. {{ receiptToShow.reference }}</span>
                        </p>
                    </div>
                    <button type="button" class="text-2xl leading-none text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar" @click="receiptToShow = null">&times;</button>
                </div>
                <div class="min-h-0 flex-1 overflow-auto rounded-xl bg-slate-100 dark:bg-slate-800">
                    <img :src="receiptUrl(receiptToShow)" alt="Comprobante de pago" class="mx-auto max-h-[65vh] w-auto object-contain" />
                </div>
                <div class="mt-4 flex flex-wrap justify-end gap-3">
                    <a :href="receiptUrl(receiptToShow)" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Abrir en otra pestaña
                    </a>
                    <button type="button" class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300" @click="paymentToReject = receiptToShow; receiptToShow = null">
                        Rechazar
                    </button>
                    <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="verify(receiptToShow); receiptToShow = null">
                        Verificar pago
                    </button>
                </div>
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
    reference: string | null;
    receipt_path: string | null;
    paid_at: string;
    created_at: string;
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

const receiptToShow = ref<PaymentRow | null>(null);
const receiptUrl = (payment: PaymentRow) => `/storage/${payment.receipt_path}`;

const dateTimeFormatter = new Intl.DateTimeFormat('es-VE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});
const formatDateTime = (value: string | null) => {
    if (!value) return '—';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : dateTimeFormatter.format(date);
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
