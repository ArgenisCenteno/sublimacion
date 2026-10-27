<template>
    <Head title="Checkout" />

    <FullScreenLayout>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <StorefrontHeader back-href="/cart" back-label="Volver al carrito" max-width-class="max-w-5xl" />

            <main class="mx-auto max-w-5xl px-6 py-10 lg:px-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">Finalizar compra</h1>

                <form class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]" @submit.prevent="submit">
                    <div class="space-y-8">
                        <!-- Datos del cliente -->
                        <section class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tus datos</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label">Nombre completo *</label>
                                    <input v-model="form.customer_name" :class="inputClass(form.errors.customer_name)" />
                                    <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-500">{{ form.errors.customer_name }}</p>
                                </div>
                                <div>
                                    <label class="label">Teléfono *</label>
                                    <input v-model="form.customer_phone" :class="inputClass(form.errors.customer_phone)" />
                                    <p v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-500">{{ form.errors.customer_phone }}</p>
                                </div>
                                <div>
                                    <label class="label">Email *</label>
                                    <input v-model="form.customer_email" type="email" :class="inputClass(form.errors.customer_email)" />
                                    <p v-if="form.errors.customer_email" class="mt-1 text-sm text-red-500">{{ form.errors.customer_email }}</p>
                                </div>
                                <div>
                                    <label class="label">Cédula/RIF</label>
                                    <input v-model="form.customer_cedula_rif" placeholder="V-12345678" :class="inputClass(form.errors.customer_cedula_rif)" />
                                    <p v-if="form.errors.customer_cedula_rif" class="mt-1 text-sm text-red-500">{{ form.errors.customer_cedula_rif }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Entrega -->
                        <section v-if="requiresDelivery" class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tipo de entrega</h2>
                            <div class="mt-4 space-y-3">
                                <label
                                    v-for="option in deliveryOptions"
                                    :key="option.value"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700"
                                    :class="form.delivery_type === option.value ? 'border-[#42AAAB] bg-[#42AAAB]/5' : ''"
                                >
                                    <input v-model="form.delivery_type" type="radio" :value="option.value" name="delivery_type" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ option.label }}</span>
                                </label>
                                <p v-if="form.errors.delivery_type" class="mt-1 text-sm text-red-500">{{ form.errors.delivery_type }}</p>
                            </div>

                            <div v-if="form.delivery_type === 'envio_local' || form.delivery_type === 'envio_nacional'" class="mt-4 space-y-4">
                                <div>
                                    <label class="label">Dirección de envío *</label>
                                    <textarea v-model="form.shipping_address" rows="3" :class="textareaClass(form.errors.shipping_address)"></textarea>
                                    <p v-if="form.errors.shipping_address" class="mt-1 text-sm text-red-500">{{ form.errors.shipping_address }}</p>
                                </div>
                            </div>

                            <div v-if="form.delivery_type === 'envio_nacional'" class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label">Ciudad destino *</label>
                                    <input v-model="form.shipping_city" :class="inputClass(form.errors.shipping_city)" />
                                    <p v-if="form.errors.shipping_city" class="mt-1 text-sm text-red-500">{{ form.errors.shipping_city }}</p>
                                </div>
                                <div>
                                    <label class="label">Empresa de encomienda *</label>
                                    <input v-model="form.shipping_carrier" placeholder="MRW, Zoom, Tealca..." :class="inputClass(form.errors.shipping_carrier)" />
                                    <p v-if="form.errors.shipping_carrier" class="mt-1 text-sm text-red-500">{{ form.errors.shipping_carrier }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Pago -->
                        <section class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Método de pago</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Solo seleccionas el método aquí — el registro del pago (comprobante, verificación) se hace en el siguiente paso.
                            </p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <label
                                    v-for="option in paymentOptions"
                                    :key="option.value"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700"
                                    :class="form.payment_method === option.value ? 'border-[#42AAAB] bg-[#42AAAB]/5' : ''"
                                >
                                    <input v-model="form.payment_method" type="radio" :value="option.value" name="payment_method" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ option.label }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-500">{{ form.errors.payment_method }}</p>

                            <div v-if="selectedPaymentAccount" class="mt-4 rounded-xl border border-[#42AAAB]/30 bg-[#42AAAB]/5 p-4 text-sm text-gray-700 dark:text-gray-200">
                                <p class="font-semibold text-gray-900 dark:text-white">Datos para tu pago</p>
                                <p v-if="hasRate" class="mt-1 text-gray-600 dark:text-gray-300">
                                    Monto a pagar: <strong class="text-base text-gray-900 dark:text-white">{{ formatBs(toBs(subtotal)) }}</strong>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">(tasa {{ formatBs(rate) }} por $1)</span>
                                </p>
                                <dl class="mt-2 space-y-1">
                                    <div v-if="selectedPaymentAccount.bank" class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Banco</dt><dd>{{ selectedPaymentAccount.bank }}</dd></div>
                                    <div v-if="selectedPaymentAccount.phone" class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Teléfono</dt><dd>{{ selectedPaymentAccount.phone }}</dd></div>
                                    <div v-if="selectedPaymentAccount.accountNumber" class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Nº de cuenta</dt><dd>{{ selectedPaymentAccount.accountNumber }}</dd></div>
                                    <div v-if="selectedPaymentAccount.cedulaRif" class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Cédula/RIF</dt><dd>{{ selectedPaymentAccount.cedulaRif }}</dd></div>
                                    <div v-if="selectedPaymentAccount.holder" class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Titular</dt><dd>{{ selectedPaymentAccount.holder }}</dd></div>
                                </dl>
                            </div>
                        </section>
                    </div>

                    <div class="h-fit rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen del pedido</h2>
                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <li v-for="item in items" :key="item.id" class="flex justify-between gap-2">
                                <span>{{ item.quantity }}x {{ item.product_name }}<span v-if="item.variant_name"> ({{ item.variant_name }})</span></span>
                                <span>${{ item.subtotal.toFixed(2) }}</span>
                            </li>
                        </ul>
                        <div class="mt-4 space-y-1 border-t border-gray-200 pt-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                            <div class="flex justify-between"><span>Subtotal</span><span>${{ subtotal.toFixed(2) }}</span></div>
                            <div class="flex justify-between"><span>Envío</span><span>$0.00</span></div>
                            <div class="mt-2 flex justify-between text-base font-semibold text-gray-900 dark:text-white">
                                <span>Total</span><span class="text-right">{{ formatUsd(subtotal) }}<span v-if="hasRate" class="block text-xs font-medium text-gray-500 dark:text-gray-400">≈ {{ formatBs(toBs(subtotal)) }}</span></span>
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-6 w-full rounded-lg bg-[#42AAAB] px-5 py-3 text-sm font-semibold text-white hover:bg-[#358C8D] disabled:opacity-60"
                        >
                            {{ form.processing ? 'Procesando...' : 'Confirmar pedido' }}
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';
import { useExchangeRate } from '@/composables/useExchangeRate';

interface CheckoutItem {
    id: number;
    quantity: number;
    unit_price: number;
    subtotal: number;
    product_name: string;
    variant_name: string | null;
}

const props = defineProps<{
    items: CheckoutItem[];
    subtotal: number;
    requiresDelivery: boolean;
    customer: { name: string; email: string; phone: string | null; cedula_rif: string | null };
}>();

const deliveryOptions = [
    { value: 'retiro_tienda', label: 'Retiro en tienda' },
    { value: 'envio_local', label: 'Envío local (misma ciudad)' },
    { value: 'envio_nacional', label: 'Envío nacional (encomienda)' },
];

const paymentOptions = [
    { value: 'pago_movil', label: 'Pago móvil' },
    { value: 'transferencia', label: 'Transferencia' },
    { value: 'efectivo', label: 'Efectivo en tienda' },
    { value: 'paypal', label: 'PayPal' },
];

const form = useForm({
    customer_name: props.customer.name,
    customer_phone: props.customer.phone ?? '',
    customer_email: props.customer.email,
    customer_cedula_rif: props.customer.cedula_rif ?? '',
    delivery_type: props.requiresDelivery ? 'retiro_tienda' : '',
    shipping_address: '',
    shipping_city: '',
    shipping_carrier: '',
    payment_method: 'pago_movil',
    notes: '',
});

const { rate, hasRate, toBs, formatUsd, formatBs } = useExchangeRate();

const page = usePage<{ store: { paymentAccounts: { pagoMovil: Record<string, string | null>; transferencia: Record<string, string | null> } } }>();

const selectedPaymentAccount = computed(() => {
    if (form.payment_method === 'pago_movil') return page.props.store.paymentAccounts.pagoMovil;
    if (form.payment_method === 'transferencia') return page.props.store.paymentAccounts.transferencia;
    return null;
});

function submit() {
    form.post('/checkout');
}

const inputBaseClass =
    'h-12 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-[#42AAAB] focus:ring-2 focus:ring-[#42AAAB]/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white';

function inputClass(error?: string) {
    return [inputBaseClass, error ? 'border-red-400' : ''];
}

function textareaClass(error?: string) {
    return [inputBaseClass.replace('h-12', ''), error ? 'border-red-400' : ''];
}
</script>
