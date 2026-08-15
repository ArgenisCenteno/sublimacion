<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Toma de Muestras'" />

        <div class="space-y-6">
            <div v-if="flash.success || flash.warning || flash.error" class="space-y-3">
                <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ flash.success }}</div>
                <div v-if="flash.warning" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">{{ flash.warning }}</div>
                <div v-if="flash.error"   class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">{{ flash.error }}</div>
            </div>

            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.30),_transparent_30%),linear-gradient(135deg,_#164e63_0%,_#1e1b4b_60%,_#0f172a_100%)] p-5 sm:p-7">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-2xl">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Laboratorio
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Cola de toma de muestras
                            </h1>
                        </div>
                    </div>

                    <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4" name="summary-card" appear>
                        <article
                            v-for="card in summaryCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md"
                        >
                            <div class="mb-3 flex items-center gap-3">
                                <div :class="['flex h-10 w-10 items-center justify-center rounded-xl', card.iconClass]">
                                    <component :is="card.icon" class="h-5 w-5" />
                                </div>
                                <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            </div>
                            <p class="text-2xl leading-none font-semibold">{{ card.value }}</p>
                            <p class="mt-2 text-sm text-white/65">{{ card.caption }}</p>
                        </article>
                    </transition-group>

                    <transition appear name="filter-panel">
                        <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                            <div class="grid gap-4 xl:grid-cols-[1fr_auto] xl:items-end">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-white/85">Buscar</label>
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Código de orden, cédula, nombre del paciente..."
                                            class="animate-fade-in h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 pl-11 text-sm text-white placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                        />
                                        <svg class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-white/45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <button v-if="search.trim()" type="button" @click="search = ''" class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </section>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Órdenes en cola</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Órdenes pendientes o en proceso de toma de muestras.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ props.ordenes.total }} en cola
                        </span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Orden</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Paciente</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Exámenes</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Muestras pendientes</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Estado</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Acciones</p></th>
                            </tr>
                        </thead>

                        <transition-group tag="tbody" name="muestra-row" appear class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="orden in displayedOrdenes"
                                :key="orden.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(34,211,238,0.04),_rgba(6,182,212,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <!-- Orden -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#cffafe_0%,_#a5f3fc_100%)] text-cyan-700 shadow-sm dark:bg-[linear-gradient(135deg,_rgba(34,211,238,0.18)_0%,_rgba(6,182,212,0.18)_100%)] dark:text-cyan-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-mono text-theme-sm font-semibold text-slate-800 dark:text-white/90">{{ orden.codigo }}</span>
                                            <span class="text-theme-xs text-slate-500 dark:text-slate-400">{{ formatDate(orden.created_at) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Paciente -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm font-semibold text-slate-800 dark:text-white/90">
                                            {{ orden.paciente?.apellidos }}, {{ orden.paciente?.nombres }}
                                        </span>
                                        <span class="font-mono text-theme-xs text-slate-500 dark:text-slate-400">
                                            {{ orden.paciente?.tipo_documento }}-{{ orden.paciente?.cedula }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Exámenes -->
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                        {{ orden.items_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Muestras pendientes -->
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    <span :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold',
                                        (orden.muestras_pendientes_count ?? 0) > 0
                                            ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
                                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                    ]">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                        {{ orden.muestras_pendientes_count ?? 0 }} pendientes
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    <span :class="estadoClass(orden.estado)">{{ estadoLabel(orden.estado) }}</span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-4 sm:px-5">
                                    <button
                                        type="button"
                                        :disabled="Boolean(activeAction)"
                                        @click="navigateTo(`/muestras/${orden.id}`, `abrir:${orden.id}`)"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-300"
                                    >
                                        <svg v-if="activeAction === `abrir:${orden.id}`" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ activeAction === `abrir:${orden.id}` ? 'Cargando...' : 'Atender' }}
                                    </button>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <!-- Estado vacío -->
                <div v-if="!displayedOrdenes.length" class="px-6 py-16 text-center">
                    <div class="mx-auto max-w-md">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Cola vacía</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No hay órdenes pendientes de toma de muestras.</p>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.ordenes.from }} a {{ props.ordenes.to }} de {{ props.ordenes.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button v-if="props.ordenes.prev_page_url" type="button" :disabled="Boolean(activeAction)" @click="navigateTo(props.ordenes.prev_page_url!, 'prev-page')" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ activeAction === 'prev-page' ? 'Cargando...' : 'Anterior' }}
                        </button>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Página {{ props.ordenes.current_page }} de {{ props.ordenes.last_page }}</span>
                        <button v-if="props.ordenes.next_page_url" type="button" :disabled="Boolean(activeAction)" @click="navigateTo(props.ordenes.next_page_url!, 'next-page')" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ activeAction === 'next-page' ? 'Cargando...' : 'Siguiente' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, defineComponent, h, onBeforeUnmount, ref, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';

