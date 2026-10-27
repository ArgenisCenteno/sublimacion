<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Cierres de caja'" />

        <div class="mt-5 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <div class="custom-scrollbar max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Empleado</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Apertura</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Cierre</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Diferencia</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Estado</th>
                            <th class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <tr v-for="shift in shifts.data" :key="shift.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-4 text-theme-sm sm:px-6">{{ shift.user.name }}</td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">{{ formatDateTime(shift.opened_at) }}</td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">{{ shift.closed_at ? formatDateTime(shift.closed_at) : '—' }}</td>
                            <td class="px-5 py-4 text-theme-sm sm:px-6">
                                <span v-if="shift.difference_usd !== null" :class="diffClass(shift.difference_usd)">
                                    ${{ Number(shift.difference_usd).toFixed(2) }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="shift.status === 'abierto' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'">
                                    {{ shift.status === 'abierto' ? 'Abierto' : 'Cerrado' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <Link :href="`/cash-shifts/${shift.id}`" class="rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">
                                    Ver
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!shifts.data.length" class="px-6 py-16 text-center">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No hay turnos registrados</h3>
            </div>

            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Mostrando {{ shifts.from }} a {{ shifts.to }} de {{ shifts.total }} resultados
                </p>
                <div class="flex items-center gap-2">
                    <Link v-if="shifts.prev_page_url" :href="shifts.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Anterior</Link>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ shifts.current_page }} de {{ shifts.last_page }}</span>
                    <Link v-if="shifts.next_page_url" :href="shifts.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Siguiente</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface ShiftRow {
    id: number;
    opened_at: string;
    closed_at: string | null;
    difference_usd: string | number | null;
    status: string;
    user: { id: number; name: string };
}

defineProps<{
    shifts: {
        data: ShiftRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
}>();

const formatDateTime = (value: string) => new Date(value).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const diffClass = (value: string | number) => {
    const n = Number(value);
    if (n === 0) return 'text-emerald-600 dark:text-emerald-400';
    return n < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400';
};
</script>
