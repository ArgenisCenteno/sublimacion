<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Editar categoría'" />

        <section class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white">Editar categoría</h1>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="max-w-md">
                    <label class="label">Nombre *</label>
                    <input v-model="form.name" :class="inputClass(form.errors.name)" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
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
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

const props = defineProps<{
    category: { id: number; name: string };
}>();

const form = useForm({
    name: props.category.name,
});

function submit() {
    form.put(`/categories/${props.category.id}`);
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
</script>
