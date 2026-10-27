<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Pedido #${order.id}`" />
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

        <div class="mt-5 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <!-- Datos del pedido -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pedido #{{ order.id }}</h2>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusBadgeClass(order.status)">
                            {{ statusLabels[order.status] ?? order.status }}
                        </span>
                    </div>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-slate-500 dark:text-slate-400">Cliente</dt><dd class="text-slate-900 dark:text-white">{{ order.customer_name }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="text-slate-900 dark:text-white">{{ order.customer_email }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Teléfono</dt><dd class="text-slate-900 dark:text-white">{{ order.customer_phone }}</dd></div>
                        <div v-if="order.customer_cedula_rif"><dt class="text-slate-500 dark:text-slate-400">Cédula/RIF</dt><dd class="text-slate-900 dark:text-white">{{ order.customer_cedula_rif }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Canal</dt><dd class="text-slate-900 dark:text-white capitalize">{{ order.canal === 'online' ? 'Online' : 'Tienda física' }}</dd></div>
                        <div v-if="order.delivery_type"><dt class="text-slate-500 dark:text-slate-400">Entrega</dt><dd class="text-slate-900 dark:text-white">{{ deliveryLabels[order.delivery_type] ?? order.delivery_type }}</dd></div>
                        <div v-if="order.shipping_address"><dt class="text-slate-500 dark:text-slate-400">Dirección</dt><dd class="text-slate-900 dark:text-white">{{ order.shipping_address }}</dd></div>
                        <div v-if="order.shipping_city"><dt class="text-slate-500 dark:text-slate-400">Ciudad</dt><dd class="text-slate-900 dark:text-white">{{ order.shipping_city }}</dd></div>
                        <div v-if="order.shipping_carrier"><dt class="text-slate-500 dark:text-slate-400">Empresa de encomienda</dt><dd class="text-slate-900 dark:text-white">{{ order.shipping_carrier }}</dd></div>
                        <div v-if="order.tracking_number"><dt class="text-slate-500 dark:text-slate-400">Nº de guía</dt><dd class="text-slate-900 dark:text-white">{{ order.tracking_number }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Método de pago</dt><dd class="text-slate-900 dark:text-white">{{ paymentMethodLabels[order.payment_method] ?? order.payment_method }}</dd></div>
                    </dl>
                </section>

                <!-- Productos -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Productos</h2>
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="item in order.items" :key="item.id" class="flex items-center justify-between py-3 text-sm">
                            <span class="text-slate-700 dark:text-slate-300">
                                {{ item.quantity }}x {{ item.product_name }}<span v-if="item.variant_name"> ({{ item.variant_name }})</span>
                            </span>
                            <span class="font-medium text-slate-900 dark:text-white">${{ Number(item.subtotal_usd).toFixed(2) }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 space-y-1 border-t border-slate-200 pt-4 text-sm dark:border-slate-800">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><span>Subtotal</span><span>${{ Number(order.subtotal_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><span>Envío</span><span>${{ Number(order.shipping_cost_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-base font-semibold text-slate-900 dark:text-white"><span>Total</span><span>${{ Number(order.total_usd).toFixed(2) }}</span></div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><span>Pagado</span><span>${{ amountPaid.toFixed(2) }}</span></div>
                        <div class="flex justify-between text-base font-semibold" :class="balanceDue > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'">
                            <span>Saldo</span><span>${{ balanceDue.toFixed(2) }}</span>
                        </div>
                    </div>
                </section>

                <!-- Pagos -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pagos</h2>
                    <ul v-if="order.payments.length" class="mt-4 space-y-2">
                        <li v-for="payment in order.payments" :key="payment.id" class="rounded-xl border border-slate-200 px-4 py-3 text-sm dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-800 dark:text-slate-200">{{ paymentMethodLabels[payment.method] ?? payment.method }} — ${{ Number(payment.amount_usd).toFixed(2) }}</span>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300': payment.status === 'pendiente',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300': payment.status === 'verificado',
                                        'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300': payment.status === 'rechazado',
                                    }"
                                >
                                    {{ paymentStatusLabels[payment.status] ?? payment.status }}
                                </span>
                            </div>
                            <p v-if="payment.bank_deposit" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Depósito vinculado: {{ payment.bank_deposit.bank_name }} ({{ payment.bank_deposit.deposited_at }})
                            </p>
                            <a
                                v-if="payment.receipt"
                                :href="`/receipts/${payment.receipt.id}/pdf`"
                                target="_blank"
                                class="mt-1 inline-block text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                            >
                                Ver recibo ({{ payment.receipt.formatted_number }}{{ payment.receipt.voided_at ? ' — anulado' : '' }})
                            </a>
                        </li>
                    </ul>
                    <p v-else class="mt-4 text-sm text-slate-500 dark:text-slate-400">Aún no se ha registrado ningún pago.</p>
                </section>

                <!-- Historial de estados -->
                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Historial de estados</h2>
                    <ul class="mt-4 space-y-2">
                        <li v-for="entry in order.status_history" :key="entry.id" class="rounded-xl bg-slate-50 px-4 py-2 text-sm dark:bg-slate-800/60">
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ statusLabels[entry.status] ?? entry.status }}</span>
                            <span class="text-slate-400"> — {{ formatDateTime(entry.created_at) }}</span>
                            <span v-if="entry.changed_by" class="text-slate-400"> · {{ entry.changed_by.name }}</span>
                            <p v-if="entry.note" class="text-xs text-slate-500 dark:text-slate-400">{{ entry.note }}</p>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Acciones -->
            <div class="h-fit rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Cambiar estado</h2>

                <div v-if="isFinal" class="mt-4 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    Este pedido ya está en un estado final y no se puede modificar.
                </div>

                <form v-else class="mt-4 space-y-4" @submit.prevent="submitStatus">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nuevo estado</label>
                        <select v-model="statusForm.status" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="en_preparacion">En preparación</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Entregado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div v-if="statusForm.status === 'enviado' && order.delivery_type === 'envio_nacional'">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nº de guía *</label>
                        <input v-model="statusForm.tracking_number" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        <p v-if="statusForm.errors.tracking_number" class="mt-1 text-sm text-red-500">{{ statusForm.errors.tracking_number }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nota (opcional)</label>
                        <input v-model="statusForm.note" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                    </div>

                    <button
                        v-if="statusForm.status !== 'cancelado'"
                        type="submit"
                        :disabled="statusForm.processing"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {{ statusForm.processing ? 'Guardando...' : 'Actualizar estado' }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
                        @click="showCancelConfirm = true"
                    >
                        Cancelar pedido
                    </button>
                </form>
            </div>
        </div>

        <ConfirmDialog
            v-if="showCancelConfirm"
            title="Cancelar pedido"
            message="Se restaurará el stock de los productos de este pedido. Esta acción no se puede deshacer."
            @cancel="showCancelConfirm = false"
            @confirm="confirmCancel"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

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
    status: string;
    bank_deposit: { id: number; bank_name: string; deposited_at: string } | null;
    receipt: { id: number; formatted_number: string; voided_at: string | null } | null;
}

interface StatusHistoryEntry {
    id: number;
    status: string;
    note: string | null;
    created_at: string;
    changed_by: { id: number; name: string } | null;
}

interface OrderData {
    id: number;
    status: string;
    canal: string;
    delivery_type: string | null;
    shipping_address: string | null;
    shipping_city: string | null;
    shipping_carrier: string | null;
    tracking_number: string | null;
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
    status_history: StatusHistoryEntry[];
}

const props = defineProps<{
    order: OrderData;
    amountPaid: number;
    balanceDue: number;
}>();

const page = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const amountPaid = computed(() => Number(props.amountPaid));
const balanceDue = computed(() => Number(props.balanceDue));

const isFinal = computed(() => ['entregado', 'cancelado'].includes(props.order.status));

const statusLabels: Record<string, string> = {
    pendiente_pago: 'Pendiente de pago',
    pago_parcial: 'Pago parcial',
    pago_confirmado: 'Pago confirmado',
    en_preparacion: 'En preparación',
    enviado: 'Enviado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
};

const paymentStatusLabels: Record<string, string> = {
    pendiente: 'Pendiente de verificar',
    verificado: 'Verificado',
    rechazado: 'Rechazado',
};

const deliveryLabels: Record<string, string> = {
    retiro_tienda: 'Retiro en tienda',
    envio_local: 'Envío local',
    envio_nacional: 'Envío nacional',
};

const paymentMethodLabels: Record<string, string> = {
    pago_movil: 'Pago móvil',
    transferencia: 'Transferencia',
    efectivo: 'Efectivo en tienda',
    paypal: 'PayPal',
};

const statusBadgeClass = (status: string) => {
    if (status === 'entregado' || status === 'pago_confirmado') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
    if (status === 'cancelado') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300';
    if (status === 'enviado' || status === 'en_preparacion') return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300';
    return 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
};

const formatDateTime = (value: string) => new Date(value).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const statusForm = useForm({
    status: 'en_preparacion',
    tracking_number: '',
    note: '',
});

function submitStatus() {
    statusForm.post(`/orders/${props.order.id}/status`, { preserveScroll: true });
}

const showCancelConfirm = ref(false);

function confirmCancel() {
    statusForm.status = 'cancelado';
    statusForm.post(`/orders/${props.order.id}/status`, {
        preserveScroll: true,
        onFinish: () => (showCancelConfirm.value = false),
    });
}
</script>
