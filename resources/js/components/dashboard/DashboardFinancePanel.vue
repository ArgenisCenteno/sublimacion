<template>
  <section :class="panelClass">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Facturas y costes</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Carga administrativa y costes registrados durante el mes.
        </p>
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
      <button
        v-for="item in financeCards"
        :key="item.key"
        type="button"
        class="finance-mini-card rounded-2xl border px-4 py-4 text-left transition hover:-translate-y-0.5"
        :class="item.className"
        :disabled="!item.drilldownKey"
        @click="item.drilldownKey && emit('open-drilldown', item.drilldownKey)"
      >
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] opacity-70">
          {{ item.label }}
        </p>
        <p class="mt-3 text-2xl font-semibold leading-none">{{ item.value }}</p>
      </button>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
      <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Costes registrados este mes</p>
      <p class="mt-3 text-3xl font-semibold text-gray-800 dark:text-white/90">
        {{ formatCurrency(finance.costs_month) }}
      </p>
      <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
        Suma de las lineas de coste imputadas a los vehiculos en el mes actual.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  finance: {
    invoices_pending_assignment: number
    invoices_pending_ocr: number
    invoices_ocr_error: number
    invoice_amount_month: number
    costs_month: number
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

const financeCards = computed(() => [
  { key: 'invoices_pending_assignment', label: 'Facturas pendientes', value: props.finance.invoices_pending_assignment, className: 'finance-mini-card--amber', drilldownKey: 'finance_invoices_pending_assignment' },
  { key: 'invoices_pending_ocr', label: 'OCR pendiente', value: props.finance.invoices_pending_ocr, className: 'finance-mini-card--sky', drilldownKey: 'finance_invoices_pending_ocr' },
  { key: 'invoices_ocr_error', label: 'OCR con error', value: props.finance.invoices_ocr_error, className: 'finance-mini-card--rose', drilldownKey: 'finance_invoices_ocr_error' },
  { key: 'invoice_amount_month', label: 'Importe facturado', value: formatCurrency(props.finance.invoice_amount_month), className: 'finance-mini-card--slate', drilldownKey: null },
])
</script>

<style scoped>
.finance-mini-card--slate {
  border-color: #e4e7ec;
  background: #f9fafb;
  color: #1d2939;
}

.finance-mini-card--amber {
  border-color: #fde68a;
  background: #fffbeb;
  color: #b45309;
}

.finance-mini-card--sky {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #465fff;
}

.finance-mini-card--rose {
  border-color: #fecdd3;
  background: #fff1f3;
  color: #be123c;
}

.dark .finance-mini-card--slate {
  border-color: #344054;
  background: #101828;
  color: rgba(255, 255, 255, 0.9);
}

.dark .finance-mini-card--amber {
  border-color: rgba(245, 158, 11, 0.18);
  background: #101828;
  color: #e9b949;
}

.dark .finance-mini-card--sky {
  border-color: rgba(70, 95, 255, 0.18);
  background: #101828;
  color: #8ea2ff;
}

.dark .finance-mini-card--rose {
  border-color: rgba(217, 45, 32, 0.18);
  background: #101828;
  color: #f97066;
}

.dark .finance-mini-card {
  box-shadow: none;
}
</style>
