<!-- Create.vue -->
<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Registrar Médico'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.28),_transparent_32%),linear-gradient(135deg,_#082f49_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Registrar nuevo médico
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
                        <div>
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del médico</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombres <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        placeholder="Carlos Alberto"
                                        @input="form.nombre = form.nombre.toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.nombre)]"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Apellidos <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.apellido"
                                        type="text"
                                        placeholder="Ramírez Torres"
                                        @input="form.apellido = form.apellido.toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.apellido)]"
                                    />
                                    <p v-if="form.errors.apellido" class="mt-1 text-sm text-red-500">{{ form.errors.apellido }}</p>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Especialidad</label>
                                    <input
                                        v-model="form.especialidad"
                                        type="text"
                                        list="especialidades-list"
                                        placeholder="Medicina interna, Pediatría..."
                                        :class="[inputBaseClass, errorClass(form.errors.especialidad)]"
                                    />
                                    <datalist id="especialidades-list">
                                        <option v-for="esp in props.especialidades" :key="esp" :value="esp" />
                                    </datalist>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Teléfono</label>
                                    <input
                                        v-model="form.telefono"
                                        type="text"
                                        placeholder="0412-1234567"
                                        maxlength="20"
                                        :class="[inputBaseClass, errorClass(form.errors.telefono)]"
                                    />
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Correo electrónico</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="doctor@clinica.com"
                                        :class="[inputBaseClass, errorClass(form.errors.email)]"
                                    />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:180ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Consultorio / Dirección</label>
                                    <input
                                        v-model="form.consultorio"
                                        type="text"
                                        placeholder="Clínica El Ávila, Piso 3, Cons. 12"
                                        @input="form.consultorio = (form.consultorio || '').toUpperCase()"
                                        :class="[inputBaseClass, errorClass(form.errors.consultorio)]"
                                    />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:210ms">
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <div
                                            @click="form.activo = !form.activo"
                                            :class="['relative h-6 w-11 rounded-full transition duration-300', form.activo ? 'bg-sky-600' : 'bg-slate-300 dark:bg-slate-700']"
                                        >
                                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-300', form.activo ? 'translate-x-5' : 'translate-x-0']" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Médico activo en el sistema</span>
                                    </label>
                                </div>
                            </div>
                        </div>

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
                                class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Registrar médico' }}
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="space-y-6">
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Resumen</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Nombre</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">
                                    {{ form.nombre || form.apellido ? `Dr. ${form.nombre} ${form.apellido}` : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Especialidad</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ form.especialidad || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Teléfono</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ form.telefono || '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Estado</dt>
                                <dd>
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', form.activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400']">
                                        {{ form.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-sky-200 bg-sky-50 p-6 dark:border-sky-500/20 dark:bg-sky-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-sky-900 dark:text-sky-200">Notas</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-sky-800 dark:text-sky-100/80">
                            <li>El médico quedará disponible para seleccionar al crear una orden de trabajo.</li>
                            <li>La especialidad es opcional pero ayuda a filtrar y organizar las referencias.</li>
                            <li>Solo los médicos activos aparecen en el selector de órdenes.</li>
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

interface Props {
    especialidades: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)

const form = useForm({
    nombre:       '',
    apellido:     '',
    especialidad: '',
    telefono:     '',
    email:        '',
    consultorio:  '',
    activo:       true,
})

const heroCards = [
    { title: 'Datos',      value: 'Médico',     caption: 'Nombre, especialidad y contacto.',             delay: 0   },
    { title: 'Referencia', value: 'Órdenes',    caption: 'Se asigna al crear una orden de trabajo.',     delay: 90  },
    { title: 'Catálogo',   value: 'Activo',     caption: 'Solo activos aparecen en el selector.',        delay: 180 },
]

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-sky-300 focus:ring-sky-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-sky-800'
}

function goToIndex() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit('/catalogo/medicos', { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.post('/catalogo/medicos')
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