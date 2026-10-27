<template>
    <Head title="Catálogo">
        <meta name="description" content="Explora nuestro catálogo de productos y servicios de sublimación personalizados." />
    </Head>

    <FullScreenLayout>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <StorefrontHeader back-href="/" back-label="Volver al inicio" :show-sign-in="true" />

            <main class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">Catálogo</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Explora nuestros productos y servicios de sublimación.</p>

                <!-- Filtros -->
                <div class="mt-8 grid gap-4 rounded-2xl border border-gray-200 bg-gray-50 p-5 lg:grid-cols-[1fr_160px_200px_120px_120px] dark:border-gray-800 dark:bg-gray-800/50">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                        <input v-model="search" type="text" placeholder="Nombre o descripción..." :class="inputClass" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select v-model="type" :class="inputClass">
                            <option value="">Todos</option>
                            <option value="producto">Productos</option>
                            <option value="servicio">Servicios</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                        <select v-model="categoryId" :class="inputClass">
                            <option value="">Todas</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio mín.</label>
                        <input v-model="priceMin" type="number" min="0" step="0.01" :class="inputClass" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio máx.</label>
                        <input v-model="priceMax" type="number" min="0" step="0.01" :class="inputClass" />
                    </div>
                </div>

                <!-- Grid de productos -->
                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="`/catalogo/${product.slug}`"
                        class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="aspect-square w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img
                                v-if="product.images[0]"
                                :src="`/storage/${product.images[0].path}`"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">Sin imagen</div>
                        </div>
                        <div class="p-4">
                            <span
                                v-if="product.type === 'servicio'"
                                class="mb-1 inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                            >
                                Servicio
                            </span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ product.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ product.category?.name ?? '' }}</p>
                            <p class="mt-2 font-semibold text-[#358C8D] dark:text-[#42AAAB]">{{ priceLabel(product) }}</p>
                        </div>
                    </Link>
                </div>

                <div v-if="!products.data.length" class="mt-16 text-center text-gray-500 dark:text-gray-400">
                    No encontramos productos con esos filtros.
                </div>

                <!-- Paginación -->
                <div v-if="products.data.length" class="mt-10 flex items-center justify-center gap-3">
                    <Link
                        v-if="products.prev_page_url"
                        :href="products.prev_page_url"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300"
                    >
                        Anterior
                    </Link>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Página {{ products.current_page }} de {{ products.last_page }}</span>
                    <Link
                        v-if="products.next_page_url"
                        :href="products.next_page_url"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300"
                    >
                        Siguiente
                    </Link>
                </div>
            </main>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';

interface ProductCard {
    id: number;
    slug: string;
    name: string;
    type: 'producto' | 'servicio';
    price_usd: string | number;
    category: { id: number; name: string } | null;
    images: Array<{ id: number; path: string }>;
    variants: Array<{ id: number; price_override: string | number | null }>;
}

interface Props {
    products: {
        data: ProductCard[];
        current_page: number;
        last_page: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    categories: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        type?: string;
        category_id?: string;
        price_min?: string;
        price_max?: string;
    };
}

const { products, categories, filters } = defineProps<Props>();

const search = ref(filters.search ?? '');
const type = ref(filters.type ?? '');
const categoryId = ref(filters.category_id ?? '');
const priceMin = ref(filters.price_min ?? '');
const priceMax = ref(filters.price_max ?? '');

let debounceTimeout: number | null = null;

const submitFilters = () => {
    const params: Record<string, string> = {};
    if (search.value.trim().length > 1) params.search = search.value.trim();
    if (type.value) params.type = type.value;
    if (categoryId.value) params.category_id = categoryId.value;
    if (priceMin.value) params.price_min = priceMin.value;
    if (priceMax.value) params.price_max = priceMax.value;

    router.get('/catalogo', params, { preserveState: true, preserveScroll: true, replace: true });
};

watch([type, categoryId, priceMin, priceMax], submitFilters);

watch(search, (newSearch) => {
    if (newSearch.trim().length > 1 || newSearch.trim() === '') {
        if (debounceTimeout !== null) window.clearTimeout(debounceTimeout);
        debounceTimeout = window.setTimeout(submitFilters, 320);
    }
});

const priceLabel = (product: ProductCard): string => {
    const overrides = product.variants
        .map((v) => (v.price_override !== null ? Number(v.price_override) : null))
        .filter((v): v is number => v !== null);

    if (!overrides.length) {
        return `$${Number(product.price_usd).toFixed(2)}`;
    }

    const min = Math.min(Number(product.price_usd), ...overrides);
    const max = Math.max(Number(product.price_usd), ...overrides);

    return min === max ? `$${min.toFixed(2)}` : `$${min.toFixed(2)} - $${max.toFixed(2)}`;
};

const inputClass =
    'h-11 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-[#42AAAB] focus:ring-2 focus:ring-[#42AAAB]/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
</script>
