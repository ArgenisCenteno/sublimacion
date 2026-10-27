<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Editar depósito'" />

        <section class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white">Editar depósito</h1>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="label">Banco *</label>
                        <input v-model="form.bank_name" :class="inputClass(form.errors.bank_name)" />
                        <p v-if="form.errors.bank_name" class="mt-1 text-sm text-red-500">{{ form.errors.bank_name }}</p>
                    </div>
                    <div>
                        <label class="label">Fecha *</label>
                        <input v-model="form.deposited_at" type="date" :class="inputClass(form.errors.deposited_at)" />
                    </div>
                    <div>
                        <label class="label">Monto (Bs)</label>
                        <MoneyInput v-model="form.amount_bs" currency="BS" :class="inputClass(form.errors.amount_bs)" />
                    </div>
                    <div>
                        <label class="label">Monto (USD)</label>
                        <MoneyInput v-model="form.amount_usd" currency="USD" :class="inputClass(form.errors.amount_usd)" />
                    </div>
                    <div>
                        <label class="label">Referencia</label>
                        <input v-model="form.reference" :class="inputClass(form.errors.reference)" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Notas</label>
                        <textarea v-model="form.notes" rows="3" :class="textareaClass()"></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import MoneyInput from '@/components/forms/MoneyInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

const props = defineProps<{
    deposit: {
        id: number;
        bank_name: string;
        amount_bs: string | number | null;
        amount_usd: string | number | null;
        reference: string | null;
        deposited_at: string;
        notes: string | null;
    };
}>();

const form = useForm({
    bank_name: props.deposit.bank_name,
    amount_bs: props.deposit.amount_bs ?? '',
    amount_usd: props.deposit.amount_usd ?? '',
    reference: props.deposit.reference ?? '',
    deposited_at: props.deposit.deposited_at,
    notes: props.deposit.notes ?? '',
});

function submit() {
    form.put(`/bank-deposits/${props.deposit.id}`);
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500';

function inputClass(error?: string) {
    return [
        inputBaseClass,
        error
            ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
    ];
}

function textareaClass() {
    return [inputBaseClass.replace('h-12', ''), 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800'];
}
</script>
