<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Resultados — ${orden.codigo}`" />

        <div class="space-y-6">
            <div v-if="flash.success || flash.warning || flash.error" class="space-y-3">
                <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ flash.success }}</div>
                <div v-if="flash.warning" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">{{ flash.warning }}</div>
                <div v-if="flash.error"   class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">{{ flash.error }}</div>
                <div v-if="errors.validacion" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">{{ errors.validacion }}</div>
            </div>

            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.28),_transparent_32%),linear-gradient(135deg,_#1e1b4b_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Laboratorio · Resultados
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ orden.paciente?.apellidos }}, {{ orden.paciente?.nombres }}
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-sm text-white/60">{{ orden.codigo }}</span>
                                <span class="text-white/30">·</span>
                                <span class="text-sm text-white/60">{{ orden.paciente?.edad }} años · {{ orden.paciente?.sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
                                <span v-if="orden.medico" class="text-white/30">·</span>
                                <span v-if="orden.medico" class="text-sm text-white/60">{{ orden.medico.nombre_completo }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <!-- Guardar todos -->
                            <button
                                type="button"
                                :disabled="Boolean(activeAction) || !hayResultadosPendientes"
                                @click="guardarTodos"
                                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg v-if="activeAction === 'guardar-todos'" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                {{ activeAction === 'guardar-todos' ? 'Guardando...' : 'Guardar todos' }}
                            </button>

                            <!-- Validar -->
                            <button
                                v-if="todosConResultado"
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="validar"
                                class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-emerald-400"
                            >
                                <svg v-if="activeAction === 'validar'" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ activeAction === 'validar' ? 'Validando...' : 'Validar y cerrar' }}
                            </button>

                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo('/resultados', 'volver')"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                {{ activeAction === 'volver' ? 'Cargando...' : 'Volver' }}
                            </button>
                        </div>
                    </div>

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

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.7fr)]">

                <!-- Tabla de resultados -->
                <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Exámenes</h2>
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ itemsConResultado }} / {{ orden.items?.length ?? 0 }} con resultado
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="item in orden.items"
                            :key="item.id"
                            :class="[
                                'rounded-[20px] border p-4 transition',
                                item.estado === 'listo'
                                    ? item.fuera_de_rango
                                        ? item.indicador === 'critico'
                                            ? 'border-rose-300 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10'
                                            : 'border-amber-200 bg-amber-50 dark:border-amber-500/25 dark:bg-amber-500/10'
                                        : 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/25 dark:bg-emerald-500/10'
                                    : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60',
                            ]"
                        >
                            <!-- Header del examen -->
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ item.examen?.nombre }}</p>
                                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ item.examen?.codigo }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Indicador de rango -->
                                    <span v-if="item.estado === 'listo' && item.fuera_de_rango" :class="indicadorClass(item.indicador)">
                                        {{ indicadorLabel(item.indicador) }}
                                    </span>
                                    <span v-else-if="item.estado === 'listo'" class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                        Normal
                                    </span>
                                    <span v-else class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        Pendiente
                                    </span>
                                </div>
                            </div>

                            <!-- Referencia -->
                            <div v-if="item.referencia" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-medium">Referencia:</span>
                                <span v-if="item.referencia.valor_texto"> {{ item.referencia.valor_texto }}</span>
                                <span v-else-if="item.referencia.valor_min !== null || item.referencia.valor_max !== null">
                                    {{ item.referencia.valor_min ?? '?' }} – {{ item.referencia.valor_max ?? '?' }}
                                    <span v-if="item.examen?.unidad"> {{ item.examen.unidad }}</span>
                                    <span v-if="item.referencia.etiqueta" class="ml-1 text-slate-400">({{ item.referencia.etiqueta }})</span>
                                </span>
                            </div>

                            <!-- Input de resultado -->
                            <div class="mt-3 flex items-center gap-3">
                                <!-- Resultado numérico -->
                                <div v-if="item.examen?.unidad" class="flex flex-1 items-center gap-2">
                                    <input
                                        v-model="resultados[item.id]"
                                        type="number"
                                        step="0.0001"
                                        :placeholder="item.resultado_valor ?? 'Valor numérico'"
                                        :disabled="item.estado === 'listo'"
                                        :class="[
                                            'h-10 flex-1 rounded-xl border bg-white px-3 py-2 text-sm font-mono text-slate-800 transition focus:outline-hidden focus:ring-4 dark:bg-slate-900 dark:text-white/90',
                                            item.estado === 'listo'
                                                ? 'cursor-not-allowed border-slate-200 opacity-70 dark:border-slate-700'
                                                : 'border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-600 dark:focus:border-indigo-800',
                                        ]"
                                    />
                                    <span v-if="item.examen?.unidad" class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ item.examen.unidad }}
                                    </span>
                                </div>

                                <!-- Resultado texto -->
                                <input
                                    v-else
                                    v-model="resultadosTexto[item.id]"
                                    type="text"
                                    :placeholder="item.resultado_texto ?? 'Ej: Negativo, Reactivo...'"
                                    :disabled="item.estado === 'listo'"
                                    :class="[
                                        'h-10 flex-1 rounded-xl border bg-white px-3 py-2 text-sm text-slate-800 transition focus:outline-hidden focus:ring-4 dark:bg-slate-900 dark:text-white/90',
                                        item.estado === 'listo'
                                            ? 'cursor-not-allowed border-slate-200 opacity-70 dark:border-slate-700'
                                            : 'border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-600',
                                    ]"
                                />

                                <!-- Botón guardar individual -->
                                <button
                                    v-if="item.estado !== 'listo'"
                                    type="button"
                                    :disabled="Boolean(activeAction) || (!resultados[item.id] && !resultadosTexto[item.id])"
                                    @click="guardarIndividual(item)"
                                    class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300"
                                >
                                    <svg v-if="activeAction === `guardar:${item.id}`" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ activeAction === `guardar:${item.id}` ? '...' : 'Guardar' }}
                                </button>

                                <!-- Botón descartar si ya tiene resultado -->
                                <button
                                    v-if="item.estado === 'listo' && orden.estado !== 'listo'"
                                    type="button"
                                    :disabled="Boolean(activeAction)"
                                    @click="descartar(item)"
                                    class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-rose-200 px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-50 dark:border-rose-500/30 dark:text-rose-400"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Descartar
                                </button>
                            </div>

                            <!-- Resultado actual si ya está cargado -->
                            <div v-if="item.estado === 'listo'" class="mt-2 flex items-center gap-2">
                                <span class="text-lg font-bold" :class="item.fuera_de_rango ? (item.indicador === 'critico' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-800 dark:text-white'">
                                    {{ item.resultado_valor ?? item.resultado_texto }}
                                </span>
                                <span v-if="item.resultado_valor && item.examen?.unidad" class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ item.examen.unidad }}
                                </span>
                                <span v-if="item.procesado_por" class="ml-auto text-xs text-slate-400 dark:text-slate-500">
                                    por {{ item.procesado_por.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <!-- Datos del paciente -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Paciente</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Nombre</dt>
                                <dd class="mt-0.5 font-semibold text-slate-800 dark:text-white">{{ orden.paciente?.apellidos }}, {{ orden.paciente?.nombres }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Cédula</dt>
                                <dd class="mt-0.5 font-mono text-slate-700 dark:text-slate-300">{{ orden.paciente?.tipo_documento }}-{{ orden.paciente?.cedula }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Edad</dt>
                                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ orden.paciente?.edad }} años</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Sexo</dt>
                                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ orden.paciente?.sexo === 'M' ? 'Masc.' : 'Fem.' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </section>

                    <!-- Progreso -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Progreso</h2>

                        <!-- Barra de progreso -->
                        <div class="mb-3 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-2.5 rounded-full bg-indigo-500 transition-all duration-500"
                                :style="{ width: `${progreso}%` }"
                            />
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ itemsConResultado }} de {{ orden.items?.length ?? 0 }} exámenes completados
                        </p>

                        <div class="mt-4 space-y-2">
                            <div v-for="item in orden.items" :key="`prog-${item.id}`" class="flex items-center justify-between text-sm">
                                <span class="truncate text-slate-600 dark:text-slate-400" style="max-width:160px">{{ item.examen?.nombre }}</span>
                                <span :class="['text-xs font-semibold', item.estado === 'listo' ? (item.fuera_de_rango ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') : 'text-slate-400']">
                                    {{ item.estado === 'listo' ? (item.fuera_de_rango ? '⚠ Fuera' : '✓ Normal') : '—' }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Notas -->
                    <section v-if="!todosConResultado" class="panel-float rounded-[28px] border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-500/20 dark:bg-indigo-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-indigo-900 dark:text-indigo-200">Flujo de trabajo</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-indigo-800 dark:text-indigo-100/80">
                            <li>Carga el resultado de cada examen individualmente o usa "Guardar todos" al final.</li>
                            <li>El sistema marcará automáticamente los valores fuera del rango de referencia.</li>
                            <li>Una vez completados todos, aparecerá el botón "Validar y cerrar".</li>
                        </ul>
                    </section>

                    <!-- Listo para validar -->
                    <section v-if="todosConResultado && orden.estado !== 'listo'" class="panel-float rounded-[28px] border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-500/20 dark:bg-emerald-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-emerald-900 dark:text-emerald-200">Listo para validar</h2>
                        <p class="mt-2 text-sm text-emerald-800 dark:text-emerald-100/80">Todos los resultados están cargados. Haz clic en "Validar y cerrar" para marcar la orden como lista.</p>
                        <button
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="validar"
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-60"
                        >
                            <svg v-if="activeAction === 'validar'" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ activeAction === 'validar' ? 'Validando...' : 'Validar y cerrar orden' }}
                        </button>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface Referencia {
    valor_min?: number | null
    valor_max?: number | null
    valor_texto?: string | null
    etiqueta?: string | null
}

interface Examen { id: number; nombre: string; codigo: string; unidad?: string; }
interface ProcessedBy { id: number; name: string; }

interface OrdenItem {
    id: number
    estado: string
    precio: string
    resultado_valor?: number | null
    resultado_texto?: string | null
    fuera_de_rango: boolean
    indicador?: string | null
    examen?: Examen
    referencia?: Referencia | null
    procesado_por?: ProcessedBy | null
}

interface Paciente { id: number; nombres: string; apellidos: string; cedula: string; tipo_documento: string; edad: number; sexo: 'M'|'F'; es_menor: boolean; }
interface Medico { id: number; nombre_completo: string; }

interface OrdenTrabajo {
    id: number
    codigo: string
    estado: string
    estado_label: string
    paciente?: Paciente
    medico?: Medico
    items?: OrdenItem[]
}

const props        = defineProps<{ orden: OrdenTrabajo }>()
const page         = usePage<{ flash?: { success?: string|null; warning?: string|null; error?: string|null }; errors?: Record<string,string> }>()
const flash        = computed(() => page.props.flash ?? {})
const errors       = computed(() => page.props.errors ?? {})
const activeAction = ref<string | null>(null)

// Estado local de inputs
const resultados      = ref<Record<number, string>>({})
const resultadosTexto = ref<Record<number, string>>({})

// Computed
const itemsConResultado = computed(() =>
    (props.orden.items ?? []).filter(i =>
        i.estado === 'listo' ||
        i.resultado_valor !== null ||
        (i.resultado_texto !== null && i.resultado_texto !== '')
    ).length
)

// DESPUÉS — aparece cuando todos tienen valor, aunque estén en 'en_proceso'
const todosConResultado = computed(() => {
    const items = props.orden.items ?? []
    if (items.length === 0) return false
    return items.every(i =>
        i.estado === 'listo' ||
        i.resultado_valor !== null ||
        (i.resultado_texto !== null && i.resultado_texto !== '')
    )
})

const hayResultadosPendientes = computed(() =>
    Object.keys(resultados.value).some(k => resultados.value[+k]) ||
    Object.keys(resultadosTexto.value).some(k => resultadosTexto.value[+k])
)

const progreso = computed(() => {
    const total = props.orden.items?.length ?? 0
    if (total === 0) return 0
    return Math.round((itemsConResultado.value / total) * 100)
})

const statsCards = computed(() => [
    { title: 'Total exámenes', value: String(props.orden.items?.length ?? 0),      caption: 'En esta orden',              delay: 0   },
    { title: 'Completados',    value: String(itemsConResultado.value),              caption: 'Con resultado cargado',      delay: 90  },
    { title: 'Fuera de rango', value: String((props.orden.items ?? []).filter(i => i.fuera_de_rango).length), caption: 'Valores anormales', delay: 180 },
    { title: 'Progreso',       value: `${progreso.value}%`,                        caption: 'De la orden completado',     delay: 270 },
])

// Labels y clases
function indicadorClass(indicador?: string | null) {
    const map: Record<string, string> = {
        alto:    'rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        bajo:    'rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
        critico: 'rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    }
    return map[indicador ?? ''] ?? ''
}

function indicadorLabel(indicador?: string | null) {
    return { alto: 'Alto', bajo: 'Bajo', critico: 'Crítico' }[indicador ?? ''] ?? ''
}

// Acciones
function navigateTo(url: string, action: string) {
    if (activeAction.value) return
    activeAction.value = action
    router.visit(url, { onFinish: () => { activeAction.value = null } })
}


function guardarIndividual(item: OrdenItem) {
    if (activeAction.value) return
    const valor = resultados.value[item.id]
    const texto = resultadosTexto.value[item.id]
    if (!valor && !texto) return

    activeAction.value = `guardar:${item.id}`
    router.patch(
        `/resultados/${props.orden.id}/items/${item.id}/cargar`,
        { resultado_valor: valor || null, resultado_texto: texto || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                delete resultados.value[item.id]
                delete resultadosTexto.value[item.id]
            },
            onFinish: () => { activeAction.value = null },
        }
    )
}

function guardarTodos() {
    if (activeAction.value) return
    const pendientes = (props.orden.items ?? [])
        .filter(i => i.estado !== 'listo')
        .map(i => ({
            item_id:          i.id,
            resultado_valor:  resultados.value[i.id] ?? null,
            resultado_texto:  resultadosTexto.value[i.id] ?? null,
        }))
        .filter(r => r.resultado_valor !== null || r.resultado_texto !== null)

    if (!pendientes.length) return

    activeAction.value = 'guardar-todos'
    router.post(
        `/resultados/${props.orden.id}/cargar-todos`,
        { resultados: pendientes },
        {
            preserveScroll: true,
            onSuccess: () => {
                resultados.value      = {}
                resultadosTexto.value = {}
            },
            onFinish: () => { activeAction.value = null },
        }
    )
}

function validar() {
    if (activeAction.value) return
    activeAction.value = 'validar'
    router.post(
        `/resultados/${props.orden.id}/validar`,
        {},
        {
            preserveScroll: true,
            onFinish: () => { activeAction.value = null },
        }
    )
}

function descartar(item: OrdenItem) {
    if (activeAction.value) return
    if (!confirm(`¿Descartar el resultado de "${item.examen?.nombre}"?`)) return
    activeAction.value = `descartar:${item.id}`
    router.patch(
        `/resultados/${props.orden.id}/items/${item.id}/descartar`,
        {},
        {
            preserveScroll: true,
            onFinish: () => { activeAction.value = null },
        }
    )
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>