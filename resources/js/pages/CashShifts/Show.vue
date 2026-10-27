<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Turno #${shift.id}`" />
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

        <div class="mt-5 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Turno de {{ shift.user.name }}</h2>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="shift.status === 'abierto' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'">
                            {{ shift.status === 'abierto' ? 'Abierto' : 'Cerrado' }}
                        </span>
                    </div>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-slate-500 dark:text-slate-400">Apertura</dt><dd class="text-slate-900 dark:text-white">{{ formatDateTime(shift.opened_at) }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Cierre</dt><dd class="text-slate-900 dark:text-white">{{ shift.closed_at ? formatDateTime(shift.closed_at) : '—' }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Fondo inicial</dt><dd class="text-slate-900 dark:text-white">${{ Number(shift.opening_cash_usd).toFixed(2) }} / {{ Number(shift.opening_cash_bs).toFixed(2) }} Bs</dd></div>
                        <div v-if="shift.expected_cash_usd !== null"><dt class="text-slate-500 dark:text-slate-400">Esperado</dt><dd class="text-slate-900 dark:text-white">${{ Number(shift.expected_cash_usd).toFixed(2) }} / {{ Number(shift.expected_cash_bs).toFixed(2) }} Bs</dd></div>
                        <div v-if="shift.counted_cash_usd !== null"><dt class="text-slate-500 dark:text-slate-400">Contado</dt><dd class="text-slate-900 dark:text-white">${{ Number(shift.counted_cash_usd).toFixed(2) }} / {{ Number(shift.counted_cash_bs).toFixed(2) }} Bs</dd></div>
                        <div v-if="shift.difference_usd !== null">
                            <dt class="text-slate-500 dark:text-slate-400">Diferencia</dt>
                            <dd :class="diffClass(shift.difference_usd)">${{ Number(shift.difference_usd).toFixed(2) }} / {{ Number(shift.difference_bs).toFixed(2) }} Bs</dd>
                        </div>
                        <div v-if="shift.notes" class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Observaciones</dt><dd class="text-slate-900 dark:text-white">{{ shift.notes }}</dd></div>
                    </dl>

                    <div v-if="shift.reopened_at" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        Reabierto el {{ formatDateTime(shift.reopened_at) }} por {{ shift.reopened_by?.name }}. Motivo: {{ shift.reopen_reason }}
                    </div>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Ventas del turno</h2>
                    <ul class="mt-4 space-y-2">
                        <li v-for="payment in shift.payments" :key="payment.id" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm dark:border-slate-700">
                            <div>
                                <Link :href="`/orders/${payment.order.id}`" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Pedido #{{ payment.order.id }}</Link>
                                <span class="text-slate-500 dark:text-slate-400"> — {{ methodLabels[payment.method] ?? payment.method }}</span>
                            </div>
                            <span class="font-semibold text-slate-900 dark:text-white">${{ Number(payment.amount_usd).toFixed(2) }}</span>
                        </li>
                    </ul>
                    <p v-if="!shift.payments.length" class="mt-4 text-sm text-slate-500 dark:text-slate-400">Sin ventas registradas en este turno.</p>
                </section>
            </div>

            <div class="h-fit space-y-6">
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Totales por método</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li v-for="(totals, method) in totalsByMethod" :key="method" class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>{{ methodLabels[method] ?? method }}</span>
                            <span>{{ totals.count }} · ${{ Number(totals.total_usd).toFixed(2) }}</span>
                        </li>
                    </ul>
                </section>

                <section v-if="canReopen && shift.status === 'cerrado'" class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Reabrir turno</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Queda registrado quién reabrió y por qué.</p>
                    <button type="button" class="mt-4 w-full rounded-xl border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 dark:border-amber-500/40 dark:text-amber-300" @click="showReopenConfirm = true">
                        Reabrir turno
                    </button>
                </section>
            </div>
        </div>

        <div v-if="showReopenConfirm" class="fixed inset-0 z-99999 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[8px]" @click="showReopenConfirm = false"></div>
            <div class="relative w-full max-w-sm rounded-[24px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reabrir turno</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Indica el motivo de la reapertura.</p>
                <textarea v-model="reopenReason" rows="3" class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="showReopenConfirm = false">
                        Cancelar
                    </button>
                    <button type="button" :disabled="!reopenReason.trim()" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-60" @click="confirmReopen">
                        Reabrir
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

interface ShiftPayment {
    id: number;
    method: string;
    amount_usd: string | number;
    order: { id: number };
}

interface ShiftData {
    id: number;
    status: string;
    opened_at: string;
    closed_at: string | null;
    opening_cash_usd: string | number;
    opening_cash_bs: string | number;
    counted_cash_usd: string | number | null;
    counted_cash_bs: string | number | null;
    expected_cash_usd: string | number | null;
    expected_cash_bs: string | number | null;
    difference_usd: string | number | null;
    difference_bs: string | number | null;
    notes: string | null;
    reopened_at: string | null;
    reopen_reason: string | null;
    reopened_by: { id: number; name: string } | null;
    user: { id: number; name: string };
    payments: ShiftPayment[];
}

const props = defineProps<{
    shift: ShiftData;
    totalsByMethod: Record<string, { total_usd: string | number; count: number }>;
}>();

const page = usePage<{ auth: { permissions: string[] }; flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});
const canReopen = computed(() => (page.props.auth?.permissions ?? []).includes('cash_shifts.reopen'));

const methodLabels: Record<string, string> = {
    pago_movil: 'Pago móvil',
    transferencia: 'Transferencia',
    efectivo: 'Efectivo',
    paypal: 'PayPal',
};

const formatDateTime = (value: string) => new Date(value).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const diffClass = (value: string | number) => {
    const n = Number(value);
    if (n === 0) return 'text-emerald-600 dark:text-emerald-400';
    return n < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400';
};

const showReopenConfirm = ref(false);
const reopenReason = ref('');

const confirmReopen = () => {
    if (!reopenReason.value.trim()) return;
    router.post(`/cash-shifts/${props.shift.id}/reopen`, { reopen_reason: reopenReason.value }, {
        onFinish: () => {
            showReopenConfirm.value = false;
            reopenReason.value = '';
        },
    });
};
</script>
