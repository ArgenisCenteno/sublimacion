<template>
    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-[160px_160px_180px_auto]">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Desde</label>
            <input v-model="from" type="date" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Hasta</label>
            <input v-model="to" type="date" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Canal</label>
            <select v-model="canal" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Todos</option>
                <option value="online">Online</option>
                <option value="tienda_fisica">Tienda física</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="button" class="h-11 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="preset('today')">Hoy</button>
            <button type="button" class="h-11 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="preset('week')">Semana</button>
            <button type="button" class="h-11 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="preset('month')">Mes</button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    filters: { from: string; to: string; canal: string };
    baseUrl: string;
}>();

const from = ref(props.filters.from);
const to = ref(props.filters.to);
const canal = ref(props.filters.canal ?? '');

const submit = () => {
    const params: Record<string, string> = { from: from.value, to: to.value };
    if (canal.value) params.canal = canal.value;
    router.get(props.baseUrl, params, { preserveState: true, preserveScroll: true, replace: true });
};

watch([from, to, canal], submit);

const toDateStr = (d: Date) => d.toISOString().slice(0, 10);

const preset = (key: 'today' | 'week' | 'month') => {
    const now = new Date();

    if (key === 'today') {
        from.value = toDateStr(now);
        to.value = toDateStr(now);
        return;
    }

    if (key === 'week') {
        const isoDay = now.getDay() === 0 ? 7 : now.getDay();
        const monday = new Date(now);
        monday.setDate(now.getDate() - isoDay + 1);
        from.value = toDateStr(monday);
        to.value = toDateStr(now);
        return;
    }

    from.value = toDateStr(new Date(now.getFullYear(), now.getMonth(), 1));
    to.value = toDateStr(now);
};
</script>
