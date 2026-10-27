<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Punto de venta'" />
        <div
            v-if="flash.success || flash.error"
            class="mt-4 space-y-3"
        >
            <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ flash.error }}
            </div>
        </div>

        <!-- Sin turno abierto -->
        <section v-if="!currentShift" class="mt-6 max-w-md rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="mb-2 text-2xl font-semibold text-slate-900 dark:text-white">Abrir turno de caja</h1>
            <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">Declara el fondo inicial con el que empiezas para poder vender.</p>

            <form class="space-y-4" @submit.prevent="submitOpenShift">
                <div>
                    <label class="label">Fondo inicial (USD)</label>
                    <input v-model="shiftForm.opening_cash_usd" type="number" min="0" step="0.01" :class="inputClass()" />
                </div>
                <div>
                    <label class="label">Fondo inicial (Bs)</label>
                    <input v-model="shiftForm.opening_cash_bs" type="number" min="0" step="0.01" :class="inputClass()" />
                </div>
                <button type="submit" :disabled="shiftForm.processing" class="btn-primary w-full">
                    {{ shiftForm.processing ? 'Abriendo...' : 'Abrir turno' }}
                </button>
            </form>
        </section>

        <!-- Turno abierto: pantalla de venta -->
        <div v-else class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <!-- Buscador de productos -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <input
                        v-model="productSearch"
                        type="text"
                        placeholder="Buscar producto por nombre o SKU..."
                        class="h-12 w-full rounded-2xl border border-slate-300 px-4 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <div class="mt-4 grid max-h-[420px] gap-2 overflow-y-auto sm:grid-cols-2">
                        <button
                            v-for="product in filteredProducts"
                            :key="product.id"
                            type="button"
                            class="rounded-xl border border-slate-200 p-3 text-left text-sm transition hover:border-indigo-300 hover:bg-indigo-50 dark:border-slate-700 dark:hover:bg-slate-800"
                            @click="selectProduct(product)"
                        >
                            <p class="font-medium text-slate-900 dark:text-white">{{ product.name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ product.category?.name ?? 'Sin categoría' }} · ${{ Number(product.price_usd).toFixed(2) }}
                                <span v-if="product.type === 'producto'"> · stock: {{ product.stock ?? '∞' }}</span>
                                <span v-else> · servicio</span>
                            </p>
                        </button>
                    </div>
                    <p v-if="!filteredProducts.length" class="mt-4 text-center text-sm text-slate-400">Sin resultados.</p>
                </section>

                <!-- Carrito -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Carrito</h2>
                    <div v-if="!cart.length" class="text-sm text-slate-400">Aún no has agregado productos.</div>
                    <ul v-else class="space-y-2">
                        <li v-for="(line, index) in cart" :key="index" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700">
                            <div>
                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ line.name }}<span v-if="line.variantName"> ({{ line.variantName }})</span></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">${{ line.unitPrice.toFixed(2) }} c/u</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input v-model.number="line.quantity" type="number" min="1" class="h-9 w-16 rounded-lg border border-slate-300 text-center text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                                <span class="w-16 text-right font-semibold text-slate-900 dark:text-white">${{ (line.unitPrice * line.quantity).toFixed(2) }}</span>
                                <button type="button" class="text-slate-400 hover:text-rose-500" @click="cart.splice(index, 1)">×</button>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Panel de venta: cliente + pago -->
            <div class="h-fit space-y-6">
                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Turno actual</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Fondo: ${{ Number(currentShift.opening_cash_usd).toFixed(2) }} / {{ Number(currentShift.opening_cash_bs).toFixed(2) }} Bs</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li v-for="(totals, method) in shiftTotals" :key="method" class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>{{ methodLabels[method] ?? method }}</span>
                            <span>{{ totals.count }} · ${{ Number(totals.total_usd).toFixed(2) }}</span>
                        </li>
                    </ul>
                    <button type="button" class="mt-4 w-full rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300" @click="showCloseShift = true">
                        Cerrar turno
                    </button>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Cliente</h2>

                    <div class="mb-3 flex gap-2">
                        <button type="button" class="flex-1 rounded-xl border px-3 py-2 text-sm font-semibold" :class="!quickSale ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'border-slate-200 text-slate-500 dark:border-slate-700'" @click="quickSale = false">
                            Cliente registrado
                        </button>
                        <button type="button" class="flex-1 rounded-xl border px-3 py-2 text-sm font-semibold" :class="quickSale ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'border-slate-200 text-slate-500 dark:border-slate-700'" @click="quickSale = true">
                            Venta rápida
                        </button>
                    </div>

                    <div v-if="!quickSale" class="space-y-2">
                        <input
                            v-model="customerSearch"
                            type="text"
                            placeholder="Buscar por nombre, teléfono o cédula..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <ul v-if="customerResults.length" class="max-h-40 space-y-1 overflow-y-auto">
                            <li v-for="customer in customerResults" :key="customer.id">
                                <button type="button" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="selectCustomer(customer)">
                                    {{ customer.name }} <span class="text-slate-400">— {{ customer.email }}</span>
                                </button>
                            </li>
                        </ul>
                        <p v-if="saleForm.customer_user_id" class="text-sm text-emerald-600 dark:text-emerald-400">
                            Seleccionado: {{ saleForm.customer_name }}
                        </p>
                    </div>

                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="label text-xs">Nombre *</label>
                            <input v-model="saleForm.customer_name" :class="inputClass(saleForm.errors.customer_name)" />
                        </div>
                        <div>
                            <label class="label text-xs">Teléfono *</label>
                            <input v-model="saleForm.customer_phone" :class="inputClass(saleForm.errors.customer_phone)" />
                        </div>
                        <div>
                            <label class="label text-xs">Email *</label>
                            <input v-model="saleForm.customer_email" type="email" :class="inputClass(saleForm.errors.customer_email)" />
                        </div>
                        <div>
                            <label class="label text-xs">Cédula/RIF</label>
                            <input v-model="saleForm.customer_cedula_rif" placeholder="V-12345678" :class="inputClass(saleForm.errors.customer_cedula_rif)" />
                        </div>
                    </div>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pago</h2>
                        <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700" @click="addPaymentLine">+ Agregar línea</button>
                    </div>

                    <div v-for="(line, index) in payments" :key="index" class="mb-3 grid gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <select v-model="line.method" class="h-10 rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="efectivo">Efectivo</option>
                            <option value="pago_movil">Pago móvil</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="paypal">PayPal</option>
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <select v-if="line.method !== 'paypal'" v-model="line.currency" class="h-10 rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="USD">USD</option>
                                <option value="BS">Bs</option>
                            </select>
                            <input v-model.number="line.amount" type="number" min="0.01" step="0.01" placeholder="Monto" class="h-10 rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        </div>
                        <button v-if="payments.length > 1" type="button" class="text-xs text-rose-500" @click="payments.splice(index, 1)">Quitar línea</button>
                    </div>

                    <div class="mt-4 space-y-1 border-t border-slate-200 pt-4 text-sm dark:border-slate-700">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><span>Total del carrito</span><span>${{ cartTotal.toFixed(2) }}</span></div>
                        <div class="flex justify-between font-semibold text-slate-900 dark:text-white"><span>Total ingresado</span><span>${{ paymentsTotal.toFixed(2) }}</span></div>
                    </div>

                    <button
                        type="button"
                        :disabled="!cart.length || saleForm.processing"
                        class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                        @click="submitSale"
                    >
                        {{ saleForm.processing ? 'Procesando...' : 'Completar venta' }}
                    </button>
                </section>
            </div>
        </div>

        <!-- Cerrar turno -->
        <div v-if="showCloseShift" class="fixed inset-0 z-99999 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[8px]" @click="showCloseShift = false"></div>
            <div class="relative w-full max-w-sm rounded-[24px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Cerrar turno</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cuenta el efectivo físico y declara el monto.</p>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="label text-xs">Efectivo contado (USD)</label>
                        <input v-model="closeForm.counted_cash_usd" type="number" min="0" step="0.01" :class="inputClass()" />
                    </div>
                    <div>
                        <label class="label text-xs">Efectivo contado (Bs)</label>
                        <input v-model="closeForm.counted_cash_bs" type="number" min="0" step="0.01" :class="inputClass()" />
                    </div>
                    <div>
                        <label class="label text-xs">Observaciones</label>
                        <textarea v-model="closeForm.notes" rows="2" :class="inputClass()"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" @click="showCloseShift = false">
                        Cancelar
                    </button>
                    <button type="button" :disabled="closeForm.processing" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-60" @click="submitCloseShift">
                        Cerrar turno
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface ProductVariant {
    id: number;
    name: string;
    price_override: string | number | null;
    stock: number | null;
}

