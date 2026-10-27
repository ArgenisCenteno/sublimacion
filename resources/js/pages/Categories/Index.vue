<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Categorías'" />
        <div
            v-if="flash.success || flash.error"
            class="space-y-3"
        >
            <div
                v-if="flash.success"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
            >
                {{ flash.error }}
            </div>
        </div>

        <div class="mt-5 space-y-6">
            <section
                class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
                <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.4),_transparent_30%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7"
                >
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Categorías
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Categorías del catálogo
                            </h1>
                        </div>

                        <Link
                            v-if="can('categories.create')"
                            href="/categories/create"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nueva categoría
                        </Link>
                    </div>

                    <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar por nombre..."
                                class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 pl-11 text-sm text-white shadow-theme-xs placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                            />
                            <svg class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-white/45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Listado de categorías</h2>
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ categories.total }} registros
                        </span>
                    </div>
                </div>

                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Nombre</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Productos</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Acciones</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="category in categories.data"
                                :key="category.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ category.name }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ category.products_count }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('categories.edit')"
                                            :href="`/categories/${category.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="can('categories.delete')"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                            @click="categoryToDelete = category"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!categories.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No encontramos categorías</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ajusta la búsqueda o crea una nueva categoría.</p>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ categories.from }} a {{ categories.to }} de {{ categories.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="categories.prev_page_url"
                            :href="categories.prev_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Anterior
                        </Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Página {{ categories.current_page }} de {{ categories.last_page }}
                        </span>
                        <Link
                            v-if="categories.next_page_url"
                            :href="categories.next_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-if="categoryToDelete"
            title="Eliminar categoría"
            :message="`¿Eliminar la categoría '${categoryToDelete.name}'? Los productos asociados quedarán sin categoría.`"
            @cancel="categoryToDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

interface CategoryRow {
    id: number;
    name: string;
    products_count: number;
}

interface Props {
    categories: {
        data: CategoryRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: {
        search?: string;
    };
}

const { categories, filters } = defineProps<Props>();

const page = usePage<{
    auth: { permissions: string[] };
    flash?: { success?: string | null; error?: string | null };
}>();

const flash = computed(() => page.props.flash ?? {});
const permissions = computed<string[]>(() => page.props.auth?.permissions ?? []);
const can = (permission: string): boolean => permissions.value.includes(permission);

const search = ref(filters.search ?? '');
let searchDebounceTimeout: number | null = null;

const submitFilters = (nextSearch: string) => {
    const trimmedSearch = nextSearch.trim();
    const params: Record<string, string> = {};
    if (trimmedSearch.length > 1) params.search = trimmedSearch;
    router.get('/categories', params, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, (newSearch) => {
    const trimmedSearch = newSearch.trim();
    if (trimmedSearch.length > 1 || trimmedSearch === '') {
        if (searchDebounceTimeout !== null) window.clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = window.setTimeout(() => submitFilters(trimmedSearch), 320);
    }
});

const categoryToDelete = ref<CategoryRow | null>(null);

const confirmDelete = () => {
    if (!categoryToDelete.value) return;
    router.delete(`/categories/${categoryToDelete.value.id}`, {
        onFinish: () => (categoryToDelete.value = null),
    });
};
</script>
