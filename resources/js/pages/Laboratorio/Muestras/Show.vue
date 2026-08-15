<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Muestras — ${orden.codigo}`" />

        <div class="space-y-6">
            <div v-if="flash.success || flash.warning || flash.error" class="space-y-3">
                <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ flash.success }}</div>
                <div v-if="flash.warning" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">{{ flash.warning }}</div>
                <div v-if="flash.error"   class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">{{ flash.error }}</div>
            </div>

            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.28),_transparent_32%),linear-gradient(135deg,_#164e63_0%,_#1e1b4b_58%,_#0f172a_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Laboratorio · Muestras
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ orden.paciente?.apellidos }}, {{ orden.paciente?.nombres }}
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-sm text-white/60">{{ orden.codigo }}</span>
                                <span class="text-white/30">·</span>
                                <span class="font-mono text-sm text-white/60">{{ orden.paciente?.tipo_documento }}-{{ orden.paciente?.cedula }}</span>
                                <span class="text-white/30">·</span>
                                <span class="text-sm text-white/60">{{ orden.paciente?.edad }} años</span>
                                <span v-if="orden.medico" class="text-white/30">·</span>
                                <span v-if="orden.medico" class="text-sm text-white/60">{{ orden.medico.nombre_completo }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <!-- Generar muestras si no existen aún -->
                            <button
                                v-if="!orden.muestras?.length"
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="generarMuestras"
                                class="inline-flex items-center gap-2 rounded-2xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-cyan-400"
                            >
                                <svg v-if="activeAction === 'generar'" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                {{ activeAction === 'generar' ? 'Generando...' : 'Generar muestras' }}
                            </button>

                            <!-- Tomar todas si hay pendientes -->
                            <button
                                v-if="muestrasPendientes.length > 1"
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="tomarTodas"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                <svg v-if="activeAction === 'tomar-todas'" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ activeAction === 'tomar-todas' ? 'Procesando...' : 'Tomar todas' }}
                            </button>

                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo('/muestras', 'volver')"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                {{ activeAction === 'volver' ? 'Cargando...' : 'Volver a cola' }}
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-4" name="hero-card" appear>
                        <article
                            v-for="card in statsCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md"
                        >
                            <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            <p class="mt-3 text-2xl font-semibold">{{ card.value }}</p>
                            <p class="mt-1 text-sm text-white/65">{{ card.caption }}</p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(300px,0.8fr)]">

                <!-- Muestras -->
                <div class="space-y-4">
                    <!-- Sin muestras generadas -->
                    <div v-if="!orden.muestras?.length" class="panel-float rounded-[28px] border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200">Sin muestras generadas</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Haz clic en "Generar muestras" para crear los tubos automáticamente.</p>
                    </div>

                    <!-- Cards de cada muestra -->
                    <transition-group name="muestra-card" appear tag="div" class="space-y-4">
                        <div
                            v-for="muestra in orden.muestras"
                            :key="muestra.id"
                            :class="[
                                'panel-float rounded-[28px] border bg-white p-6 shadow-lg dark:bg-slate-900',
                                muestra.estado === 'rechazada'
                                    ? 'border-rose-200 dark:border-rose-500/25'
                                    : muestra.estado === 'tomada' || muestra.estado === 'procesada'
                                        ? 'border-emerald-200 dark:border-emerald-500/25'
                                        : 'border-slate-200 dark:border-slate-800',
                            ]"
                        >
                            <!-- Header de la muestra -->
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div :class="[
                                        'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl',
                                        tuboColor(muestra.tubo),
                                    ]">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ muestra.tipo }}</p>
                                        <p v-if="muestra.tubo" class="text-sm text-slate-500 dark:text-slate-400">{{ muestra.tubo }}</p>
                                    </div>
                                </div>

                                <span :class="muestraEstadoClass(muestra.estado)">
                                    {{ muestra.estado_label ?? muestraEstadoLabel(muestra.estado) }}
                                </span>
                            </div>

                            <!-- Exámenes de esta muestra -->
                            <div class="mt-4 space-y-1.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Exámenes incluidos</p>
                                <div
                                    v-for="item in muestra.items"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"
                                >
                                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ item.examen?.nombre }}</span>
                                    <span class="font-mono text-xs text-slate-500">{{ item.examen?.codigo }}</span>
                                </div>
                            </div>

                            <!-- Info si fue tomada -->
                            <div v-if="muestra.tomada_at" class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 dark:bg-emerald-500/10">
                                <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                    Tomada el {{ formatDateTime(muestra.tomada_at) }}
                                    <span v-if="muestra.tomada_por">por {{ muestra.tomada_por.name }}</span>
                                </p>
                                <p v-if="muestra.observaciones" class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ muestra.observaciones }}</p>
                            </div>

                            <!-- Motivo de rechazo -->
                            <div v-if="muestra.estado === 'rechazada' && muestra.motivo_rechazo" class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 dark:bg-rose-500/10">
                                <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">Motivo de rechazo:</p>
                                <p class="mt-0.5 text-xs text-rose-600 dark:text-rose-400">{{ muestra.motivo_rechazo }}</p>
                            </div>

                            <!-- Acciones de la muestra -->
                            <div v-if="muestra.estado === 'pendiente'" class="mt-5 space-y-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-slate-600 dark:text-slate-400">Observaciones (opcional)</label>
                                    <input
                                        v-model="observacionesMuestra[muestra.id]"
                                        type="text"
                                        placeholder="Ej: vena difícil, segundo intento..."
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-500/10 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white/90"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        :disabled="Boolean(activeAction)"
                                        @click="registrarToma(muestra)"
                                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500 disabled:opacity-60"
                                    >
                                        <svg v-if="activeAction === `tomar:${muestra.id}`" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ activeAction === `tomar:${muestra.id}` ? 'Registrando...' : 'Muestra tomada' }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="Boolean(activeAction)"
                                        @click="abrirRechazo(muestra)"
                                        class="rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            </div>

                            <!-- Acción de retomar si fue rechazada -->
                            <div v-if="muestra.estado === 'rechazada'" class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <button
                                    type="button"
                                    :disabled="Boolean(activeAction)"
                                    @click="registrarToma(muestra)"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-300"
                                >
                                    Retomar muestra
                                </button>
                            </div>
                        </div>
                    </transition-group>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <!-- Datos del paciente -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Paciente</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Nombre</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">{{ orden.paciente?.apellidos }}, {{ orden.paciente?.nombres }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Cédula</dt>
                                <dd class="font-mono font-medium text-slate-800 dark:text-white">{{ orden.paciente?.tipo_documento }}-{{ orden.paciente?.cedula }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Edad / Sexo</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ orden.paciente?.edad }} años · {{ orden.paciente?.sexo === 'M' ? 'Masculino' : 'Femenino' }}</dd>
                            </div>
                            <div v-if="orden.paciente?.es_menor" class="pt-1">
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                    Menor de edad
                                </span>
                            </div>
                        </dl>
                    </section>

                    <!-- Exámenes de la orden -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Exámenes solicitados</h2>
                        <div class="space-y-2">
                            <div
                                v-for="item in orden.items"
                                :key="item.id"
                                class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5 dark:bg-slate-800/60"
                            >
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-white">{{ item.examen?.nombre }}</p>
                                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ item.examen?.muestra }}</p>
                                </div>
                                <span :class="itemEstadoClass(item.estado)">{{ itemEstadoLabel(item.estado) }}</span>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <!-- Modal de rechazo -->
        <transition name="modal">
            <div v-if="modalRechazo" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)">
                <div class="w-full max-w-md rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Rechazar muestra</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Muestra de <strong>{{ modalRechazo.tipo }}</strong>. Los exámenes asociados volverán a pendiente.
                    </p>
                    <div class="mt-4">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Motivo <span class="text-red-500">*</span></label>
                        <input
                            v-model="motivoRechazo"
                            type="text"
                            placeholder="Muestra hemolizada, volumen insuficiente..."
                            class="h-12 w-full rounded-2xl border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-rose-300 focus:ring-4 focus:ring-rose-500/10 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white/90"
                            @keyup.enter="confirmarRechazo"
                        />
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button type="button" @click="modalRechazo = null; motivoRechazo = ''" class="flex-1 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            :disabled="!motivoRechazo.trim() || Boolean(activeAction)"
                            @click="confirmarRechazo"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500 disabled:opacity-60"
                        >
                            <svg v-if="activeAction === `rechazar:${modalRechazo?.id}`" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ activeAction === `rechazar:${modalRechazo?.id}` ? 'Rechazando...' : 'Confirmar rechazo' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface Examen { id: number; nombre: string; codigo: string; muestra: string; }
interface OrdenExamen { id: number; estado: string; examen?: Examen; }
interface TomadaPor { id: number; name: string; }

interface Muestra {
    id: number
    tipo: string
    tubo?: string
    estado: string
    estado_label?: string
    tomada_at?: string
    tomada_por?: TomadaPor
    motivo_rechazo?: string
    observaciones?: string
    items?: OrdenExamen[]
}

interface Paciente {
    id: number
    nombres: string
    apellidos: string
    cedula: string
    tipo_documento: string
    edad: number
    sexo: 'M' | 'F'
    es_menor: boolean
}

interface Medico { id: number; nombre_completo: string; }

interface OrdenTrabajo {
    id: number
    codigo: string
    estado: string
    paciente?: Paciente
    medico?: Medico
    muestras?: Muestra[]
    items?: OrdenExamen[]
}

const props       = defineProps<{ orden: OrdenTrabajo }>()
const page        = usePage<{ flash?: { success?: string | null; warning?: string | null; error?: string | null } }>()
const flash       = computed(() => page.props.flash ?? {})
const activeAction = ref<string | null>(null)

// Estado local
const observacionesMuestra = ref<Record<number, string>>({})
const modalRechazo  = ref<Muestra | null>(null)
const motivoRechazo = ref('')

const muestrasPendientes = computed(() =>
    (props.orden.muestras ?? []).filter(m => m.estado === 'pendiente')
)

const statsCards = computed(() => [
    { title: 'Muestras',   value: String(props.orden.muestras?.length ?? 0),           caption: 'Tubos generados para esta orden',  delay: 0   },
    { title: 'Pendientes', value: String(muestrasPendientes.value.length),              caption: 'Sin tomar todavía',                delay: 90  },
    { title: 'Tomadas',    value: String((props.orden.muestras ?? []).filter(m => m.estado === 'tomada').length), caption: 'Listas para analizar', delay: 180 },
    { title: 'Exámenes',   value: String(props.orden.items?.length ?? 0),              caption: 'Exámenes en la orden',             delay: 270 },
])

// Colores de tubo
function tuboColor(tubo?: string) {
    if (!tubo) return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300'
    const t = tubo.toLowerCase()
    if (t.includes('lila') || t.includes('edta'))    return 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300'
    if (t.includes('rojo'))                          return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300'
    if (t.includes('verde') || t.includes('heparin'))return 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300'
    if (t.includes('azul') || t.includes('citrat'))  return 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300'
    if (t.includes('gris') || t.includes('fluoru'))  return 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
    if (t.includes('amarillo'))                      return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300'
    return 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300'
}

function muestraEstadoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:  'rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        tomada:     'rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        en_proceso: 'rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        procesada:  'rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
        rechazada:  'rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    }
    return map[estado] ?? map.pendiente
}

function muestraEstadoLabel(estado: string) {
    return { pendiente: 'Pendiente', tomada: 'Tomada', en_proceso: 'En proceso', procesada: 'Procesada', rechazada: 'Rechazada' }[estado] ?? estado
}

function itemEstadoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:     'rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400',
        muestra_tomada:'rounded-full bg-cyan-50 px-2 py-0.5 text-[11px] font-semibold text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
        en_proceso:    'rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        listo:         'rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
    }
    return map[estado] ?? map.pendiente
}

function itemEstadoLabel(estado: string) {
    return { pendiente: 'Pendiente', muestra_tomada: 'Muestra tomada', en_proceso: 'En proceso', listo: 'Listo' }[estado] ?? estado
}

function formatDateTime(iso: string) {
    return new Date(iso).toLocaleString('es-VE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

// Acciones
function navigateTo(url: string, action: string) {
    if (activeAction.value) return
    activeAction.value = action
    router.visit(url, { onFinish: () => { activeAction.value = null } })
}

function generarMuestras() {
    if (activeAction.value) return
    activeAction.value = 'generar'
    router.post(`/muestras/${props.orden.id}/generar`, {}, {
        preserveScroll: true,
        onFinish: () => { activeAction.value = null },
    })
}

function tomarTodas() {
    if (activeAction.value) return
    if (!confirm('¿Marcar todas las muestras pendientes como tomadas?')) return
    activeAction.value = 'tomar-todas'
    router.patch(`/muestras/${props.orden.id}/tomar-todas`, {}, {
        preserveScroll: true,
        onFinish: () => { activeAction.value = null },
    })
}

function registrarToma(muestra: Muestra) {
    if (activeAction.value) return
    activeAction.value = `tomar:${muestra.id}`
    router.patch(
        `/muestras/${props.orden.id}/${muestra.id}/registrar-toma`,
        { observaciones: observacionesMuestra.value[muestra.id] ?? '' },
        {
            preserveScroll: true,
            onSuccess: () => { delete observacionesMuestra.value[muestra.id] },
            onFinish: () => { activeAction.value = null },
        }
    )
}

function abrirRechazo(muestra: Muestra) {
    modalRechazo.value  = muestra
    motivoRechazo.value = ''
}

function confirmarRechazo() {
    if (!modalRechazo.value || !motivoRechazo.value.trim() || activeAction.value) return
    const muestra = modalRechazo.value
    activeAction.value = `rechazar:${muestra.id}`
    router.patch(
        `/muestras/${props.orden.id}/${muestra.id}/rechazar`,
        { motivo_rechazo: motivoRechazo.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => { modalRechazo.value = null; motivoRechazo.value = '' },
            onFinish: () => { activeAction.value = null },
        }
    )
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }

.muestra-card-enter-active, .muestra-card-appear-active { transition: opacity 500ms ease, transform 500ms cubic-bezier(0.22,1,0.36,1); }
.muestra-card-enter-from,  .muestra-card-appear-from    { opacity:0; transform:translateY(20px); }

.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }

.modal-enter-active, .modal-leave-active { transition: opacity 250ms ease; }
.modal-enter-from,   .modal-leave-to     { opacity:0; }

.muestra-row-enter-from, .muestra-row-appear-from { opacity:0; transform:translateY(16px); }
.muestra-row-enter-to,   .muestra-row-appear-to   { opacity:1; transform:translateY(0); }
.muestra-row-leave-active { transition: opacity 300ms ease; }
.muestra-row-leave-to     { opacity:0; }
.muestra-row-enter-active, .muestra-row-appear-active { transition: opacity 500ms ease, transform 500ms cubic-bezier(0.22,1,0.36,1); }

.filter-panel-enter-active, .filter-panel-appear-active { transition: opacity 540ms ease, transform 540ms cubic-bezier(0.22,1,0.36,1); }
.filter-panel-enter-from,   .filter-panel-appear-from   { opacity:0; transform:translateY(32px); }
.filter-panel-enter-to,     .filter-panel-appear-to     { opacity:1; transform:translateY(0); }

@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>