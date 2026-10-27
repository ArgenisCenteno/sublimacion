<template>
    <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ title }}</h2>
            <ExportButtons :report-key="reportKey" />
        </div>

        <div class="custom-scrollbar max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <th
                            v-for="(heading, index) in headings"
                            :key="index"
                            class="px-5 py-3 text-left text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 sm:px-6 dark:text-slate-500"
                        >
                            {{ heading }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <tr v-for="(row, rowIndex) in rows" :key="rowIndex" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <td v-for="(cell, cellIndex) in row" :key="cellIndex" class="px-5 py-4 text-theme-sm sm:px-6">
                            {{ cell }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!rows.length" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            Sin datos para este período.
        </div>
    </div>
</template>

<script setup lang="ts">
import ExportButtons from './ExportButtons.vue';

defineProps<{
    reportKey: string;
    title: string;
    headings: string[];
    rows: (string | number)[][];
}>();
</script>
