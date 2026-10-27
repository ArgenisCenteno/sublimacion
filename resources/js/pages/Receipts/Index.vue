<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Recibos'" />
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
            <div class="flex items-center gap-3">
                <select
                    v-model="status"
                    class="h-11 rounded-xl border border-slate-300 px-4 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    @change="submitFilter"
                >
                    <option value="">Todos</option>
                    <option value="vigente">Vigentes</option>
                    <option value="anulado">Anulados</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Número</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Pedido</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Cliente</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Monto</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Emitido</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Estado</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ receipt.formatted_number }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <Link :href="`/orders/${receipt.order.id}`" class="text-indigo-600 hover:underline dark:text-indigo-400">#{{ receipt.order.id }}</Link>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ receipt.customer_name }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">${{ Number(receipt.amount_usd).toFixed(2) }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDate(receipt.issued_at) }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="receipt.voided_at ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'"
                                    >
                                        {{ receipt.voided_at ? 'Anulado' : 'Vigente' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <a :href="`/receipts/${receipt.id}/pdf`" target="_blank" class="rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">
                                            Ver PDF
                                        </a>
                                        <button
                                            v-if="!receipt.voided_at"
                                            type="button"
                                            class="rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300"
                                            @click="receiptToVoid = receipt"
                                        >
                                            Anular
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!receipts.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No encontramos recibos</h3>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ receipts.from }} a {{ receipts.to }} de {{ receipts.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link v-if="receipts.prev_page_url" :href="receipts.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Anterior</Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ receipts.current_page }} de {{ receipts.last_page }}</span>
                        <Link v-if="receipts.next_page_url" :href="receipts.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Siguiente</Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="receiptToVoid" class="fixed inset-0 z-99999 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[8px]" @click="receiptToVoid = null"></div>
            <div class="relative w-full max-w-sm rounded-[24px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Anular recibo {{ receiptToVoid.formatted_number }}</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    El recibo queda marcado como anulado, pero sus datos originales no se modifican. Indica el motivo.
                </p>
                <textarea v-model="voidReason" rows="3" class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="receiptToVoid = null">
                        Cancelar
                    </button>
                    <button type="button" :disabled="!voidReason.trim()" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-60" @click="confirmVoid">
                        Anular
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

interface ReceiptRow {
    id: number;
    formatted_number: string;
    customer_name: string;
    amount_usd: string | number;
    issued_at: string;
    voided_at: string | null;
    order: { id: number };
}

interface Props {
    receipts: {
        data: ReceiptRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: { status?: string };
}

const { receipts, filters } = defineProps<Props>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const status = ref(filters.status ?? '');

const submitFilter = () => {
    router.get('/receipts', status.value ? { status: status.value } : {}, { preserveState: true, preserveScroll: true, replace: true });
};

const formatDate = (value: string) => new Date(value).toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });

const receiptToVoid = ref<ReceiptRow | null>(null);
const voidReason = ref('');

const confirmVoid = () => {
    if (!receiptToVoid.value || !voidReason.value.trim()) return;
    router.post(`/receipts/${receiptToVoid.value.id}/void`, { void_reason: voidReason.value }, {
        preserveScroll: true,
        onFinish: () => {
            receiptToVoid.value = null;
            voidReason.value = '';
        },
    });
};
</script>
