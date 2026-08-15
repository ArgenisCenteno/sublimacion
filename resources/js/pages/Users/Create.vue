<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Crear Usuario'" />

        <section
            class="panel-float mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
        >
            <h1 class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white">
                Alta de usuario
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">

                    <!-- NOMBRE -->
                    <div class="field-reveal">
                        <label class="label">Nombre *</label>
                        <input
                            v-model="form.name"
                            :class="inputClass(form.errors.name)"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- EMAIL -->
                    <div class="field-reveal">
                        <label class="label">Email *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            :class="inputClass(form.errors.email)"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- PASSWORD -->
                    <div class="field-reveal">
                        <label class="label">Contraseña *</label>
                        <input
                            v-model="form.password"
                            type="password"
                            :class="inputClass(form.errors.password)"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div class="field-reveal">
                        <label class="label">Confirmar contraseña *</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :class="inputClass(form.errors.password_confirmation)"
                        />
                    </div>

                    <!-- EMPRESA -->
                    <div class="field-reveal">
                        <label class="label">Empresa</label>
                        <select
                            v-model="form.company_id"
                            :class="inputClass(form.errors.company_id)"
                        >
                            <option :value="null">— Sin empresa —</option>
                            <option
                                v-for="company in companies"
                                :key="company.id"
                                :value="company.id"
                            >
                                {{ company.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.company_id" class="mt-1 text-sm text-red-500">
                            {{ form.errors.company_id }}
                        </p>
                    </div>

                    <!-- ESTADO -->
                    <div class="field-reveal">
                        <label class="label">Estado</label>
                        <select
                            v-model="form.active"
                            :class="inputClass(form.errors.active)"
                        >
                            <option :value="true">Activo</option>
                            <option :value="false">Inactivo</option>
                        </select>
                    </div>

                    <!-- VER TODAS LAS EMPRESAS -->
                    <div class="field-reveal">
                        <label class="inline-flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.can_view_all_companies"
                            />
                            Puede ver todas las empresas
                        </label>
                    </div>

                    <!-- ROLES -->
                    <div class="field-reveal md:col-span-2">
                        <label class="label">Roles</label>
                        <div class="mt-2 grid gap-2 md:grid-cols-2">
                            <label
                                v-for="role in allRoles"
                                :key="role.id"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white/10 px-3 py-1 text-sm text-slate-900 hover:bg-indigo-50 dark:border-slate-700 dark:text-white/90 dark:hover:bg-slate-800"
                            >
                                <input
                                    type="checkbox"
                                    :value="role.id"
                                    v-model="form.roles"
                                    class="h-4 w-4 rounded border-slate-300 focus:ring-2 focus:ring-brand-500"
                                />
                                {{ role.name }}
                            </label>
                        </div>
                        <p v-if="form.errors.roles" class="mt-1 text-sm text-red-500">
                            {{ form.errors.roles }}
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
                        {{ form.processing ? 'Guardando...' : 'Crear usuario' }}
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

interface UserForm {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    company_id: number | null;
    roles: number[];
    can_view_all_companies: boolean;
    active: boolean | string;
}

const props = defineProps<{
    allRoles: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
}>();

const allRoles = ref(props.allRoles);
const companies = ref(props.companies);

const form = useForm<UserForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company_id: null,
    roles: [],
    can_view_all_companies: false,
    active: true,
});

function submit() {
    form.transform((data) => ({
        ...data,
        active: data.active === true || data.active === 'true',
    })).post('/users', {
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