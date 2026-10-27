<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Conciliación bancaria'" />
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

        <div class="mt-5 grid gap-6 lg:grid-cols-2">
            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Depósitos sin pago vinculado</h2>
                <ul class="mt-4 space-y-3">
                    <li v-for="deposit in unmatchedDeposits" :key="deposit.id" class="rounded-xl border border-slate-200 p-4 text-sm dark:border-slate-700">
                        <p class="font-medium text-slate-900 dark:text-white">{{ deposit.bank_name }}</p>
                        <p class="text-slate-500 dark:text-slate-400">
                            <span v-if="deposit.amount_usd">${{ Number(deposit.amount_usd).toFixed(2) }}</span>
                            <span v-if="deposit.amount_bs"> {{ deposit.amount_usd ? '/' : '' }} {{ Number(deposit.amount_bs).toFixed(2) }} Bs</span>
                            — {{ deposit.deposited_at }}
                        </p>
                        <p v-if="deposit.reference" class="text-slate-400">Ref: {{ deposit.reference }}</p>
                    </li>
                </ul>
                <p v-if="!unmatchedDeposits.length" class="mt-4 text-sm text-slate-500 dark:text-slate-400">Todos los depósitos están vinculados.</p>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pagos sin depósito vinculado</h2>
                <ul class="mt-4 space-y-3">
                    <li v-for="payment in unmatchedPayments" :key="payment.id" class="rounded-xl border border-slate-200 p-4 text-sm dark:border-slate-700">
                        <p class="font-medium text-slate-900 dark:text-white">Pedido #{{ payment.order.id }} — ${{ Number(payment.amount_usd).toFixed(2) }}</p>
                        <p class="text-slate-500 dark:text-slate-400">{{ payment.method }} — {{ payment.paid_at }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <select v-model="selectedDeposit[payment.id]" class="h-10 flex-1 rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">Selecciona un depósito...</option>
                                <option v-for="deposit in unmatchedDeposits" :key="deposit.id" :value="deposit.id">
                                    {{ deposit.bank_name }} — {{ deposit.deposited_at }}
                                </option>
                            </select>
                            <button
                                type="button"
                                :disabled="!selectedDeposit[payment.id]"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                @click="link(payment)"
                            >
                                Vincular
                            </button>
                        </div>
                    </li>
                </ul>
                <p v-if="!unmatchedPayments.length" class="mt-4 text-sm text-slate-500 dark:text-slate-400">Todos los pagos están vinculados.</p>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface DepositRow {
    id: number;
    bank_name: string;
    amount_bs: string | number | null;
    amount_usd: string | number | null;
    reference: string | null;
    deposited_at: string;
}

interface PaymentRow {
    id: number;
    method: string;
    amount_usd: string | number;
    paid_at: string;
    order: { id: number };
}

defineProps<{
    unmatchedDeposits: DepositRow[];
    unmatchedPayments: PaymentRow[];
}>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const selectedDeposit = reactive<Record<number, string>>({});

const link = (payment: PaymentRow) => {
    const depositId = selectedDeposit[payment.id];
    if (!depositId) return;
    router.post(`/conciliacion/${payment.id}/link`, { bank_deposit_id: depositId }, { preserveScroll: true });
};
</script>
