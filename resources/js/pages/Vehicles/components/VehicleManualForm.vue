<template>
  <form class="space-y-8" @submit.prevent="$emit('submit')">
    <div class="field-reveal grid gap-6 md:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
          Empresa <span class="text-error-500">*</span>
        </label>
        <select v-model="form.company_id" :class="inputClass(form.errors.company_id)">
          <option :value="null">Selecciona una empresa</option>
          <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
        </select>
        <p v-if="form.errors.company_id" class="mt-1 text-sm text-red-500">{{ form.errors.company_id }}</p>
      </div>

      <div class="field-reveal" style="animation-delay: 60ms">
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Proveedor</label>
        <select v-model="form.provider_id" :class="inputClass(form.errors.provider_id)">
          <option :value="null">Sin proveedor</option>
          <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
        </select>
        <p v-if="form.errors.provider_id" class="mt-1 text-sm text-red-500">{{ form.errors.provider_id }}</p>
      </div>
    </div>

    <div class="field-reveal" style="animation-delay: 100ms">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Identificacion</h2>
      <div class="mt-4 grid gap-6 md:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Matricula <span class="text-error-500">*</span></label>
          <input v-model="form.plate" type="text" placeholder="1234ABC" :class="inputClass(form.errors.plate)" />
          <p v-if="form.errors.plate" class="mt-1 text-sm text-red-500">{{ form.errors.plate }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">VIN</label>
          <input v-model="form.vin" type="text" placeholder="1HGCM82633A004352" :class="inputClass(form.errors.vin)" />
          <p v-if="form.errors.vin" class="mt-1 text-sm text-red-500">{{ form.errors.vin }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Marca <span class="text-error-500">*</span></label>
          <input v-model="form.brand" type="text" placeholder="Toyota" :class="inputClass(form.errors.brand)" />
          <p v-if="form.errors.brand" class="mt-1 text-sm text-red-500">{{ form.errors.brand }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Modelo <span class="text-error-500">*</span></label>
          <input v-model="form.model" type="text" placeholder="Corolla" :class="inputClass(form.errors.model)" />
          <p v-if="form.errors.model" class="mt-1 text-sm text-red-500">{{ form.errors.model }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Version</label>
          <input v-model="form.version" type="text" placeholder="Sedan" :class="inputClass(form.errors.version)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Color</label>
          <input v-model="form.color" type="text" placeholder="Blanco" :class="inputClass(form.errors.color)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Fecha matriculacion</label>
          <div class="relative">
            <flat-pickr
              v-model="form.registration_date"
              :config="datePickerConfig"
              placeholder="Selecciona una fecha"
              :class="inputClass(form.errors.registration_date)"
            />
            <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500">
              <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z"
                  fill=""
                />
              </svg>
            </span>
          </div>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Kilometraje</label>
          <input v-model="form.mileage_km" type="number" min="0" placeholder="45000" :class="inputClass(form.errors.mileage_km)" />
        </div>
      </div>
    </div>

    <div class="field-reveal" style="animation-delay: 160ms">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Operacion y estado</h2>
      <div class="mt-4 grid gap-6 md:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Estado fisico <span class="text-error-500">*</span></label>
          <select v-model="form.physical_status" :class="inputClass(form.errors.physical_status)">
            <option v-for="option in physicalStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Estado comercial <span class="text-error-500">*</span></label>
          <select v-model="form.commercial_status" :class="inputClass(form.errors.commercial_status)">
            <option v-for="option in commercialStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Ubicacion bruta</label>
          <input v-model="form.raw_location" type="text" placeholder="Campa Madrid" :class="inputClass(form.errors.raw_location)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Estado proveedor</label>
          <input v-model="form.raw_physical_status" type="text" placeholder="EN TRANSITO" :class="inputClass(form.errors.raw_physical_status)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Comercial</label>
          <input v-model="form.salesperson_name" type="text" placeholder="Maria Gomez" :class="inputClass(form.errors.salesperson_name)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Numero de llaves</label>
          <select v-model="form.num_keys" :class="inputClass(form.errors.num_keys)">
            <option :value="0">0</option>
            <option :value="1">1</option>
            <option :value="2">2</option>
          </select>
        </div>
      </div>
    </div>

    <div class="field-reveal" style="animation-delay: 220ms">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Precios y notas</h2>
      <div class="mt-4 grid gap-6 md:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Base imponible</label>
          <input v-model="form.taxable_base" type="number" min="0" step="0.01" placeholder="15000" :class="inputClass(form.errors.taxable_base)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">PVI</label>
          <input v-model="form.pvi" type="number" min="0" step="0.01" placeholder="14000" :class="inputClass(form.errors.pvi)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio financiado</label>
          <input v-model="form.price_financed" type="number" min="0" step="0.01" placeholder="18000" :class="inputClass(form.errors.price_financed)" />
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Precio contado</label>
          <input v-model="form.price_cash" type="number" min="0" step="0.01" placeholder="17500" :class="inputClass(form.errors.price_cash)" />
        </div>
      </div>

      <div class="mt-6 grid gap-3 md:grid-cols-2">
        <label class="option-card flex items-start gap-3 rounded-2xl border border-slate-200 p-4 text-sm text-slate-700 transition duration-300 dark:border-slate-700 dark:text-slate-300">
          <input v-model="form.has_ficha_tecnica" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500" />
          <span>Tiene ficha tecnica</span>
        </label>
        <label class="option-card flex items-start gap-3 rounded-2xl border border-slate-200 p-4 text-sm text-slate-700 transition duration-300 dark:border-slate-700 dark:text-slate-300">
          <input v-model="form.has_permiso_circulacion" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500" />
          <span>Tiene permiso de circulacion</span>
        </label>
        <label class="option-card flex items-start gap-3 rounded-2xl border border-slate-200 p-4 text-sm text-slate-700 transition duration-300 dark:border-slate-700 dark:text-slate-300">
          <input v-model="form.insurance_active" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500" />
          <span>Seguro activo</span>
        </label>
        <label class="option-card flex items-start gap-3 rounded-2xl border border-slate-200 p-4 text-sm text-slate-700 transition duration-300 dark:border-slate-700 dark:text-slate-300">
          <input v-model="form.exported_to_motorflash" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500" />
          <span>Exportado a Motorflash</span>
        </label>
      </div>

      <div class="mt-6">
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Notas</label>
        <textarea v-model="form.notes" rows="4" placeholder="Observaciones internas del vehiculo" :class="textareaClass(form.errors.notes)" />
      </div>
    </div>

    <div class="field-reveal flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800" style="animation-delay: 280ms">
      <p class="text-sm text-slate-500 dark:text-slate-400">Los campos marcados con <span class="text-error-500">*</span> son obligatorios.</p>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-brand-500 dark:hover:bg-brand-600"
      >
        {{ form.processing ? processingLabel : submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

interface SelectOption {
  id: number
  name: string
}

interface FormLike {
  [key: string]: any
  errors: Record<string, string | undefined>
  processing: boolean
}

const form = defineModel<FormLike>('form', { required: true })

defineProps<{
  companies: SelectOption[]
  providers: SelectOption[]
  submitLabel: string
  processingLabel: string
}>()

defineEmits<{
  submit: []
}>()

const physicalStatusOptions = [
  { value: 'en_transito', label: 'En transito' },
  { value: 'en_campa', label: 'En campa' },
  { value: 'taller_chapa', label: 'Taller chapa' },
  { value: 'taller_dab', label: 'Taller DAB' },
  { value: 'taller_limpieza', label: 'Taller limpieza' },
  { value: 'pendiente_recogida', label: 'Pendiente recogida' },
  { value: 'disponible', label: 'Disponible' },
  { value: 'entregado', label: 'Entregado' },
] as const

const commercialStatusOptions = [
  { value: 'no_publicable', label: 'No publicable' },
  { value: 'disponible', label: 'Disponible' },
  { value: 'reservado', label: 'Reservado' },
  { value: 'vendido', label: 'Vendido' },
] as const

const datePickerConfig = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'd/m/Y',
  allowInput: true,
}

const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

const textareaBaseClass =
  'w-full rounded-2xl border bg-transparent px-4 py-3 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function inputClass(error?: string) {
  return [
    inputBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}

function textareaClass(error?: string) {
  return [
    textareaBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}
</script>

<style scoped>
.field-reveal {
  animation: fieldReveal 520ms ease-out both;
}

.option-card:hover {
  transform: translateY(-2px);
  border-color: rgb(148 163 184 / 0.55);
  box-shadow: 0 18px 40px -28px rgb(15 23 42 / 0.45);
}

@keyframes fieldReveal {
  from {
    opacity: 0;
    transform: translateY(14px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
