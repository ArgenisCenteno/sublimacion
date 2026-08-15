<!-- Create.vue -->
<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Registrar Perfil'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(167,139,250,0.28),_transparent_32%),linear-gradient(135deg,_#4c1d95_0%,_#1e1b4b_58%,_#0f172a_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Registrar nuevo perfil
                            </h1>
                        </div>
                        <button
                            type="button"
                            :disabled="isNavigatingBack || form.processing"
                            @click="goToIndex"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{ isNavigatingBack ? 'Cargando...' : 'Volver al listado' }}
                        </button>
                    </div>

                    <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-3" name="hero-card" appear>
                        <article
                            v-for="card in heroCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1"
                        >
                            <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            <p class="mt-3 text-2xl font-semibold">{{ card.value }}</p>
                            <p class="mt-2 text-sm text-white/70">{{ card.caption }}</p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo" :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error" :message="flash.error" />
            </transition-group>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Datos del perfil -->
                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del perfil</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Código <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.codigo"
                                        type="text"
                                        placeholder="PERL-001"
                                        @input="form.codigo = form.codigo.toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.codigo)]"
                                    />
                                    <p v-if="form.errors.codigo" class="mt-1 text-sm text-red-500">{{ form.errors.codigo }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Categoría</label>
                                    <input
                                        v-model="form.categoria"
                                        type="text"
                                        list="categorias-list"
                                        placeholder="Perfil lipídico, Prenatal..."
                                        :class="[inputBaseClass, errorClass(form.errors.categoria)]"
                                    />
                                    <datalist id="categorias-list">
                                        <option v-for="cat in props.categorias" :key="cat" :value="cat" />
                                    </datalist>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        placeholder="Perfil lipídico completo"
                                        :class="[inputBaseClass, errorClass(form.errors.nombre)]"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio del perfil (USD) <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.precio"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="15.00"
                                        :class="[inputBaseClass, errorClass(form.errors.precio)]"
                                    />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-500">{{ form.errors.precio }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="flex h-12 cursor-pointer items-center gap-3">
                                        <div
                                            @click="form.activo = !form.activo"
                                            :class="['relative h-6 w-11 rounded-full transition duration-300', form.activo ? 'bg-violet-600' : 'bg-slate-300 dark:bg-slate-700']"
                                        >
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
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Selecciona los exámenes que componen este perfil.</p>
                                </div>
                                <span :class="[
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    form.examenes_ids.length
                                        ? 'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                ]">
                                    {{ form.examenes_ids.length }} seleccionados
                                </span>
                            </div>

                            <!-- Búsqueda de exámenes -->
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

                            <!-- Lista agrupada por categoría -->
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
                                                :class="[
                                                    'flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 transition',
                                                    isSelected(examen.id)
                                                        ? 'bg-violet-50 dark:bg-violet-500/10'
                                                        : 'hover:bg-slate-100 dark:hover:bg-slate-700/60',
                                                ]"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div :class="[
                                                        'flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md border-2 transition',
                                                        isSelected(examen.id)
                                                            ? 'border-violet-600 bg-violet-600'
                                                            : 'border-slate-300 dark:border-slate-600',
                                                    ]">
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
                                                    <input
                                                        type="checkbox"
                                                        :value="examen.id"
                                                        v-model="form.examenes_ids"
                                                        class="sr-only"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p v-if="form.errors.examenes_ids" class="mt-1 text-sm text-red-500">{{ form.errors.examenes_ids }}</p>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                            <button
                                type="button"
                                :disabled="form.processing || isNavigatingBack"
                                @click="goToIndex"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing || isNavigatingBack"
                                class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Registrar perfil' }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Resumen</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Código</dt>
                                <dd class="font-mono font-semibold text-slate-800 dark:text-white">{{ form.codigo || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Nombre</dt>
                                <dd class="text-right font-medium text-slate-800 dark:text-white">{{ form.nombre || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Precio perfil</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">
                                    {{ form.precio ? `$${Number(form.precio).toFixed(2)}` : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Precio individual</dt>
                                <dd class="font-semibold text-slate-500 dark:text-slate-400">
                                    ${{ precioIndividual.toFixed(2) }}
                                </dd>
                            </div>
                            <div v-if="ahorro > 0" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Ahorro</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400">
                                    ${{ ahorro.toFixed(2) }}
                                </dd>
                            </div>
                            <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                                <dt class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Exámenes seleccionados</dt>
                                <div v-if="examenesSeleccionados.length" class="space-y-1">
                                    <div v-for="ex in examenesSeleccionados" :key="ex.id" class="flex items-center justify-between text-xs">
                                        <span class="text-slate-700 dark:text-slate-300">{{ ex.nombre }}</span>
                                        <span class="font-mono text-slate-500">${{ Number(ex.precio).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-slate-400 dark:text-slate-500">Ninguno seleccionado.</p>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-violet-200 bg-violet-50 p-6 dark:border-violet-500/20 dark:bg-violet-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-violet-900 dark:text-violet-200">Notas</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-violet-800 dark:text-violet-100/80">
                            <li>El código debe ser único. Usa un formato como PERL-001.</li>
                            <li>El precio del perfil puede ser menor que la suma de los exámenes individuales para ofrecer descuento.</li>
                            <li>Solo se muestran exámenes activos en el catálogo.</li>
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

interface Props {
    examenes: Examen[]
    categorias: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)
const busquedaExamen   = ref('')

const form = useForm({
    codigo:       '',
    nombre:       '',
    categoria:    '',
    precio:       '',
    activo:       true,
    examenes_ids: [] as number[],
})

const heroCards = [
    { title: 'Paso 1', value: 'Perfil',    caption: 'Código, nombre y precio del paquete.',        delay: 0   },
    { title: 'Paso 2', value: 'Exámenes',  caption: 'Selecciona los exámenes que lo componen.',    delay: 90  },
    { title: 'Paso 3', value: 'Catálogo',  caption: 'El perfil quedará disponible para órdenes.',  delay: 180 },
]

const examenesFiltrados = computed(() => {
    const q = busquedaExamen.value.trim().toLowerCase()
    if (!q) return props.examenes
    return props.examenes.filter(e =>
        e.nombre.toLowerCase().includes(q) ||
        e.codigo.toLowerCase().includes(q) ||
        e.categoria.toLowerCase().includes(q)
    )
})

const examenesPorCategoria = computed(() => {
    return examenesFiltrados.value.reduce((acc, examen) => {
        const cat = examen.categoria
        if (!acc[cat]) acc[cat] = []
        acc[cat].push(examen)
        return acc
    }, {} as Record<string, Examen[]>)
})

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

function goToIndex() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit('/catalogo/perfiles', { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.post('/catalogo/perfiles')
}
</script>

<style scoped>
.panel-float      { animation: panelFloat  720ms cubic-bezier(0.22,1,0.36,1) both; }
.field-reveal     { animation: fieldReveal 520ms ease-out both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat  { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes fieldReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>