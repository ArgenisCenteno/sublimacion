<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Reportes" />

        <div class="mt-5 space-y-6">
            <ReportTabs current="/reportes/envios" />
            <DateRangeFilter :filters="filters" base-url="/reportes/envios" />

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

defineProps<{
    filters: { from: string; to: string; canal: string };
    tables: Record<string, ReportTableData>;
}>();
</script>
