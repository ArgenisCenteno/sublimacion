<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Ordenes de taller'" />

    <div class="space-y-6">
      <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
        {{ flash.success }}
      </div>

      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.35),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#111827_45%,_#052e2b_100%)] p-5 sm:p-7">
          <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-2xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">
                Taller
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Ordenes de taller
              </h1>
              <p class="mt-3 text-sm leading-6 text-white/75">
                Gestiona manualmente las ordenes de chapa, DAB y limpieza desde el panel interno.
              </p>
            </div>

            <Link href="/workshop/orders/create" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-lg shadow-emerald-950/10 transition hover:bg-emerald-50">
              Nueva orden
            </Link>
          </div>

          <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <article v-for="card in summaryCards" :key="card.title" class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/55">{{ card.title }}</p>
              <p class="mt-3 text-3xl font-semibold leading-none">{{ card.value }}</p>
              <p class="mt-2 text-sm text-white/70">{{ card.caption }}</p>
            </article>
          </div>

          <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
            <div class="grid gap-4 xl:grid-cols-[1fr_220px_220px_auto] xl:items-end">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-white/85">Buscar</label>
                <input v-model="search" type="text" placeholder="Placa, vehiculo, proveedor..." class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/45 focus:border-white/30 focus:outline-hidden focus:ring-4 focus:ring-white/10" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-white/85">Tipo</label>
                <select v-model="selectedType" class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/45 focus:border-white/30 focus:outline-hidden focus:ring-4 focus:ring-white/10">
                  <option value="" class="text-slate-900">Todos</option>
                  <option v-for="type in typeOptions" :key="type.value" :value="type.value" class="text-slate-900">{{ type.label }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-white/85">Estado</label>
                <select v-model="selectedStatus" class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/45 focus:border-white/30 focus:outline-hidden focus:ring-4 focus:ring-white/10">
                  <option value="" class="text-slate-900">Todos</option>
                  <option v-for="status in statusOptions" :key="status.value" :value="status.value" class="text-slate-900">{{ status.label }}</option>
                </select>
              </div>
              <button v-if="search || selectedType || selectedStatus" type="button" class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15" @click="clearFilters">
                Limpiar
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Listado de ordenes</h2>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ headline }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ orders.total }} registros</span>
          </div>
        </div>

        <div class="max-w-full overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800">
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Orden</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Vehiculo</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Proveedor</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Estado</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Fechas</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr v-for="order in orders.data" :key="order.id" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-5 py-4">
                  <div class="flex flex-col">
                    <span class="font-semibold text-slate-900 dark:text-white">#{{ order.id }}</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ typeLabel(order.type) }}</span>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <div class="flex flex-col">
                    <span class="font-semibold text-slate-900 dark:text-white">{{ order.vehicle?.plate ?? 'Sin vehiculo' }}</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ order.vehicle ? `${order.vehicle.brand} ${order.vehicle.model}` : 'Sin datos' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <div class="flex flex-col">
                    <span class="font-medium text-slate-900 dark:text-white">{{ order.provider?.name ?? 'Sin proveedor' }}</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ order.provider?.type ?? 'No asignado' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <span :class="statusClass(order.status)" class="rounded-full px-2.5 py-1 text-xs font-semibold">{{ statusLabel(order.status) }}</span>
                </td>
                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                  <div>Entrada: {{ order.entry_at || 'Pendiente' }}</div>
                  <div>Salida: {{ order.exit_at || 'Pendiente' }}</div>
                </td>
                <td class="px-5 py-4">
                  <div class="flex flex-wrap items-center gap-2">
                    <Link :href="`/workshop/orders/${order.id}`" class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Ver</Link>
                    <Link :href="`/workshop/orders/${order.id}/edit`" class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Editar</Link>
                    <button type="button" class="inline-flex items-center rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10" @click="destroy(order.id)">Eliminar</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!orders.data.length" class="px-6 py-16 text-center">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No hay ordenes para mostrar</h3>
          <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ajusta los filtros o crea una nueva orden manual.</p>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
          <p class="text-sm text-slate-600 dark:text-slate-400">Mostrando {{ orders.from || 0 }} a {{ orders.to || 0 }} de {{ orders.total }} resultados</p>
          <div class="flex items-center gap-2">
            <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Anterior</Link>
            <span class="text-sm text-slate-600 dark:text-slate-400">Pagina {{ orders.current_page }} de {{ orders.last_page }}</span>
            <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Siguiente</Link>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface OrderRow {
  id: number
  type: string
  status: string
  entry_at: string | null
  exit_at: string | null
  vehicle: { plate: string; brand: string; model: string } | null
  provider: { name: string; type: string } | null
}

const props = defineProps<{
  orders: {
    data: OrderRow[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
    prev_page_url?: string | null
    next_page_url?: string | null
  }
  filters: { search?: string; type?: string; status?: string }
  stats: { total: number; active: number; pending: number; finished: number }
}>()

const page = usePage<{ flash?: { success?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})
const search = ref(props.filters.search ?? '')
const selectedType = ref(props.filters.type ?? '')
const selectedStatus = ref(props.filters.status ?? '')

const typeOptions = [
  { value: 'chapa', label: 'Chapa' },
  { value: 'dab', label: 'DAB' },
  { value: 'limpieza', label: 'Limpieza' },
]
const statusOptions = [
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'incidencia', label: 'Incidencia' },
  { value: 'finalizado', label: 'Finalizado' },
]

const summaryCards = computed(() => [
  { title: 'Total', value: props.stats.total, caption: 'Ordenes registradas' },
  { title: 'Activas', value: props.stats.active, caption: 'Pendientes, en proceso o con incidencia' },
  { title: 'Pendientes', value: props.stats.pending, caption: 'Esperando recepcion en taller' },
  { title: 'Finalizadas', value: props.stats.finished, caption: 'Etapas cerradas correctamente' },
])

const headline = computed(() => {
  const filters = []

  if (search.value.trim()) {
filters.push(`busqueda "${search.value.trim()}"`)
}

  if (selectedType.value) {
filters.push(`tipo ${typeLabel(selectedType.value)}`)
}

  if (selectedStatus.value) {
filters.push(`estado ${statusLabel(selectedStatus.value)}`)
}

  return filters.length ? `Vista filtrada por ${filters.join(' - ')}` : 'Consulta general de ordenes manuales y operativas.'
})

let debounceId: number | null = null
watch([search, selectedType, selectedStatus], () => {
  if (debounceId) {
window.clearTimeout(debounceId)
}

  debounceId = window.setTimeout(() => {
    const params: Record<string, string> = {}

    if (search.value.trim()) {
params.search = search.value.trim()
}

    if (selectedType.value) {
params.type = selectedType.value
}

    if (selectedStatus.value) {
params.status = selectedStatus.value
}

    router.get('/workshop/orders', params, { preserveState: true, preserveScroll: true, replace: true })
  }, 250)
})

const clearFilters = () => {
  search.value = ''
  selectedType.value = ''
  selectedStatus.value = ''
}

const destroy = (id: number) => {
  if (window.confirm('Estas seguro de que quieres eliminar esta orden?')) {
    router.delete(`/workshop/orders/${id}`)
  }
}

const typeLabel = (value: string) => typeOptions.find((type) => type.value === value)?.label ?? value
const statusLabel = (value: string) => statusOptions.find((status) => status.value === value)?.label ?? value
const statusClass = (status: string) => ({
  pendiente: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
  en_proceso: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
  incidencia: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
  finalizado: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
}[status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200')
</script>


