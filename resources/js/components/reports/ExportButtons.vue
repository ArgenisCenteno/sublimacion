<template>
    <div class="flex items-center gap-2">
        <a :href="buildHref('excel')" class="rounded-xl border border-emerald-200 px-3 py-1.5 text-theme-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
            Excel
        </a>
        <a :href="buildHref('pdf')" class="rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10">
            PDF
        </a>
    </div>
</template>

<script setup lang="ts">
const props = defineProps<{ reportKey: string }>();

const buildHref = (format: 'excel' | 'pdf') => {
    const url = new URL(window.location.href);
    url.searchParams.set('report', props.reportKey);
    url.searchParams.set('export', format);
    return url.toString();
};
</script>
