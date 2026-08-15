<template>
  <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-5 flex items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Historial</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Pulsa cualquier evento para ver mas detalle.
        </p>
      </div>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
        {{ timelineItems.length }} eventos
      </span>
    </div>

    <div v-if="timelineItems.length" class="space-y-4">
      <button
        v-for="item in timelineItems"
        :key="item.key"
        type="button"
        class="block w-full rounded-2xl border border-slate-200 p-4 text-left transition hover:border-indigo-200 hover:bg-slate-50/70 dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-800/40"
        @click="selectedEvent = item"
      >
        <div class="flex gap-4">
          <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-indigo-500 shadow-[0_0_0_6px_rgba(99,102,241,0.12)]" />

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p class="font-medium text-slate-900 dark:text-white">{{ item.title }}</p>
                <p v-if="item.subtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                  {{ item.subtitle }}
                </p>
              </div>

              <div class="flex flex-wrap items-center justify-end gap-2">
                <span
                  v-if="item.badge"
                  class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"
                >
                  {{ item.badge }}
                </span>
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ item.date }}</span>
              </div>
            </div>

            <div v-if="item.meta.length" class="mt-3 flex flex-wrap gap-2">
              <span
                v-for="meta in item.meta"
                :key="meta"
                class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
              >
                {{ meta }}
              </span>
            </div>
          </div>
        </div>
      </button>
    </div>

    <div
      v-else
      class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-800/40"
    >
      <p class="text-base font-semibold text-slate-900 dark:text-white">Sin historial visible</p>
      <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
        Todavia no hay eventos suficientes para construir la linea temporal.
      </p>
    </div>

    <teleport to="body">
      <div
        v-if="selectedEvent"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        @click.self="selectedEvent = null"
      >
        <div class="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">
                {{ selectedEvent.badge || 'Detalle' }}
              </p>
              <h3 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">{{ selectedEvent.title }}</h3>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ selectedEvent.date }}</p>
            </div>

            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
              @click="selectedEvent = null"
            >
              x
            </button>
          </div>

          <div class="overflow-hidden rounded-b-[28px]">
            <div class="max-h-[calc(90vh-96px)] overflow-y-auto px-6 py-5 pr-4 [scrollbar-gutter:stable]">
              <div class="space-y-6 pb-6">
              <div v-if="selectedEvent.subtitle" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-300">
                {{ selectedEvent.subtitle }}
              </div>

              <div v-if="selectedEvent.imageUrl" class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40">
                <img :src="selectedEvent.imageUrl" alt="Adjunto del historial" class="max-h-[360px] w-full object-contain" />
              </div>

              <div v-if="selectedEvent.fields.length" class="grid gap-3 sm:grid-cols-2">
                <div
                  v-for="field in selectedEvent.fields"
                  :key="field.label"
                  class="rounded-2xl border border-slate-200 bg-white px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40"
                >
                  <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                    {{ field.label }}
                  </p>
                  <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">
                    {{ field.value }}
                  </p>
                </div>
              </div>

              <div v-if="selectedEvent.meta.length" class="flex flex-wrap gap-2">
                <span
                  v-for="meta in selectedEvent.meta"
                  :key="meta"
                  class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                  {{ meta }}
                </span>
              </div>

              <div v-if="selectedEvent.linkUrl" class="pt-2">
                <a
                  :href="selectedEvent.linkUrl"
                  target="_blank"
                  rel="noreferrer"
                  class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500"
                >
                  Abrir adjunto
                </a>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import type {
  VehicleAlert,
  VehicleCostEntry,
  VehicleData,
  VehicleLocation,
  VehicleWorkshopOrder,
} from './vehicleShow'
import { formatCurrency, formatDate } from './vehicleShow'

const props = defineProps<{
  vehicle: VehicleData
}>()

interface DetailField {
  label: string
  value: string
}

