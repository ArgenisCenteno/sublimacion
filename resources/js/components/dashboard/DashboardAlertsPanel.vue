<template>
  <section class="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 sm:px-6 sm:pt-6">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Alertas y tareas</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Prioridades activas y seguimiento rapido.
        </p>
      </div>
      <Link href="/alerts" class="text-sm font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-300">
        Ver centro de alertas
      </Link>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <button
        v-for="item in summaryCards"
        :key="item.key"
        type="button"
        class="alert-mini-card rounded-2xl border px-4 py-4 text-left transition hover:-translate-y-0.5"
        :class="item.className"
        @click="emit('open-drilldown', item.drilldownKey)"
      >
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] opacity-70">
          {{ item.label }}
        </p>
        <p class="mt-3 text-2xl font-semibold leading-none">{{ item.value }}</p>
      </button>
    </div>

    <div class="mt-6 space-y-3">
      <article
        v-for="alert in alerts.recent"
        :key="alert.id"
        class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-white/[0.02]"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-2.5 w-2.5 rounded-full" :class="dotTone(alert.priority)" />
              <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ alertTitle(alert.type) }}</p>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              {{ alert.vehicle?.plate ?? 'Sin vehiculo' }}
              <span v-if="alert.vehicle?.label"> - {{ alert.vehicle.label }}</span>
            </p>
          </div>
          <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ alert.created_at }}</span>
        </div>
      </article>

      <div
        v-if="!alerts.recent.length"
        class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center dark:border-gray-700"
      >
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sin alertas activas</p>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          No hay tareas pendientes para revisar ahora mismo.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
  alerts: {
    pending: number
    high: number
    medium: number
    low: number
    resolved_today: number
    recent: Array<{
      id: number
      type: string
      priority: string
      created_at: string | null
      vehicle: {
        plate: string
        label: string
      } | null
    }>
  }
}>()

const emit = defineEmits<{
  (e: 'open-drilldown', key: string): void
}>()

const alertTitle = (type: string) => ({
  insurance_active: 'Seguro activo',
  key_missing: 'Llave pendiente',
  documentation_incomplete: 'Documentacion pendiente',
  bottleneck: 'Cuello de botella',
  taller_exit: 'Salida de taller',
}[type] ?? type.replaceAll('_', ' '))

const dotTone = (priority: string) => ({
  alta: 'bg-error-500',
  media: 'bg-amber-500',
  baja: 'bg-success-500',
}[priority] ?? 'bg-gray-400')

const summaryCards = computed(() => [
  {
    key: 'pending',
    label: 'Pendientes',
    value: props.alerts.pending,
    className: 'alert-mini-card--slate',
    drilldownKey: 'alerts_pending',
  },
  {
    key: 'high',
    label: 'Alta',
    value: props.alerts.high,
    className: 'alert-mini-card--rose',
    drilldownKey: 'alerts_high',
  },
  {
    key: 'medium',
    label: 'Media',
    value: props.alerts.medium,
    className: 'alert-mini-card--amber',
    drilldownKey: 'alerts_medium',
  },
  {
    key: 'resolved_today',
    label: 'Resueltas hoy',
    value: props.alerts.resolved_today,
    className: 'alert-mini-card--emerald',
    drilldownKey: 'alerts_resolved_today',
  },
])
</script>

<style scoped>
.alert-mini-card--slate {
  border-color: #e4e7ec;
  background: #f9fafb;
  color: #1d2939;
}

.alert-mini-card--rose {
  border-color: #fecdd3;
  background: #fff1f3;
  color: #be123c;
}

.alert-mini-card--amber {
  border-color: #fde68a;
  background: #fffbeb;
  color: #b45309;
}

.alert-mini-card--emerald {
  border-color: #a7f3d0;
  background: #ecfdf3;
  color: #047857;
}

.dark .alert-mini-card--slate {
  border-color: #344054;
  background: #101828;
  color: rgba(255, 255, 255, 0.9);
}

.dark .alert-mini-card--rose {
  border-color: rgba(217, 45, 32, 0.18);
  background: #101828;
  color: #f97066;
}

.dark .alert-mini-card--amber {
  border-color: rgba(245, 158, 11, 0.18);
  background: #101828;
  color: #e9b949;
}

.dark .alert-mini-card--emerald {
  border-color: rgba(18, 183, 106, 0.18);
  background: #101828;
  color: #52d3a2;
}

.dark .alert-mini-card {
  box-shadow: none;
}
</style>

