<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Centro de alertas'" />

    <div class="space-y-6">
      <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
        {{ flash.success }}
      </div>

      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(248,113,113,0.26),_transparent_30%),linear-gradient(135deg,_#111827_0%,_#0f172a_55%,_#1f2937_100%)] p-5 sm:p-7">
          <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-2xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">
                Task Manager
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Centro de alertas y tareas
              </h1>
              <p class="mt-3 text-sm leading-6 text-white/75">
                Prioriza acciones por criticidad y por rol responsable.
              </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/80 backdrop-blur-md">
              {{ alerts.length }} alertas en esta vista
            </div>
          </div>

          <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <article v-for="card in summaryCards" :key="card.title" :class="['rounded-2xl border p-4 text-white backdrop-blur-md', card.className]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/55">{{ card.title }}</p>
              <p class="mt-3 text-3xl font-semibold leading-none">{{ card.value }}</p>
              <p class="mt-2 text-sm text-white/70">{{ card.caption }}</p>
            </article>
          </div>

          <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
            <div class="grid gap-4 xl:grid-cols-[220px_220px_auto] xl:items-end">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-white/85">Estado</label>
                <select v-model="selectedStatus" class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white focus:border-white/30 focus:outline-hidden focus:ring-4 focus:ring-white/10">
                  <option value="" class="text-slate-900">Pendientes</option>
                  <option value="pendiente" class="text-slate-900">Pendientes</option>
                  <option value="resuelta" class="text-slate-900">Resueltas</option>
                  <option value="descartada" class="text-slate-900">Descartadas</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-white/85">Rol objetivo</label>
                <select v-model="selectedRole" class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white focus:border-white/30 focus:outline-hidden focus:ring-4 focus:ring-white/10">
                  <option value="" class="text-slate-900">Todos</option>
                  <option value="comercial" class="text-slate-900">Comercial / Gerencia</option>
                  <option value="operaciones" class="text-slate-900">Operaciones</option>
                  <option value="logistica" class="text-slate-900">Logistica</option>
                </select>
              </div>
              <button
                v-if="selectedStatus || selectedRole"
                type="button"
                class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15"
                @click="clearFilters"
              >
                Limpiar filtros
              </button>
            </div>
          </div>
        </div>
      </section>

      <section v-for="section in groupedSections" :key="section.key" class="space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ section.title }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ section.description }}</p>
          </div>
          <span class="rounded-full px-3 py-1 text-sm font-medium" :class="section.badgeClass">
            {{ section.items.length }} alertas
          </span>
        </div>

        <div v-if="section.items.length" class="grid gap-4 xl:grid-cols-2">
          <article
            v-for="alert in section.items"
            :key="alert.id"
            :class="['rounded-[24px] border p-5 shadow-sm transition', alertCardClass(alert.segment)]"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="priorityBadgeClass(alert.priority)">
                    {{ alert.priority }}
                  </span>
                  <span class="rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:border-slate-700 dark:text-slate-300">
                    {{ humanize(alert.role_target) }}
                  </span>
                </div>

                <h3 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ alert.title }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ alertSubtitle(alert) }}</p>
              </div>

              <div class="text-right text-sm text-slate-500 dark:text-slate-400">
                <div>{{ alert.created_at }}</div>
                <div v-if="alert.vehicle" class="mt-1 font-medium text-slate-700 dark:text-slate-200">{{ alert.vehicle.plate }}</div>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <span v-if="alert.vehicle" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ alert.vehicle.brand }} {{ alert.vehicle.model }}
              </span>
              <span v-for="meta in payloadTags(alert.payload)" :key="meta" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ meta }}
              </span>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                @click="runAction(alert)"
              >
                {{ alert.action_label }}
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="resolve(alert)"
              >
                Resolver
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                @click="dismiss(alert)"
              >
                Descartar
              </button>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-slate-700 dark:bg-slate-800/40"
        >
          <p class="text-base font-semibold text-slate-900 dark:text-white">Sin alertas en esta seccion</p>
          <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            No hay tareas activas para este segmento con los filtros actuales.
          </p>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface AlertRow {
  id: number
  type: string
  priority: string
  status: string
  segment: 'critica' | 'seguimiento' | 'operativa'
  role_target: string
  title: string
  action_label: string
  payload: Record<string, unknown>
  created_at: string | null
  resolved_at: string | null
  resolution_notes: string | null
  resolved_by: string | null
  vehicle: {
    id: number
    plate: string
    brand: string
    model: string
    physical_status: string
    commercial_status: string
  } | null
}

const props = defineProps<{
  alerts: AlertRow[]
  filters: {
    status?: string
    role_target?: string
  }
  stats: {
    critical: number
    follow_up: number
    operational: number
    resolved_today: number
  }
}>()

