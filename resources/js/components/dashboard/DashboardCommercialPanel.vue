<template>
  <section :class="panelClass">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Actividad comercial</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Reservas, ventas y renting con foco en el mes actual.
        </p>
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
      <button
        v-for="item in commercialCards"
        :key="item.key"
        type="button"
        class="commercial-mini-card rounded-2xl border px-4 py-4 text-left transition hover:-translate-y-0.5"
        :class="item.className"
        @click="emit('open-drilldown', item.drilldownKey)"
      >
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] opacity-70">
          {{ item.label }}
        </p>
        <p class="mt-3 text-2xl font-semibold leading-none">{{ item.value }}</p>
      </button>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
      <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Importe vendido este mes</p>
      <p class="mt-3 text-3xl font-semibold text-gray-800 dark:text-white/90">
        {{ formatCurrency(commercial.sold_amount_month) }}
      </p>
    </div>

    <div class="mt-6 space-y-3">
      <article
        v-for="sale in commercial.recent_sales"
        :key="sale.id"
        class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-[#101828]"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
              {{ sale.vehicle?.plate ?? 'Sin matricula' }}
              <span v-if="sale.vehicle?.label"> - {{ sale.vehicle.label }}</span>
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              {{ sale.client || 'Cliente sin nombre' }}
              <span v-if="sale.signed_at"> - {{ sale.signed_at }}</span>
            </p>
          </div>
          <span class="text-sm font-semibold text-success-600 dark:text-success-400">
            {{ formatCurrency(sale.amount) }}
          </span>
        </div>
      </article>

      <div
        v-if="!commercial.recent_sales.length"
        class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center dark:border-gray-700 dark:bg-[#101828]"
      >
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sin ventas recientes</p>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Todavia no hay ventas cerradas para mostrar en esta lista.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  commercial: {
    reserved: number
    sold_month: number
    sold_amount_month: number
    renting_active: number
    renting_expiring_30: number
    recent_sales: Array<{
      id: number
      amount: number
      signed_at: string | null
      client?: string | null
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

const formatCurrency = (value: number) => new Intl.NumberFormat('es-ES', {
  style: 'currency',
  currency: 'EUR',
  maximumFractionDigits: 0,
}).format(value || 0)

const commercialCards = computed(() => [
  { key: 'reserved', label: 'Reservados', value: props.commercial.reserved, className: 'commercial-mini-card--amber', drilldownKey: 'commercial_reserved' },
  { key: 'sold_month', label: 'Vendidos este mes', value: props.commercial.sold_month, className: 'commercial-mini-card--emerald', drilldownKey: 'commercial_sold_month' },
  { key: 'renting_active', label: 'Renting activos', value: props.commercial.renting_active, className: 'commercial-mini-card--sky', drilldownKey: 'commercial_renting_active' },
  { key: 'renting_expiring_30', label: 'Renting por vencer', value: props.commercial.renting_expiring_30, className: 'commercial-mini-card--rose', drilldownKey: 'commercial_renting_expiring_30' },
])
</script>

<style scoped>
.commercial-mini-card--amber {
  border-color: #fde68a;
  background: #fffbeb;
  color: #b45309;
}

.commercial-mini-card--emerald {
  border-color: #a7f3d0;
  background: #ecfdf3;
  color: #047857;
}

.commercial-mini-card--sky {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #465fff;
}

.commercial-mini-card--rose {
  border-color: #fecdd3;
  background: #fff1f3;
  color: #be123c;
}

.dark .commercial-mini-card--amber {
  border-color: rgba(245, 158, 11, 0.18);
  background: #101828;
  color: #e9b949;
}

.dark .commercial-mini-card--emerald {
  border-color: rgba(18, 183, 106, 0.18);
  background: #101828;
  color: #52d3a2;
}

.dark .commercial-mini-card--sky {
  border-color: rgba(70, 95, 255, 0.18);
  background: #101828;
  color: #8ea2ff;
}

.dark .commercial-mini-card--rose {
  border-color: rgba(217, 45, 32, 0.18);
  background: #101828;
  color: #f97066;
}

.dark .commercial-mini-card {
  box-shadow: none;
}
</style>

