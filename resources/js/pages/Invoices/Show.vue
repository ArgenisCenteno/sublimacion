<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Factura'" />

        <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            <!-- 🔹 Columna izquierda: Factura, vehículos y costos -->
            <div class="space-y-6">
                <!-- Información principal -->
                <div
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Información de la factura
                    </h2>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <InfoField
                            label="Número"
                            :value="invoice.invoice_number"
                        />
                        <InfoField
                            label="Proveedor"
                            :value="invoice.provider?.name || 'Sin proveedor'"
                        />
                        <div
                            class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                        >
                            <p class="text-sm text-slate-500">Subtotal</p>
                            <input
                                v-model="form.subtotal_amount"
                                type="number"
                                step="0.01"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                        >
                            <p class="text-sm text-slate-500">IVA</p>
                            <input
                                v-model="form.vat_amount"
                                type="number"
                                step="0.01"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                        <InfoField label="Total" :value="formatMoney(total)" />
                        <InfoField
                            label="Fecha emisión"
                            :value="formatDate(invoice.issued_at)"
                        />
                        <InfoField
                            label="Usuario"
                            :value="invoice.uploaded_by?.name || '—'"
                        />
                        <InfoField
                            label="Creado"
                            :value="formatDate(invoice.created_at)"
                        />
                        <InfoField
                            label="Actualizado"
                            :value="formatDate(invoice.updated_at)"
                        />
                        <button
                            @click="updateInvoice"
                            class="mt-4 rounded-xl bg-indigo-600 px-2 py-2 text-white"
                        >
                            Guardar cambios
                        </button>
                    </div>
                </div>
                <!-- Items de la factura -->
                <div
                    v-if="invoice.items?.length"
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Detalle de la factura
                    </h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 dark:border-slate-700"
                                >
                                    <th
                                        class="py-2 text-left text-sm font-semibold text-slate-500"
                                    >
                                        Cant.
                                    </th>
                                    <th
                                        class="py-2 text-left text-sm font-semibold text-slate-500"
                                    >
                                        Descripción
                                    </th>
                                    <th
                                        class="py-2 text-right text-sm font-semibold text-slate-500"
                                    >
                                        Precio Unitario
                                    </th>
                                    <th
                                        class="py-2 text-right text-sm font-semibold text-slate-500"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="item in invoice.items"
                                    :key="item.id"
                                    class="border-b border-slate-100 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50"
                                >
                                    <td
                                        class="py-3 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ item.quantity || '—' }}
                                    </td>

                                    <td
                                        class="py-3 text-sm font-medium text-slate-900 dark:text-white"
                                    >
                                        {{ item.description }}
                                    </td>

                                    <td
                                        class="py-3 text-right text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ formatMoney(item.unit_price) }}
                                    </td>

                                    <td
                                        class="py-3 text-right text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(item.total_price) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Vehículos asociados -->
                <div
                    v-if="invoice.invoice_vehicles?.length"
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Vehículos asociados
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="iv in invoice.invoice_vehicles"
                            :key="iv.id"
                            class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                        >
                            <p class="text-sm text-slate-500">Placa</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.plate || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">Marca / Modelo</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.brand || '—' }}
                                {{ iv.vehicle?.model || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">Versión</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.version || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">Color</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.color || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">VIN</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.vin || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">
                                Fecha matrícula
                            </p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    iv.vehicle?.registration_date
                                        ? new Date(
                                              iv.vehicle.registration_date,
                                          ).toLocaleDateString('es-ES')
                                        : '—'
                                }}
                            </p>

                            <p class="text-sm text-slate-500">Kilometraje</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.mileage_km || '—' }} km
                            </p>

                            <p class="text-sm text-slate-500">
                                Estado físico / comercial
                            </p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.vehicle?.physical_status || '—' }} /
                                {{ iv.vehicle?.commercial_status || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">Monto asignado</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.assigned_amount }} EUR
                            </p>

                            <p class="text-sm text-slate-500">Notas</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.notes || '—' }}
                            </p>

                            <p class="text-sm text-slate-500">Origen</p>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ iv.assigned_by.toUpperCase() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Costes asociados -->
                <div
                    v-if="invoice.invoice_vehicles?.length"
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Costes asociados
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="iv in invoice.invoice_vehicles"
                            :key="iv.id + '-costs'"
                        >
                            <div
                                v-if="iv.cost_entries?.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="ce in iv.cost_entries"
                                    :key="ce.id"
                                    class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                                >
                                    <p class="text-sm text-slate-500">Tipo</p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ ce.type }}
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        Concepto
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ ce.concept || '—' }}
                                    </p>

                                    <p class="text-sm text-slate-500">Monto</p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ ce.amount }} EUR
                                    </p>

                                    <p class="text-sm text-slate-500">Notas</p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ ce.notes || '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 Columna derecha: Estado, archivo y notas -->
            <div class="space-y-6">
                <!-- Estado -->
                <div
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Estado
                    </h2>
                    <div class="mt-5 space-y-4">
                        <StatusPill
                            title="Total factura"
                            :value="`${invoice.total_amount} EUR`"
                            class-name="bg-indigo-100 text-indigo-800 dark:bg-indigo-800/30 dark:text-indigo-400"
                        />
                        <StatusPill
                            title="Estatus"
                            :value="formatStatus(invoice.status)"
                            :class-name="getStatusClass(invoice.status)"
                        />
                    </div>
                </div>

                <!-- Archivo -->
                <div
                    v-if="invoice.pdf_url"
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Archivo
                    </h2>
                    <div class="mt-4">
                        <a
                            :href="'/' + invoice.pdf_url"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Ver / Descargar factura
                        </a>
                    </div>
                    <div class="mt-4">
                        <a
                            :href="'/' + invoice.albaran_url"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Ver / Descargar albarán
                        </a>
                    </div>
                </div>

                <!-- Notas -->
                <div
                    v-if="invoice.notes"
                    class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Notas
                    </h2>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">
                        {{ invoice.notes }}
                    </p>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
