<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Perfil'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(167,139,250,0.28),_transparent_32%),linear-gradient(135deg,_#4c1d95_0%,_#1e1b4b_58%,_#0f172a_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo · Perfil
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ perfil.nombre }}
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-sm text-white/60">{{ perfil.codigo }}</span>
                                <span v-if="perfil.categoria" class="text-white/30">·</span>
                                <span v-if="perfil.categoria" class="text-sm text-white/60">{{ perfil.categoria }}</span>
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', perfil.activo ? 'bg-emerald-400/20 text-emerald-300' : 'bg-slate-400/20 text-slate-300']">
                                    {{ perfil.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/catalogo/perfiles/${perfil.id}/edit`, 'edit')"
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

                    <!-- Exámenes del perfil -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Exámenes incluidos</h2>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ perfil.examenes?.length ?? 0 }} exámenes
                            </span>
                        </div>

                        <div v-if="perfil.examenes?.length" class="space-y-2">
                            <div
                                v-for="examen in perfil.examenes"
                                :key="examen.id"
                                class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 transition hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-[linear-gradient(135deg,_#ede9fe_0%,_#ddd6fe_100%)] text-violet-700 dark:bg-[linear-gradient(135deg,_rgba(139,92,246,0.18)_0%,_rgba(167,139,250,0.18)_100%)] dark:text-violet-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ examen.nombre }}</p>
                                        <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ examen.codigo }} · {{ examen.categoria }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        ${{ Number(examen.precio).toFixed(2) }}
                                    </span>
                                    <button
                                        type="button"
                                        :disabled="Boolean(activeAction)"
                                        @click="navigateTo(`/catalogo/examenes/${examen.id}`, `examen:${examen.id}`)"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400"
                                    >
                                        <svg v-if="activeAction === `examen:${examen.id}`" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <svg v-else class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Totales -->
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Precio individual (suma)</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">${{ perfil.precio_individual.toFixed(2) }}</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-900 dark:text-white">Precio del perfil</span>
                                    <span class="text-lg font-bold text-violet-700 dark:text-violet-400">${{ Number(perfil.precio).toFixed(2) }}</span>
                                </div>
                                <div v-if="perfil.ahorro > 0" class="mt-2 flex items-center justify-between text-sm">
                                    <span class="text-emerald-600 dark:text-emerald-400">Ahorro para el paciente</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">${{ perfil.ahorro.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            Este perfil no tiene exámenes asignados.
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
                                @click="navigateTo(`/catalogo/perfiles/${perfil.id}/edit`, 'edit')"
                                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ activeAction === 'edit' ? 'Cargando...' : 'Editar perfil' }}
                            </button>

                            <button
                                type="button"
                                :disabled="Boolean(activeAction)"
                                @click="toggleActivo"
                                :class="['flex w-full items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold transition', perfil.activo ? 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10']"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ activeAction === 'toggle' ? 'Procesando...' : perfil.activo ? 'Desactivar perfil' : 'Activar perfil' }}
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
                                {{ activeAction === 'delete' ? 'Eliminando...' : 'Eliminar perfil' }}
                            </button>
                        </div>
                    </section>

                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Registro del sistema</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Creado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(perfil.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Modificado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(perfil.updated_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">ID interno</dt>
                                <dd class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ perfil.id }}</dd>
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

interface Examen {
    id: number
    codigo: string
    nombre: string
    categoria: string
    precio: string
}

interface Perfil {
    id: number
    codigo: string
    nombre: string
    categoria?: string
    precio: string
    activo: boolean
    examenes?: Examen[]
    precio_individual: number
    ahorro: number
    created_at: string
    updated_at: string
}

const props       = defineProps<{ perfil: Perfil }>()
const page        = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash       = computed(() => page.props.flash ?? {})
const activeAction = ref<string | null>(null)

const statsCards = computed(() => [
    { title: 'Precio perfil',    value: `$${Number(props.perfil.precio).toFixed(2)}`,          caption: 'Precio en USD',                    delay: 0   },
    { title: 'Precio individual',value: `$${props.perfil.precio_individual.toFixed(2)}`,       caption: 'Suma de exámenes por separado',    delay: 90  },
    { title: 'Ahorro',           value: `$${props.perfil.ahorro.toFixed(2)}`,                  caption: 'Descuento vs precio individual',   delay: 180 },
    { title: 'Exámenes',         value: String(props.perfil.examenes?.length ?? 0),            caption: 'Incluidos en el perfil',           delay: 270 },
])

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
    router.patch(`/catalogo/perfiles/${props.perfil.id}/toggle-activo`, {}, {
        preserveScroll: true,
        onFinish: () => { activeAction.value = null },
    })
}

function eliminar() {
    if (activeAction.value) return
    if (!confirm(`¿Eliminar el perfil "${props.perfil.nombre}"?`)) return
    activeAction.value = 'delete'
    router.delete(`/catalogo/perfiles/${props.perfil.id}`, {
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