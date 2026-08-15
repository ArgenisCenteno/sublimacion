<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Orden ${orden.codigo}`" />

        <div class="space-y-6">
            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.28),_transparent_32%),linear-gradient(135deg,_#1e1b4b_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Recepción
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Orden {{ orden.codigo }}
                            </h1>
                            <p class="mt-2 text-sm text-white/60">
                                Creada el {{ formatFecha(orden.created_at) }} · por {{ orden.recepcionista?.name ?? '—' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-if="orden.estado === 'pendiente'"
                                type="button"
                                :disabled="isNavigating"
                                @click="goToEdit"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                :disabled="isNavigating"
                                @click="goToIndex"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                Volver al listado
                            </button>
                        </div>
                    </div>

                    <transition-group tag="div" class="mt-6 grid gap-3 sm:grid-cols-2 md:grid-cols-4" name="hero-card" appear>
                        <article
                            v-for="card in heroCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1"
                        >
                            <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            <p class="mt-3 text-xl font-semibold">{{ card.value }}</p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <!-- Flash -->
            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo" :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error" :message="flash.error" />
            </transition-group>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">

                <!-- Contenido principal -->
                <div class="space-y-6">

                    <!-- Exámenes -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Exámenes solicitados</h2>
                            
                               <a v-if="['listo','entregado'].includes(orden.estado)"
                                :href="`/resultados/${orden.id}/pdf`"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Descargar PDF
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700">
                                        <th class="pb-3 text-left text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400">Examen</th>
                                        <th class="pb-3 text-left text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400">Referencia</th>
                                        <th v-if="tieneResultados" class="pb-3 text-center text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400">Resultado</th>
                                        <th class="pb-3 text-right text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400">Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="item in orden.items" :key="item.id">
                                        <td class="py-3">
                                            <p class="font-medium text-slate-800 dark:text-white">{{ item.examen?.nombre }}</p>
                                            <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ item.examen?.codigo }}</p>
                                        </td>

                                        <td class="py-3 text-xs text-slate-500 dark:text-slate-400">
                                            <span v-if="item.referencia">
                                                <template v-if="item.referencia.valor_texto">
                                                    {{ item.referencia.valor_texto }}
                                                </template>
                                                <template v-else-if="item.referencia.valor_min !== null || item.referencia.valor_max !== null">
                                                    {{ item.referencia.valor_min ?? '—' }} – {{ item.referencia.valor_max ?? '—' }}
                                                    <span v-if="item.referencia.etiqueta" class="ml-1 text-slate-400">({{ item.referencia.etiqueta }})</span>
                                                </template>
                                            </span>
                                            <span v-else class="text-slate-300 dark:text-slate-600">Sin referencia</span>
                                        </td>

                                        <td v-if="tieneResultados" class="py-3 text-center">
                                            <div v-if="item.resultado_valor !== null || item.resultado_texto" class="flex flex-col items-center gap-1">
                                                <span :class="[
                                                    'text-base font-bold',
                                                    item.fuera_de_rango
                                                        ? item.indicador === 'critico'
                                                            ? 'text-rose-600 dark:text-rose-400'
                                                            : 'text-amber-600 dark:text-amber-400'
                                                        : 'text-slate-800 dark:text-white'
                                                ]">
                                                    {{ item.resultado_valor ?? item.resultado_texto }}
                                                    <span v-if="item.resultado_valor && item.examen?.unidad" class="text-xs font-normal text-slate-500">
                                                        {{ item.examen.unidad }}
                                                    </span>
                                                </span>
                                                <span v-if="item.estado === 'listo' && item.fuera_de_rango" :class="indicadorClass(item.indicador)">
                                                    {{ indicadorLabel(item.indicador) }}
                                                </span>
                                                <span v-else-if="item.estado === 'listo'" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                    Normal
                                                </span>
                                            </div>
                                            <span v-else class="text-slate-300 dark:text-slate-600">—</span>
                                        </td>

                                        <td class="py-3 text-right font-semibold text-slate-700 dark:text-slate-300">
                                            ${{ Number(item.precio).toFixed(2) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-slate-200 dark:border-slate-700">
                                        <td colspan="2" class="pt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Subtotal</td>
                                        <td v-if="tieneResultados"></td>
                                        <td class="pt-3 text-right text-sm font-semibold text-slate-700 dark:text-slate-300">${{ Number(orden.subtotal).toFixed(2) }}</td>
                                    </tr>
                                    <tr v-if="Number(orden.descuento) > 0">
                                        <td colspan="2" class="pt-1 text-sm font-medium text-slate-500 dark:text-slate-400">Descuento</td>
                                        <td v-if="tieneResultados"></td>
                                        <td class="pt-1 text-right text-sm font-semibold text-rose-600">-${{ Number(orden.descuento).toFixed(2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="pt-2 text-base font-bold text-slate-900 dark:text-white">Total</td>
                                        <td v-if="tieneResultados"></td>
                                        <td class="pt-2 text-right text-base font-bold text-indigo-600 dark:text-indigo-400">${{ Number(orden.total).toFixed(2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <!-- Observaciones -->
                    <section
                        v-if="orden.observaciones"
                        class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay:60ms"
                    >
                        <h2 class="mb-3 text-base font-semibold text-slate-900 dark:text-white">Observaciones</h2>
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ orden.observaciones }}</p>
                    </section>

                    <!-- Acciones -->
                    <section
                        class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay:90ms"
                    >
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Acciones</h2>
                        <div class="flex flex-wrap gap-3">

                            <!-- Registrar cobro -->
                            <button
                                v-if="['pendiente','parcial'].includes(orden.estado_pago) && orden.estado !== 'anulado'"
                                type="button"
                                @click="router.visit(`/recepcion/ordenes/${orden.id}/cobrar`)"
                                class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-500"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Registrar cobro
                            </button>

                            <!-- Ir a muestras -->
                            <button
                                v-if="!['listo','entregado','anulado'].includes(orden.estado)"
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/muestras/${orden.id}`, 'muestras')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:bg-cyan-100 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-300"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                                {{ activeAction === 'muestras' ? 'Cargando...' : 'Ir a toma de muestras' }}
                            </button>

                            <!-- Ir a resultados -->
                            <button
                                v-if="['en_proceso','pendiente'].includes(orden.estado)"
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/resultados/${orden.id}`, 'resultados')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ activeAction === 'resultados' ? 'Cargando...' : 'Cargar resultados' }}
                            </button>

                            <!-- Marcar entregado -->
                            <button
                                v-if="orden.estado === 'listo'"
                                type="button"
                                @click="confirmar('entregado')"
                                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-indigo-500"
                            >
                                Marcar como entregada
                            </button>

                            <!-- Anular -->
                            <button
                                v-if="['pendiente','en_proceso'].includes(orden.estado)"
                                type="button"
                                @click="confirmar('anular')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10"
                            >
                                Anular orden
                            </button>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Paciente</h2>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Nombre</dt>
                                <dd class="mt-0.5 font-semibold text-slate-800 dark:text-white">{{ orden.paciente?.nombres }} {{ orden.paciente?.apellidos }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Cédula</dt>
                                <dd class="font-mono mt-0.5 text-slate-700 dark:text-slate-300">{{ orden.paciente?.cedula }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Edad</dt>
                                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ orden.paciente?.edad }} años</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Sexo</dt>
                                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ orden.paciente?.sexo === 'M' ? 'Masculino' : 'Femenino' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:60ms">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Estado</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Orden</dt>
                                <dd>
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', estadoClass(orden.estado)]">
                                        {{ orden.estado_label }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Pago</dt>
                                <dd>
                                    <span :class="[
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        orden.estado_pago === 'pagado'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
                                    ]">
                                        {{ orden.estado_pago_label }}
                                    </span>
                                </dd>
                            </div>
                            <div v-if="orden.medico" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Médico</dt>
                                <dd class="text-right font-medium text-slate-700 dark:text-slate-300">{{ orden.medico.nombre_completo }}</dd>
                            </div>
                            <div v-if="orden.fecha_entrega_estimada" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Entrega estimada</dt>
                                <dd class="font-medium text-slate-700 dark:text-slate-300">{{ formatFecha(orden.fecha_entrega_estimada) }}</dd>
                            </div>
                            <div v-if="orden.fecha_entregado" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Entregado</dt>
                                <dd class="font-medium text-emerald-600 dark:text-emerald-400">{{ formatFecha(orden.fecha_entregado) }}</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <transition name="modal">
                <div v-if="accionPendiente" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-sm rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            {{ confirmConfig[accionPendiente].titulo }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            {{ confirmConfig[accionPendiente].mensaje }}
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="accionPendiente = null" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="ejecutarAccion"
                                :class="['rounded-2xl px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5', confirmConfig[accionPendiente].btnClass]"
                            >
                                {{ confirmConfig[accionPendiente].btnLabel }}
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Alert from '@/components/ui/Alert.vue';

const props = defineProps<{ orden: any }>();

const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const isNavigating    = ref(false);
const activeAction    = ref<string | null>(null);
const accionPendiente = ref<'entregado' | 'anular' | null>(null);

const tieneResultados = computed(() =>
    ['listo', 'entregado'].includes(props.orden.estado) ||
    (props.orden.items ?? []).some((i: any) =>
        i.resultado_valor !== null || i.resultado_texto
    )
)

const heroCards = computed(() => [
    { title: 'Paciente', value: `${props.orden.paciente?.nombres ?? ''} ${props.orden.paciente?.apellidos ?? ''}`, delay: 0 },
    { title: 'Exámenes', value: props.orden.items_count ?? props.orden.items?.length ?? 0, delay: 90 },
    { title: 'Estado',   value: props.orden.estado_label, delay: 180 },
    { title: 'Total',    value: `$${Number(props.orden.total).toFixed(2)}`, delay: 270 },
]);

const confirmConfig = {
    entregado: {
        titulo:   '¿Marcar como entregada?',
        mensaje:  'Se registrará la fecha de entrega al paciente.',
        btnLabel: 'Confirmar',
        btnClass: 'bg-indigo-600 hover:bg-indigo-500',
    },
    anular: {
        titulo:   '¿Anular esta orden?',
        mensaje:  'Esta acción no se puede deshacer. La orden quedará anulada.',
        btnLabel: 'Anular orden',
        btnClass: 'bg-rose-600 hover:bg-rose-500',
    },
};

function confirmar(accion: 'entregado' | 'anular') {
    accionPendiente.value = accion;
}

function ejecutarAccion() {
    if (!accionPendiente.value) return;
    const routes: Record<string, string> = {
        entregado: `/recepcion/ordenes/${props.orden.id}/entregar`,
        anular:    `/recepcion/ordenes/${props.orden.id}/anular`,
    };
    router.patch(routes[accionPendiente.value], {}, {
        onFinish: () => { accionPendiente.value = null; },
    });
}

function navigateTo(url: string, action: string) {
    if (activeAction.value) return;
    activeAction.value = action;
    router.visit(url, {
        preserveScroll: true,
        onFinish: () => { activeAction.value = null; },
    });
}

function indicadorClass(indicador?: string | null) {
    const map: Record<string, string> = {
        alto:    'rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        bajo:    'rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
        critico: 'rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    };
    return map[indicador ?? ''] ?? '';
}

function indicadorLabel(indicador?: string | null) {
    return { alto: 'Alto', bajo: 'Bajo', critico: 'Crítico' }[indicador ?? ''] ?? '';
}

function estadoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:  'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        en_proceso: 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        listo:      'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        entregado:  'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        anulado:    'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    };
    return map[estado] ?? 'bg-slate-100 text-slate-500';
}

function formatFecha(fecha: string) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleString('es-ES', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function goToIndex() {
    if (isNavigating.value) return;
    isNavigating.value = true;
    router.visit('/recepcion/ordenes', { onFinish: () => { isNavigating.value = false; } });
}

function goToEdit() {
    if (isNavigating.value) return;
    isNavigating.value = true;
    router.visit(`/recepcion/ordenes/${props.orden.id}/edit`, { onFinish: () => { isNavigating.value = false; } });
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from,   .modal-leave-to     { opacity:0; }
@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>