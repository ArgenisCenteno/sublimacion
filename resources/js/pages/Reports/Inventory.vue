<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Reportes" />

        <div class="mt-5 space-y-6">
            <ReportTabs current="/reportes/inventario" />

            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-[200px_auto]">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Umbral de stock bajo</label>
                    <input
                        v-model.number="thresholdInput"
                        type="number"
                        min="0"
                        class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        @change="submitThreshold"
                    />
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
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ReportTabs from '@/components/reports/ReportTabs.vue';
import ReportTable from '@/components/reports/ReportTable.vue';

interface ReportTableData {
    title: string;
    headings: string[];
    rows: (string | number)[][];
}

const { threshold, tables } = defineProps<{
    filters: { from: string; to: string; canal: string };
    threshold: number;
    tables: Record<string, ReportTableData>;
}>();

const thresholdInput = ref(threshold);

const submitThreshold = () => {
    router.get('/reportes/inventario', { threshold: thresholdInput.value }, { preserveState: true, preserveScroll: true, replace: true });
};
</script>
