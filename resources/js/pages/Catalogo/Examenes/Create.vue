<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Registrar Examen'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.28),_transparent_32%),linear-gradient(135deg,_#1e1b4b_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Registrar nuevo examen
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

                        <!-- Datos del examen -->
                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del examen</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Código <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.codigo"
                                        type="text"
                                        placeholder="HEM-001"
                                        @input="form.codigo = form.codigo.toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.codigo)]"
                                    />
                                    <p v-if="form.errors.codigo" class="mt-1 text-sm text-red-500">{{ form.errors.codigo }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre corto</label>
                                    <input
                                        v-model="form.nombre_corto"
                                        type="text"
                                        placeholder="HGB"
                                        @input="form.nombre_corto = form.nombre_corto.toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.nombre_corto)]"
                                    />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre completo <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        placeholder="Hemograma completo"
                                        :class="[inputBaseClass, errorClass(form.errors.nombre)]"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Categoría <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.categoria"
                                        type="text"
                                        list="categorias-list"
                                        placeholder="Hematología"
                                        :class="[inputBaseClass, errorClass(form.errors.categoria)]"
                                    />
                                    <datalist id="categorias-list">
                                        <option v-for="cat in props.categorias" :key="cat" :value="cat" />
                                    </datalist>
                                    <p v-if="form.errors.categoria" class="mt-1 text-sm text-red-500">{{ form.errors.categoria }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tipo de muestra <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.muestra"
                                        type="text"
                                        list="muestras-list"
                                        placeholder="Sangre venosa"
                                        :class="[inputBaseClass, errorClass(form.errors.muestra)]"
                                    />
                                    <datalist id="muestras-list">
                                        <option value="Sangre venosa" />
                                        <option value="Sangre capilar" />
                                        <option value="Orina" />
                                        <option value="Heces" />
                                        <option value="Esputo" />
                                        <option value="Secreción" />
                                    </datalist>
                                    <p v-if="form.errors.muestra" class="mt-1 text-sm text-red-500">{{ form.errors.muestra }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:180ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tubo / Recipiente</label>
                                    <input
                                        v-model="form.tubo"
                                        type="text"
                                        list="tubos-list"
                                        placeholder="Lila (EDTA)"
                                        :class="[inputBaseClass, errorClass(form.errors.tubo)]"
                                    />
                                    <datalist id="tubos-list">
                                        <option value="Lila (EDTA)" />
                                        <option value="Rojo (sin aditivo)" />
                                        <option value="Verde (heparina)" />
                                        <option value="Azul (citrato)" />
                                        <option value="Gris (fluoruro)" />
                                        <option value="Frasco estéril" />
                                    </datalist>
                                </div>

                                <div class="field-reveal" style="animation-delay:210ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Unidad de resultado</label>
                                    <input
                                        v-model="form.unidad"
                                        type="text"
                                        placeholder="g/dL"
                                        :class="[inputBaseClass, errorClass(form.errors.unidad)]"
                                    />
                                </div>

                                <div class="field-reveal" style="animation-delay:240ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Método analítico</label>
                                    <input
                                        v-model="form.metodo"
                                        type="text"
                                        placeholder="Colorimétrico enzimático"
                                        :class="[inputBaseClass, errorClass(form.errors.metodo)]"
                                    />
                                </div>

                                <div class="field-reveal" style="animation-delay:270ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Tiempo de resultado (horas) <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.tiempo_resultado"
                                        type="number"
                                        min="1"
                                        max="720"
                                        placeholder="24"
                                        :class="[inputBaseClass, errorClass(form.errors.tiempo_resultado)]"
                                    />
                                    <p v-if="form.errors.tiempo_resultado" class="mt-1 text-sm text-red-500">{{ form.errors.tiempo_resultado }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:300ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio (USD) <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.precio"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="5.00"
                                        :class="[inputBaseClass, errorClass(form.errors.precio)]"
                                    />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-500">{{ form.errors.precio }}</p>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:330ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Instrucciones al paciente</label>
                                    <textarea
                                        v-model="form.instrucciones"
                                        rows="2"
                                        placeholder="Ayuno de 8 horas. No realizar ejercicio previo..."
                                        :class="[inputBaseClass, 'h-auto resize-none py-3', errorClass(form.errors.instrucciones)]"
                                    />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:360ms">
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <div
                                            @click="form.activo = !form.activo"
                                            :class="[
                                                'relative h-6 w-11 rounded-full transition duration-300',
                                                form.activo ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700',
                                            ]"
                                        >
                                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-300', form.activo ? 'translate-x-5' : 'translate-x-0']" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Examen activo en el catálogo
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Valores de referencia -->
                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Valores de referencia</h2>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Define los rangos normales por sexo y edad.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="agregarReferencia"
                                    class="inline-flex items-center gap-1.5 rounded-2xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300"
                                >
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
                                        <button
                                            type="button"
                                            @click="quitarReferencia(idx)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Etiqueta</label>
                                            <input v-model="vr.etiqueta" type="text" placeholder="Adulto, Niño..." :class="[inputSmClass]" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Sexo <span class="text-red-500">*</span></label>
                                            <select v-model="vr.sexo" :class="[inputSmClass, errorClass(form.errors[`valores_referencia.${idx}.sexo`])]">
                                                <option value="ambos">Ambos</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Edad mín. (años) <span class="text-red-500">*</span></label>
                                            <input v-model="vr.edad_min" type="number" min="0" placeholder="0" :class="[inputSmClass]" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Edad máx. (años) <span class="text-red-500">*</span></label>
                                            <input v-model="vr.edad_max" type="number" min="0" placeholder="999" :class="[inputSmClass]" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor mínimo</label>
                                            <input v-model="vr.valor_min" type="number" step="0.0001" placeholder="0.00" :class="[inputSmClass]" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor máximo</label>
                                            <input v-model="vr.valor_max" type="number" step="0.0001" placeholder="0.00" :class="[inputSmClass]" />
                                        </div>
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Valor texto (resultados cualitativos)</label>
                                            <input v-model="vr.valor_texto" type="text" placeholder="Negativo, Reactivo..." :class="[inputSmClass]" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Sin rangos definidos. Haz clic en "Agregar rango" para añadir valores de referencia.
                                </p>
                            </div>
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
                                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Registrar examen' }}
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
                                <dt class="text-slate-500 dark:text-slate-400">Categoría</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ form.categoria || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Precio</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">
                                    {{ form.precio ? `$${Number(form.precio).toFixed(2)}` : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Rangos definidos</dt>
                                <dd>
                                    <span :class="[
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        form.valores_referencia.length
                                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
                                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                    ]">
                                        {{ form.valores_referencia.length }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-500/20 dark:bg-indigo-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-indigo-900 dark:text-indigo-200">Notas</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-indigo-800 dark:text-indigo-100/80">
                            <li>El código debe ser único. Usa un formato consistente como HEM-001.</li>
                            <li>Define al menos un rango de referencia para que el sistema pueda marcar resultados fuera de rango.</li>
                            <li>Para exámenes cualitativos (Negativo/Positivo) usa el campo "Valor texto".</li>
                            <li>Puedes agregar múltiples rangos para el mismo examen diferenciados por sexo o edad.</li>
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

interface ValorReferenciaForm {
    etiqueta: string
    sexo: 'M' | 'F' | 'ambos'
    edad_min: number | null
    edad_max: number | null
    valor_min: number | null
    valor_max: number | null
    valor_texto: string
}

interface Props {
    categorias: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)

const form = useForm({
    codigo:            '',
    nombre:            '',
    nombre_corto:      '',
    categoria:         '',
    muestra:           '',
    tubo:              '',
    unidad:            '',
    metodo:            '',
    tiempo_resultado:  24,
    precio:            '',
    activo:            true,
    instrucciones:     '',
    valores_referencia: [] as ValorReferenciaForm[],
})

const heroCards = [
    { title: 'Paso 1', value: 'Examen',      caption: 'Código, nombre, muestra y precio.',           delay: 0   },
    { title: 'Paso 2', value: 'Referencias', caption: 'Rangos normales por sexo y edad.',             delay: 90  },
    { title: 'Paso 3', value: 'Catálogo',    caption: 'El examen quedará disponible para órdenes.',   delay: 180 },
]

function agregarReferencia() {
    form.valores_referencia.push({
        etiqueta:    '',
        sexo:        'ambos',
        edad_min:    0,
        edad_max:    999,
        valor_min:   null,
        valor_max:   null,
        valor_texto: '',
    })
}

function quitarReferencia(idx: number) {
    form.valores_referencia.splice(idx, 1)
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

const inputSmClass =
    'h-10 w-full rounded-xl border bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 transition duration-300 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-600 dark:bg-slate-900 dark:focus:border-indigo-800'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-800'
}

function goToIndex() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit('/catalogo/examenes', { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.post('/catalogo/examenes')
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