interface TimelineItem {
  key: string
  kind: 'vehicle' | 'location' | 'workshop_order' | 'document' | 'photo' | 'cost' | 'alert' | 'sale'
  title: string
  subtitle: string
  badge?: string
  rawDate: string
  date: string
  meta: string[]
  fields: DetailField[]
  imageUrl?: string
  linkUrl?: string
}

const selectedEvent = ref<TimelineItem | null>(null)

const timelineItems = computed<TimelineItem[]>(() => {
  const items: TimelineItem[] = []

  if (props.vehicle.created_at) {
    items.push({
      key: `vehicle-created-${props.vehicle.id}`,
      kind: 'vehicle',
      title: 'Vehiculo dado de alta en el sistema',
      subtitle: `${props.vehicle.brand} ${props.vehicle.model} - ${props.vehicle.plate}`,
      badge: 'Alta',
      rawDate: props.vehicle.created_at,
      date: formatDate(props.vehicle.created_at),
      meta: compactMeta([
        props.vehicle.company?.name ? `Empresa ${props.vehicle.company.name}` : null,
        props.vehicle.provider?.name ? `Proveedor ${props.vehicle.provider.name}` : null,
        props.vehicle.physical_status ? `Estado ${humanizeValue(props.vehicle.physical_status)}` : null,
      ]),
      fields: buildFields([
        ['Marca', props.vehicle.brand],
        ['Modelo', props.vehicle.model],
        ['Version', props.vehicle.version],
        ['Matricula', props.vehicle.plate],
        ['VIN', props.vehicle.vin],
        ['Estado fisico', humanizeValue(props.vehicle.physical_status)],
        ['Estado comercial', humanizeValue(props.vehicle.commercial_status)],
      ]),
    })
  }

  for (const location of props.vehicle.locations || []) {
    const recordedAt = location.recorded_at ?? null

    if (!recordedAt) {
      continue
    }

    items.push({
      key: `location-${location.id}`,
      kind: 'location',
      title: locationTitle(location),
      subtitle: location.notes || 'Movimiento operativo registrado para este vehiculo.',
      badge: 'Ubicacion',
      rawDate: recordedAt,
      date: formatDate(recordedAt),
      meta: compactMeta([
        location.provider?.name ? `Proveedor ${location.provider.name}` : null,
        location.pin_prefix ? `PIN ${location.pin_prefix}` : null,
        location.location_type ? `Tipo ${humanizeValue(location.location_type)}` : null,
      ]),
      fields: buildFields([
        ['Proveedor', location.provider?.name],
        ['Tipo', humanizeValue(location.location_type)],
        ['Latitud', formatCoordinate(location.latitude)],
        ['Longitud', formatCoordinate(location.longitude)],
        ['Precision', location.accuracy_meters !== null && location.accuracy_meters !== undefined ? `${location.accuracy_meters} m` : null],
        ['Notas', location.notes],
      ]),
    })
  }

  for (const order of props.vehicle.workshop_orders || []) {
    if (order.created_at) {
      items.push(buildWorkshopEvent(order, 'created'))
    }

    if (order.entry_at) {
      items.push(buildWorkshopEvent(order, 'entry'))
    }

    if (order.exit_at) {
      items.push(buildWorkshopEvent(order, 'exit'))
    }
  }

  for (const document of props.vehicle.documents || []) {
    if (!document.created_at) {
      continue
    }

    items.push({
      key: `document-${document.id}`,
      kind: 'document',
      title: 'Documento vinculado al vehiculo',
      subtitle: document.name || 'Documento sin nombre registrado.',
      badge: 'Documento',
      rawDate: document.created_at,
      date: formatDate(document.created_at),
      meta: compactMeta([
        document.document_type ? `Tipo ${humanizeValue(document.document_type)}` : null,
        document.mime_type || null,
      ]),
      fields: buildFields([
        ['Nombre', document.name],
        ['Tipo', humanizeValue(document.document_type)],
        ['Formato', document.mime_type],
        ['Tamano', document.size_kb ? `${document.size_kb} KB` : null],
      ]),
      linkUrl: document.file_url || document.download_url || undefined,
    })
  }

  for (const photo of props.vehicle.photos || []) {
    if (!photo.created_at) {
      continue
    }

    const imageUrl = buildStorageUrl(photo.url)

    items.push({
      key: `photo-${photo.id}`,
      kind: 'photo',
      title: photo.label || 'Foto registrada',
      subtitle: 'Evidencia fotografica asociada al vehiculo.',
      badge: 'Foto',
      rawDate: photo.created_at,
      date: formatDate(photo.created_at),
      meta: compactMeta([
        photo.stage ? `Etapa ${humanizeValue(photo.stage)}` : null,
        photo.type ? `Tipo ${humanizeValue(photo.type)}` : null,
        photo.is_mandatory ? 'Obligatoria' : 'Complementaria',
      ]),
      fields: buildFields([
        ['Etapa', humanizeValue(photo.stage)],
        ['Tipo', humanizeValue(photo.type)],
        ['Formato', photo.mime_type],
        ['Tamano', photo.size_bytes ? `${Math.round(photo.size_bytes / 1024)} KB` : null],
      ]),
      imageUrl,
      linkUrl: imageUrl,
    })
  }

  for (const entry of props.vehicle.cost_entries || []) {
    const eventDate = entry.entry_date || entry.created_at || null

    if (!eventDate) {
      continue
    }

    items.push(buildCostEvent(entry, eventDate))
  }

  for (const alert of props.vehicle.alerts || []) {
    if (!alert.created_at) {
      continue
    }

    items.push(buildAlertEvent(alert))
  }

  for (const contract of props.vehicle.sale_contracts || []) {
    if (!contract.created_at) {
      continue
    }

    items.push({
      key: `sale-${contract.id}`,
      kind: 'sale',
      title: 'Contrato de venta creado',
      subtitle: 'El vehiculo quedo vinculado a un proceso comercial.',
      badge: 'Venta',
      rawDate: contract.created_at,
      date: formatDate(contract.created_at),
      meta: [],
      fields: buildFields([
        ['Contrato', `#${contract.id}`],
        ['Fecha', formatDate(contract.created_at)],
      ]),
    })
  }

  return items
    .filter((item) => item.rawDate)
    .sort((a, b) => new Date(b.rawDate).getTime() - new Date(a.rawDate).getTime())
})

