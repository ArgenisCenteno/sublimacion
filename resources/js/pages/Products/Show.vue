<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Ver producto'" />

        <section class="panel-float mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ product.name }}</h2>
                <p v-if="product.description" class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ product.description }}</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <InfoField label="Tipo" :value="product.type" />
                    <InfoField label="Categoría" :value="product.category?.name ?? 'Sin categoría'" />
                    <InfoField label="Precio (USD)" :value="`$${Number(product.price_usd).toFixed(2)}`" />
                    <InfoField label="SKU" :value="product.sku ?? '—'" />
                    <InfoField label="Stock" :value="product.stock !== null ? String(product.stock) : '—'" />
                    <InfoField label="Estado" :value="product.active ? 'Activo' : 'Inactivo'" />
                </div>

                <div v-if="product.images.length" class="mt-6 flex flex-wrap gap-3">
                    <img
                        v-for="image in product.images"
                        :key="image.id"
                        :src="`/storage/${image.path}`"
                        class="h-24 w-24 rounded-2xl border border-slate-200 object-cover dark:border-slate-700"
                    />
                </div>

                <div v-if="product.variants.length" class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Variantes</h3>
                    <ul class="mt-2 space-y-2">
                        <li v-for="variant in product.variants" :key="variant.id" class="rounded-xl bg-slate-50 px-4 py-2 text-sm dark:bg-slate-800/60">
                            {{ variant.name }}
                            <span v-if="variant.price_override" class="text-slate-500 dark:text-slate-400">— ${{ Number(variant.price_override).toFixed(2) }}</span>
                            <span v-if="variant.stock !== null" class="text-slate-500 dark:text-slate-400"> · stock: {{ variant.stock }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Acciones</h2>
                    <div class="mt-4 flex flex-col gap-3">
                        <Link :href="`/products/${product.id}/edit`" class="btn-primary text-center">Editar</Link>
                        <button type="button" class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10" @click="showDeleteConfirm = true">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <ConfirmDialog
            v-if="showDeleteConfirm"
            title="Eliminar producto"
            :message="`¿Eliminar '${product.name}'? Esta acción no se puede deshacer.`"
            @cancel="showDeleteConfirm = false"
            @confirm="deleteProduct"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, defineComponent, h } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

interface ProductData {
    id: number;
    name: string;
    description: string | null;
    type: string;
    price_usd: string | number;
    sku: string | null;
    stock: number | null;
    active: boolean;
    category: { id: number; name: string } | null;
    images: Array<{ id: number; path: string }>;
    variants: Array<{ id: number; name: string; price_override: string | number | null; stock: number | null }>;
}

const { product } = defineProps<{ product: ProductData }>();

const showDeleteConfirm = ref(false);

const deleteProduct = () => {
    router.delete(`/products/${product.id}`);
};

const InfoField = defineComponent({
    name: 'InfoField',
    props: {
        label: { type: String, required: true },
        value: { type: String, required: true },
    },
    setup(props) {
        return () =>
            h('div', { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' }, [
                h('p', { class: 'text-sm font-medium text-slate-500 dark:text-slate-400' }, props.label),
                h('p', { class: 'mt-1 text-base font-semibold text-slate-900 dark:text-white capitalize' }, props.value),
            ]);
    },
});
</script>
