<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="`Paciente`" />

    <div class="space-y-6">
      <!-- Hero --> 
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(52,211,153,0.28),_transparent_32%),linear-gradient(135deg,_#042f2e_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
              <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">
                Recepción · Paciente
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
               {{ paciente.nombres }} {{ paciente.apellidos }}
              </h1>
              <div class="mt-3 flex flex-wrap items-center gap-3">
                <span class="font-mono text-sm text-white/60">{{ paciente.tipo_documento }}-{{ paciente.cedula }}</span>
                <span class="text-white/30">·</span>
                <span class="text-sm text-white/60">{{ paciente.edad }} años</span>
                <span class="text-white/30">·</span>
                <span class="text-sm text-white/60">{{ paciente.sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
                <span
                  v-if="paciente.es_menor"
                  class="rounded-full bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-300"
                >
                  Menor de edad
                </span>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <button
                type="button"
                :disabled="Boolean(activeAction)"
                @click="navigateTo(`/recepcion/ordenes/create?paciente_id=${paciente.id}`, 'nueva-orden')"
                class="inline-flex items-center gap-2 rounded-2xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-teal-400"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ activeAction === 'nueva-orden' ? 'Cargando...' : 'Nueva orden' }}
              </button>
              <button
                type="button"
                :disabled="Boolean(activeAction)"
                @click="navigateTo(`/recepcion/pacientes/${paciente.id}/edit`, 'edit')"
                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
              >
                {{ activeAction === 'edit' ? 'Cargando...' : 'Editar' }}
              </button>
            </div>
          </div>

          <!-- Stats -->
          <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-4" name="hero-card" appear>
            <article
              v-for="card in statsCards"
              :key="card.title"
              :style="{ transitionDelay: `${card.delay}ms` }"
              class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md"
            >
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/55">{{ card.title }}</p>
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

        <!-- Columna principal -->
        <div class="space-y-6">

          <!-- Datos personales -->
          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-5 text-base font-semibold text-slate-900 dark:text-white">Datos personales</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
              <div v-for="field in datosPersonales" :key="field.label" class="rounded-2xl bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ field.label }}</dt>
                <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{{ field.value || '—' }}</dd>
              </div>
            </dl>
          </section>

          <!-- Representante -->
          <section v-if="paciente.es_menor" class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/25 dark:bg-amber-500/10">
            <h2 class="mb-5 text-base font-semibold text-amber-900 dark:text-amber-200">Representante o tutor</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
              <div v-for="field in datosRepresentante" :key="field.label" class="rounded-2xl bg-amber-100/60 px-4 py-3 dark:bg-amber-500/10">
                <dt class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">{{ field.label }}</dt>
                <dd class="mt-1 text-sm font-medium text-amber-900 dark:text-amber-100">{{ field.value || '—' }}</dd>
              </div>
            </dl>
          </section>

          <!-- Historial de órdenes -->
          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex items-center justify-between">
              <h2 class="text-base font-semibold text-slate-900 dark:text-white">Órdenes de trabajo</h2>
              <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ paciente.ordenes?.length ?? 0 }} en total
              </span>
            </div>

            <div v-if="paciente.ordenes?.length" class="space-y-3">
              <div
                v-for="orden in paciente.ordenes"
                :key="orden.id"
                class="map-card flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 transition duration-300 dark:bg-slate-800/60"
              >
                <div class="flex flex-col">
                  <span class="font-mono text-sm font-semibold text-slate-800 dark:text-white">{{ orden.codigo }}</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(orden.created_at) }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <span :class="estadoOrdenClass(orden.estado)">{{ estadoOrdenLabel(orden.estado) }}</span>
                  <button
                    type="button"
                    :disabled="Boolean(activeAction)"
                    @click="navigateTo(`/recepcion/ordenes/${orden.id}`, `orden:${orden.id}`)"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300"
                  >
                    {{ activeAction === `orden:${orden.id}` ? '...' : 'Ver' }}
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
              Este paciente no tiene órdenes registradas aún.
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
                @click="navigateTo(`/recepcion/ordenes/create?paciente_id=${paciente.id}`, 'nueva-orden')"
                class="flex w-full items-center gap-3 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ activeAction === 'nueva-orden' ? 'Cargando...' : 'Nueva orden de trabajo' }}
              </button>

              <button
                type="button"
                :disabled="Boolean(activeAction)"
                @click="navigateTo(`/recepcion/pacientes/${paciente.id}/edit`, 'edit')"
                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ activeAction === 'edit' ? 'Cargando...' : 'Editar datos' }}
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
                {{ activeAction === 'delete' ? 'Eliminando...' : 'Eliminar paciente' }}
              </button>
            </div>
          </section>

          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:90ms">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Registro del sistema</h2>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex justify-between">
                <dt class="text-slate-500 dark:text-slate-400">Creado el</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(paciente.created_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500 dark:text-slate-400">Modificado el</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(paciente.updated_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500 dark:text-slate-400">ID interno</dt>
                <dd class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ paciente.id }}</dd>
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