interface ProductRow {
    id: number;
    name: string;
    sku: string | null;
    type: 'producto' | 'servicio';
    price_usd: string | number;
    stock: number | null;
    category: { id: number; name: string } | null;
    variants: ProductVariant[];
}

interface ShiftData {
    id: number;
    opening_cash_usd: string | number;
    opening_cash_bs: string | number;
}

interface CustomerResult {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    cedula_rif: string | null;
}

const props = defineProps<{
    currentShift: ShiftData | null;
    shiftTotals: Record<string, { total_usd: string | number; count: number }> | null;
    products: ProductRow[];
}>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const methodLabels: Record<string, string> = {
    pago_movil: 'Pago móvil',
    transferencia: 'Transferencia',
    efectivo: 'Efectivo',
    paypal: 'PayPal',
};

// ── Apertura de turno ─────────────────────────────────────────────────────

const shiftForm = useForm({
    opening_cash_usd: '0',
    opening_cash_bs: '0',
});

function submitOpenShift() {
    shiftForm.post('/pos/shifts', { preserveScroll: true });
}

// ── Cierre de turno ────────────────────────────────────────────────────────

const showCloseShift = ref(false);
const closeForm = useForm({
    counted_cash_usd: '0',
    counted_cash_bs: '0',
    notes: '',
});