function buildWorkshopEvent(order: VehicleWorkshopOrder, phase: 'created' | 'entry' | 'exit'): TimelineItem {
  const phaseMap = {
    created: {
      key: `workshop-created-${order.id}`,
      title: `Orden de ${humanizeWorkshopType(order.type)} creada`,
      subtitle: order.notes || 'La etapa quedo preparada dentro del flujo del vehiculo.',
      badge: 'Orden',
      rawDate: order.created_at || '',
    },
    entry: {
      key: `workshop-entry-${order.id}`,
      title: `${humanizeWorkshopType(order.type)} recepcionado`,
      subtitle: 'El vehiculo entro oficialmente en esta etapa de taller.',
      badge: 'Entrada',
      rawDate: order.entry_at || '',
    },
    exit: {
      key: `workshop-exit-${order.id}`,
      title: `${humanizeWorkshopType(order.type)} finalizado`,
      subtitle: order.notes || 'La etapa se cerro y el vehiculo siguio con su flujo.',
      badge: 'Salida',
      rawDate: order.exit_at || '',
    },
  } as const

  const event = phaseMap[phase]
  const imageUrl = buildStorageUrl(order.exit_photo_url)

  return {
    key: event.key,
    kind: 'workshop_order',
    title: event.title,
    subtitle: event.subtitle,
    badge: event.badge,
    rawDate: event.rawDate,
    date: formatDate(event.rawDate),
    meta: compactMeta([
      order.status ? `Estado ${humanizeValue(order.status)}` : null,
      order.provider?.name ? `Proveedor ${order.provider.name}` : null,
    ]),
    fields: buildFields([
      ['Orden', `#${order.id}`],
      ['Etapa', humanizeWorkshopType(order.type)],
      ['Estado', humanizeValue(order.status)],
      ['Proveedor', order.provider?.name],
      ['Entrada', formatDate(order.entry_at)],
      ['Salida', formatDate(order.exit_at)],
      ['Notas', order.notes],
    ]),
    imageUrl: phase === 'exit' ? imageUrl : undefined,
    linkUrl: phase === 'exit' ? imageUrl : undefined,
  }
}

