<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Reportes" />

        <div class="mt-5 space-y-6">
            <ReportTabs current="/reportes/financieros" />
            <DateRangeFilter :filters="filters" base-url="/reportes/financieros" />

            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Para gestionar depósitos y pagos pendientes de conciliar, usa la vista operativa.
                </p>
                <Link href="/conciliacion" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/30 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                    Ir a conciliación
                </Link>
            </div>

            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-[220px_auto]">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Empleado (cierres de caja)</label>
                    <select v-model="employeeId" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" @change="submitEmployee">
                        <option value="">Todos</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                    </select>
                </div>
            </div>

            <ReportTable
                v-for="(table, key) in tables"
                :key="key"
                :report-key="key"
                :title="table.title"
                :headings="table.headings"
                :rows="table.rows"
            />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ReportTabs from '@/components/reports/ReportTabs.vue';
import DateRangeFilter from '@/components/reports/DateRangeFilter.vue';
import ReportTable from '@/components/reports/ReportTable.vue';

interface ReportTableData {
    title: string;
    headings: string[];
    rows: (string | number)[][];
}

const { filters, tables, employees } = defineProps<{
    filters: { from: string; to: string; canal: string };
    tables: Record<string, ReportTableData>;
    employees: { id: number; name: string }[];
}>();

const employeeId = ref('');

const submitEmployee = () => {
    router.get('/reportes/financieros', { ...filters, employee_id: employeeId.value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
};
</script>
