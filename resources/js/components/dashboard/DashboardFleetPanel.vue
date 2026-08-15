<template>
  <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 sm:px-6 sm:pt-6">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Estado de la flota</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Vista general por estado logistico y comercial.
        </p>
      </div>
      <div class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-500 dark:bg-brand-500/15 dark:text-brand-300">
        {{ totalVehicles }} vehiculos
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.25fr_0.95fr]">
      <div class="flex min-h-[420px] flex-col rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900">
        <div id="fleet-status-chart" class="fleet-status-chart flex-1">
          <VueApexCharts type="bar" height="100%" :options="chartOptions" :series="series" />
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Logistico</p>
          <div class="mt-4 space-y-3">
            <button
              v-for="item in physicalRows"
              :key="item.key"
              type="button"
              class="flex w-full items-center justify-between gap-3 rounded-xl px-2 py-2 text-left transition hover:bg-white/60 dark:hover:bg-white/[0.03]"
              @click="emit('open-drilldown', item.drilldownKey)"
            >
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</span>
              <span
                class="text-sm"
                :class="item.strong ? 'font-semibold text-gray-800 dark:text-white/90' : 'font-medium text-gray-700 dark:text-gray-200'"
              >
                {{ item.value }}
              </span>
            </button>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Comercial</p>
          <div class="mt-4 grid grid-cols-2 gap-3">
            <div
              v-for="item in commercialCards"
              :key="item.key"
              class="mini-stat rounded-xl border px-3 py-3 text-left transition hover:-translate-y-0.5"
              :class="item.className"
              role="button"
              tabindex="0"
              @click="emit('open-drilldown', item.drilldownKey)"
              @keydown.enter.prevent="emit('open-drilldown', item.drilldownKey)"
              @keydown.space.prevent="emit('open-drilldown', item.drilldownKey)"
            >
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] opacity-70">
                {{ item.label }}
              </p>
              <p class="mt-2 text-xl font-semibold leading-none">{{ item.value }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps<{
  fleet: {
    physical: Record<string, number>
    commercial: Record<string, number>
  }
}>()

const emit = defineEmits<{
  (e: 'open-drilldown', key: string): void
}>()

const totalVehicles = computed(() => Object.values(props.fleet.physical).reduce((sum, value) => sum + value, 0))

const physicalRows = computed(() => [
  { key: 'en_transito', label: 'En transito', value: props.fleet.physical.en_transito, strong: false, drilldownKey: 'fleet_physical_en_transito' },
  { key: 'en_campa', label: 'En campa', value: props.fleet.physical.en_campa, strong: false, drilldownKey: 'fleet_physical_en_campa' },
  { key: 'taller_chapa', label: 'En chapa', value: props.fleet.physical.taller_chapa, strong: false, drilldownKey: 'fleet_physical_taller_chapa' },
  { key: 'taller_dab', label: 'En DAB', value: props.fleet.physical.taller_dab, strong: false, drilldownKey: 'fleet_physical_taller_dab' },
  { key: 'taller_limpieza', label: 'En limpieza', value: props.fleet.physical.taller_limpieza, strong: false, drilldownKey: 'fleet_physical_taller_limpieza' },
  { key: 'pendiente_recogida', label: 'Pendiente de recogida', value: props.fleet.physical.pendiente_recogida, strong: true, drilldownKey: 'fleet_physical_pendiente_recogida' },
  { key: 'disponible', label: 'Disponibles', value: props.fleet.physical.disponible, strong: true, drilldownKey: 'fleet_physical_disponible' },
  { key: 'entregado', label: 'Entregados', value: props.fleet.physical.entregado, strong: false, drilldownKey: 'fleet_physical_entregado' },
])

const commercialCards = computed(() => [
  {
    key: 'no_publicable',
    label: 'No publicables',
    value: props.fleet.commercial.no_publicable,
    className: 'mini-stat--slate',
    drilldownKey: 'fleet_commercial_no_publicable',
  },
  {
    key: 'disponible',
    label: 'Disponibles',
    value: props.fleet.commercial.disponible,
    className: 'mini-stat--sky',
    drilldownKey: 'fleet_commercial_disponible',
  },
  {
    key: 'reservado',
    label: 'Reservados',
    value: props.fleet.commercial.reservado,
    className: 'mini-stat--amber',
    drilldownKey: 'fleet_commercial_reservado',
  },
  {
    key: 'vendido',
    label: 'Vendidos',
    value: props.fleet.commercial.vendido,
    className: 'mini-stat--emerald',
    drilldownKey: 'fleet_commercial_vendido',
  },
])

const series = computed(() => [
  {
    name: 'Vehiculos',
    data: [
      props.fleet.physical.en_transito,
      props.fleet.physical.en_campa,
      props.fleet.physical.taller_chapa,
      props.fleet.physical.taller_dab,
      props.fleet.physical.taller_limpieza,
      props.fleet.physical.pendiente_recogida,
      props.fleet.physical.disponible,
      props.fleet.physical.entregado,
    ],
  },
])

const chartOptions = computed(() => ({
  colors: ['#465FFF'],
  chart: {
    type: 'bar',
    toolbar: {
      show: false,
    },
    fontFamily: 'Outfit, sans-serif',
    background: 'transparent',
    foreColor: '#98A2B3',
  },
  plotOptions: {
    bar: {
      borderRadius: 6,
      columnWidth: '52%',
    },
  },
  dataLabels: {
    enabled: false,
  },
  legend: {
    show: false,
  },
  grid: {
    borderColor: '#344054',
    strokeDashArray: 4,
  },
  xaxis: {
    categories: ['Transito', 'Campa', 'Chapa', 'DAB', 'Limpieza', 'Recogida', 'Disponibles', 'Entregados'],
    labels: {
      style: {
        colors: Array(8).fill('#98A2B3'),
        fontSize: '11px',
      },
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: ['#98A2B3', '#98A2B3', '#98A2B3', '#98A2B3', '#98A2B3'],
      },
    },
  },
  tooltip: {
    theme: 'dark',
  },
  theme: {
    mode: 'dark',
  },
}))

