<template>
    <Head :title="`Pedido #${order.id}`" />

    <FullScreenLayout>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <StorefrontHeader back-href="/catalogo" back-label="Seguir comprando" max-width-class="max-w-3xl" />

            <main class="mx-auto max-w-3xl px-6 py-10 lg:px-8">
                <div
                    class="rounded-2xl border p-6"
                    :class="balanceDue <= 0
                        ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10'
                        : 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10'"
                >
                    <h1 class="text-2xl font-semibold" :class="balanceDue <= 0 ? 'text-emerald-800 dark:text-emerald-200' : 'text-amber-800 dark:text-amber-200'">
                        Pedido #{{ order.id }}
                    </h1>
                    <p class="mt-1 text-sm" :class="balanceDue <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300'">
                        {{ statusLabel }} — saldo pendiente: <strong>{{ formatUsd(balanceDue) }}</strong><span v-if="balanceDue > 0 && hasRate"> ≈ <strong>{{ formatBs(toBs(balanceDue)) }}</strong></span>
                    </p>
                    <p v-if="balanceDue > 0 && pendingTotal > 0" class="mt-2 text-sm font-medium text-amber-800 dark:text-amber-200">
                        Tienes {{ formatUsd(pendingTotal) }} en pagos enviados que la tienda está verificando.
                    </p>
                </div>

                <section class="mt-8 rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Datos del pedido</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400">Cliente</dt><dd class="text-gray-900 dark:text-white">{{ order.customer_name }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Correo electrónico</dt><dd class="text-gray-900 dark:text-white">{{ order.customer_email }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Teléfono</dt><dd class="text-gray-900 dark:text-white">{{ order.customer_phone }}</dd></div>
                        <div v-if="order.customer_cedula_rif"><dt class="text-gray-500 dark:text-gray-400">Cédula/RIF</dt><dd class="text-gray-900 dark:text-white">{{ order.customer_cedula_rif }}</dd></div>
                        <div v-if="order.delivery_type"><dt class="text-gray-500 dark:text-gray-400">Entrega</dt><dd class="text-gray-900 dark:text-white">{{ deliveryLabel }}</dd></div>
                        <div v-if="order.shipping_address"><dt class="text-gray-500 dark:text-gray-400">Dirección</dt><dd class="text-gray-900 dark:text-white">{{ order.shipping_address }}</dd></div>
                        <div v-if="order.shipping_city"><dt class="text-gray-500 dark:text-gray-400">Ciudad</dt><dd class="text-gray-900 dark:text-white">{{ order.shipping_city }}</dd></div>
                        <div v-if="order.shipping_carrier"><dt class="text-gray-500 dark:text-gray-400">Empresa de encomienda</dt><dd class="text-gray-900 dark:text-white">{{ order.shipping_carrier }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Método de pago</dt><dd class="text-gray-900 dark:text-white">{{ paymentLabel }}</dd></div>
                    </dl>
                </section>

                <section class="mt-6 rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Productos</h2>
                    <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                        <li v-for="item in order.items" :key="item.id" class="flex items-center justify-between py-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ item.quantity }}x {{ item.product_name }}<span v-if="item.variant_name"> ({{ item.variant_name }})</span>
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">${{ Number(item.subtotal_usd).toFixed(2) }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 space-y-1 border-t border-gray-200 pt-4 text-sm dark:border-gray-700">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Subtotal</span><span>${{ Number(order.subtotal_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Envío</span><span>${{ Number(order.shipping_cost_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white"><span>Total</span><span>${{ Number(order.total_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Pagado</span><span>${{ amountPaid.toFixed(2) }}</span></div>
                        <div class="flex justify-between text-base font-semibold" :class="balanceDue > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'">
                            <span>Saldo</span><span class="text-right">{{ formatUsd(balanceDue) }}<span v-if="balanceDue > 0 && hasRate" class="block text-xs font-medium opacity-80">≈ {{ formatBs(toBs(balanceDue)) }}</span></span>
                        </div>
                    </div>
                </section>

                <section ref="paymentsSection" class="mt-6 scroll-mt-6 rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pagos</h2>

                    <!-- Confirmación / error tras enviar un pago -->
                    <div
                        v-if="flash.success"
                        role="status"
                        class="mt-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    >
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        <div>
                            <p class="font-semibold">¡Pago enviado!</p>
                            <p>{{ flash.success }} Te avisaremos cuando la tienda lo confirme.</p>
                        </div>
                    </div>
                    <div
                        v-if="flash.error || hasFormErrors"
                        role="alert"
                        class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
                    >
                        {{ flash.error || 'No se pudo registrar el pago. Revisa los campos marcados en el formulario.' }}
                    </div>

                    <ul v-if="order.payments.length" class="mt-4 space-y-2">
                        <li v-for="payment in order.payments" :key="payment.id" class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm dark:border-gray-700">
                            <div>
                                <p class="text-gray-800 dark:text-gray-200">{{ methodLabels[payment.method] ?? payment.method }} — {{ formatUsd(payment.amount_usd) }}<span v-if="payment.amount_bs" class="text-gray-500 dark:text-gray-400"> ({{ formatBs(payment.amount_bs) }} a {{ formatBs(payment.exchange_rate) }})</span></p>
                                <p v-if="payment.status === 'rechazado'" class="text-xs text-rose-500">Rechazado: {{ payment.rejection_reason }}</p>
                                <p v-else-if="payment.status === 'pendiente'" class="text-xs text-amber-600 dark:text-amber-400">En revisión por la tienda<span v-if="payment.reference"> · Ref. {{ payment.reference }}</span></p>
                                <a
                                    v-if="payment.receipt_path"
                                    :href="`/storage/${payment.receipt_path}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="mr-3 text-xs font-medium text-[#358C8D] hover:underline dark:text-[#42AAAB]"
                                >
                                    Ver comprobante enviado
                                </a>
                                <a
                                    v-if="payment.receipt"
                                    :href="`/receipts/${payment.receipt.id}/pdf`"
                                    target="_blank"
                                    class="text-xs font-medium text-[#358C8D] hover:underline dark:text-[#42AAAB]"
                                >
                                    Ver recibo ({{ payment.receipt.formatted_number }})
                                </a>
                            </div>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300': payment.status === 'pendiente',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300': payment.status === 'verificado',
                                    'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300': payment.status === 'rechazado',
                                }"
                            >
                                {{ statusLabels[payment.status] ?? payment.status }}
                            </span>
                        </li>
                    </ul>
                    <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">Aún no se ha registrado ningún pago.</p>

                    <!-- Lo enviado ya cubre el saldo: no invitar a pagar de nuevo salvo que el cliente lo pida -->
                    <div v-if="balanceDue > 0 && pendingCoversBalance && !showPaymentForm" class="mt-6 rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-white/5 dark:text-gray-300">
                        <p>Tu pago ya fue enviado y está en revisión. No necesitas hacer nada más por ahora.</p>
                        <button type="button" class="mt-2 font-semibold text-[#358C8D] hover:underline dark:text-[#42AAAB]" @click="showPaymentForm = true">
                            Registrar otro pago
                        </button>
                    </div>

                    <div v-else-if="balanceDue > 0" class="mt-6">
                        <!-- PayPal -->
                        <div v-if="order.payment_method === 'paypal'">
                            <p v-if="!paypalClientId" class="text-sm text-amber-600 dark:text-amber-400">
                                PayPal aún no está configurado (faltan las credenciales sandbox).
                            </p>
                            <div v-else id="paypal-button-container" class="mt-2"></div>
                        </div>

                        <!-- Pago manual -->
                        <form v-else class="mt-2 space-y-4" @submit.prevent="submitPayment">
                            <!-- Cuánto pagar en Bs y a dónde -->
                            <div class="rounded-2xl border border-brand-200 bg-brand-25 p-5 dark:border-brand-800 dark:bg-brand-500/5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">Monto a pagar</p>
                                <div class="mt-1 flex flex-wrap items-end gap-x-3 gap-y-1">
                                    <p v-if="paysInBs && hasRate" class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ formatBs(toBs(balanceDue)) }}</p>
                                    <p :class="paysInBs && hasRate ? 'pb-1 text-sm font-medium text-gray-500 dark:text-gray-400' : 'text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white'">
                                        {{ formatUsd(balanceDue) }}
                                    </p>
                                    <button
                                        v-if="paysInBs && hasRate"
                                        type="button"
                                        class="mb-1 rounded-lg border border-brand-200 bg-white px-2 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-50 dark:border-brand-800 dark:bg-transparent dark:text-brand-300"
                                        @click="copy(toBs(balanceDue).toFixed(2).replace('.', ','), 'monto')"
                                    >
                                        {{ copied === 'monto' ? '¡Copiado!' : 'Copiar monto' }}
                                    </button>
                                </div>
                                <p v-if="hasRate" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tasa del día: {{ formatBs(rate) }} por $1</p>
                                <p v-else class="mt-1 text-xs text-amber-600 dark:text-amber-400">La tienda aún no ha cargado la tasa del día; consulta el monto en Bs por WhatsApp.</p>

                                <dl v-if="paymentAccount" class="mt-4 grid gap-2 border-t border-brand-200/70 pt-4 text-sm dark:border-brand-800/60 sm:grid-cols-2">
                                    <div v-for="row in paymentAccount" :key="row.label" class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 dark:bg-white/5">
                                        <div class="min-w-0">
                                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ row.label }}</dt>
                                            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ row.value }}</dd>
                                        </div>
                                        <button type="button" class="shrink-0 text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400" @click="copy(row.value, row.label)">
                                            {{ copied === row.label ? '¡Listo!' : 'Copiar' }}
                                        </button>
                                    </div>
                                </dl>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Método</label>
                                    <select v-model="paymentForm.method" class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="pago_movil">Pago móvil</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="efectivo">Efectivo</option>
                                    </select>
                                    <p v-if="paymentForm.errors.method" class="mt-1 text-sm text-red-500">{{ paymentForm.errors.method }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda en que pagaste</label>
                                    <select v-model="paymentForm.currency" class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="BS">Bolívares (Bs)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto pagado *</label>
                                    <MoneyInput v-model="paymentForm.amount" :currency="paymentForm.currency" class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                    <p v-if="Number(paymentForm.amount) > 0 && hasRate" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Equivale a
                                        <strong class="text-gray-700 dark:text-gray-200">{{ paymentForm.currency === 'BS' ? formatUsd(toUsd(paymentForm.amount)) : formatBs(toBs(paymentForm.amount)) }}</strong>
                                    </p>
                                    <p v-if="paymentForm.errors.amount" class="mt-1 text-sm text-red-500">{{ paymentForm.errors.amount }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Referencia</label>
                                    <input v-model="paymentForm.reference" class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                    <p v-if="paymentForm.errors.reference" class="mt-1 text-sm text-red-500">{{ paymentForm.errors.reference }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Comprobante<span v-if="receiptRequired" class="text-red-500"> *</span>
                                </label>
                                <input
                                    :key="receiptInputKey"
                                    type="file"
                                    accept="image/jpg,image/jpeg,image/png,image/webp"
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                    @change="onReceiptChange"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Captura o foto del pago (JPG, PNG o WEBP, máx. 2 MB).</p>
                                <p v-if="receiptTooBig" class="mt-1 text-sm text-red-500">La imagen supera los 2 MB. Elige una más liviana.</p>
                                <p v-else-if="paymentForm.errors.receipt" class="mt-1 text-sm text-red-500">{{ paymentForm.errors.receipt }}</p>
                            </div>
                            <div v-if="paymentForm.progress" class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full bg-[#42AAAB] transition-all" :style="{ width: `${paymentForm.progress.percentage ?? 0}%` }"></div>
                            </div>
                            <button type="submit" :disabled="paymentForm.processing || receiptTooBig" class="btn-primary">
                                {{ paymentForm.processing ? 'Enviando pago...' : 'Registrar pago' }}
                            </button>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import MoneyInput from '@/components/forms/MoneyInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useExchangeRate } from '@/composables/useExchangeRate';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';

interface OrderItemRow {
    id: number;
    product_name: string;
    variant_name: string | null;
    quantity: number;
    subtotal_usd: string | number;
}

interface OrderPayment {
    id: number;
    method: string;
    amount_usd: string | number;
    amount_bs: string | number | null;
    exchange_rate: string | number | null;
    status: string;
    rejection_reason: string | null;
    reference: string | null;
    receipt_path: string | null;
    receipt: { id: number; formatted_number: string } | null;
}

interface OrderData {
    id: number;
    status: string;
    delivery_type: string | null;
    shipping_address: string | null;
    shipping_city: string | null;
    shipping_carrier: string | null;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    customer_cedula_rif: string | null;
    payment_method: string;
    subtotal_usd: string | number;
    shipping_cost_usd: string | number;
    total_usd: string | number;
    items: OrderItemRow[];
    payments: OrderPayment[];
}

const props = defineProps<{
    order: OrderData;
    amountPaid: number;
    balanceDue: number;
    paypalClientId: string | null;
}>();

const amountPaid = computed(() => Number(props.amountPaid));
const balanceDue = computed(() => Number(props.balanceDue));

const statusLabels: Record<string, string> = {
    pendiente_pago: 'Pendiente de pago',
    pago_parcial: 'Pago parcial',
    pago_confirmado: 'Pago confirmado',
    en_preparacion: 'En preparación',
    enviado: 'Enviado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
    pendiente: 'Pendiente de verificar',
    verificado: 'Verificado',
    rechazado: 'Rechazado',
};

const deliveryLabels: Record<string, string> = {
    retiro_tienda: 'Retiro en tienda',
    envio_local: 'Envío local',
    envio_nacional: 'Envío nacional',
};

const methodLabels: Record<string, string> = {
    pago_movil: 'Pago móvil',
    transferencia: 'Transferencia',
    efectivo: 'Efectivo en tienda',
    paypal: 'PayPal',
};

const statusLabel = computed(() => statusLabels[props.order.status] ?? props.order.status);
const deliveryLabel = computed(() => (props.order.delivery_type ? deliveryLabels[props.order.delivery_type] ?? props.order.delivery_type : ''));
const paymentLabel = computed(() => methodLabels[props.order.payment_method] ?? props.order.payment_method);

// ── Pago manual ──────────────────────────────────────────────────────────

const { rate, hasRate, toBs, toUsd, formatUsd, formatBs } = useExchangeRate();

// Pagos enviados por el cliente que aún no verifica la tienda (no descuentan el saldo hasta verificarse)
const pendingTotal = computed(() =>
    props.order.payments.filter((p) => p.status === 'pendiente').reduce((sum, p) => sum + Number(p.amount_usd), 0),
);
const pendingCoversBalance = computed(() => pendingTotal.value > 0 && pendingTotal.value + 0.005 >= balanceDue.value);
const showPaymentForm = ref(false);
// Lo que falta por enviar: saldo menos lo que ya está en revisión (si ya cubre, se sugiere el saldo completo)
const remainingToSend = computed(() => {
    const remaining = balanceDue.value - pendingTotal.value;
    return remaining > 0.005 ? remaining : balanceDue.value;
});

const initialMethod = (props.order.payment_method === 'paypal' ? 'pago_movil' : props.order.payment_method) as string;
const defaultCurrency = (method: string): 'USD' | 'BS' => (method === 'pago_movil' || method === 'transferencia' ? 'BS' : 'USD');
const balanceIn = (currency: 'USD' | 'BS') => {
    if (currency === 'USD') return Math.round(remainingToSend.value * 100) / 100;
    return hasRate.value ? toBs(remainingToSend.value) : null;
};

// Se precarga con el saldo pendiente en la moneda que corresponde al método
const paymentForm = useForm({
    amount: balanceIn(defaultCurrency(initialMethod)) as number | string | null,
    currency: defaultCurrency(initialMethod),
    method: initialMethod,
    reference: '',
    receipt: null as File | null,
});

const paysInBs = computed(() => paymentForm.currency === 'BS');

watch(
    () => paymentForm.method,
    (method) => {
        paymentForm.currency = defaultCurrency(method);
    },
);
watch(
    () => paymentForm.currency,
    (currency) => {
        paymentForm.amount = balanceIn(currency);
    },
);

const page = usePage<{
    store: { paymentAccounts: { pagoMovil: Record<string, string | null>; transferencia: Record<string, string | null> } };
    flash?: { success?: string | null; error?: string | null };
}>();

const flash = computed(() => page.props.flash ?? {});
const hasFormErrors = computed(() => Object.keys(paymentForm.errors).length > 0);
const receiptRequired = computed(() => paymentForm.method === 'pago_movil' || paymentForm.method === 'transferencia');
const receiptTooBig = computed(() => !!paymentForm.receipt && paymentForm.receipt.size > 2 * 1024 * 1024);
const receiptInputKey = ref(0);
const paymentsSection = ref<HTMLElement | null>(null);

const accountLabels: Record<string, string> = {
    bank: 'Banco',
    phone: 'Teléfono',
    accountNumber: 'Nº de cuenta',
    cedulaRif: 'Cédula/RIF',
    holder: 'Titular',
};

const paymentAccount = computed(() => {
    const accounts = page.props.store?.paymentAccounts;
    const account =
        paymentForm.method === 'pago_movil' ? accounts?.pagoMovil : paymentForm.method === 'transferencia' ? accounts?.transferencia : null;
    if (!account) return null;
    const rows = Object.entries(account)
        .filter(([, value]) => !!value)
        .map(([key, value]) => ({ label: accountLabels[key] ?? key, value: value as string }));
    return rows.length ? rows : null;
});

const copied = ref<string | null>(null);
async function copy(text: string, key: string) {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = key;
        window.setTimeout(() => (copied.value = null), 1500);
    } catch {
        // portapapeles no disponible (http sin TLS); el usuario puede copiar a mano
    }
}

function onReceiptChange(event: Event) {
    paymentForm.receipt = (event.target as HTMLInputElement).files?.[0] ?? null;
    paymentForm.clearErrors('receipt');
}

function scrollToPayments() {
    paymentsSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function submitPayment() {
    if (receiptTooBig.value) return;

    paymentForm.post(`/pedidos/${props.order.id}/payments`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            paymentForm.reset('reference', 'receipt');
            receiptInputKey.value++; // limpia el <input type="file">
            showPaymentForm.value = false;
            paymentForm.amount = balanceIn(paymentForm.currency);
            scrollToPayments();
        },
        onError: () => scrollToPayments(),
    });
}

// ── PayPal Smart Buttons ─────────────────────────────────────────────────

onMounted(() => {
    if (props.order.payment_method !== 'paypal' || !props.paypalClientId || balanceDue.value <= 0) {
        return;
    }

    const script = document.createElement('script');
    script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(props.paypalClientId)}&currency=USD`;
    script.onload = renderPayPalButtons;
    document.body.appendChild(script);
});

function xsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function renderPayPalButtons() {
    const paypal = (window as any).paypal;
    if (!paypal) return;

    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-XSRF-TOKEN': xsrfToken(),
    };

    paypal.Buttons({
        createOrder: () =>
            fetch(`/pedidos/${props.order.id}/paypal/create`, { method: 'POST', headers })
                .then((r) => r.json())
                .then((data: { id: string }) => data.id),
        onApprove: (data: { orderID: string }) =>
            fetch(`/pedidos/${props.order.id}/paypal/capture`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ paypal_order_id: data.orderID }),
            })
                .then((r) => r.json())
                .then(() => window.location.reload()),
    }).render('#paypal-button-container');
}
</script>
