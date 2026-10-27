<template>
    <Head :title="product.name">
        <meta name="description" :content="(product.description ?? `${product.name} — sublimación personalizada.`).slice(0, 160)" />
    </Head>

    <FullScreenLayout>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <StorefrontHeader back-href="/catalogo" back-label="Volver al catálogo" :show-sign-in="true" />

            <main class="mx-auto max-w-5xl px-6 py-10 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Galería -->
                    <div>
                        <div class="aspect-square w-full overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
                            <img
                                v-if="activeImage"
                                :src="`/storage/${activeImage.path}`"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">Sin imagen</div>
                        </div>
                        <div v-if="product.images.length > 1" class="mt-3 flex gap-3">
                            <button
                                v-for="image in product.images"
                                :key="image.id"
                                type="button"
                                class="h-16 w-16 overflow-hidden rounded-xl border-2"
                                :class="activeImage?.id === image.id ? 'border-[#42AAAB]' : 'border-transparent'"
                                @click="activeImage = image"
                            >
                                <img :src="`/storage/${image.path}`" class="h-full w-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div>
                        <span
                            v-if="product.type === 'servicio'"
                            class="mb-2 inline-flex rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                        >
                            Servicio
                        </span>
                        <p v-if="product.category" class="text-sm text-gray-500 dark:text-gray-400">{{ product.category.name }}</p>
                        <h1 class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ product.name }}</h1>
                        <p class="mt-4 text-2xl font-semibold text-[#358C8D] dark:text-[#42AAAB]">${{ Number(product.price_usd).toFixed(2) }}</p>

                        <p v-if="product.description" class="mt-6 leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ product.description }}
                        </p>

                        <div v-if="product.variants.length" class="mt-8">
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Elige una opción</h2>
                            <div class="mt-3 space-y-2">
                                <label
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                    class="flex cursor-pointer items-center justify-between rounded-xl border px-4 py-3 text-sm dark:border-gray-700"
                                    :class="selectedVariantId === variant.id ? 'border-[#42AAAB] bg-[#42AAAB]/5' : 'border-gray-200'"
                                >
                                    <span class="flex items-center gap-3">
                                        <input v-model="selectedVariantId" type="radio" :value="variant.id" name="variant" />
                                        <span class="text-gray-800 dark:text-gray-200">{{ variant.name }}</span>
                                    </span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        ${{ Number(variant.price_override ?? product.price_usd).toFixed(2) }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <p v-if="product.type === 'producto' && currentStock !== null" class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                            {{ currentStock > 0 ? `${currentStock} disponibles` : 'Sin stock disponible' }}
                        </p>

                        <form class="mt-6 flex items-center gap-3" @submit.prevent="addToCart">
                            <input
                                v-model.number="quantity"
                                type="number"
                                min="1"
                                :max="currentStock ?? undefined"
                                class="h-12 w-20 rounded-xl border border-gray-300 text-center text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing || outOfStock"
                                class="flex-1 rounded-lg bg-[#42AAAB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#358C8D] disabled:opacity-60"
                            >
                                {{ outOfStock ? 'Sin stock' : form.processing ? 'Agregando...' : 'Agregar al carrito' }}
                            </button>
                        </form>
                        <p v-if="form.errors.quantity" class="mt-2 text-sm text-red-500">{{ form.errors.quantity }}</p>
                    </div>
                </div>
            </main>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';

interface ProductImage {
    id: number;
    path: string;
}

interface ProductVariant {
    id: number;
    name: string;
    price_override: string | number | null;
    stock: number | null;
}

interface ProductData {
    id: number;
    slug: string;
    name: string;
    type: 'producto' | 'servicio';
    description: string | null;
    price_usd: string | number;
    stock: number | null;
    category: { id: number; name: string } | null;
    images: ProductImage[];
    variants: ProductVariant[];
}

const { product } = defineProps<{ product: ProductData }>();

const activeImage = ref<ProductImage | null>(product.images[0] ?? null);

const selectedVariantId = ref<number | null>(product.variants[0]?.id ?? null);
const quantity = ref(1);

const selectedVariant = computed(() => product.variants.find((v) => v.id === selectedVariantId.value) ?? null);

const currentStock = computed<number | null>(() => {
    if (product.variants.length) {
        return selectedVariant.value?.stock ?? null;
    }
    return product.stock;
});

const outOfStock = computed(() => currentStock.value !== null && currentStock.value <= 0);

const form = useForm({
    product_id: product.id,
    product_variant_id: selectedVariantId.value,
    quantity: 1,
});

function addToCart() {
    form.product_variant_id = selectedVariantId.value;
    form.quantity = quantity.value;
    form.post('/cart/items', { preserveScroll: true });
}
</script>
