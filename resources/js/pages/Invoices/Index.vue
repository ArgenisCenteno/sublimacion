<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Facturas'" />
        <div
            v-if="flash.success || flash.warning || flash.error"
            class="space-y-3"
        >
            <div
                v-if="flash.success"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.warning"
                class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
            >
                {{ flash.warning }}
            </div>
            <div
                v-if="flash.error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
            >
                {{ flash.error }}
            </div>
        </div>
        <div class="space-y-6">
            <!-- Header + Nueva Factura -->
            <section
                class="overflow-hidden mt-5 rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
                <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.4),_transparent_30%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7"
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                    >
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Facturación
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Vista operativa de facturas
                            </h1>
                        </div>

                        <Link
                            v-if="can('invoices.create')"
                            href="/invoices/create"
                            class="animate-pop inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            Nueva factura
                        </Link>
                    </div>

                    <!-- Summary Cards -->
                    <transition-group
                        tag="div"
                        class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4"
                        name="summary-card"
                        appear
                    >
                        <article
                            v-for="card in summaryCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md"
                        >
                            <div class="mb-3 flex items-center gap-3">
                                <div
                                    :class="[
                                        'flex h-10 w-10 items-center justify-center rounded-xl',
                                        card.iconClass,
                                    ]"
                                >
                                    <component
                                        :is="card.icon"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <p
                                    class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase"
                                >
                                    {{ card.title }}
                                </p>
                            </div>
                            <p class="text-2xl leading-none font-semibold">
                                {{ card.value }}
                            </p>
                            <p class="mt-2 text-sm text-white/65">
                                {{ card.caption }}
                            </p>
                        </article>
                    </transition-group>

                    <!-- Filtros -->
                    <transition appear name="filter-panel">
                        <div
                            class="mt-6 mb-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md"
                        >
                            <div
                                class="grid gap-4 xl:grid-cols-[1fr_320px_auto] xl:items-end"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Buscar</label
                                    >
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Buscar por nombre..."
                                            class="animate-fade-in h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 pl-11 text-sm text-white shadow-theme-xs placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                        />
                                        <svg
                                            class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-white/45"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <button
                                    v-if="activeFilterChips.length"
                                    type="button"
                                    @click="clearFilters"
                                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15"
                                >
                                    Limpiar filtros
                                </button>
                            </div>

                            <div
                                v-if="activeFilterChips.length"
                                class="mt-4 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="chip in activeFilterChips"
                                    :key="chip"
                                    class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-sm font-medium text-white/90"
                                >
                                    {{ chip }}
                                </span>
                            </div>
                        </div>
                    </transition>
                    <div
                        class="mb-6 flex flex-col rounded-xl bg-slate-50 p-4 shadow-sm md:flex-row md:items-end md:gap-4 dark:bg-slate-800"
                    >
                        <!-- Rango de fechas -->
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center"
                        >
                            <label
                                class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                >Desde:</label
                            >
                            <input
                                type="date"
                                v-model="startDate"
                                class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />

                            <label
                                class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                >Hasta:</label
                            >
                            <input
                                type="date"
                                v-model="endDate"
                                class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Botones -->
                        <div class="mt-4 flex gap-2 md:mt-0">
                            <a
                                :href="`/export-invoices?start_date=${startDate}&end_date=${endDate}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-700"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v4h16v-4M4 12V8h16v4"
                                    ></path>
                                </svg>
                                Exportar CSV
                            </a>

                            <a
                                :href="`/export-zip-invoices?start_date=${startDate}&end_date=${endDate}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-green-700"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 12H4M12 4v16"
                                    ></path>
                                </svg>
                                Exportar ZIP PDFs
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Tabla de Facturas -->
            <div
                class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-xl font-semibold text-slate-900 dark:text-white"
                            >
                                Listado de facturas
                            </h2>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ invoices.total }} registros
                        </span>
                    </div>
                </div>

                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-200 dark:border-slate-800"
                            >
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Número
                                    </p>
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Proveedor
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Emitida
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Usuario
                                </th>
                                <th
                                    class="px-5 py-3 text-left text-theme-xs font-medium text-slate-400 uppercase sm:px-6 dark:text-slate-500"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="invoice in displayedInvoices"
                                :key="invoice.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(168,85,247,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >{{ invoice.invoice_number }}</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >{{
                                            invoice.provider?.name ||
                                            'Sin asignar'
                                        }}</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >{{ invoice.total_amount }} EUR</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                    >
                                        {{
                                            new Date(
                                                invoice.issued_at,
                                            ).toLocaleDateString('es-ES')
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >{{
                                            invoice.uploaded_by?.name || ''
                                        }}</span
                                    >
                                </td>

                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/invoices/${invoice.id}`"
                                            v-if="can('invoices.edit')"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            Ver</Link
                                        >

                                        <button
                                            type="button"
                                            v-if="can('companies.delete')"
                                            @click="deleteInvoice(invoice)"
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch, defineComponent, h } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
const page = usePage<{
    auth: {
        user: any;
        permissions: string[];
        roles: string[];
    };
    flash?: {
        success?: string | null;
        warning?: string | null;
        error?: string | null;
    };
}>();
const flash = computed(() => page.props.flash ?? {});
const permissions = computed<string[]>(() => {
    return (page.props.auth?.permissions as string[]) ?? [];
});

const can = (permission: string): boolean => {
    return permissions.value.includes(permission);
};
interface Invoice {
    id: number;
    invoice_number: string;
    provider?: { name: string };
    total_amount: number;
    issued_at: string;
    uploaded_by: { name: string };
}

interface Props {
    invoices: {
        data: Invoice[];
        total: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: {
        search?: string;
    };
}

// 1. Definir Props primero
const props = defineProps<Props>();

// 2. Referencias reactivas
const search = ref(props.filters.search ?? '');
const displayedInvoices = ref<Invoice[]>([]);
let searchDebounceTimeout: number | null = null;

// 3. Computados
const activeFilterChips = computed(() => {
    const chips: string[] = [];
    if (search.value.trim()) chips.push(`Búsqueda: ${search.value.trim()}`);
    return chips;
});

// 4. Watchers
watch(
    () => props.invoices.data,
    (newData) => {
        displayedInvoices.value = [...newData];
    },
    { immediate: true },
);

// Watch para búsqueda (Debounce opcional)
watch(search, (value) => {
    if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);

    searchDebounceTimeout = window.setTimeout(() => {
        router.get(
            '/invoices',
            { search: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
});

// 5. Funciones
const deleteInvoice = (invoice: Invoice) => {
    if (confirm(`¿Deseas eliminar la factura ${invoice.invoice_number}?`)) {
        router.delete(`/invoices/${invoice.id}`);
    }
};

const clearFilters = () => {
    search.value = '';
};


// Helper para iconos (Render function)
function icon(path: string) {
    return defineComponent({
        setup(_, { attrs }) {
            return () =>
                h(
                    'svg',
                    {
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        'stroke-width': '1.8',
                        ...attrs,
                    },
                    [
                        h('path', {
                            d: path,
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        }),
                    ],
                );
        },
    });
}
const startDate = ref('');
const endDate = ref('');
const summaryCards = computed(() => [
    {
        title: 'Facturas en esta página',
        value: String(props.invoices.data.length),
        caption: 'Visibles según filtros',
        icon: icon('M16 14a4 4 0 10-8 0'),
        iconClass: 'bg-white/15 text-sky-300',
        delay: 0,
    },
    {
        title: 'Filtros activos',
        value: String(activeFilterChips.value.length),
        caption: activeFilterChips.value.length
            ? activeFilterChips.value.join(' - ')
            : 'Sin filtros',
        icon: icon('M3 4.5h18'),
        iconClass: 'bg-white/15 text-indigo-200',
        delay: 200,
    },
]);
</script>
