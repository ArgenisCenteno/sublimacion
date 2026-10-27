<template>
    <Head title="Carrito" />

    <FullScreenLayout>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <StorefrontHeader back-href="/catalogo" back-label="Volver al catálogo" max-width-class="max-w-5xl" />

            <main class="mx-auto max-w-5xl px-6 py-10 lg:px-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">Tu carrito</h1>

                <div
                    v-if="flash.success || flash.error"
                    class="mt-4"
                >
                    <div
                        v-if="flash.success"
                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    >
                        {{ flash.success }}
                    </div>
                    <div
                        v-if="flash.error"
                        class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
                    >
                        {{ flash.error }}
                    </div>
                </div>

                <div v-if="!items.length" class="mt-16 text-center text-gray-500 dark:text-gray-400">
                    <p>Tu carrito está vacío.</p>
                    <Link href="/catalogo" class="mt-4 inline-block rounded-lg bg-[#42AAAB] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#358C8D]">
                        Ir al catálogo
                    </Link>
                </div>

                <div v-else class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
                    <div class="space-y-4">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="flex items-center gap-4 rounded-2xl border border-gray-200 p-4 dark:border-gray-800"
                        >
                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                                <img v-if="item.product.image" :src="`/storage/${item.product.image}`" class="h-full w-full object-cover" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ item.product.name }}</p>
                                <p v-if="item.variant" class="text-sm text-gray-500 dark:text-gray-400">{{ item.variant.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">${{ item.unit_price.toFixed(2) }} c/u</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    min="1"
                                    :max="item.available_stock ?? undefined"
                                    :value="item.quantity"
                                    class="h-10 w-16 rounded-lg border border-gray-300 text-center text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    @change="updateQuantity(item, $event)"
                                />
                            </div>
                            <p class="w-20 text-right font-semibold text-gray-900 dark:text-white">${{ item.subtotal.toFixed(2) }}</p>
                            <button
                                type="button"
                                class="text-gray-400 hover:text-rose-500"
                                aria-label="Quitar"
                                @click="itemToRemove = item"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="h-fit rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen</h2>
                        <div class="mt-4 flex justify-between text-sm text-gray-600 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span>${{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="mt-6">
                            <Link
                                href="/checkout"
                                class="block w-full rounded-lg bg-[#42AAAB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-[#358C8D]"
                            >
                                Continuar al pago
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <ConfirmDialog
            v-if="itemToRemove"
            title="Quitar producto"
            :message="`¿Quitar '${itemToRemove.product.name}' del carrito?`"
            @cancel="itemToRemove = null"
            @confirm="confirmRemove"
        />
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';

interface CartItemRow {
    id: number;
    quantity: number;
    unit_price: number;
    subtotal: number;
    available_stock: number | null;
    product: {
        id: number;
        name: string;
        slug: string;
        type: string;
        image: string | null;
    };
    variant: { id: number; name: string } | null;
}

defineProps<{
    items: CartItemRow[];
    subtotal: number;
}>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const updateQuantity = (item: CartItemRow, event: Event) => {
    const value = Number((event.target as HTMLInputElement).value);
    if (!value || value < 1) return;
    router.patch(`/cart/items/${item.id}`, { quantity: value }, { preserveScroll: true });
};

const itemToRemove = ref<CartItemRow | null>(null);

const confirmRemove = () => {
    if (!itemToRemove.value) return;
    router.delete(`/cart/items/${itemToRemove.value.id}`, {
        preserveScroll: true,
        onFinish: () => (itemToRemove.value = null),
    });
};
</script>