<script setup lang="ts">
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import { computed, defineComponent, h, reactive } from 'vue';

import axios from 'axios';

interface CostEntry {
    id: number;
    type: string;
    concept?: string | null;
    amount: number | string;
    notes?: string | null;
}

interface Vehicle {
    id: number;
    plate: string;
    vin?: string;
    brand?: string;
    model?: string;
    version?: string;
    color?: string;
    registration_date: string;
    mileage_km?: number;
    physical_status?: string;
    commercial_status?: string;
    taxable_base?: string;
    pvi?: string;
    price_financed?: string;
    price_cash?: string;
    num_keys?: number;
    has_ficha_tecnica?: boolean;
    has_permiso_circulacion?: boolean;
    insurance_active?: boolean;
    notes?: string | null;
}

interface InvoiceVehicle {
    id: number;
    vehicle?: Vehicle | null;
    assigned_amount: number | string;
    assigned_by: string;
    notes?: string | null;
    cost_entries?: CostEntry[];
}

interface Provider {
    name: string;
}

interface User {
    name: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    provider?: Provider | null;
    total_amount: number | string;
    issued_at: string;
    created_at: string;
    updated_at: string;
    uploaded_by?: User | null;
    pdf_url?: string;
    notes?: string | null;
    status: string;
    invoice_vehicles: InvoiceVehicle[];
    subtotal_amount: number | string;
    vat_amount: number | string;
    albaran_url?: string;
    items: {
        id: number;
        quantity?: number;
        description: string;
        unit_price?: number | string;
        total_price?: number | string;
    }[];
}

const props = defineProps<{
    invoice: Invoice;
}>();

// 🔹 Formateador de fechas
const formatDate = (date: string) => {
    if (!date) return '—';
    return new Date(date.replace(' ', 'T')).toLocaleDateString('es-ES');
};

// 🔹 Componentes reutilizables
const InfoField = defineComponent({
    props: {
        label: String,
        value: String,
    },
    setup(props) {
        return () =>
            h(
                'div',
                { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' },
                [
                    h('p', { class: 'text-sm text-slate-500' }, props.label),
                    h(
                        'p',
                        {
                            class: 'mt-1 font-semibold text-slate-900 dark:text-white',
                        },
                        props.value,
                    ),
                ],
            );
    },
});

const StatusPill = defineComponent({
    props: {
        title: String,
        value: String,
        className: String,
    },
    setup(props) {
        return () =>
            h(
                'div',
                { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' },
                [
                    h('p', { class: 'text-sm text-slate-500' }, props.title),
                    h(
                        'span',
                        {
                            class: `mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold ${props.className}`,
                        },
                        props.value,
                    ),
                ],
            );
    },
});

const formatStatus = (status: string) => {
    if (!status) return '—';
    return status.toUpperCase();
};
const form = reactive({
    subtotal_amount: Number(props.invoice.subtotal_amount) || 0,
    vat_amount: Number(props.invoice.vat_amount) || 0,
});
const total = computed(() => {
    return (Number(form.subtotal_amount) || 0) + (Number(form.vat_amount) || 0);
});
const getStatusClass = (status: string) => {
    switch (status) {
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400';

        case 'procesado':
            return 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400';

        case 'error':
            return 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400';

        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-400';
    }
};

const formatMoney = (value: number | string | undefined) => {
    if (!value) return '—';

    return Number(value).toLocaleString('es-ES', {
        style: 'currency',
        currency: 'EUR',
    });
};

const updateInvoice = async () => {
    try {
        const invoiceId = props.invoice?.id;
        if (!invoiceId) {
            alert('No se encontró el ID de la factura');
            return;
        }

        // CSRF token
        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const headers: any = {
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (token) headers['X-CSRF-TOKEN'] = token;

        // 🔹 POST con _method: 'PUT'
        await axios.post(
            `/invoices/${invoiceId}`,
            {
                _method: 'PUT',
                subtotal_amount: form.subtotal_amount,
                vat_amount: form.vat_amount,
                total_amount: total.value,
            },
            { headers },
        );

        alert('Factura actualizada correctamente');
    } catch (error: any) {
        console.error(error.response?.data || error);
        alert('Error al actualizar la factura');
    }
};
</script>
