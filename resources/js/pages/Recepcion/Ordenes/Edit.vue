<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Editar Orden ${orden.codigo}`" />

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
                                Editar orden {{ orden.codigo }}
                            </h1>
                            <p class="mt-2 text-sm text-white/60">
                                Paciente: {{ orden.paciente?.nombres }} {{ orden.paciente?.apellidos }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                :disabled="isNavigatingBack || form.processing"
                                @click="goToShow"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                Ver orden
                            </button>
                            <button
                                type="button"
                                :disabled="isNavigatingBack || form.processing"
                                @click="goToIndex"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                            >
                                {{ isNavigatingBack ? 'Cargando...' : 'Volver al listado' }}
                            </button>
                        </div>
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

            <!-- Flash -->
            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo"  :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error"  :message="flash.error" />
            </transition-group>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">

                <!-- Formulario principal -->
                <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                    <form @submit.prevent="submit" class="space-y-8">

                        <!-- Paciente (solo lectura) -->
                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Paciente</h2>
                            <div class="flex items-center justify-between rounded-2xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
                                <div>
                                    <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                                        {{ orden.paciente?.nombres }} {{ orden.paciente?.apellidos }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-indigo-700 dark:text-indigo-300">
                                        CI: {{ orden.paciente?.cedula }} · {{ orden.paciente?.edad }} años · {{ orden.paciente?.sexo === 'M' ? 'Masculino' : 'Femenino' }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                                    No editable
                                </span>
                            </div>
                        </div>

                        <!-- Datos generales -->
                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos de la orden</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Médico solicitante</label>
                                    <select v-model="form.medico_id" :class="[inputBaseClass, errorClass(form.errors.medico_id)]">
                                        <option value="">Sin médico asignado</option>
                                        <option v-for="m in props.medicos" :key="m.id" :value="m.id">
                                            {{ m.nombre_completo }} — {{ m.especialidad }}
                                        </option>
                                    </select>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Fecha de entrega estimada</label>
                                    <input
                                        v-model="form.fecha_entrega_estimada"
                                        type="datetime-local"
                                        :class="[inputBaseClass, errorClass(form.errors.fecha_entrega_estimada)]"
                                    />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Observaciones</label>
                                    <textarea
                                        v-model="form.observaciones"
                                        rows="2"
                                        placeholder="Indicaciones especiales, urgencias..."
                                        :class="[inputBaseClass, 'h-auto resize-none py-3']"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Exámenes -->
                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Exámenes solicitados</h2>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Puedes añadir o quitar exámenes.</p>
                                </div>
                            </div>

                            <!-- Buscador -->
                            <div class="mb-4">
                                <input
                                    v-model="busquedaExamen"
                                    type="text"
                                    placeholder="Añadir examen por nombre o código..."
                                    :class="[inputBaseClass]"
                                />
                                <ul v-if="examensFiltrados.length && busquedaExamen" class="mt-1 max-h-56 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                                    <li
                                        v-for="ex in examensFiltrados"
                                        :key="ex.id"
                                        @click="agregarExamen(ex)"
                                        class="flex cursor-pointer items-center justify-between px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-500/10"
                                    >
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 dark:text-white">{{ ex.nombre }}</p>
                                            <p class="text-xs text-slate-500">{{ ex.codigo }} · {{ ex.categoria }}</p>
                                        </div>
                                        <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">${{ Number(ex.precio).toFixed(2) }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Lista actual -->
                            <div v-if="form.items.length" class="space-y-2">
                                <div
                                    v-for="(item, idx) in form.items"
                                    :key="idx"
                                    class="flex items-center justify-between rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-white">{{ item.nombre }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ item.codigo }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">${{ Number(item.precio).toFixed(2) }}</span>
                                        <button
                                            type="button"
                                            @click="quitarItem(idx)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                <p class="text-sm text-slate-500 dark:text-slate-400">No hay exámenes. Añade al menos uno.</p>
                            </div>
                            <p v-if="form.errors.items" class="mt-2 text-sm text-red-500">{{ form.errors.items }}</p>

                            <!-- Descuento -->
                            <div class="mt-4 flex justify-end">
                                <div class="w-full max-w-xs">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Descuento (USD)</label>
                                    <input
                                        v-model="form.descuento"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        :class="[inputBaseClass]"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                            <button
                                type="button"
                                :disabled="form.processing || isNavigatingBack"
                                @click="goToShow"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing || isNavigatingBack"
                                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
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
                                <dd class="font-mono font-semibold text-slate-800 dark:text-white">{{ orden.codigo }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Exámenes</dt>
                                <dd>
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', form.items.length ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400']">
                                        {{ form.items.length }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Subtotal</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">${{ subtotal.toFixed(2) }}</dd>
                            </div>
                            <div v-if="Number(form.descuento) > 0" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Descuento</dt>
                                <dd class="font-semibold text-rose-600">-${{ Number(form.descuento).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                                <dt class="font-semibold text-slate-900 dark:text-white">Total</dt>
                                <dd class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${{ total.toFixed(2) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/20 dark:bg-amber-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-amber-900 dark:text-amber-200">Atención</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-amber-800 dark:text-amber-100/80">
                            <li>Solo se pueden editar órdenes en estado <strong>pendiente</strong>.</li>
                            <li>Al guardar, los ítems anteriores se reemplazarán por los nuevos.</li>
                            <li>El paciente no se puede cambiar una vez creada la orden.</li>
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
    nombre: string
    codigo: string
    categoria: string
    precio: number
    tiempo_resultado: number
}

interface Medico {
    id: number
    nombre: string
    apellido: string
    especialidad: string
    nombre_completo: string
}

interface Props {
    orden: any
    examenes: Examen[]
    medicos: Medico[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})

const isNavigatingBack = ref(false)
const busquedaExamen   = ref('')

// Inicializar los ítems desde la orden existente
const itemsIniciales = (props.orden.items ?? []).map((item: any) => ({
    examen_id: item.examen_id,
    precio:    item.precio,
    nombre:    item.examen?.nombre ?? '',
    codigo:    item.examen?.codigo ?? '',
}))

const form = useForm({
    medico_id:              props.orden.medico_id ?? null as number | null,
    fecha_entrega_estimada: props.orden.fecha_entrega_estimada
        ? props.orden.fecha_entrega_estimada.slice(0, 16)
        : '',
    observaciones: props.orden.observaciones ?? '',
    descuento:     props.orden.descuento ?? 0,
    items: itemsIniciales as { examen_id: number; precio: number; nombre: string; codigo: string }[],
})

const heroCards = [
    { title: 'Paso 1', value: 'Datos',     caption: 'Médico, entrega y observaciones.',      delay: 0   },
    { title: 'Paso 2', value: 'Exámenes',  caption: 'Revisa o modifica los exámenes.',       delay: 90  },
    { title: 'Paso 3', value: 'Guardar',   caption: 'Confirma los cambios de la orden.',     delay: 180 },
]

const subtotal = computed(() => form.items.reduce((acc, i) => acc + Number(i.precio), 0))
const total    = computed(() => Math.max(0, subtotal.value - Number(form.descuento ?? 0)))

const examensFiltrados = computed(() => {
    if (!busquedaExamen.value) return []
    const q = busquedaExamen.value.toLowerCase()
    return props.examenes.filter(e =>
        e.nombre.toLowerCase().includes(q) || e.codigo.toLowerCase().includes(q)
    ).slice(0, 10)
})

function agregarExamen(ex: Examen) {
    if (form.items.find(i => i.examen_id === ex.id)) return
    form.items.push({ examen_id: ex.id, precio: ex.precio, nombre: ex.nombre, codigo: ex.codigo })
    busquedaExamen.value = ''
}

function quitarItem(idx: number) {
    form.items.splice(idx, 1)
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-800'
}

function goToIndex() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit('/recepcion/ordenes', { onFinish: () => { isNavigatingBack.value = false } })
}

function goToShow() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit(`/recepcion/ordenes/${props.orden.id}`, { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.put(`/recepcion/ordenes/${props.orden.id}`)
}
</script>

<style scoped>
.panel-float         { animation: panelFloat  720ms cubic-bezier(0.22,1,0.36,1) both; }
.field-reveal        { animation: fieldReveal 520ms ease-out both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from, .hero-card-appear-from     { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat  { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes fieldReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>