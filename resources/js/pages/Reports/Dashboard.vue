<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Reportes" />

        <div class="mt-5 space-y-6">
            <ReportTabs current="/reportes" />
            <DateRangeFilter :filters="filters" base-url="/reportes" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Total vendido</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">${{ kpis.total_vendido.toFixed(2) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pedidos</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ kpis.num_pedidos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Ticket promedio</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">${{ kpis.ticket_promedio.toFixed(2) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Pendientes de pago</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ kpis.pendientes_pago }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">En preparación/enviado</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ kpis.en_proceso }}</p>
                </div>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Ventas diarias: período actual vs. período anterior</h2>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div class="min-w-[600px]">
                        <VueApexCharts type="area" height="310" :options="chartOptions" :series="chartSeries" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ReportTabs from '@/components/reports/ReportTabs.vue';
import DateRangeFilter from '@/components/reports/DateRangeFilter.vue';

interface SeriesPoint {
    date: string;
    total: number;
}

const { filters, kpis, currentSeries, previousSeries } = defineProps<{
    filters: { from: string; to: string; canal: string };
    kpis: {
        total_vendido: number;
        num_pedidos: number;
        ticket_promedio: number;
        pendientes_pago: number;
        en_proceso: number;
    };
    currentSeries: SeriesPoint[];
    previousSeries: SeriesPoint[];
}>();

const chartSeries = computed(() => [
    { name: 'Período actual', data: currentSeries.map((p) => p.total) },
    { name: 'Período anterior', data: previousSeries.map((p) => p.total) },
]);

const chartOptions = computed<ApexOptions>(() => ({
    legend: { show: true, position: 'top', horizontalAlign: 'left' },
    colors: ['#465FFF', '#9CB9FF'],
    chart: { fontFamily: 'Outfit, sans-serif', type: 'area' as const, toolbar: { show: false } },
    fill: { gradient: { enabled: true, opacityFrom: 0.55, opacityTo: 0 } },
    stroke: { curve: 'straight', width: [2, 2] },
    markers: { size: 0 },
    dataLabels: { enabled: false },
    grid: { xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
    xaxis: {
        type: 'category',
        categories: currentSeries.map((p) => p.date),
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: { title: { style: { fontSize: '0px' } } },
    tooltip: { y: { formatter: (value: number) => `$${value.toFixed(2)}` } },
}));
</script>
