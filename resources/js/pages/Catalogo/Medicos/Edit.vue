<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Editar Médico'" />

        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.28),_transparent_32%),linear-gradient(135deg,_#0c4a6e_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Catálogo · Editar
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ medico.nombre_completo }}
                            </h1>
                            <p v-if="medico.especialidad" class="mt-2 text-sm text-white/60">{{ medico.especialidad }}</p>
                        </div>
                        <button
                            type="button"
                            :disabled="isNavigatingBack || form.processing"
                            @click="goToShow"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{ isNavigatingBack ? 'Cargando...' : 'Ver médico' }}
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
                            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos del médico</h2>
                            <div class="grid gap-4 md:grid-cols-2">

                                <div class="field-reveal">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombres <span class="text-red-500">*</span></label>
                                    <input v-model="form.nombre" type="text" @input="form.nombre = form.nombre.toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.nombre)]" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</p>
                                </div>

                                <div class="field-reveal" style="animation-delay:60ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Apellidos <span class="text-red-500">*</span></label>
                                    <input v-model="form.apellido" type="text" @input="form.apellido = form.apellido.toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.apellido)]" />
                                    <p v-if="form.errors.apellido" class="mt-1 text-sm text-red-500">{{ form.errors.apellido }}</p>
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:90ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Especialidad</label>
                                    <input v-model="form.especialidad" type="text" list="especialidades-list" :class="[inputBaseClass, errorClass(form.errors.especialidad)]" />
                                    <datalist id="especialidades-list">
                                        <option v-for="esp in props.especialidades" :key="esp" :value="esp" />
                                    </datalist>
                                </div>

                                <div class="field-reveal" style="animation-delay:120ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Teléfono</label>
                                    <input v-model="form.telefono" type="text" maxlength="20" :class="[inputBaseClass, errorClass(form.errors.telefono)]" />
                                </div>

                                <div class="field-reveal" style="animation-delay:150ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Correo electrónico</label>
                                    <input v-model="form.email" type="email" :class="[inputBaseClass, errorClass(form.errors.email)]" />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:180ms">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Consultorio / Dirección</label>
                                    <input v-model="form.consultorio" type="text" @input="form.consultorio = (form.consultorio || '').toUpperCase()" :class="[inputBaseClass, errorClass(form.errors.consultorio)]" />
                                </div>

                                <div class="field-reveal md:col-span-2" style="animation-delay:210ms">
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <div @click="form.activo = !form.activo" :class="['relative h-6 w-11 rounded-full transition duration-300', form.activo ? 'bg-sky-600' : 'bg-slate-300 dark:bg-slate-700']">
                                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-300', form.activo ? 'translate-x-5' : 'translate-x-0']" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Médico activo en el sistema</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                            <button type="button" :disabled="form.processing" @click="goToShow"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing || isNavigatingBack"
                                class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60">
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
                                <dt class="text-slate-500 dark:text-slate-400">Registrado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(medico.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Modificado el</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(medico.updated_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Órdenes referidas</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">{{ medico.ordenes_count ?? 0 }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/20 dark:bg-amber-500/10" style="animation-delay:180ms">
                        <h2 class="text-base font-semibold text-amber-900 dark:text-amber-200">Atención</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-amber-800 dark:text-amber-100/80">
                            <li>Si desactivas al médico, dejará de aparecer en el selector de nuevas órdenes.</li>
                            <li>Las órdenes ya creadas con este médico no se ven afectadas.</li>
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

interface Medico {
    id: number
    nombre: string
    apellido: string
    nombre_completo: string
    especialidad?: string
    telefono?: string
    email?: string
    consultorio?: string
    activo: boolean
    ordenes_count?: number
    created_at: string
    updated_at: string
}

interface Props {
    medico: Medico
    especialidades: string[]
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)

const form = useForm({
    nombre:       props.medico.nombre,
    apellido:     props.medico.apellido,
    especialidad: props.medico.especialidad ?? '',
    telefono:     props.medico.telefono ?? '',
    email:        props.medico.email ?? '',
    consultorio:  props.medico.consultorio ?? '',
    activo:       props.medico.activo,
})

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-sky-300 focus:ring-sky-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-sky-800'
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

function goToShow() {
    if (isNavigatingBack.value || form.processing) return
    isNavigatingBack.value = true
    router.visit(`/catalogo/medicos/${props.medico.id}`, { onFinish: () => { isNavigatingBack.value = false } })
}

function submit() {
    if (form.processing || isNavigatingBack.value) return
    form.put(`/catalogo/medicos/${props.medico.id}`)
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