<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Vehiculos'" />

    <div class="space-y-6">
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/30">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.45),_transparent_35%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7 lg:p-8">
          <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex-1">
              <button
                type="button"
                :disabled="Boolean(activeAction)"
                @click="navigate('/vehicles', 'back')"
                class="mb-5 inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur-sm transition hover:bg-white/15"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ activeAction === 'back' ? 'Cargando...' : 'Volver' }}
              </button>

              <transition appear name="hero-identity">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                  <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl border border-white/15 bg-white/15 text-white shadow-lg shadow-indigo-950/20 backdrop-blur-md">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10" />
                    </svg>
                  </div>

                  <div class="min-w-0 space-y-3">
                    <div>
                      <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        {{ vehicleDisplayName }}
                      </h1>
                      <p class="mt-1 text-sm text-white/75 sm:text-base">
                        {{ vehicle.version || 'Version no registrada' }}
                      </p>
                    </div>

                    <div class="flex flex-wrap gap-2.5">
                      <span class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-3 py-2 text-sm font-medium text-white/90 backdrop-blur-sm">
                        <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ vehicle.plate }}
                      </span>
                      <span class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-3 py-2 text-sm font-medium text-white/90 backdrop-blur-sm">
                        <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h10" />
                        </svg>
                        {{ vehicle.vin || 'VIN pendiente' }}
                      </span>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <transition-group
              tag="div"
              class="grid w-full gap-3 sm:grid-cols-2 xl:w-[380px]"
              name="hero-stat"
              appear
            >
              <article
                v-for="stat in summaryCards"
                :key="stat.title"
                :style="{ transitionDelay: `${stat.delay}ms` }"
                class="rounded-2xl border border-sky-300/30 bg-white/10 p-4 text-white shadow-lg shadow-slate-950/10 backdrop-blur-md"
              >
                <div class="mb-3 flex items-center gap-3">
                  <div :class="['flex h-10 w-10 items-center justify-center rounded-xl', stat.iconClass]">
                    <component :is="stat.icon" class="h-5 w-5" />
                  </div>
                  <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/55">
                    {{ stat.title }}
                  </p>
                </div>
                <p class="text-2xl font-semibold leading-none">
                  {{ stat.value }}
                </p>
                <p class="mt-2 text-sm text-white/65">
                  {{ stat.caption }}
                </p>
              </article>
            </transition-group>
          </div>

          <transition-group
            tag="div"
            class="mt-6 flex flex-wrap items-center gap-3 xl:justify-end"
            name="hero-action"
            appear
          >
            <button
              type="button"
              :disabled="Boolean(activeAction)"
              @click="navigate(`/vehicles/${vehicle.id}/edit`, 'edit')"
              :style="{ transitionDelay: '140ms' }"
              key="edit"
              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50"
            >
              <PencilIcon class="h-4 w-4" />
              {{ activeAction === 'edit' ? 'Cargando...' : 'Editar' }}
            </button>
            <button
              type="button"
              :style="{ transitionDelay: '220ms' }"
              key="print"
              class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/15"
            >
              <PrintIcon class="h-4 w-4" />
              Imprimir
            </button>
            <button
              type="button"
              :disabled="Boolean(activeAction)"
              @click="deleteVehicle"
              :style="{ transitionDelay: '300ms' }"
              key="delete"
              class="inline-flex items-center gap-2 rounded-xl bg-rose-500/85 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500"
            >
              <TrashIcon class="h-4 w-4" />
              {{ activeAction === 'delete' ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </transition-group>
        </div>
      </section>

      <section class="rounded-[24px] border border-slate-200/80 bg-white p-2 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-2 md:grid-cols-5">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            @click="activeTab = tab.id"
            :class="[
              'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition',
              activeTab === tab.id
                ? 'bg-[linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] text-white shadow-lg shadow-indigo-500/25'
                : 'text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label }}
            <span v-if="tab.count !== undefined" class="text-xs opacity-80">
              {{ tab.count }}
            </span>
          </button>
        </div>
      </section>

      <VehicleShowTabContent :vehicle="vehicle" :tab="activeTab" />
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, defineComponent, h, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import VehicleShowTabContent from './show/Show.vue'
import type { VehicleData } from './show/vehicleShow'
import {
  formatCurrency,
  getCommercialStatusLabel,
  getPhysicalStatusLabel,
  parseNumber,
} from './show/vehicleShow'

const props = defineProps<{
  vehicle: VehicleData
  initialTab?: TabKey
}>()
const activeAction = ref<string | null>(null)

// const activeTab = ref<'general' | 'photos' | 'documents' | 'costs' | 'history'>('general')
// lo definimos asi porque en v-for no reconoce el tipo si lo hacemos inline, y da error de que activeTab no es asignable a string
type TabKey = 'general' | 'photos' | 'documents' | 'costs' | 'history'

const activeTab = ref<TabKey>(props.initialTab && ['general', 'photos', 'documents', 'costs', 'history'].includes(props.initialTab)
  ? props.initialTab
  : 'general')

const HomeIcon = icon('M3 10.5l9-7 9 7M5.25 9.75V19.5h13.5V9.75')
const PhotoIcon = icon('M2.25 15.75l4.72-4.72a1.5 1.5 0 012.12 0l3.16 3.16 1.72-1.72a1.5 1.5 0 012.12 0l5.66 5.66M3.75 6.75h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5zm12 3.75h.008v.008h-.008z')
const FolderIcon = icon('M3.75 6.75h4.19a1.5 1.5 0 011.06.44l1.12 1.12a1.5 1.5 0 001.06.44h8.07a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z')
const CoinIcon = icon('M12 3c-4.97 0-9 1.79-9 4s4.03 4 9 4 9-1.79 9-4-4.03-4-9-4zm-9 9v5c0 2.21 4.03 4 9 4s9-1.79 9-4v-5')
const HistoryIcon = icon('M12 6v6l4 2.5M3.75 12a8.25 8.25 0 101.76-5.04M3.75 3.75v4.5h4.5')
const TagIcon = icon('M20.59 13.41L11 3.83H4v7l9.59 9.58a2 2 0 002.82 0l4.18-4.17a2 2 0 000-2.83zM7.5 7.5h.01')
const ChartIcon = icon('M4.5 19.5h15M7.5 16.5V9m4.5 7.5V6m4.5 10.5v-4.5')
const StatusIcon = icon('M12 8.25v3.75l2.25 2.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z')
const CalendarIcon = icon('M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z')
const PencilIcon = icon('M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM19.5 7.125L16.875 4.5')
const PrintIcon = icon('M6 9V4.5h12V9M6 17.25h12V21H6zM4.5 10.5h15A1.5 1.5 0 0121 12v4.5A1.5 1.5 0 0119.5 18H18')
const TrashIcon = icon('M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79A48.108 48.108 0 013.75 6m16.5 0a48.11 48.11 0 00-1.022-.21m0 0L18.16 4.3A2.25 2.25 0 0015.94 3H8.06A2.25 2.25 0 005.84 4.3L5.25 5.79m13.978 0H5.25')

const vehicleDisplayName = computed(() => `${props.vehicle.brand} ${props.vehicle.model}`.trim())
const photoCount = computed(() => props.vehicle.photos?.length ?? 0)
const documentCount = computed(() => props.vehicle.documents?.length ?? 0)
const totalCost = computed(() =>
  (props.vehicle.cost_entries || []).reduce((sum, entry) => sum + parseNumber(entry.amount), 0)
)
const grossMargin = computed(() => parseNumber(props.vehicle.price_cash) - totalCost.value)
const marginPercent = computed(() => {
  const cashPrice = parseNumber(props.vehicle.price_cash)

  if (!cashPrice) {
    return 0
  }

  return (grossMargin.value / cashPrice) * 100
})

const stockCaption = computed(() => {
  const createdAt = props.vehicle.created_at

  if (!createdAt) {
    return 'Fecha de alta no disponible'
  }

  const start = new Date(createdAt)
  const now = new Date()
  const diffMs = now.getTime() - start.getTime()
  const diffDays = Math.max(0, Math.floor(diffMs / (1000 * 60 * 60 * 24)))

  return `${diffDays} dias en stock`
})

const summaryCards = computed(() => [
  {
    title: 'PVP',
    value: formatCurrency(props.vehicle.price_cash),
    caption: props.vehicle.price_financed ? `Financiado ${formatCurrency(props.vehicle.price_financed)}` : 'Precio financiado pendiente',
    icon: TagIcon,
    iconClass: 'bg-white/15 text-sky-300',
    delay: 0,
  },
  {
    title: 'Margen',
    value: `${marginPercent.value.toFixed(1)}%`,
    caption: formatCurrency(grossMargin.value),
    icon: ChartIcon,
    iconClass: 'bg-white/15 text-cyan-300',
    delay: 90,
  },
  {
    title: 'Estado',
    value: getPhysicalStatusLabel(props.vehicle.physical_status),
    caption: getCommercialStatusLabel(props.vehicle.commercial_status),
    icon: StatusIcon,
    iconClass: 'bg-amber-400 text-slate-900',
    delay: 180,
  },
  {
    title: 'En stock',
    value: stockCaption.value.split(' ')[0] || '0',
    caption: stockCaption.value,
    icon: CalendarIcon,
    iconClass: 'bg-white/15 text-indigo-200',
    delay: 270,
  },
])

const tabs = computed((): Array<{ id: TabKey; label: string; icon: any; count?: number }> => [
  { id: 'general', label: 'General', icon: HomeIcon },
  { id: 'photos', label: 'Fotos', icon: PhotoIcon, count: photoCount.value },
  { id: 'documents', label: 'Documentos', icon: FolderIcon, count: documentCount.value },
  { id: 'costs', label: 'Costes & Margen', icon: CoinIcon },
  { id: 'history', label: 'Historial', icon: HistoryIcon },
])

function deleteVehicle() {
  if (activeAction.value) {
    return
  }

  if (confirm(`Estas seguro de que quieres eliminar el vehiculo ${props.vehicle.plate}?`)) {
    activeAction.value = 'delete'
    router.delete(`/vehicles/${props.vehicle.id}`, {
      onFinish: () => {
        activeAction.value = null
      },
    })
  }
}

function navigate(url: string, action: 'back' | 'edit') {
  if (activeAction.value) {
    return
  }

  activeAction.value = action
  router.visit(url, {
    onFinish: () => {
      activeAction.value = null
    },
  })
}

function icon(path: string) {
  return defineComponent({
    name: 'InlineIcon',
    setup(_, { attrs }) {
      return () =>
        h(
          'svg',
          {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '1.8',
            ...attrs,
          },
          [
            h('path', {
              d: path,
              'stroke-linecap': 'round',
              'stroke-linejoin': 'round',
            }),
          ]
        )
    },
  })
}
</script>

<style scoped>
.hero-identity-enter-active,
.hero-identity-appear-active {
  transition:
    opacity 620ms ease,
    transform 620ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-identity-enter-from,
.hero-identity-appear-from {
  opacity: 0;
  transform: translateX(-34px);
}

.hero-identity-enter-to,
.hero-identity-appear-to {
  opacity: 1;
  transform: translateX(0);
}

.hero-stat-enter-active,
.hero-stat-appear-active {
  transition:
    opacity 520ms ease,
    transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-stat-enter-from,
.hero-stat-appear-from {
  opacity: 0;
  transform: translateY(-24px);
}

.hero-stat-enter-to,
.hero-stat-appear-to {
  opacity: 1;
  transform: translateY(0);
}

.hero-action-enter-active,
.hero-action-appear-active {
  transition:
    opacity 520ms ease,
    transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-action-enter-from,
.hero-action-appear-from {
  opacity: 0;
  transform: translateY(24px);
}

.hero-action-enter-to,
.hero-action-appear-to {
  opacity: 1;
  transform: translateY(0);
}
</style>
