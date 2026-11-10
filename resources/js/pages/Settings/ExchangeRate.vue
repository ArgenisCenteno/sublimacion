<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Tasa de cambio'" />

        <section class="mt-6 max-w-md rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="mb-2 text-2xl font-semibold text-slate-900 dark:text-white">Tasa de cambio USD → Bs</h1>
            <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                {{ canEdit ? 'Esta tasa se usa para convertir los pagos registrados en bolívares.' : 'Solo el Admin puede modificar la tasa de cambio.' }}
            </p>

            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <label class="label">Tasa (Bs por 1 USD) *</label>
                    <MoneyInput
                        v-model="form.exchange_rate_bs"
                        currency="BS"
                        :decimals="2"
                        :disabled="!canEdit"
                        :class="inputClass(form.errors.exchange_rate_bs)"
                    />
                    <p v-if="form.errors.exchange_rate_bs" class="mt-1 text-sm text-red-500">{{ form.errors.exchange_rate_bs }}</p>
                </div>

                <div v-if="canEdit" class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        {{ form.processing ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import MoneyInput from '@/components/forms/MoneyInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

const props = defineProps<{
    setting: { exchange_rate_bs: string | number };
}>();

const page = usePage<{ auth: { permissions: string[] } }>();
const canEdit = computed(() => (page.props.auth?.permissions ?? []).includes('settings.edit'));

const form = useForm({
    exchange_rate_bs: props.setting.exchange_rate_bs,
});

function submit() {
    if (!canEdit.value) return;
    form.put('/settings/exchange-rate');
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 disabled:opacity-60';

function inputClass(error?: string) {
    return [
        inputBaseClass,
        error
            ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
    ];
}
</script>
