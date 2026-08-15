<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Examen'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.28),_transparent_32%),linear-gradient(135deg,_#1e1b4b_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo · Examen
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ examen.nombre }}
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-sm text-white/60">{{ examen.codigo }}</span>
                                <span v-if="examen.nombre_corto" class="text-white/30">·</span>
                                <span v-if="examen.nombre_corto" class="font-mono text-sm text-white/60">{{ examen.nombre_corto }}</span>
                                <span class="text-white/30">·</span>
                                <span class="text-sm text-white/60">{{ examen.categoria }}</span>
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', examen.activo ? 'bg-emerald-400/20 text-emerald-300' : 'bg-slate-400/20 text-slate-300']">
                                    {{ examen.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/catalogo/examenes/${examen.id}/edit`, 'edit')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                {{ activeAction === 'edit' ? 'Cargando...' : 'Editar' }}
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

            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo" :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error" :message="flash.error" />
            </transition-group>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <div class="space-y-6">

                    <!-- Datos generales -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="mb-5 text-base font-semibold text-slate-900 dark:text-white">Datos del examen</h2>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div v-for="field in datosExamen" :key="field.label" class="rounded-2xl bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ field.label }}</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{{ field.value || '—' }}</dd>
                            </div>
                        </dl>
                        <div v-if="examen.instrucciones" class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 dark:bg-amber-500/10">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Instrucciones al paciente</dt>
                            <dd class="mt-1 text-sm text-amber-800 dark:text-amber-200">{{ examen.instrucciones }}</dd>
                        </div>
                    </section>

                    <!-- Valores de referencia -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Valores de referencia</h2>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ examen.valores_referencia?.length ?? 0 }} rangos
                            </span>
                        </div>

                        <div v-if="examen.valores_referencia?.length" class="w-full overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700">
                                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Etiqueta</th>
                                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Sexo</th>
                                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Edad</th>
                                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Rango normal</th>
                                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Valor texto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="vr in examen.valores_referencia" :key="vr.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="py-3 pr-4 text-sm font-medium text-slate-800 dark:text-white">{{ vr.etiqueta || '—' }}</td>
                                        <td class="py-3 pr-4">
                                            <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', sexoClass(vr.sexo)]">
                                                {{ sexoLabel(vr.sexo) }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4 text-sm text-slate-700 dark:text-slate-300">
                                            {{ vr.edad_min === 0 && vr.edad_max === 999 ? 'Todas' : `${vr.edad_min} – ${vr.edad_max} años` }}
                                        </td>
                                        <td class="py-3 pr-4 text-sm font-mono text-slate-700 dark:text-slate-300">
                                            <span v-if="vr.valor_min !== null || vr.valor_max !== null">
                                                {{ vr.valor_min ?? '?' }} – {{ vr.valor_max ?? '?' }}
                                                <span v-if="examen.unidad" class="ml-1 text-slate-400">{{ examen.unidad }}</span>
                                            </span>
                                            <span v-else class="text-slate-400">—</span>
                                        </td>
                                        <td class="py-3 text-sm text-slate-700 dark:text-slate-300">{{ vr.valor_texto || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            Este examen no tiene rangos de referencia definidos.
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Acciones</h2>
                        <div class="mt-4 space-y-2">
                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/catalogo/examenes/${examen.id}/edit`, 'edit')"
                                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ activeAction === 'edit' ? 'Cargando...' : 'Editar examen' }}
                            </button>

                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="toggleActivo"
                                :class="[
                                    'flex w-full items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                    examen.activo
                                        ? 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10'
                                        : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10'
                                ]"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ activeAction === 'toggle' ? 'Procesando...' : examen.activo ? 'Desactivar examen' : 'Activar examen' }}
                            </button>

                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="eliminar"
                                class="flex w-full items-center gap-3 rounded-2xl border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ activeAction === 'delete' ? 'Eliminando...' : 'Eliminar examen' }}
                            </button>
                        </div>
                    </section>

                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Registro del sistema</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Creado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(examen.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Modificado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(examen.updated_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">ID interno</dt>
                                <dd class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ examen.id }}</dd>
                            </div>
                        </dl>
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
import Alert from '@/components/ui/Alert.vue'

interface ValorReferencia {
    id: number
    etiqueta?: string
    sexo: 'M' | 'F' | 'ambos'
    edad_min: number
    edad_max: number
    valor_min: number | null
    valor_max: number | null
    valor_texto?: string
}

interface Examen {
    id: number
    codigo: string
    nombre: string
    nombre_corto?: string
    categoria: string
    muestra: string
    tubo?: string
    unidad?: string
    metodo?: string
    tiempo_resultado: number
    precio: string
    activo: boolean
    instrucciones?: string
    valores_referencia?: ValorReferencia[]
    created_at: string
    updated_at: string
}

const props       = defineProps<{ examen: Examen }>()
const page        = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash       = computed(() => page.props.flash ?? {})
const activeAction = ref<string | null>(null)

const statsCards = computed(() => [
    { title: 'Precio',       value: `$${Number(props.examen.precio).toFixed(2)}`,          caption: 'Precio en USD',                              delay: 0   },
    { title: 'Muestra',      value: props.examen.muestra,                                  caption: props.examen.tubo ?? 'Sin tubo especificado', delay: 90  },
    { title: 'Tiempo',       value: `${props.examen.tiempo_resultado}h`,                   caption: 'Tiempo estimado de resultado',               delay: 180 },
    { title: 'Referencias',  value: String(props.examen.valores_referencia?.length ?? 0),  caption: 'Rangos de referencia definidos',             delay: 270 },
])

const datosExamen = computed(() => [
    { label: 'Código',    value: props.examen.codigo },
    { label: 'Nombre corto', value: props.examen.nombre_corto },
    { label: 'Categoría', value: props.examen.categoria },
    { label: 'Muestra',   value: props.examen.muestra },
    { label: 'Tubo',      value: props.examen.tubo },
    { label: 'Unidad',    value: props.examen.unidad },
    { label: 'Método',    value: props.examen.metodo },
    { label: 'Tiempo de resultado', value: props.examen.tiempo_resultado ? `${props.examen.tiempo_resultado} horas` : null },
])

function sexoLabel(sexo: string) {
    return { M: 'Masculino', F: 'Femenino', ambos: 'Ambos' }[sexo] ?? sexo
}

function sexoClass(sexo: string) {
    return {
        M:     'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        F:     'bg-pink-50 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300',
        ambos: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    }[sexo] ?? ''
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

function navigateTo(url: string, action: string) {
    if (activeAction.value) return
    activeAction.value = action
    router.visit(url, { onFinish: () => { activeAction.value = null } })
}

function toggleActivo() {
    if (activeAction.value) return
    activeAction.value = 'toggle'
    router.patch(`/catalogo/examenes/${props.examen.id}/toggle-activo`, {}, {
        preserveScroll: true,
        onFinish: () => { activeAction.value = null },
    })
}

function eliminar() {
    if (activeAction.value) return
    if (!confirm(`¿Eliminar el examen "${props.examen.nombre}"?`)) return
    activeAction.value = 'delete'
    router.delete(`/catalogo/examenes/${props.examen.id}`, {
        onFinish: () => { activeAction.value = null },
    })
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>