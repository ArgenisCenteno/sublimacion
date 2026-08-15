<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Proveedores'" />
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
            <!-- Header + resumen -->
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
                                Proveedores
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Vista operativa de proveedores
                            </h1>
                        </div>

                        <Link
                            v-if="can('providers.create')"
                            href="/providers/create"
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
                            Nuevo proveedor
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
                            class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md"
                        >
                            <div
                                class="grid gap-4 xl:grid-cols-[1fr_320px_auto] xl:items-end"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                    >
                                        Buscar
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Buscar por nombre, serie..."
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

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                    >
                                        Estatus
                                    </label>
                                    <select
                                        v-model="selectedType"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos los tipos
                                        </option>
                                        <option
                                            v-for="option in clientTypeOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            class="text-slate-900"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <button
                                    v-if="selectedType"
                                    type="button"
                                    @click="clearTypeFilter"
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
                </div>
            </section>

            <!-- Tabla de Proveedores -->
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
                                Listado de Proveedores
                            </h2>
                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ tableHeadline }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ providers.total }} registros
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
                                        Nombre
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Empresa
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Tipo
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Estatus
                                    </p>
                                </th>

                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Acciones
                                    </p>
                                </th>
                            </tr>
                        </thead>

                        <transition-group
                            tag="tbody"
                            name="provider"
                            appear
                            enter-active-class="transition-opacity transition-transform duration-500 ease-in-out"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-opacity transition-transform duration-400 ease-in-out"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="provider in displayedCompanies"
                                :key="provider.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(168,85,247,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <!-- Nombre del proveedor -->
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ provider.name || '-' }}
                                </td>

                                <!-- Empresa asociada -->
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ provider.company?.name || '-' }}
                                </td>

                                <!-- Tipo -->
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ provider.type || '-' }}
                                </td>

                                <!-- Activo / Inactivo -->
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{
                                        provider.active ? 'Activo' : 'Inactivo'
                                    }}
                                </td>

                                <!-- Acciones -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('providers.view')"
                                            :href="`/providers/${provider.id}`"
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
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            Ver
                                        </Link>
                                        <Link
                                            v-if="can('providers.edit')"
                                            :href="`/providers/${provider.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
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
                                            Editar
                                        </Link>
                                        <button
                                            type="button"
                                            v-if="can('providers.delete')"
                                            @click="deleteClient(provider)"
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
                        </transition-group>
                    </table>
                </div>

                <div
                    v-if="!displayedCompanies.length"
                    class="px-6 py-16 text-center"
                >
                    <div class="mx-auto max-w-md">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <svg
                                class="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos clientes
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o limpia la busqueda para volver
                            a ver todos los clientes.
                        </p>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ providers.from }} a {{ providers.to }} de
                        {{ providers.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="providers.prev_page_url"
                            :href="providers.prev_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Anterior
                        </Link>
                        <span
                            class="text-sm text-slate-600 dark:text-slate-400"
                        >
                            Pagina {{ providers.current_page }} de
                            {{ providers.last_page }}
                        </span>
                        <Link
                            v-if="providers.next_page_url"
                            :href="providers.next_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, onBeforeUnmount, ref, watch } from 'vue';
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
// Tipos de clientes
type companyStatusType = '1' | '0';

const clientTypeLabels: Record<companyStatusType, string> = {
    1: 'Activo',
    0: 'Inactivo',
};

const clientTypeOptions = (
    Object.entries(clientTypeLabels) as Array<[companyStatusType, string]>
).map(([value, label]) => ({ value, label }));

// Props de Inertia

interface Props {
    providers: {
        data: Array<{
            id: number;
            active: companyStatusType;
            name?: string;
            type?: string; // opcional, si quieres mostrar tipo
            email?: string; // opcional, si quieres mostrar email
            phone?: string; // opcional, si quieres mostrar teléfono
            logo_url?: string; // si tienes logos para proveedores
            company?: {
                id: number;
                name: string;
                logo_url?: string;
            };
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: {
        search?: string;
        type?: companyStatusType;
    };
}

const { providers, filters } = defineProps<Props>();

type Company = Props['providers']['data'][number];

const displayedCompanies = ref<Company[]>([]);
const search = ref(filters.search ?? '');
const selectedType = ref(filters.type ?? '');
const animationTimeouts = ref<number[]>([]);
let searchDebounceTimeout: number | null = null;

const STAGGER_DELAY_MS = 120;
const SEARCH_DEBOUNCE_MS = 320;

// Animación al cargar
watch(
    () => providers.data,
    (newClients) => {
        clearAnimationTimeouts();
        displayedCompanies.value = [];

        if (newClients.length === 0) return;

        newClients.forEach((client, index) => {
            const timeoutId = window.setTimeout(() => {
                displayedCompanies.value.push(client);
            }, index * STAGGER_DELAY_MS);
            animationTimeouts.value.push(timeoutId);
        });
    },
    { immediate: true },
);

// Filtrado
const submitFilters = (nextSearch: string, nextType: string) => {
    const trimmedSearch = nextSearch.trim();
    const params: Record<string, string> = {};

    if (trimmedSearch.length > 2) params.search = trimmedSearch;
    if (nextType) params.type = nextType;

    router.get('/providers', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Watchers de filtros
watch(search, (newSearch) => {
    const trimmedSearch = newSearch.trim();

    if (trimmedSearch.length > 2 || trimmedSearch === '') {
        clearSearchDebounce();
        searchDebounceTimeout = window.setTimeout(() => {
            submitFilters(trimmedSearch, selectedType.value);
        }, SEARCH_DEBOUNCE_MS);
    }
});

watch(selectedType, (newType) => {
    clearSearchDebounce();
    const trimmedSearch = search.value.trim();
    const nextSearch = trimmedSearch.length > 2 ? trimmedSearch : '';
    submitFilters(nextSearch, newType);
});

onBeforeUnmount(() => {
    clearAnimationTimeouts();
    clearSearchDebounce();
});

const clearTypeFilter = () => {
    selectedType.value = '';
};

const activeFilterChips = computed(() => {
    const chips: string[] = [];

    if (search.value.trim()) chips.push(`Busqueda: ${search.value.trim()}`);
    if (selectedType.value)
        chips.push(
            `Tipo: ${clientTypeLabels[selectedType.value as companyStatusType]}`,
        );

    return chips;
});

// Resumen
const summaryCards = computed(() => [
    {
        title: 'En esta pagina',
        value: String(providers.data.length),
        caption: 'Proveedores visibles según filtros y paginación',
        icon: icon(
            'M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10',
        ),
        iconClass: 'bg-white/15 text-sky-300',
        delay: 0,
    },
    {
        title: 'Filtros activos',
        value: String(activeFilterChips.value.length),
        caption: activeFilterChips.value.length
            ? activeFilterChips.value.join(' - ')
            : 'Sin restricciones activas',
        icon: icon('M3 4.5h18M6.75 12h10.5M10.5 19.5h3'),
        iconClass: 'bg-white/15 text-indigo-200',
        delay: 270,
    },
]);

const tableHeadline = computed(() => {
    if (activeFilterChips.value.length) {
        return `Vista filtrada por ${activeFilterChips.value.join(' - ')}`;
    }
    return '';
});

// Helpers
const deleteClient = (client: Company) => {
    if (
        confirm(
            `¿Estás seguro de que quieres eliminar al cliente ${client.name}?`,
        )
    ) {
        router.delete(`/providers/${client.id}`);
    }
};

function icon(path: string) {
    return defineComponent({
        name: 'SummaryIcon',
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

function clearAnimationTimeouts() {
    animationTimeouts.value.forEach((timeoutId) =>
        window.clearTimeout(timeoutId),
    );
    animationTimeouts.value = [];
}

function clearSearchDebounce() {
    if (searchDebounceTimeout !== null) {
        window.clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = null;
    }
}
</script>

<style scoped>
.vehicle-enter-from,
.vehicle-appear-from {
    opacity: 0;
    transform: translateY(16px);
}

.vehicle-enter-to,
.vehicle-appear-to {
    opacity: 1;
    transform: translateY(0);
}

.vehicle-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.vehicle-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pop {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }
    60% {
        transform: scale(1.02);
        opacity: 1;
    }
    100% {
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fadeIn 400ms ease-out;
}

.animate-pop {
    animation: pop 450ms ease-out;
}

.summary-card-enter-active,
.summary-card-appear-active {
    transition:
        opacity 520ms ease,
        transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.summary-card-enter-from,
.summary-card-appear-from {
    opacity: 0;
    transform: translateX(-28px);
}

.summary-card-enter-to,
.summary-card-appear-to {
    opacity: 1;
    transform: translateX(0);
}

.filter-panel-enter-active,
.filter-panel-appear-active {
    transition:
        opacity 540ms ease,
        transform 540ms cubic-bezier(0.22, 1, 0.36, 1);
}

.filter-panel-enter-from,
.filter-panel-appear-from {
    opacity: 0;
    transform: translateY(32px);
}

.filter-panel-enter-to,
.filter-panel-appear-to {
    opacity: 1;
    transform: translateY(0);
}
</style>
