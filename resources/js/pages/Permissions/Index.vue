<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Permisos'" />
        <div
            v-if="flash.success || flash.warning || flash.error"
            class="space-y-3"
        >
            <div
                v-if="flash.success"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.warning"
                class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
            >
                {{ flash.warning }}
            </div>
            <div
                v-if="flash.error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
            >
                {{ flash.error }}
            </div>
        </div>
        <div class="space-y-6">
            <!-- Header + Nuevo Permiso -->
            <section
                class="mt-5 overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
                <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.4),_transparent_30%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7"
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                    >
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Permisos
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Vista operativa de permisos
                            </h1>
                        </div>

                        <Link
                            href="/permissions/create"
                            v-if="can('permissions.create')"
                            class="animate-pop inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            Nuevo Permiso
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Tabla de permisos -->
            <div
                class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-xl font-semibold text-slate-900 dark:text-white"
                            >
                                Listado de permisos
                            </h2>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ permissions.total }} registros
                        </span>
                    </div>
                </div>

                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-200 dark:border-slate-800"
                            >
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Nombre
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="permission in displayedPermissions"
                                :key="permission.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(168,85,247,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ permission.name }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('permissions.view')"
                                            :href="`/permissions/${permission.id}`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            Ver</Link
                                        >
                                        <Link
                                            v-if="can('permissions.edit')"
                                            :href="`/permissions/${permission.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            Editar</Link
                                        >
                                        <button
                                            type="button"
                                            v-if="can('permissions.delete')"
                                            @click="
                                                deletePermission(permission)
                                            "
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
const page = usePage<{
    auth: {
        user: any;
        permissions: string[];
        roles: string[];
    };
    flash?: {
        success?: string | null;
        warning?: string | null;
        error?: string | null;
    };
}>();
const flash = computed(() => page.props.flash ?? {});
const permissions2 = computed<string[]>(() => {
    return (page.props.auth?.permissions as string[]) ?? [];
});

const can = (permission: string): boolean => {
    return permissions2.value.includes(permission);
};
interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: {
        data: Permission[];
        total: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
}

const { permissions } = defineProps<Props>();
const displayedPermissions = ref<Permission[]>([]);

watch(
    () => permissions.data,
    (newData) => {
        displayedPermissions.value = [...newData];
    },
    { immediate: true },
);

const deletePermission = (permission: Permission) => {
    if (confirm(`¿Deseas eliminar el permiso ${permission.name}?`)) {
        router.delete(`/permissions/${permission.id}`);
    }
};
</script>