</script>

<style scoped>
.mini-stat--slate {
  border-color: #e4e7ec;
  background: #ffffff;
  color: #1d2939;
}

.mini-stat--sky {
  border-color: #dbeafe;
  background: #eff6ff;
  color: #465fff;
}

.mini-stat--amber {
  border-color: #fde68a;
  background: #fffbeb;
  color: #d97706;
}

.mini-stat--emerald {
  border-color: #a7f3d0;
  background: #ecfdf3;
  color: #047857;
}

.dark .mini-stat--slate {
  border-color: #344054;
  background: #101828;
  color: rgba(255, 255, 255, 0.9);
}

.dark .mini-stat--sky {
  border-color: rgba(70, 95, 255, 0.18);
  background: #101828;
  color: #8ea2ff;
}

.dark .mini-stat--amber {
  border-color: rgba(245, 158, 11, 0.18);
  background: #101828;
  color: #e9b949;
}

.dark .mini-stat--emerald {
  border-color: rgba(16, 185, 129, 0.18);
  background: #101828;
  color: #52d3a2;
}

.dark .mini-stat {
  box-shadow: none;
}

.fleet-status-chart :deep(.apexcharts-canvas),
.fleet-status-chart :deep(.apexcharts-svg),
.fleet-status-chart :deep(.apexcharts-inner),
.fleet-status-chart :deep(foreignObject) {
  background: transparent !important;
}

.fleet-status-chart :deep(.apexcharts-gridline) {
  stroke: #344054 !important;
}

.fleet-status-chart :deep(.apexcharts-text),
.fleet-status-chart :deep(.apexcharts-xaxis-texts-g text),
.fleet-status-chart :deep(.apexcharts-yaxis-texts-g text) {
  fill: #98a2b3 !important;
}
</style>
