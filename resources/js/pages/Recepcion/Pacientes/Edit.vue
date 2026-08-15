<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="`Editar Paciente`" />

    <div class="space-y-6">
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.28),_transparent_32%),linear-gradient(135deg,_#1e1b4b_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
              <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">
                Recepción · Editar
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                {{ paciente.apellidos }}, {{ paciente.nombres }}
              </h1>
              <p class="mt-2 font-mono text-sm text-white/60">
                {{ paciente.tipo_documento }}-{{ paciente.cedula }}
              </p>
            </div>
            <button
              type="button"
              :disabled="isNavigatingBack || form.processing"
              @click="goToShow"
              class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
            >
              {{ isNavigatingBack ? 'Cargando...' : 'Ver paciente' }}
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
              <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Datos personales</h2>
              <div class="grid gap-4 md:grid-cols-2">

                <div class="field-reveal md:col-span-2">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Cédula <span class="text-red-500">*</span></label>
                  <div class="flex gap-2">
                    <select v-model="form.tipo_documento" :class="[inputBaseClass, 'w-24 flex-shrink-0', errorClass(form.errors.tipo_documento)]">
                      <option value="V">V</option>
                      <option value="E">E</option>
                      <option value="P">P</option>
                      <option value="J">J</option>
                    </select>
                    <input v-model="form.cedula" type="text" :class="[inputBaseClass, 'flex-1', errorClass(form.errors.cedula)]" />
                  </div>
                  <p v-if="form.errors.cedula" class="mt-1 text-sm text-red-500">{{ form.errors.cedula }}</p>
                </div>

                <div class="field-reveal" style="animation-delay:60ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nombres <span class="text-red-500">*</span></label>
                  <input v-model="form.nombres" type="text" :class="[inputBaseClass, errorClass(form.errors.nombres)]" />
                  <p v-if="form.errors.nombres" class="mt-1 text-sm text-red-500">{{ form.errors.nombres }}</p>
                </div>

                <div class="field-reveal" style="animation-delay:90ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Apellidos <span class="text-red-500">*</span></label>
                  <input v-model="form.apellidos" type="text" :class="[inputBaseClass, errorClass(form.errors.apellidos)]" />
                  <p v-if="form.errors.apellidos" class="mt-1 text-sm text-red-500">{{ form.errors.apellidos }}</p>
                </div>

                <div class="field-reveal" style="animation-delay:120ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Fecha de nacimiento <span class="text-red-500">*</span></label>
                  <input v-model="form.fecha_nacimiento" type="date" :class="[inputBaseClass, errorClass(form.errors.fecha_nacimiento)]" />
                  <transition name="badge-pop">
                    <span v-if="esMenor" class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                      Menor de edad
                    </span>
                  </transition>
                </div>

                <div class="field-reveal" style="animation-delay:150ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Sexo <span class="text-red-500">*</span></label>
                  <select v-model="form.sexo" :class="[inputBaseClass, errorClass(form.errors.sexo)]">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                  </select>
                </div>

                <div class="field-reveal" style="animation-delay:180ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Teléfono</label>
                  <input v-model="form.telefono" type="text" :class="[inputBaseClass, errorClass(form.errors.telefono)]" />
                </div>

                <div class="field-reveal" style="animation-delay:210ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Correo electrónico</label>
                  <input v-model="form.email" type="email" :class="[inputBaseClass, errorClass(form.errors.email)]" />
                </div>

                <div class="field-reveal md:col-span-2" style="animation-delay:240ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Dirección</label>
                  <input v-model="form.direccion" type="text" :class="[inputBaseClass, errorClass(form.errors.direccion)]" />
                </div>

                <div class="field-reveal md:col-span-2" style="animation-delay:270ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Médico referente</label>
                  <input v-model="form.medico_referente" type="text" :class="[inputBaseClass, errorClass(form.errors.medico_referente)]" />
                </div>
              </div>
            </div>

            <!-- Representante -->
            <transition name="rep-block">
              <div v-if="esMenor" class="rounded-[20px] border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/25 dark:bg-amber-500/10">
                <h2 class="mb-4 text-base font-semibold text-amber-900 dark:text-amber-200">Representante o tutor</h2>
                <div class="grid gap-4 md:grid-cols-2">
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300">Nombres <span class="text-red-500">*</span></label>
                    <input v-model="form.rep_nombres" type="text" :class="[inputBaseClass, errorClass(form.errors.rep_nombres)]" />
                    <p v-if="form.errors.rep_nombres" class="mt-1 text-sm text-red-500">{{ form.errors.rep_nombres }}</p>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300">Apellidos <span class="text-red-500">*</span></label>
                    <input v-model="form.rep_apellidos" type="text" :class="[inputBaseClass, errorClass(form.errors.rep_apellidos)]" />
                    <p v-if="form.errors.rep_apellidos" class="mt-1 text-sm text-red-500">{{ form.errors.rep_apellidos }}</p>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300">Cédula <span class="text-red-500">*</span></label>
                    <input v-model="form.rep_cedula" type="text" :class="[inputBaseClass, errorClass(form.errors.rep_cedula)]" />
                    <p v-if="form.errors.rep_cedula" class="mt-1 text-sm text-red-500">{{ form.errors.rep_cedula }}</p>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300">Teléfono <span class="text-red-500">*</span></label>
                    <input v-model="form.rep_telefono" type="text" :class="[inputBaseClass, errorClass(form.errors.rep_telefono)]" />
                    <p v-if="form.errors.rep_telefono" class="mt-1 text-sm text-red-500">{{ form.errors.rep_telefono }}</p>
                  </div>
                  <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300">Parentesco <span class="text-red-500">*</span></label>
                    <select v-model="form.rep_parentesco" :class="[inputBaseClass, errorClass(form.errors.rep_parentesco)]">
                      <option value="">Seleccionar</option>
                      <option value="madre">Madre</option>
                      <option value="padre">Padre</option>
                      <option value="abuelo">Abuelo</option>
                      <option value="abuela">Abuela</option>
                      <option value="tutor_legal">Tutor legal</option>
                      <option value="otro">Otro</option>
                    </select>
                    <p v-if="form.errors.rep_parentesco" class="mt-1 text-sm text-red-500">{{ form.errors.rep_parentesco }}</p>
                  </div>
                </div>
              </div>
            </transition>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
              <button type="button" :disabled="form.processing" @click="goToShow"
                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                Cancelar
              </button>
              <button type="submit" :disabled="form.processing || isNavigatingBack"
                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60">
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
                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(paciente.created_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500 dark:text-slate-400">Última modificación</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(paciente.updated_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500 dark:text-slate-400">Edad actual</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ paciente.edad }} años</dd>
              </div>
            </dl>
          </section>

          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:180ms">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Acciones</h2>
            <div class="mt-4 space-y-2">
              <button
                type="button"
                :disabled="Boolean(activeAction)"
                @click="navigateTo(`/recepcion/ordenes/create?paciente_id=${paciente.id}`, 'nueva-orden')"
                class="flex w-full items-center gap-3 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ activeAction === 'nueva-orden' ? 'Cargando...' : 'Nueva orden de trabajo' }}
              </button>
            </div>
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

