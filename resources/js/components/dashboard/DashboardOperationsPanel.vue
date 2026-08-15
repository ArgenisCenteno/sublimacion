<template>
  <section :class="panelClass">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Operaciones de taller</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Ordenes activas, carga por etapa y vehiculos bloqueados.
        </p>
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
      <div class="flex min-h-[420px] flex-col rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900">
        <div id="operations-chart" class="flex-1">
          <VueApexCharts type="donut" height="100%" :options="chartOptions" :series="series" />
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Estado de ordenes</p>
          <div class="mt-4 space-y-3">
            <div
              v-for="item in orderRows"
              :key="item.key"
              class="flex items-center justify-between gap-3 rounded-xl px-2 py-2 transition hover:bg-white/60 dark:hover:bg-white/[0.03]"
              role="button"
              tabindex="0"
              @click="emit('open-drilldown', item.drilldownKey)"
              @keydown.enter.prevent="emit('open-drilldown', item.drilldownKey)"
              @keydown.space.prevent="emit('open-drilldown', item.drilldownKey)"
            >
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</span>
              <span
                class="text-sm"
                :class="item.strong ? 'font-semibold text-gray-800 dark:text-white/90' : 'font-medium text-gray-700 dark:text-gray-200'"
              >
                {{ item.value }}
              </span>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Carga actual</p>
          <div class="mt-4 space-y-3">
            <div
              v-for="item in workloadRows"
              :key="item.key"
              class="flex items-center justify-between gap-3 rounded-xl px-2 py-2 transition hover:bg-white/60 dark:hover:bg-white/[0.03]"
              role="button"
              tabindex="0"
              @click="emit('open-drilldown', item.drilldownKey)"
              @keydown.enter.prevent="emit('open-drilldown', item.drilldownKey)"
              @keydown.space.prevent="emit('open-drilldown', item.drilldownKey)"
            >
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</span>
              <span
                class="text-sm"
                :class="item.strong ? 'font-semibold text-gray-800 dark:text-white/90' : 'font-medium text-gray-700 dark:text-gray-200'"
              >
                {{ item.value }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Vehiculos bloqueados</p>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
          Mas de 15 dias
        </span>
      </div>

      <div v-if="operations.bottleneck_list.length" class="mt-4 space-y-3">
        <article
          v-for="order in operations.bottleneck_list"
          :key="order.id"
          class="rounded-2xl border border-amber-200 bg-white px-4 py-4 dark:border-amber-500/20 dark:bg-[#101828]"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                {{ order.vehicle?.plate ?? 'Sin matricula' }}
                <span v-if="order.vehicle?.label"> - {{ order.vehicle.label }}</span>
              </p>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ stageLabel(order.type) }}<span v-if="order.provider"> - {{ order.provider }}</span>
              </p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
              {{ order.days }} dias
            </span>
          </div>
        </article>
      </div>

      <div
        v-else
        class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-white/70 px-4 py-8 text-center dark:border-gray-700 dark:bg-[#101828]"
      >
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sin bloqueos detectados</p>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Ningun vehiculo ha superado el tiempo esperado en taller.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps<{
  operations: {
    orders: Record<string, number>
    by_type: Record<string, number>
    pending_pickup: number
    bottlenecks: number
    workflow_plan: Record<string, number>
    bottleneck_list: Array<{
      id: number
      type: string
      days: number
      provider?: string | null
      vehicle: {
        plate: string
        label: string
      } | null
    }>
  }
  embedded?: boolean
}>()

const emit = defineEmits<{
  (e: 'open-drilldown', key: string): void
}>()

const panelClass = computed(() => props.embedded
  ? 'h-full'
  : 'rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 sm:px-6 sm:pt-6')

const series = computed(() => [
  props.operations.orders.pendiente,
  props.operations.orders.en_proceso,
  props.operations.orders.incidencia,
  props.operations.orders.finalizado,
])

const orderRows = computed(() => [
  { key: 'pendiente', label: 'Pendientes', value: props.operations.orders.pendiente, strong: false, drilldownKey: 'operations_order_pendiente' },
  { key: 'en_proceso', label: 'En proceso', value: props.operations.orders.en_proceso, strong: false, drilldownKey: 'operations_order_en_proceso' },
  { key: 'incidencia', label: 'Con incidencia', value: props.operations.orders.incidencia, strong: false, drilldownKey: 'operations_order_incidencia' },
  { key: 'finalizado', label: 'Finalizadas', value: props.operations.orders.finalizado, strong: false, drilldownKey: 'operations_order_finalizado' },
])

const workloadRows = computed(() => [
  { key: 'chapa', label: 'Chapa', value: props.operations.by_type.chapa, strong: false, drilldownKey: 'operations_by_type_chapa' },
  { key: 'dab', label: 'DAB', value: props.operations.by_type.dab, strong: false, drilldownKey: 'operations_by_type_dab' },
  { key: 'limpieza', label: 'Limpieza', value: props.operations.by_type.limpieza, strong: false, drilldownKey: 'operations_by_type_limpieza' },
  { key: 'pending_pickup', label: 'Pendiente de recogida', value: props.operations.pending_pickup, strong: true, drilldownKey: 'operations_pending_pickup' },
  { key: 'bottlenecks', label: 'Cuellos de botella', value: props.operations.bottlenecks, strong: true, drilldownKey: 'operations_bottlenecks' },
])

const chartOptions = computed(() => ({
  labels: ['Pendientes', 'En proceso', 'Incidencia', 'Finalizadas'],
  colors: ['#9CB9FF', '#465FFF', '#F59E0B', '#12B76A'],
  chart: {
    type: 'donut',
    fontFamily: 'Outfit, sans-serif',
    background: 'transparent',
  },
  stroke: {
    show: false,
  },
  legend: {
    position: 'bottom',
    fontSize: '13px',
    labels: {
      colors: ['#98A2B3'],
    },
  },
  dataLabels: {
    enabled: false,
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
      },
    },
  },
  noData: {
    text: 'Sin ordenes activas',
    align: 'center',
    verticalAlign: 'middle',
    style: {
      color: '#98A2B3',
      fontSize: '14px',
      fontFamily: 'Outfit, sans-serif',
    },
  },
  theme: {
    mode: 'dark',
  },
  tooltip: {
    theme: 'dark',
  },
}))

const stageLabel = (type: string) => ({
  chapa: 'Chapa',
  dab: 'DAB',
  limpieza: 'Limpieza',
}[type] ?? type)
</script>

