<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Crear Rol'" />

        <section
            class="panel-float mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
        >
            <h1
                class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white"
            >
                Alta de rol
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- NOMBRE -->
                    <div class="field-reveal">
                        <label class="label">Nombre del rol *</label>
                        <input
                            v-model="form.name"
                            :class="inputClass(form.errors.name)"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- PERMISOS -->
                    <div class="field-reveal md:col-span-2">
                        <label class="label">Permisos</label>
                        <div class="mt-2 grid gap-2 md:grid-cols-2">
                            <label
                                v-for="permission in allPermissions"
                                :key="permission.id"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white/10 px-3 py-1 text-sm text-slate-900 hover:bg-indigo-50 dark:border-slate-700 dark:text-white/90 dark:hover:bg-slate-800"
                            >
                                <input
                                    type="checkbox"
                                    :value="permission.id"
                                    v-model="form.permissions"
                                    class="h-4 w-4 rounded border-slate-300 focus:ring-2 focus:ring-brand-500"
                                />
                                {{ permission.name }}
                            </label>
                        </div>
                        <p
                            v-if="form.errors.permissions"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.permissions }}
                        </p>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary"
                    >
                        {{ form.processing ? 'Guardando...' : 'Crear rol' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface RoleForm {
    name: string;
    permissions: number[];
}

const props = defineProps<{
    allPermissions: Array<{ id: number; name: string }>;
}>();

const allPermissions = ref(props.allPermissions);

const form = useForm<RoleForm>({
    name: '',
    permissions: [],
});

function submit() {
    form.post('/roles', {
        onSuccess: () => form.reset(),
    });
}

// ================================
// 🧠 CLASES INPUT
// ================================
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

<style scoped>
.field-reveal {
    animation: fieldReveal 520ms ease-out both;
}

@keyframes fieldReveal {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