function buildCostEvent(entry: VehicleCostEntry, eventDate: string): TimelineItem {
  return {
    key: `cost-${entry.id}`,
    kind: 'cost',
    title: 'Coste registrado',
    subtitle: entry.concept || 'Movimiento economico asociado al vehiculo.',
    badge: 'Coste',
    rawDate: eventDate,
    date: formatDate(eventDate),
    meta: compactMeta([
      entry.amount !== undefined && entry.amount !== null ? formatCurrency(entry.amount) : null,
    ]),
    fields: buildFields([
      ['Concepto', entry.concept],
      ['Importe', entry.amount !== undefined && entry.amount !== null ? formatCurrency(entry.amount) : null],
      ['Fecha operativa', formatDate(entry.entry_date)],
      ['Fecha de registro', formatDate(entry.created_at)],
    ]),
  }
}

function buildAlertEvent(alert: VehicleAlert): TimelineItem {
  return {
    key: `alert-${alert.id}`,
    kind: 'alert',
    title: alert.title || 'Alerta generada',
    subtitle: 'Se registro una alerta asociada a este vehiculo.',
    badge: 'Alerta',
    rawDate: alert.created_at || '',
    date: formatDate(alert.created_at),
    meta: compactMeta([
      alert.type ? `Tipo ${humanizeValue(alert.type)}` : null,
      alert.status ? `Estado ${humanizeValue(alert.status)}` : null,
    ]),
    fields: buildFields([
      ['Alerta', alert.title],
      ['Tipo', humanizeValue(alert.type)],
      ['Estado', humanizeValue(alert.status)],
    ]),
  }
}

function buildFields(values: Array<[string, string | null | undefined]>) {
  return values
    .filter(([, value]) => Boolean(value && String(value).trim() && value !== 'No disponible'))
    .map(([label, value]) => ({
      label,
      value: String(value),
    }))
}

function compactMeta(values: Array<string | null | undefined>) {
  return values.filter((value): value is string => Boolean(value && value.trim()))
}

function humanizeWorkshopType(type?: string | null) {
  if (!type) {
    return 'Taller'
  }

  const labels: Record<string, string> = {
    chapa: 'Chapa',
    dab: 'DAB',
    limpieza: 'Limpieza',
  }

  return labels[type] ?? humanizeValue(type)
}

function humanizeValue(value?: string | null) {
  if (!value) {
    return 'No disponible'
  }

  return value.replaceAll('_', ' ')
}

function locationTitle(location: VehicleLocation) {
  const labels: Record<string, string> = {
    recepcion_campa: 'Recepcion registrada en campa',
    entrada_taller: 'Entrada a taller registrada',
    salida_taller: 'Salida de taller registrada',
  }

  return labels[location.location_type || ''] ?? 'Movimiento de ubicacion registrado'
}

function formatCoordinate(value?: number | null) {
  if (value === null || value === undefined) {
    return 'No disponible'
  }

  return value.toFixed(6)
}

function buildStorageUrl(path?: string | null) {
  if (!path) {
    return undefined
  }

  if (/^https?:\/\//i.test(path)) {
    return path
  }

  return `/storage/${path.replace(/^\/+/, '')}`
}
</script>
