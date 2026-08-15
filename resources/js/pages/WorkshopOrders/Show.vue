<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="`Orden #${order.id}`" />

    <div class="space-y-6">
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.35),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#111827_45%,_#052e2b_100%)] p-5 sm:p-7">
          <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-2xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">
                {{ typeLabel(order.type) }}
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Orden #{{ order.id }}
              </h1>
              <p class="mt-3 text-sm leading-6 text-white/75">
                Seguimiento completo de la orden y del vehiculo asociado.
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
              <Link :href="`/workshop/orders/${order.id}/edit`" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">Editar</Link>
              <Link href="/workshop/orders" class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">Volver</Link>
            </div>
          </div>
        </div>
      </section>

      <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
          <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Resumen</h2>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Vehiculo</p>
              <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ order.vehicle?.plate ?? 'Sin vehiculo' }}</p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ order.vehicle ? `${order.vehicle.brand} ${order.vehicle.model}` : 'No asignado' }}</p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Estado</p>
              <span :class="statusClass(order.status)" class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                {{ statusLabel(order.status) }}
              </span>
              <p class="info-subtitle mt-3">Tipo: {{ typeLabel(order.type) }}</p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Proveedor</p>
              <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ order.provider?.name ?? 'Sin proveedor' }}</p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ order.provider?.type ?? 'No asignado' }}</p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Creada por</p>
              <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ order.created_by?.name ?? 'Sin usuario' }}</p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ order.checklist_items_count }} items de checklist</p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Entrada</p>
              <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ order.entry_at ?? 'Pendiente' }}</p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dias en taller: {{ order.days_in_workshop }}</p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Salida</p>
              <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ order.exit_at ?? 'Pendiente' }}</p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Disco: {{ order.exit_photo_disk || 'Sin dato' }}</p>
            </article>
          </div>

          <article class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Notas</p>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
              {{ order.notes || 'Esta orden no tiene notas registradas.' }}
            </p>
          </article>
        </section>

        <section class="space-y-6">
          <article class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Foto de salida</h2>
            <div class="mt-4 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
              <template v-if="order.exit_photo_url">
                <a :href="order.exit_photo_url" target="_blank" class="font-semibold text-emerald-600 hover:underline dark:text-emerald-300">
                  Ver foto registrada
                </a>
              </template>
              <template v-else>
                No hay foto de salida registrada en esta orden.
              </template>
            </div>
          </article>

          <article class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Checklist vinculado</h2>
            <div v-if="order.checklist_items.length" class="mt-4 space-y-3">
              <div v-for="item in order.checklist_items" :key="item.id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/60">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.label }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                      Revisado: {{ item.checked_at || 'Pendiente' }}
                    </p>
                  </div>
                  <span :class="itemBadge(item)" class="rounded-full px-3 py-1 text-xs font-semibold">
                    {{ itemLabel(item) }}
                  </span>
                </div>
                <p v-if="item.notes" class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                  {{ item.notes }}
                </p>
              </div>
            </div>
            <div v-else class="mt-4 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
              Esta orden no tiene checklist asociado.
            </div>
          </article>
        </section>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface ChecklistItem {
  id: number
  label: string
  sort_order: number
  passed: boolean | null
  has_incident: boolean
  notes: string | null
  checked_at: string | null
}

defineProps<{
  order: {
    id: number
    type: string
    status: string
    entry_at: string | null
    exit_at: string | null
    notes: string | null
    exit_photo_url: string | null
    exit_photo_disk: string | null
    days_in_workshop: number
    checklist_items_count: number
    vehicle: { plate: string; brand: string; model: string } | null
    provider: { name: string; type: string } | null
    created_by: { name: string } | null
    checklist_items: ChecklistItem[]
  }
}>()

const typeLabel = (value: string) => ({ chapa: 'Chapa', dab: 'DAB', limpieza: 'Limpieza' }[value] ?? value)
const statusLabel = (value: string) => ({ pendiente: 'Pendiente', en_proceso: 'En proceso', incidencia: 'Incidencia', finalizado: 'Finalizado' }[value] ?? value)
const statusClass = (status: string) => ({
  pendiente: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
  en_proceso: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
  incidencia: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
  finalizado: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
}[status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200')

const itemLabel = (item: ChecklistItem) => {
  if (item.has_incident) {
return 'Incidencia'
}

  if (item.passed === true) {
return 'OK'
}

  return 'Pendiente'
}

const itemBadge = (item: ChecklistItem) => {
  if (item.has_incident) {
return 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
}

  if (item.passed === true) {
return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
}

  return 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}
</script>


