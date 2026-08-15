export type PhysicalStatus =
  | 'en_transito'
  | 'en_campa'
  | 'taller_chapa'
  | 'taller_dab'
  | 'taller_limpieza'
  | 'pendiente_recogida'
  | 'disponible'
  | 'entregado'

export type CommercialStatus = 'no_publicable' | 'disponible' | 'reservado' | 'vendido'

export interface VehicleRelationName {
  id: number
  name: string
}

export interface VehiclePhoto {
  id: number
  label?: string | null
  type?: string | null
  stage?: string | null
  is_mandatory?: boolean
  url?: string | null
  disk?: string | null
  mime_type?: string | null
  size_bytes?: number | null
  created_at?: string | null
}

export interface VehicleDocument {
  id: number
  name?: string | null
  document_type?: string | null
  created_at?: string | null
  updated_at?: string | null
  file_url?: string | null
  download_url?: string | null
  mime_type?: string | null
  size_kb?: number | null
  status?: 'pendiente' | 'descargado' | 'procesado' | string | null
  notes?: string | null
}

export interface VehicleCostEntry {
  id: number
  concept?: string | null
  amount?: number | string | null
  entry_date?: string | null
  created_at?: string | null
}

export interface VehicleHistoryItem {
  id: number
  created_at?: string | null
}

export interface VehicleLocation {
  id: number
  latitude?: number | null
  longitude?: number | null
  accuracy_meters?: number | null
  location_type?: string | null
  recorded_at?: string | null
  notes?: string | null
  pin_prefix?: string | null
  provider?: VehicleRelationName | null
}

export interface VehicleWorkshopOrder {
  id: number
  type?: string | null
  status?: string | null
  created_at?: string | null
  entry_at?: string | null
  exit_at?: string | null
  notes?: string | null
  exit_photo_url?: string | null
  exit_photo_disk?: string | null
  provider?: VehicleRelationName | null
}

export interface VehicleAlert {
  id: number
  title?: string | null
  type?: string | null
  status?: string | null
  created_at?: string | null
}

export interface VehicleData {
  id: number
  plate: string
  vin?: string | null
  brand: string
  model: string
  version?: string | null
  color?: string | null
  registration_date?: string | null
  mileage_km?: number | null
  physical_status: PhysicalStatus | string
  commercial_status: CommercialStatus | string
  price_cash?: number | string | null
  price_financed?: number | string | null
  taxable_base?: number | string | null
  pvi?: number | string | null
  notes?: string | null
  created_at?: string | null
  company?: VehicleRelationName | null
  provider?: VehicleRelationName | null
  locations?: VehicleLocation[]
  photos: VehiclePhoto[]
  documents: VehicleDocument[]
  cost_entries: VehicleCostEntry[]
  workshop_orders: VehicleWorkshopOrder[]
  sale_contracts: VehicleHistoryItem[]
  alerts: VehicleAlert[]
}

export const physicalStatusLabels: Record<PhysicalStatus, string> = {
  en_transito: 'En transito',
  en_campa: 'En campa',
  taller_chapa: 'En taller chapa',
  taller_dab: 'En taller DAB',
  taller_limpieza: 'En limpieza',
  pendiente_recogida: 'Pendiente recogida',
  disponible: 'Disponible',
  entregado: 'Entregado',
}

export const commercialStatusLabels: Record<CommercialStatus, string> = {
  no_publicable: 'No publicable',
  disponible: 'Disponible',
  reservado: 'Reservado',
  vendido: 'Vendido',
}

export function parseNumber(value: number | string | null | undefined) {
  if (typeof value === 'number') {
    return value
  }

  if (typeof value === 'string' && value.trim() !== '') {
    return Number(value)
  }

  return 0
}

export function formatCurrency(value: number | string | null | undefined) {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
  }).format(parseNumber(value))
}

export function formatNumber(value: number | null | undefined) {
  return new Intl.NumberFormat('es-ES').format(value ?? 0)
}

export function formatDate(value: string | null | undefined) {
  if (!value) {
    return 'No disponible'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return 'No disponible'
  }

  return new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

export function getPhysicalStatusLabel(status: string) {
  return physicalStatusLabels[status as PhysicalStatus] || status
}

export function getCommercialStatusLabel(status: string) {
  return commercialStatusLabels[status as CommercialStatus] || status
}

export function getPhysicalStatusClass(status: string) {
  const classes = {
    en_transito: 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    en_campa: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300',
    taller_chapa: 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
    taller_dab: 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300',
    taller_limpieza: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
    pendiente_recogida: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
    disponible: 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-300',
    entregado: 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200',
  } as const

  return classes[status as keyof typeof classes] || 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200'
}

export function getCommercialStatusClass(status: string) {
  const classes = {
    no_publicable: 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200',
    disponible: 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-300',
    reservado: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300',
    vendido: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
  } as const

  return classes[status as keyof typeof classes] || 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200'
}

export function normalizeDocumentText(value: string | null | undefined) {
  return (value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}
