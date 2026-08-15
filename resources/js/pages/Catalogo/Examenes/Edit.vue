<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Editar Examen'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(139,92,246,0.28),_transparent_32%),linear-gradient(135deg,_#2e1065_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo · Editar
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ examen.nombre }}
                            </h1>
                            <p class="mt-2 font-mono text-sm text-white/60">{{ examen.codigo }}</p>
                        </div>
                        <button
                            type="button"
                            :disabled="isNavigatingBack || form.processing"
                            @click="goToShow"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{ isNavigatingBack ? 'Cargando...' : 'Ver examen' }}
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
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del examen</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Código <span class="text-red-500">*</span></label>
                                    <input v-model="form.codigo" type="text" @input="form.codigo = form.codigo.toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.codigo)]" />
                                    <p v-if="form.errors.codigo" class="mt-1 text-sm text-red-500">{{ form.errors.codigo }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre corto</label>
                                    <input v-model="form.nombre_corto" type="text" @input="form.nombre_corto = (form.nombre_corto || '').toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.nombre_corto)]" />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre completo <span class="text-red-500">*</span></label>
                                    <input v-model="form.nombre" type="text" :class="[inputBaseClass, errorClass(form.errors.nombre)]" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Categoría <span class="text-red-500">*</span></label>
                                    <input v-model="form.categoria" type="text" list="categorias-list" :class="[inputBaseClass, errorClass(form.errors.categoria)]" />
                                    <datalist id="categorias-list">
                                        <option v-for="cat in props.categorias" :key="cat" :value="cat" />
                                    </datalist>
                                    <p v-if="form.errors.categoria" class="mt-1 text-sm text-red-500">{{ form.errors.categoria }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tipo de muestra <span class="text-red-500">*</span></label>
                                    <input v-model="form.muestra" type="text" list="muestras-list" :class="[inputBaseClass, errorClass(form.errors.muestra)]" />
                                    <datalist id="muestras-list">
                                        <option value="Sangre venosa" /><option value="Sangre capilar" />
                                        <option value="Orina" /><option value="Heces" /><option value="Esputo" />
                                    </datalist>
                                    <p v-if="form.errors.muestra" class="mt-1 text-sm text-red-500">{{ form.errors.muestra }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:180ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tubo / Recipiente</label>
                                    <input v-model="form.tubo" type="text" list="tubos-list" :class="[inputBaseClass, errorClass(form.errors.tubo)]" />
                                    <datalist id="tubos-list">
                                        <option value="Lila (EDTA)" /><option value="Rojo (sin aditivo)" />
                                        <option value="Verde (heparina)" /><option value="Azul (citrato)" />
                                        <option value="Gris (fluoruro)" /><option value="Frasco estéril" />
                                    </datalist>
                                </div>

                                <div class="field-reveal" style="animation-delay:210ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Unidad de resultado</label>
                                    <input v-model="form.unidad" type="text" :class="[inputBaseClass, errorClass(form.errors.unidad)]" />
                                </div>

                                <div class="field-reveal" style="animation-delay:240ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Método analítico</label>
                                    <input v-model="form.metodo" type="text" :class="[inputBaseClass, errorClass(form.errors.metodo)]" />
                                </div>

                                <div class="field-reveal" style="animation-delay:270ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tiempo de resultado (horas) <span class="text-red-500">*</span></label>
                                    <input v-model="form.tiempo_resultado" type="number" min="1" max="720" :class="[inputBaseClass, errorClass(form.errors.tiempo_resultado)]" />
                                </div>

                                <div class="field-reveal" style="animation-delay:300ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio (USD) <span class="text-red-500">*</span></label>
                                    <input v-model="form.precio" type="number" min="0" step="0.01" :class="[inputBaseClass, errorClass(form.errors.precio)]" />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-500">{{ form.errors.precio }}</p>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:330ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Instrucciones al paciente</label>
                                    <textarea v-model="form.instrucciones" rows="2" :class="[inputBaseClass, 'h-auto resize-none py-3', errorClass(form.errors.instrucciones)]" />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:360ms">
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <div
                                            @click="form.activo = !form.activo"
                                            :class="['relative h-6 w-11 rounded-full transition duration-300', form.activo ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700']"
                                        >
                                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-300', form.activo ? 'translate-x-5' : 'translate-x-0']" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Examen activo en el catálogo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Valores de referencia -->
                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Valores de referencia</h2>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Los rangos existentes se reemplazarán al guardar.</p>
                                </div>
                                <button type="button" @click="agregarReferencia"
                                    class="inline-flex items-center gap-1.5 rounded-2xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar rango
                                </button>
                            </div>

                            <div v-if="form.valores_referencia.length" class="space-y-3">
                                <div
                                    v-for="(vr, idx) in form.valores_referencia"
                                    :key="idx"
                                    class="rounded-[20px] border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                                >
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            Rango {{ idx + 1 }}
                                            <span v-if="vr.etiqueta" class="ml-1.5 font-normal text-slate-500">— {{ vr.etiqueta }}</span>
                                        </span>
                                        <button type="button" @click="quitarReferencia(idx)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Etiqueta</label>
                                            <input v-model="vr.etiqueta" type="text" placeholder="Adulto, Niño..." :class="inputSmClass" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Sexo <span class="text-red-500">*</span></label>
                                            <select v-model="vr.sexo" :class="inputSmClass">
                                                <option value="ambos">Ambos</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Edad mín.</label>
                                            <input v-model="vr.edad_min" type="number" min="0" :class="inputSmClass" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Edad máx.</label>
                                            <input v-model="vr.edad_max" type="number" min="0" :class="inputSmClass" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor mínimo</label>
                                            <input v-model="vr.valor_min" type="number" step="0.0001" :class="inputSmClass" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor máximo</label>
                                            <input v-model="vr.valor_max" type="number" step="0.0001" :class="inputSmClass" />
                                        </div>
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor texto</label>
                                            <input v-model="vr.valor_texto" type="text" placeholder="Negativo, Reactivo..." :class="inputSmClass" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Sin rangos definidos.</p>
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
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Información actual</h2>
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
                                <dt class="text-slate-500 dark:text-slate-400">Rangos actuales</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">{{ examen.valores_referencia?.length ?? 0 }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/20 dark:bg-amber-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-amber-900 dark:text-amber-200">Atención</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-amber-800 dark:text-amber-100/80">
                            <li>Al guardar, todos los rangos de referencia anteriores serán reemplazados por los nuevos.</li>
                            <li>Los resultados ya registrados no se ven afectados.</li>
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

interface ValorReferencia {
    id?: number
    etiqueta: string
    sexo: 'M' | 'F' | 'ambos'
    edad_min: number
    edad_max: number
    valor_min: number | null
    valor_max: number | null
    valor_texto: string
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

interface Props {
    examen: Examen
    categorias: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)

const form = useForm({
    codigo:             props.examen.codigo,
    nombre:             props.examen.nombre,
    nombre_corto:       props.examen.nombre_corto ?? '',
    categoria:          props.examen.categoria,
    muestra:            props.examen.muestra,
    tubo:               props.examen.tubo ?? '',
    unidad:             props.examen.unidad ?? '',
    metodo:             props.examen.metodo ?? '',
    tiempo_resultado:   props.examen.tiempo_resultado,
    precio:             props.examen.precio,
    activo:             props.examen.activo,
    instrucciones:      props.examen.instrucciones ?? '',
    valores_referencia: (props.examen.valores_referencia ?? []).map(vr => ({
        etiqueta:    vr.etiqueta ?? '',
        sexo:        vr.sexo,
        edad_min:    vr.edad_min,
        edad_max:    vr.edad_max,
        valor_min:   vr.valor_min,
        valor_max:   vr.valor_max,
        valor_texto: vr.valor_texto ?? '',
    })),
})

function agregarReferencia() {
    form.valores_referencia.push({ etiqueta: '', sexo: 'ambos', edad_min: 0, edad_max: 999, valor_min: null, valor_max: null, valor_texto: '' })
}

function quitarReferencia(idx: number) {
    form.valores_referencia.splice(idx, 1)
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

const inputSmClass =
    'h-10 w-full rounded-xl border bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 transition focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-600 dark:bg-slate-900 dark:focus:border-indigo-800'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-800'
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

function goToShow() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit(`/catalogo/examenes/${props.examen.id}`, { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.put(`/catalogo/examenes/${props.examen.id}`)
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