function submitCloseShift() {
    if (!props.currentShift) return;
    closeForm.post(`/pos/shifts/${props.currentShift.id}/close`, {
        onFinish: () => (showCloseShift.value = false),
    });
}

// ── Buscador de productos ──────────────────────────────────────────────────

const productSearch = ref('');
const filteredProducts = computed(() => {
    const term = productSearch.value.trim().toLowerCase();
    if (!term) return props.products;
    return props.products.filter(
        (p) => p.name.toLowerCase().includes(term) || (p.sku ?? '').toLowerCase().includes(term),
    );
});

// ── Carrito en memoria ─────────────────────────────────────────────────────

interface CartLine {
    productId: number;
    variantId: number | null;
    name: string;
    variantName: string | null;
    unitPrice: number;
    quantity: number;
}

const cart = ref<CartLine[]>([]);

function selectProduct(product: ProductRow) {
    if (product.variants.length) {
        // Con variantes: agrega la primera por defecto, el cajero puede quitar/ajustar.
        const variant = product.variants[0];
        cart.value.push({
            productId: product.id,
            variantId: variant.id,
            name: product.name,
            variantName: variant.name,
            unitPrice: Number(variant.price_override ?? product.price_usd),
            quantity: 1,
        });
        return;
    }

    const existing = cart.value.find((l) => l.productId === product.id && l.variantId === null);
    if (existing) {
        existing.quantity += 1;
        return;
    }

    cart.value.push({
        productId: product.id,
        variantId: null,
        name: product.name,
        variantName: null,
        unitPrice: Number(product.price_usd),
        quantity: 1,
    });
}

const cartTotal = computed(() => cart.value.reduce((sum, line) => sum + line.unitPrice * line.quantity, 0));

// ── Cliente ────────────────────────────────────────────────────────────────

const quickSale = ref(true);
const customerSearch = ref('');
const customerResults = ref<CustomerResult[]>([]);
let customerDebounce: number | null = null;

watch(customerSearch, (value) => {
    if (customerDebounce !== null) window.clearTimeout(customerDebounce);
    if (value.trim().length < 2) {
        customerResults.value = [];
        return;
    }
    customerDebounce = window.setTimeout(async () => {
        const response = await fetch(`/pos/customers/search?q=${encodeURIComponent(value.trim())}`, {
            headers: { Accept: 'application/json' },
        });
        customerResults.value = await response.json();
    }, 300);
});

function selectCustomer(customer: CustomerResult) {
    saleForm.customer_user_id = customer.id;
    saleForm.customer_name = customer.name;
    saleForm.customer_email = customer.email;
    saleForm.customer_phone = customer.phone ?? '';
    saleForm.customer_cedula_rif = customer.cedula_rif ?? '';
    customerResults.value = [];
    customerSearch.value = '';
}

watch(quickSale, (value) => {
    if (value) {
        saleForm.customer_user_id = null;
    }
});

// ── Pago ───────────────────────────────────────────────────────────────────

interface PaymentLine {
    method: string;
    currency: 'USD' | 'BS';
    amount: number | string;
}

const payments = reactive<PaymentLine[]>([{ method: 'efectivo', currency: 'USD', amount: '' }]);

function addPaymentLine() {
    payments.push({ method: 'efectivo', currency: 'USD', amount: '' });
}

const paymentsTotal = computed(() =>
    payments.reduce((sum, line) => sum + (Number(line.amount) || 0), 0),
);

// ── Venta ──────────────────────────────────────────────────────────────────

const saleForm = useForm({
    customer_user_id: null as number | null,
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    customer_cedula_rif: '',
});

function submitSale() {
    saleForm
        .transform((data) => ({
            ...data,
            items: cart.value.map((line) => ({
                product_id: line.productId,
                product_variant_id: line.variantId,
                quantity: line.quantity,
            })),
            payments: payments.map((line) => ({
                method: line.method,
                currency: line.method === 'paypal' ? null : line.currency,
                amount: line.amount,
            })),
        }))
        .post('/pos/sale', {
            onSuccess: () => {
                cart.value = [];
                payments.splice(0, payments.length, { method: 'efectivo', currency: 'USD', amount: '' });
            },
        });
}

const inputBaseClass =
    'h-11 w-full rounded-xl border bg-transparent px-3 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 dark:text-white/90 dark:placeholder:text-slate-500';

function inputClass(error?: string) {
    return [
        inputBaseClass,
        error
            ? 'border-red-300 focus:border-red-400 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 dark:border-slate-700 dark:bg-slate-900',
    ];
}
</script>
