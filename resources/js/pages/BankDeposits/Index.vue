<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Depósitos bancarios'" />
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
            <div class="flex items-center justify-between">
                <div class="relative max-w-sm">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por banco o referencia..."
                        class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>
                <Link
                    v-if="can('bank_deposits.create')"
                    href="/bank-deposits/create"
                    class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    + Nuevo depósito
                </Link>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Banco</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Monto</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Referencia</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Fecha</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="deposit in deposits.data" :key="deposit.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ deposit.bank_name }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span v-if="deposit.amount_usd">${{ Number(deposit.amount_usd).toFixed(2) }}</span>
                                    <span v-if="deposit.amount_bs"> {{ deposit.amount_usd ? '/' : '' }} {{ Number(deposit.amount_bs).toFixed(2) }} Bs</span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ deposit.reference ?? '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ deposit.deposited_at }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="can('bank_deposits.edit')" :href="`/bank-deposits/${deposit.id}/edit`" class="rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">
                                            Editar
                                        </Link>
                                        <button v-if="can('bank_deposits.delete')" type="button" class="rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300" @click="depositToDelete = deposit">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!deposits.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No encontramos depósitos</h3>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ deposits.from }} a {{ deposits.to }} de {{ deposits.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link v-if="deposits.prev_page_url" :href="deposits.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Anterior</Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ deposits.current_page }} de {{ deposits.last_page }}</span>
                        <Link v-if="deposits.next_page_url" :href="deposits.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Siguiente</Link>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-if="depositToDelete"
            title="Eliminar depósito"
            :message="`¿Eliminar el depósito de '${depositToDelete.bank_name}'?`"
            @cancel="depositToDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

interface DepositRow {
    id: number;
    bank_name: string;
    amount_bs: string | number | null;
    amount_usd: string | number | null;
    reference: string | null;
    deposited_at: string;
}

interface Props {
    deposits: {
        data: DepositRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: { search?: string };
}

const { deposits, filters } = defineProps<Props>();

const page = usePage<{ auth: { permissions: string[] }; flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});
const permissions = computed<string[]>(() => page.props.auth?.permissions ?? []);
const can = (permission: string): boolean => permissions.value.includes(permission);

const search = ref(filters.search ?? '');
let debounceTimeout: number | null = null;

watch(search, (value) => {
    const trimmed = value.trim();
    if (trimmed.length > 1 || trimmed === '') {
        if (debounceTimeout !== null) window.clearTimeout(debounceTimeout);
        debounceTimeout = window.setTimeout(() => {
            router.get('/bank-deposits', trimmed ? { search: trimmed } : {}, { preserveState: true, preserveScroll: true, replace: true });
        }, 320);
    }
});

const depositToDelete = ref<DepositRow | null>(null);

const confirmDelete = () => {
    if (!depositToDelete.value) return;
    router.delete(`/bank-deposits/${depositToDelete.value.id}`, {
        onFinish: () => (depositToDelete.value = null),
    });
};
</script>