interface Orden {
  id: number
  codigo: string
  estado: string
  created_at: string
}

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
  ordenes?: Orden[]
}

const props  = defineProps<{ paciente: Paciente }>()
const page   = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash  = computed(() => page.props.flash ?? {})
const activeAction = ref<string | null>(null)

const statsCards = computed(() => [
  { title: 'Órdenes totales', value: String(props.paciente.ordenes?.length ?? 0), caption: 'Historial completo', delay: 0 },
  { title: 'Edad',            value: `${props.paciente.edad} años`,                caption: props.paciente.es_menor ? 'Menor de edad' : 'Mayor de edad', delay: 90 },
  { title: 'Sexo',            value: props.paciente.sexo === 'M' ? 'Masc.' : 'Fem.', caption: props.paciente.sexo === 'M' ? 'Masculino' : 'Femenino', delay: 180 },
  { title: 'Médico ref.',     value: props.paciente.medico_referente ? '1' : '0',  caption: props.paciente.medico_referente ?? 'Sin médico referente', delay: 270 },
])

const datosPersonales = computed(() => [
  { label: 'Cédula',            value: `${props.paciente.tipo_documento}-${props.paciente.cedula}` },
  { label: 'Fecha nacimiento',  value: formatDate(props.paciente.fecha_nacimiento) },
  { label: 'Teléfono',          value: props.paciente.telefono },
  { label: 'Correo',            value: props.paciente.email },
  { label: 'Dirección',         value: props.paciente.direccion },
  { label: 'Médico referente',  value: props.paciente.medico_referente },
])

const datosRepresentante = computed(() => [
  { label: 'Nombres',     value: props.paciente.rep_nombres },
  { label: 'Apellidos',   value: props.paciente.rep_apellidos },
  { label: 'Cédula',      value: props.paciente.rep_cedula },
  { label: 'Teléfono',    value: props.paciente.rep_telefono },
  { label: 'Parentesco',  value: props.paciente.rep_parentesco?.replace('_', ' ') },
])

function estadoOrdenClass(estado: string) {
  const map: Record<string, string> = {
    pendiente:   'rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    en_proceso:  'rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    listo:       'rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
    entregado:   'rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300',
  }
  return map[estado] ?? map.pendiente
}

function estadoOrdenLabel(estado: string) {
  const map: Record<string, string> = {
    pendiente: 'Pendiente', en_proceso: 'En proceso', listo: 'Listo', entregado: 'Entregado',
  }
  return map[estado] ?? estado
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

function navigateTo(url: string, action: string) {
  if (activeAction.value) return
  activeAction.value = action
  router.visit(url, { onFinish: () => { activeAction.value = null } })
}

function eliminar() {
  if (activeAction.value) return
  if (!confirm(`¿Eliminar al paciente ${props.paciente.nombres} ${props.paciente.apellidos}?`)) return
  activeAction.value = 'delete'
  router.delete(`/recepcion/pacientes/${props.paciente.id}`, {
    onFinish: () => { activeAction.value = null },
  })
}
</script>

<style scoped>
.panel-float { animation: panelFloat 720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.map-card:hover { transform: translateX(4px); }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>