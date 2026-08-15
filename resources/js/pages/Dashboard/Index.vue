<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Dashboard'" />

    <div class="space-y-6"></div>

    <DashboardDrilldownDrawer
      :open="drilldown.open"
      :loading="drilldown.loading"
      :title="drilldown.title"
      :subtitle="drilldown.subtitle"
      :empty-message="drilldown.emptyMessage"
      :error="drilldown.error"
      :items="drilldown.items"
      @close="closeDrilldown"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import DashboardFleetPanel from '@/components/dashboard/DashboardFleetPanel.vue'
import DashboardAlertsPanel from '@/components/dashboard/DashboardAlertsPanel.vue'
import DashboardOperationsPanel from '@/components/dashboard/DashboardOperationsPanel.vue'
import DashboardDocumentsPanel from '@/components/dashboard/DashboardDocumentsPanel.vue'
import DashboardCommercialPanel from '@/components/dashboard/DashboardCommercialPanel.vue'
import DashboardFinancePanel from '@/components/dashboard/DashboardFinancePanel.vue'
import DashboardDrilldownDrawer from '@/components/dashboard/DashboardDrilldownDrawer.vue'

defineProps<{
  hero: {
    fleet_total: number
    available_now: number
    in_workshop_now: number
    alerts_pending: number
  }
  fleet: {
    physical: Record<string, number>
    commercial: Record<string, number>
  }
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
  documents: {
    vehicles_pending: number
    missing_ficha_tecnica: number
    missing_permiso_circulacion: number
    vault_pending: number
    vault_downloaded: number
    vault_processed: number
  }
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
  finance: {
    invoices_pending_assignment: number
    invoices_pending_ocr: number
    invoices_ocr_error: number
    invoice_amount_month: number
    costs_month: number
  }
}>()

interface DrilldownItem {
  id: number | string
  title: string
  subtitle: string
  meta?: string[]
  badge?: string | null
  href?: string | null
  hrefLabel?: string | null
}

const drilldown = reactive<{
  open: boolean
  loading: boolean
  title: string
  subtitle: string
  emptyMessage: string
  error: string | null
  items: DrilldownItem[]
}>({
  open: false,
  loading: false,
  title: '',
  subtitle: '',
  emptyMessage: 'No hay elementos para mostrar en este desglose.',
  error: null,
  items: [],
})

const openDrilldown = async (key: string) => {
  drilldown.open = true
  drilldown.loading = true
  drilldown.error = null
  drilldown.items = []
  drilldown.title = 'Cargando detalle...'
  drilldown.subtitle = 'Estamos preparando la lista asociada a este indicador.'
  drilldown.emptyMessage = 'No hay elementos para mostrar en este desglose.'

  try {
    const response = await fetch(`/dashboard/drilldown?key=${encodeURIComponent(key)}`, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (!response.ok) {
      throw new Error('No pudimos obtener el detalle de este indicador.')
    }

    const payload = await response.json()

    drilldown.title = payload.title ?? 'Detalle'
    drilldown.subtitle = payload.subtitle ?? ''
    drilldown.emptyMessage = payload.emptyMessage ?? 'No hay elementos para mostrar en este desglose.'
    drilldown.items = payload.items ?? []
  } catch (error) {
    drilldown.error = error instanceof Error ? error.message : 'No pudimos cargar el detalle.'
    drilldown.title = 'Detalle no disponible'
    drilldown.subtitle = 'Prueba otra vez en unos segundos.'
  } finally {
    drilldown.loading = false
  }
}

const closeDrilldown = () => {
  drilldown.open = false
}
</script>

<style scoped>
.dashboard-section {
  animation: dashboard-section-enter 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
  will-change: opacity, transform;
}

.dashboard-section--1 { animation-delay: 20ms; }
.dashboard-section--2 { animation-delay: 70ms; }
.dashboard-section--3 { animation-delay: 120ms; }
.dashboard-section--4 { animation-delay: 170ms; }
.dashboard-section--5 { animation-delay: 220ms; }
.dashboard-section--6 { animation-delay: 270ms; }
.dashboard-section--7 { animation-delay: 320ms; }

@keyframes dashboard-section-enter {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .dashboard-section {
    animation: none;
  }
}
</style>