interface Paciente { id: number; nombres: string; apellidos: string; cedula: string; tipo_documento: string; }

interface Orden {
    id: number;
    codigo: string;
    estado: string;
    created_at: string;
    paciente?: Paciente;
    items_count?: number;
    muestras_pendientes_count?: number;
}

interface Props {
    ordenes: {
        data: Orden[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: { search?: string };
}

const props = defineProps<Props>();
const page  = usePage<{ flash?: { success?: string | null; warning?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const displayedOrdenes = ref<Orden[]>([]);
const search           = ref(props.filters.search ?? '');
const activeAction     = ref<string | null>(null);
let searchDebounce: number | null = null;

watch(() => props.ordenes.data, (data) => { displayedOrdenes.value = [...data]; }, { immediate: true });

watch(search, (val) => {
    clearDebounce();
    if (val.trim().length > 1 || val.trim() === '') {
        searchDebounce = window.setTimeout(() => {
            if (activeAction.value) return;
            const params: Record<string, string> = {};
            if (val.trim().length > 1) params.search = val.trim();
            router.get('/muestras', params, { preserveState: true, preserveScroll: true, replace: true });
        }, 320);
    }
});

onBeforeUnmount(clearDebounce);
function clearDebounce() { if (searchDebounce !== null) { window.clearTimeout(searchDebounce); searchDebounce = null; } }

const summaryCards = computed(() => [
    {
        title: 'En cola',
        value: String(props.ordenes.total),
        caption: 'Órdenes pendientes de muestra',
        icon: icon('M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'),
        iconClass: 'bg-white/15 text-cyan-300',
        delay: 0,
    },
    {
        title: 'Pendientes',
        value: String(props.ordenes.data.filter(o => o.estado === 'pendiente').length),
        caption: 'Sin iniciar en esta página',
        icon: icon('M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-amber-300',
        delay: 90,
    },
    {
        title: 'En proceso',
        value: String(props.ordenes.data.filter(o => o.estado === 'en_proceso').length),
        caption: 'Con muestras parciales',
        icon: icon('M13 10V3L4 14h7v7l9-11h-7z'),
        iconClass: 'bg-white/15 text-blue-300',
        delay: 180,
    },
    {
        title: 'Hoy',
        value: String(props.ordenes.data.filter(o => new Date(o.created_at).toDateString() === new Date().toDateString()).length),
        caption: 'Creadas hoy en esta página',
        icon: icon('M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'),
        iconClass: 'bg-white/15 text-teal-300',
        delay: 270,
    },
]);

function estadoLabel(estado: string) {
    return { pendiente: 'Pendiente', en_proceso: 'En proceso', listo: 'Listo', entregado: 'Entregado', anulado: 'Anulado' }[estado] ?? estado;
}

function estadoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:  'rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        en_proceso: 'rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    };
    return map[estado] ?? map.pendiente;
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' });
}

function navigateTo(url: string, action: string) {
    if (activeAction.value) return;
    activeAction.value = action;
    router.visit(url, { preserveScroll: true, onFinish: () => { activeAction.value = null; } });
}

function icon(path: string) {
    return defineComponent({
        setup(_, { attrs }) {
            return () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', ...attrs }, [
                h('path', { d: path, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }),
            ]);
        },
    });
}
</script>

<style scoped>
.muestra-row-enter-from, .muestra-row-appear-from { opacity:0; transform:translateY(16px); }
.muestra-row-enter-to,   .muestra-row-appear-to   { opacity:1; transform:translateY(0); }
.muestra-row-leave-from  { opacity:1; transform:translateY(0); }
.muestra-row-leave-to    { opacity:0; transform:translateY(-8px); }
.muestra-row-enter-active, .muestra-row-appear-active, .muestra-row-leave-active { transition: opacity 500ms ease, transform 500ms cubic-bezier(0.22,1,0.36,1); }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
@keyframes pop    { 0%{transform:scale(.95);opacity:0} 60%{transform:scale(1.02);opacity:1} 100%{transform:scale(1)} }
.animate-fade-in { animation: fadeIn 400ms ease-out; }
.animate-pop     { animation: pop   450ms ease-out; }
.summary-card-enter-active, .summary-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.summary-card-enter-from,   .summary-card-appear-from   { opacity:0; transform:translateX(-28px); }
.summary-card-enter-to,     .summary-card-appear-to     { opacity:1; transform:translateX(0); }
.filter-panel-enter-active, .filter-panel-appear-active { transition: opacity 540ms ease, transform 540ms cubic-bezier(0.22,1,0.36,1); }
.filter-panel-enter-from,   .filter-panel-appear-from   { opacity:0; transform:translateY(32px); }
.filter-panel-enter-to,     .filter-panel-appear-to     { opacity:1; transform:translateY(0); }
</style>