const page = usePage<{ flash?: { success?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const selectedStatus = ref(props.filters.status ?? '')
const selectedRole = ref(props.filters.role_target ?? '')

const summaryCards = computed(() => [
  { title: 'Criticas', value: props.stats.critical, caption: 'Accion inmediata', className: 'border-rose-300/20 bg-rose-500/10' },
  { title: 'Seguimiento', value: props.stats.follow_up, caption: 'Requieren revision', className: 'border-amber-300/20 bg-amber-500/10' },
  { title: 'Operativas', value: props.stats.operational, caption: 'Tareas logisticas', className: 'border-emerald-300/20 bg-emerald-500/10' },
  { title: 'Resueltas hoy', value: props.stats.resolved_today, caption: 'Actividad del dia', className: 'border-sky-300/20 bg-sky-500/10' },
])

const groupedSections = computed(() => ([
  {
    key: 'critica',
    title: 'Alertas criticas',
    description: 'Incidencias de alto impacto para comercial y operaciones.',
    badgeClass: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    items: props.alerts.filter((alert) => alert.segment === 'critica'),
  },
  {
    key: 'seguimiento',
    title: 'Alertas de seguimiento',
    description: 'Cuellos de botella y revisiones pendientes.',
    badgeClass: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    items: props.alerts.filter((alert) => alert.segment === 'seguimiento'),
  },
  {
    key: 'operativa',
    title: 'Alertas operativas',
    description: 'Eventos listos para coordinacion o recogida.',
    badgeClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    items: props.alerts.filter((alert) => alert.segment === 'operativa'),
  },
]))

let debounceId: number | null = null
watch([selectedStatus, selectedRole], () => {
  if (debounceId) {
    window.clearTimeout(debounceId)
  }

  debounceId = window.setTimeout(() => {
    const params: Record<string, string> = {}

    if (selectedStatus.value) {
      params.status = selectedStatus.value
    }

    if (selectedRole.value) {
      params.role_target = selectedRole.value
    }

    router.get('/alerts', params, { preserveState: true, preserveScroll: true, replace: true })
  }, 150)
})

const clearFilters = () => {
  selectedStatus.value = ''
  selectedRole.value = ''
}

const runAction = (alert: AlertRow) => {
  router.post(`/alerts/${alert.id}/action`)
}

const resolve = (alert: AlertRow) => {
  router.post(`/alerts/${alert.id}/resolve`)
}

const dismiss = (alert: AlertRow) => {
  router.post(`/alerts/${alert.id}/dismiss`)
}

const humanize = (value: string) => value.replaceAll('_', ' ')

const alertSubtitle = (alert: AlertRow) => {
  const vehicleName = alert.vehicle ? `${alert.vehicle.brand} ${alert.vehicle.model}` : 'vehiculo sin asignar'

  return `${vehicleName}. ${messageForType(alert)}`
}

const payloadTags = (payload: Record<string, unknown>) => {
  const tags: string[] = []

  if (payload.days_in_workshop) {
    tags.push(`${payload.days_in_workshop} dias en taller`)
  }

  if (payload.workshop_type) {
    tags.push(`Taller ${humanize(String(payload.workshop_type))}`)
  }

  if (payload.num_keys) {
    tags.push(`${payload.num_keys} llave`)
  }

  if (payload.insurance_status) {
    tags.push(`Seguro ${humanize(String(payload.insurance_status))}`)
  }

  if (payload.policy_number) {
    tags.push(`Poliza ${String(payload.policy_number)}`)
  }

  if (Array.isArray(payload.missing_documents)) {
    const labels: Record<string, string> = {
      ficha_tecnica: 'Ficha tecnica',
      permiso_circulacion: 'Permiso circulacion',
    }

    for (const documentKey of payload.missing_documents) {
      tags.push(`Falta ${labels[String(documentKey)] ?? humanize(String(documentKey))}`)
    }
  }

  return tags
}

const messageForType = (alert: AlertRow) => ({
  insurance_active: 'Este vehiculo ya figura como vendido, pero el seguro sigue activo.',
  key_missing: 'Este vehiculo vendido solo tiene una llave registrada.',
  documentation_incomplete: 'Este vehiculo ya esta listo para avanzar, pero aun tiene documentos pendientes.',
  bottleneck: 'Este vehiculo lleva mas tiempo del esperado en taller.',
  taller_exit: 'Este vehiculo ya salio de limpieza y esta pendiente de recogida.',
}[alert.type] ?? 'Hay una tarea pendiente asociada a este vehiculo.')

const alertCardClass = (segment: AlertRow['segment']) => ({
  critica: 'border-rose-200 bg-white dark:border-rose-500/20 dark:bg-slate-900',
  seguimiento: 'border-amber-200 bg-white dark:border-amber-500/20 dark:bg-slate-900',
  operativa: 'border-emerald-200 bg-white dark:border-emerald-500/20 dark:bg-slate-900',
}[segment])

const priorityBadgeClass = (priority: string) => ({
  alta: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
  media: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
  baja: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
}[priority] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200')
</script>



