<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Editar Vehiculo'" />

    <div class="space-y-6">
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.28),_transparent_30%),linear-gradient(135deg,_#172554_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm animate-badge-float">
                Edicion de inventario
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Ajusta la informacion del vehiculo {{ vehicle.plate }}
              </h1>
             
            </div>

            <transition-group tag="div" class="flex flex-wrap gap-3" name="hero-action" appear>
              <button
                key="show"
                type="button"
                :disabled="Boolean(activeNavigation) || form.processing"
                @click="navigateTo('show')"
                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
              >
                {{ activeNavigation === 'show' ? 'Cargando...' : 'Ver detalle' }}
              </button>
              <button
                key="index"
                type="button"
                :disabled="Boolean(activeNavigation) || form.processing"
                @click="navigateTo('index')"
                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
              >
                {{ activeNavigation === 'index' ? 'Cargando...' : 'Volver al listado' }}
              </button>
            </transition-group>
          </div>

          <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-3" name="hero-card" appear>
            <article
              v-for="card in heroCards"
              :key="card.title"
              :style="{ transitionDelay: `${card.delay}ms` }"
              class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:bg-white/14"
            >
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/55">{{ card.title }}</p>
              <p class="mt-3 text-2xl font-semibold">{{ card.value }}</p>
              <p class="mt-2 text-sm text-white/70">{{ card.caption }}</p>
            </article>
          </transition-group>
        </div>
      </section>

      <transition appear name="panel-fade">
        <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
          <VehicleManualForm
            :form="form"
            :companies="companies"
            :providers="providers"
            submit-label="Guardar cambios"
            processing-label="Actualizando..."
            @submit="submit"
          />
        </section>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import VehicleManualForm from './components/VehicleManualForm.vue'

interface SelectOption {
  id: number
  name: string
}

interface VehicleData {
  id: number
  company_id: number | null
  provider_id: number | null
  plate: string
  vin: string | null
  brand: string
  model: string
  version: string | null
  color: string | null
  registration_date: string | null
  mileage_km: number | null
  raw_location: string | null
  raw_physical_status: string | null
  salesperson_name: string | null
  physical_status: string
  commercial_status: string
  taxable_base: number | null
  pvi: number | null
  price_financed: number | null
  price_cash: number | null
  num_keys: number | null
  has_ficha_tecnica: boolean
  has_permiso_circulacion: boolean
  insurance_active: boolean
  insurance_activated_at: string | null
  exported_to_motorflash: boolean
  last_export_at: string | null
  latitude: number | null
  longitude: number | null
  location_updated_at: string | null
  notes: string | null
}

interface Props {
  vehicle: VehicleData
  companies: SelectOption[]
  providers: SelectOption[]
}

const props = defineProps<Props>()
const activeNavigation = ref<null | 'show' | 'index'>(null)

function formatStatusValue(value: unknown) {
  if (typeof value !== 'string' || value.trim() === '') {
    return 'No definido'
  }

  return value.replaceAll('_', ' ')
}

const heroCards = [
  {
    title: 'Matricula',
    value: props.vehicle.plate,
    caption: 'Identificador principal del vehiculo.',
    delay: 0,
  },
  {
    title: 'Estado fisico',
    value: formatStatusValue(props.vehicle.physical_status),
    caption: 'Situacion operativa actual.',
    delay: 90,
  },
  {
    title: 'Estado comercial',
    value: formatStatusValue(props.vehicle.commercial_status),
    caption: 'Disponibilidad comercial de la unidad.',
    delay: 180,
  },
]

const form = useForm({
  company_id: props.vehicle.company_id,
  provider_id: props.vehicle.provider_id,
  plate: props.vehicle.plate ?? '',
  vin: props.vehicle.vin ?? '',
  brand: props.vehicle.brand ?? '',
  model: props.vehicle.model ?? '',
  version: props.vehicle.version ?? '',
  color: props.vehicle.color ?? '',
  registration_date: props.vehicle.registration_date ?? '',
  mileage_km: props.vehicle.mileage_km ?? null,
  raw_location: props.vehicle.raw_location ?? '',
  raw_physical_status: props.vehicle.raw_physical_status ?? '',
  salesperson_name: props.vehicle.salesperson_name ?? '',
  physical_status: props.vehicle.physical_status ?? 'en_transito',
  commercial_status: props.vehicle.commercial_status ?? 'no_publicable',
  taxable_base: props.vehicle.taxable_base ?? null,
  pvi: props.vehicle.pvi ?? null,
  price_financed: props.vehicle.price_financed ?? null,
  price_cash: props.vehicle.price_cash ?? null,
  num_keys: props.vehicle.num_keys ?? 0,
  has_ficha_tecnica: props.vehicle.has_ficha_tecnica ?? false,
  has_permiso_circulacion: props.vehicle.has_permiso_circulacion ?? false,
  insurance_active: props.vehicle.insurance_active ?? false,
  insurance_activated_at: props.vehicle.insurance_activated_at ?? '',
  exported_to_motorflash: props.vehicle.exported_to_motorflash ?? false,
  last_export_at: props.vehicle.last_export_at ?? '',
  latitude: props.vehicle.latitude ?? null,
  longitude: props.vehicle.longitude ?? null,
  location_updated_at: props.vehicle.location_updated_at ?? '',
  notes: props.vehicle.notes ?? '',
})

function submit() {
  if (form.processing || activeNavigation.value) {
    return
  }

  form.put(`/vehicles/${props.vehicle.id}`)
}

function navigateTo(target: 'show' | 'index') {
  if (activeNavigation.value || form.processing) {
    return
  }

  activeNavigation.value = target
  router.visit(target === 'show' ? `/vehicles/${props.vehicle.id}` : '/vehicles', {
    onFinish: () => {
      activeNavigation.value = null
    },
  })
}
</script>

<style scoped>
.panel-float {
  animation: panelFloat 720ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.animate-badge-float {
  animation: badgeFloat 5.5s ease-in-out infinite;
}

.hero-card-enter-active,
.hero-card-appear-active,
.hero-action-enter-active,
.hero-action-appear-active {
  transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-card-enter-from,
.hero-card-appear-from,
.hero-action-enter-from,
.hero-action-appear-from {
  opacity: 0;
  transform: translateY(24px) scale(0.98);
}

.panel-fade-enter-active,
.panel-fade-appear-active {
  transition: opacity 420ms ease, transform 420ms ease;
}

.panel-fade-enter-from,
.panel-fade-appear-from {
  opacity: 0;
  transform: translateY(18px);
}

@keyframes panelFloat {
  from {
    opacity: 0;
    transform: translateY(28px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes badgeFloat {
  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-2px);
  }
}
</style>