interface Paciente {
  id: number
  tipo_documento: string
  cedula: string
  nombres: string
  apellidos: string
  sexo: 'M' | 'F'
  fecha_nacimiento: string
  edad: number
  es_menor: boolean
  telefono?: string
  email?: string
  direccion?: string
  medico_referente?: string
  rep_nombres?: string
  rep_apellidos?: string
  rep_cedula?: string
  rep_telefono?: string
  rep_parentesco?: string
  created_at: string
  updated_at: string
}

const props = defineProps<{ paciente: Paciente }>()
const page  = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)
const activeAction     = ref<string | null>(null)

const form = useForm({
  tipo_documento:   props.paciente.tipo_documento,
  cedula:           props.paciente.cedula,
  nombres:          props.paciente.nombres,
  apellidos:        props.paciente.apellidos,
  sexo:             props.paciente.sexo,
  fecha_nacimiento: props.paciente.fecha_nacimiento,
  telefono:         props.paciente.telefono ?? '',
  email:            props.paciente.email ?? '',
  direccion:        props.paciente.direccion ?? '',
  medico_referente: props.paciente.medico_referente ?? '',
  rep_nombres:      props.paciente.rep_nombres ?? '',
  rep_apellidos:    props.paciente.rep_apellidos ?? '',
  rep_cedula:       props.paciente.rep_cedula ?? '',
  rep_telefono:     props.paciente.rep_telefono ?? '',
  rep_parentesco:   props.paciente.rep_parentesco ?? '',
})

const edadCalculada = computed<number | null>(() => {
  if (!form.fecha_nacimiento) return null
  const hoy = new Date()
  const nac = new Date(form.fecha_nacimiento)
  const edad = hoy.getFullYear() - nac.getFullYear()
  const cumplioEsteAnio =
    hoy.getMonth() > nac.getMonth() ||
    (hoy.getMonth() === nac.getMonth() && hoy.getDate() >= nac.getDate())
  return cumplioEsteAnio ? edad : edad - 1
})

const esMenor = computed(() => edadCalculada.value !== null && edadCalculada.value < 18)

const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

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
  router.visit(`/recepcion/pacientes/${props.paciente.id}`, { onFinish: () => { isNavigatingBack.value = false } })
}

function navigateTo(url: string, action: string) {
  if (activeAction.value) return
  activeAction.value = action
  router.visit(url, { onFinish: () => { activeAction.value = null } })
}

function submit() {
  if (form.processing || isNavigatingBack.value) return
  form.put(`/recepcion/pacientes/${props.paciente.id}`)
}
</script>

<style scoped>
.panel-float  { animation: panelFloat  720ms cubic-bezier(0.22,1,0.36,1) both; }
.field-reveal { animation: fieldReveal 520ms ease-out both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.rep-block-enter-active { transition: opacity 400ms ease, transform 400ms cubic-bezier(0.22,1,0.36,1); }
.rep-block-leave-active { transition: opacity 280ms ease, transform 280ms ease; }
.rep-block-enter-from, .rep-block-leave-to { opacity:0; transform:translateY(12px); }
.badge-pop-enter-active { transition: opacity 300ms ease, transform 300ms cubic-bezier(0.22,1,0.36,1); }
.badge-pop-enter-from   { opacity:0; transform:scale(0.85); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat  { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes fieldReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>