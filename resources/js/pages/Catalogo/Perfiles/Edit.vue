<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Editar Perfil'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(192,132,252,0.28),_transparent_32%),linear-gradient(135deg,_#581c87_0%,_#1e1b4b_58%,_#0f172a_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo · Editar
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ perfil.nombre }}
                            </h1>
                            <p class="mt-2 font-mono text-sm text-white/60">{{ perfil.codigo }}</p>
                        </div>
                        <button
                            type="button"
                            :disabled="isNavigatingBack || form.processing"
                            @click="goToShow"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{ isNavigatingBack ? 'Cargando...' : 'Ver perfil' }}
                        </button>
                    </div>
                </div>
            </section>

            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo" :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error" :message="flash.error" />
            </transition-group>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del perfil</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Código <span class="text-red-500">*</span></label>
                                    <input v-model="form.codigo" type="text" @input="form.codigo = form.codigo.toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.codigo)]" />
                                    <p v-if="form.errors.codigo" class="mt-1 text-sm text-red-500">{{ form.errors.codigo }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Categoría</label>
                                    <input v-model="form.categoria" type="text" list="categorias-list" :class="[inputBaseClass, errorClass(form.errors.categoria)]" />
                                    <datalist id="categorias-list">
                                        <option v-for="cat in props.categorias" :key="cat" :value="cat" />
                                    </datalist>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre <span class="text-red-500">*</span></label>
                                    <input v-model="form.nombre" type="text" :class="[inputBaseClass, errorClass(form.errors.nombre)]" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio (USD) <span class="text-red-500">*</span></label>
                                    <input v-model="form.precio" type="number" min="0" step="0.01" :class="[inputBaseClass, errorClass(form.errors.precio)]" />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-500">{{ form.errors.precio }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="flex h-12 cursor-pointer items-center gap-3">
                                        <div @click="form.activo = !form.activo" :class="['relative h-6 w-11 rounded-full transition duration-300', form.activo ? 'bg-violet-600' : 'bg-slate-300 dark:bg-slate-700']">
                                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-300', form.activo ? 'translate-x-5' : 'translate-x-0']" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Perfil activo en el catálogo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de exámenes -->
                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Exámenes del perfil</h2>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Los exámenes existentes se reemplazarán al guardar.</p>
                                </div>
                                <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', form.examenes_ids.length ? 'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400']">
                                    {{ form.examenes_ids.length }} seleccionados
                                </span>
                            </div>

                            <div class="mb-3 relative">
                                <input
                                    v-model="busquedaExamen"
                                    type="text"
                                    placeholder="Filtrar exámenes..."
                                    class="h-10 w-full rounded-2xl border border-slate-300 bg-transparent px-4 py-2 pl-10 text-sm text-slate-800 placeholder:text-slate-400 focus:border-violet-300 focus:ring-4 focus:ring-violet-500/10 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white/90"
                                />
                                <svg class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <div class="max-h-80 space-y-3 overflow-y-auto rounded-[20px] border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/40">
                                <div v-if="!examenesFiltrados.length" class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                    No se encontraron exámenes.
                                </div>
                                <template v-for="(grupo, categoria) in examenesPorCategoria" :key="categoria">
                                    <div>
                                        <p class="mb-1.5 px-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ categoria }}</p>
                                        <div class="space-y-1">
                                            <label
                                                v-for="examen in grupo"
                                                :key="examen.id"
                                                :class="['flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 transition', isSelected(examen.id) ? 'bg-violet-50 dark:bg-violet-500/10' : 'hover:bg-slate-100 dark:hover:bg-slate-700/60']"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div :class="['flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md border-2 transition', isSelected(examen.id) ? 'border-violet-600 bg-violet-600' : 'border-slate-300 dark:border-slate-600']">
                                                        <svg v-if="isSelected(examen.id)" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-800 dark:text-white">{{ examen.nombre }}</p>
                                                        <p class="font-mono text-xs text-slate-400">{{ examen.codigo }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">${{ Number(examen.precio).toFixed(2) }}</span>
                                                    <input type="checkbox" :value="examen.id" v-model="form.examenes_ids" class="sr-only" />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                            <button type="button" :disabled="form.processing" @click="goToShow"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing || isNavigatingBack"
                                class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-60">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="space-y-6">
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Resumen</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Precio perfil</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">{{ form.precio ? `$${Number(form.precio).toFixed(2)}` : '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Precio individual</dt>
                                <dd class="font-semibold text-slate-500 dark:text-slate-400">${{ precioIndividual.toFixed(2) }}</dd>
                            </div>
                            <div v-if="ahorro > 0" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Ahorro</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400">${{ ahorro.toFixed(2) }}</dd>
                            </div>
                            <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                                <dt class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Exámenes</dt>
                                <div v-if="examenesSeleccionados.length" class="space-y-1">
                                    <div v-for="ex in examenesSeleccionados" :key="ex.id" class="flex items-center justify-between text-xs">
                                        <span class="text-slate-700 dark:text-slate-300">{{ ex.nombre }}</span>
                                        <span class="font-mono text-slate-500">${{ Number(ex.precio).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-slate-400">Ninguno seleccionado.</p>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Información actual</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Creado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(perfil.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Modificado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(perfil.updated_at) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/20 dark:bg-amber-500/10" style="animation-delay:270ms">
                        <h2 class="text-base font-semibold text-amber-900 dark:text-amber-200">Atención</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-amber-800 dark:text-amber-100/80">
                            <li>Al guardar, los exámenes anteriores del perfil serán reemplazados.</li>
                            <li>Las órdenes ya creadas con este perfil no se ven afectadas.</li>
                        </ul>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3'
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

interface Props {
    perfil: Perfil
    examenes: Examen[]
    categorias: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)
const busquedaExamen   = ref('')

const form = useForm({
    codigo:       props.perfil.codigo,
    nombre:       props.perfil.nombre,
    categoria:    props.perfil.categoria ?? '',
    precio:       props.perfil.precio,
    activo:       props.perfil.activo,
    examenes_ids: (props.perfil.examenes ?? []).map(e => e.id),
})

const examenesFiltrados = computed(() => {
    const q = busquedaExamen.value.trim().toLowerCase()
    if (!q) return props.examenes
    return props.examenes.filter(e =>
        e.nombre.toLowerCase().includes(q) ||
        e.codigo.toLowerCase().includes(q) ||
        e.categoria.toLowerCase().includes(q)
    )
})

const examenesPorCategoria = computed(() =>
    examenesFiltrados.value.reduce((acc, examen) => {
        const cat = examen.categoria
        if (!acc[cat]) acc[cat] = []
        acc[cat].push(examen)
        return acc
    }, {} as Record<string, Examen[]>)
)

const examenesSeleccionados = computed(() =>
    props.examenes.filter(e => form.examenes_ids.includes(e.id))
)

const precioIndividual = computed(() =>
    examenesSeleccionados.value.reduce((sum, e) => sum + Number(e.precio), 0)
)

const ahorro = computed(() => {
    if (!form.precio) return 0
    return Math.max(0, precioIndividual.value - Number(form.precio))
})

function isSelected(id: number) {
    return form.examenes_ids.includes(id)
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-violet-300 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-violet-800'
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

function goToShow() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit(`/catalogo/perfiles/${props.perfil.id}`, { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.put(`/catalogo/perfiles/${props.perfil.id}`)
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.field-reveal { animation: fieldReveal 520ms ease-out both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat  { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes fieldReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>