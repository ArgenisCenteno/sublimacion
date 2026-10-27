<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Productos'" />
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
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Productos y servicios
                            </h1>
                        </div>

                        <Link
                            v-if="can('products.create')"
                            href="/products/create"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nuevo producto
                        </Link>
                    </div>

                    <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                        <div class="grid gap-4 lg:grid-cols-[1fr_180px_220px_auto] lg:items-end">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-white/85">Buscar</label>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Buscar por nombre o SKU..."
                                    class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-white/85">Tipo</label>
                                <select
                                    v-model="type"
                                    class="h-12 w-full rounded-2xl border border-white/15 bg-slate-900/60 px-4 text-sm text-white focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                >
                                    <option value="">Todos</option>
                                    <option value="producto">Producto</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-white/85">Categoría</label>
                                <select
                                    v-model="categoryId"
                                    class="h-12 w-full rounded-2xl border border-white/15 bg-slate-900/60 px-4 text-sm text-white focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                >
                                    <option value="">Todas</option>
                                    <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15"
                                @click="clearFilters"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Listado</h2>
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ products.total }} registros
                        </span>
                    </div>
                </div>

                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Nombre</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Tipo</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Categoría</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Precio USD</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Stock</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Estado</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Acciones</p></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ product.name }}
                                    <span v-if="product.featured" class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        Destacado
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm capitalize sm:px-6">{{ product.type }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ product.category?.name ?? '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">${{ Number(product.price_usd).toFixed(2) }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">{{ product.stock ?? '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="product.active ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400'"
                                    >
                                        {{ product.active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('products.view')"
                                            :href="`/products/${product.id}`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            v-if="can('products.edit')"
                                            :href="`/products/${product.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="can('products.delete')"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                            @click="productToDelete = product"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!products.data.length" class="px-6 py-16 text-center">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No encontramos productos</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ajusta los filtros o crea un nuevo producto.</p>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ products.from }} a {{ products.to }} de {{ products.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="products.prev_page_url"
                            :href="products.prev_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Anterior
                        </Link>
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Página {{ products.current_page }} de {{ products.last_page }}
                        </span>
                        <Link
                            v-if="products.next_page_url"
                            :href="products.next_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-if="productToDelete"
            title="Eliminar producto"
            :message="`¿Eliminar '${productToDelete.name}'? Esta acción no se puede deshacer.`"
            @cancel="productToDelete = null"
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

interface ProductRow {
    id: number;
    name: string;
    type: 'producto' | 'servicio';
    price_usd: string | number;
    stock: number | null;
    active: boolean;
    featured: boolean;
    category: { id: number; name: string } | null;
}

interface Props {
    products: {
        data: ProductRow[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    categories: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        type?: string;
        category_id?: string;
    };
}

const { products, categories, filters } = defineProps<Props>();

const page = usePage<{
    auth: { permissions: string[] };
    flash?: { success?: string | null; error?: string | null };
}>();

const flash = computed(() => page.props.flash ?? {});
const permissions = computed<string[]>(() => page.props.auth?.permissions ?? []);
const can = (permission: string): boolean => permissions.value.includes(permission);

const search = ref(filters.search ?? '');
const type = ref(filters.type ?? '');
const categoryId = ref(filters.category_id ?? '');

let searchDebounceTimeout: number | null = null;

const hasActiveFilters = computed(() => !!(search.value || type.value || categoryId.value));

const submitFilters = () => {
    const params: Record<string, string> = {};
    const trimmedSearch = search.value.trim();
    if (trimmedSearch.length > 1) params.search = trimmedSearch;
    if (type.value) params.type = type.value;
    if (categoryId.value) params.category_id = categoryId.value;

    router.get('/products', params, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, (newSearch) => {
    const trimmedSearch = newSearch.trim();
    if (trimmedSearch.length > 1 || trimmedSearch === '') {
        if (searchDebounceTimeout !== null) window.clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = window.setTimeout(submitFilters, 320);
    }
});

watch([type, categoryId], () => submitFilters());

const clearFilters = () => {
    search.value = '';
    type.value = '';
    categoryId.value = '';
    submitFilters();
};

const productToDelete = ref<ProductRow | null>(null);

const confirmDelete = () => {
    if (!productToDelete.value) return;
    router.delete(`/products/${productToDelete.value.id}`, {
        onFinish: () => (productToDelete.value = null),
    });
};
</script>
