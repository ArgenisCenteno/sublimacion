<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Usuarios'" />

        <div class="space-y-6">
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
            <!-- Header + resumen -->
            <section
                class="mt-5 overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
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
                                Roles
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Vista operativa de usuarios
                            </h1>
                        </div>

                        <Link
                            href="/users/create"
                            v-if="can('users.create')"
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
                            Nuevo Usuario
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
                </div>
            </section>

            <!-- Tabla de roles -->
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
                                Listado de usuarios
                            </h2>
                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            ></p>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ users.total }} registros
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
                                        Email
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Roles
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
                            name="client"
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
                                v-for="user in displayedUsers"
                                :key="user.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(168,85,247,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ user.name }}
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{ user.email }}
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6">
                                    {{
                                        user.roles?.length
                                            ? user.roles
                                                  .map((r) => r.name)
                                                  .join(', ')
                                            : 'Sin roles'
                                    }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('users.view')"
                                            :href="`/users/${user.id}`"
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
                                            v-if="can('users.edit')"
                                            :href="`/users/${user.id}/edit`"
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
                                            v-if="can('users.delete')"
                                            @click="deleteUser(user)"
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
                                        <button
                                            type="button"
                                            v-if="can('users.impersonate') && !isAdminUser(user)"
                                            @click="userToImpersonate = user"
                                            class="inline-flex items-center gap-1 rounded-xl border border-amber-200 px-3 py-1.5 text-theme-xs font-semibold text-amber-600 transition hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10"
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
                                                    d="M8 7a4 4 0 118 0 4 4 0 01-8 0zM12 15c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5zM17 8l2 2 4-4"
                                                />
                                            </svg>
                                            Impersonar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <div
                    v-if="!displayedUsers.length"
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
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos roles
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o limpia la búsqueda para volver
                            a ver todos los roles.
                        </p>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ users.from }} a {{ users.to }} de
                        {{ users.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="users.prev_page_url"
                            :href="users.prev_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Anterior
                        </Link>
                        <span
                            class="text-sm text-slate-600 dark:text-slate-400"
                        >
                            Página {{ users.current_page }} de
                            {{ users.last_page }}
                        </span>
                        <Link
                            v-if="users.next_page_url"
                            :href="users.next_page_url"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-if="userToImpersonate"
            title="Impersonar usuario"
            :message="`Vas a entrar como ${userToImpersonate.name}. Podrás volver a tu cuenta en cualquier momento.`"
            confirm-label="Impersonar"
            @confirm="confirmImpersonate"
            @cancel="userToImpersonate = null"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount, defineComponent, h } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
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
interface Props {
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            active: boolean;
            roles?: Array<{ id: number; name: string }>;
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
    };
}

const { users, filters } = defineProps<Props>();

const search = ref(filters.search ?? '');
const displayedUsers = ref<Props['users']['data']>([]);
const animationTimeouts = ref<number[]>([]);
let searchDebounceTimeout: number | null = null;

const STAGGER_DELAY_MS = 120;
const SEARCH_DEBOUNCE_MS = 320;

// Animación
watch(
    () => users.data,
    (newUsers) => {
        clearAnimationTimeouts();
        displayedUsers.value = [];
        newUsers.forEach((user, index) => {
            const timeoutId = window.setTimeout(() => {
                displayedUsers.value.push(user);
            }, index * STAGGER_DELAY_MS);
            animationTimeouts.value.push(timeoutId);
        });
    },
    { immediate: true },
);

// Filtros
const submitFilters = (nextSearch: string) => {
    const trimmedSearch = nextSearch.trim();
    const params: Record<string, string> = {};
    if (trimmedSearch.length > 2) params.search = trimmedSearch;

    router.get('/users', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(search, (newSearch) => {
    const trimmedSearch = newSearch.trim();
    if (trimmedSearch.length > 2 || trimmedSearch === '') {
        clearSearchDebounce();
        searchDebounceTimeout = window.setTimeout(
            () => submitFilters(trimmedSearch),
            SEARCH_DEBOUNCE_MS,
        );
    }
});

onBeforeUnmount(() => {
    clearAnimationTimeouts();
    clearSearchDebounce();
});

const activeFilterChips = computed(() => {
    const chips: string[] = [];
    if (search.value.trim()) chips.push(`Busqueda: ${search.value.trim()}`);
    return chips;
});

const summaryCards = computed(() => [
    {
        title: 'Usuarios en esta pagina',
        value: String(users.data.length),
        caption: 'Usuarios visibles según filtros',
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

const deleteUser = (user: Props['users']['data'][number]) => {
    if (confirm(`¿Eliminar usuario ${user.name}?`)) {
        router.delete(`/users/${user.id}`);
    }
};

const isAdminUser = (user: Props['users']['data'][number]): boolean =>
    user.roles?.some((role) => role.name === 'Admin') ?? false;

const userToImpersonate = ref<Props['users']['data'][number] | null>(null);

const confirmImpersonate = () => {
    if (!userToImpersonate.value) return;
    router.post(`/impersonate/${userToImpersonate.value.id}`, {}, {
        onFinish: () => {
            userToImpersonate.value = null;
        },
    });
};

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

function clearAnimationTimeouts() {
    animationTimeouts.value.forEach((id) => window.clearTimeout(id));
    animationTimeouts.value = [];
}

function clearSearchDebounce() {
    if (searchDebounceTimeout !== null) {
        window.clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = null;
    }
}

const clearFilters = () => {
    search.value = '';
};
</script>
