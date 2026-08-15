<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Crear Cliente'" />

    <div class="space-y-6">
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.28),_transparent_32%),linear-gradient(135deg,_#082f49_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm animate-badge-float">
                Alta de clientes
              </span>
              <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                Crear clientes uno a uno o importar un lote completo
              </h1>
            </div>

            <Link
              href="/clients"
              class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
            >
              Volver al listado
            </Link>
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

      <transition-group v-if="flash.success || flash.warning || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
        <Alert key="success" v-if="flash.success" variant="success" title="Proceso completado" :message="flash.success" />
        <Alert key="warning" v-if="flash.warning" variant="warning" title="Algunas filas fueron omitidas" :message="flash.warning" />
        <Alert key="error" v-if="flash.error" variant="error" title="No pudimos completar la accion" :message="flash.error" />
      </transition-group>

      <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
        <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
          <div class="flex flex-wrap gap-3 rounded-2xl bg-slate-100 p-2 dark:bg-slate-800/80">
            <button type="button" @click="activeMode = 'manual'" :class="tabClass('manual')">
              Creacion manual
            </button>
            <button type="button" @click="activeMode = 'import'" :class="tabClass('import')">
              Importacion CSV
            </button>
          </div>

          <transition name="mode-panel" mode="out-in">
            <ClientManualForm
              v-if="activeMode === 'manual'"
              key="manual"
              class="mt-6"
              :form="manualForm"
              :companies="companies"
              :providers="providers"
              submit-label="Crear cliente"
              processing-label="Guardando..."
              @submit="submitManual"
            />

            <form v-else key="import" class="mt-6 space-y-6" @submit.prevent="submitImport">
              <div class="field-reveal grid gap-6 md:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Empresa por defecto <span class="text-error-500">*</span></label>
                  <select v-model="importForm.company_id" :class="inputClass(importForm.errors.company_id)">
                    <option :value="null">Selecciona una empresa</option>
                    <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                  </select>
                  <p v-if="importForm.errors.company_id" class="mt-1 text-sm text-red-500">{{ importForm.errors.company_id }}</p>
                </div>
                <div class="field-reveal" style="animation-delay: 60ms">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Proveedor por defecto</label>
                  <select v-model="importForm.provider_id" :class="inputClass(importForm.errors.provider_id)">
                    <option :value="null">Sin proveedor</option>
                    <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                  </select>
                </div>
              </div>

              <div class="field-reveal rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800/40" style="animation-delay: 120ms">
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Archivo CSV <span class="text-error-500">*</span></label>
                <input
                  type="file"
                  accept=".csv,.txt"
                  class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition duration-300 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-950 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-brand-500"
                  @change="handleImportFileChange"
                />
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                  {{ selectedImportFileName ? `Archivo seleccionado: ${selectedImportFileName}` : 'No hay archivo seleccionado.' }}
                </p>
                <p v-if="importForm.errors.file" class="mt-1 text-sm text-red-500">{{ importForm.errors.file }}</p>
              </div>

              <div class="field-reveal flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" style="animation-delay: 180ms">
                <div class="text-sm text-slate-500 dark:text-slate-400">La posicion `0` del CSV es `tipo`. La importacion no usa encabezados; solo orden de columnas.</div>
                <button
                  type="submit"
                  :disabled="importForm.processing"
                  class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-brand-500 dark:hover:bg-brand-600"
                >
                  {{ importForm.processing ? 'Importando...' : 'Importar clientes' }}
                </button>
              </div>
            </form>
          </transition>
        </section>

        <aside class="space-y-6">
          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Mapa posicional del CSV</h2>
            <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
              <div v-for="column in csvColumns" :key="column.index" class="map-card rounded-2xl bg-slate-100 px-4 py-3 transition duration-300 dark:bg-slate-800">
                <div class="flex items-center justify-between gap-3">
                  <div><span class="font-semibold">{{ column.index }}</span> -> {{ column.label }}</div>
                  <span
                    v-if="column.required"
                    class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 transition duration-300 dark:bg-rose-500/15 dark:text-rose-200"
                  >
                    Obligatorio
                  </span>
                </div>
              </div>
            </div>
            <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
              Si envias una cabecera como primera fila, se intentara importar como datos. El archivo debe venir ya en el orden esperado.
            </p>
          </section>

          <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900" style="animation-delay: 90ms">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Ejemplo rapido</h2>
            <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100"><code>fisica,Carlos Perez,,12345678A,carlos@mail.com,600123456,,Calle Falsa 123,Madrid,28001,Madrid,España,Juan,Cliente VIP
juridica,Empresa S.A.,Empresa S.A.,B12345678,contacto@empresa.com,912345678,,Av. Industria 5,Valencia,46001,Valencia,España,Carlos,Importante</code></pre>
          </section>

          <section class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 shadow-lg shadow-amber-100/60 dark:border-amber-500/20 dark:bg-amber-500/10" style="animation-delay: 180ms">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-200">Notas de importacion</h2>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-amber-800 dark:text-amber-100/80">
              <li>Cada fila del archivo representa un cliente.</li>
              <li>Debes completar como minimo las posiciones 0 Tipo, 1 Nombre, 3 NIF.</li>
              <li>Si un cliente ya existe (segun NIF), se actualiza en lugar de duplicarse.</li>
              <li>La empresa seleccionada en esta pantalla se aplicara automaticamente a todas las filas importadas.</li>
            </ul>
          </section>
        </aside>
      </div>
    </div>
  </AdminLayout>
</template>
<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import Alert from '@/components/ui/Alert.vue'
import ClientManualForm from '@/components/clients/ClientManualForm.vue'

interface SelectOption {
  id: number
  name: string
}

interface FlashProps {
  success?: string | null
  warning?: string | null
  error?: string | null
}

interface Props {
  companies: SelectOption[]
  providers: SelectOption[]
}

defineProps<Props>()
const page = usePage<{ flash?: FlashProps }>()

const activeMode = ref<'manual' | 'import'>('manual')
const selectedImportFileName = ref('')
const flash = computed(() => page.props.flash ?? {})

// Campos clave para CSV de clientes
const csvColumns = [
  { index: 0, label: 'Tipo (fisica/juridica)', required: true },
  { index: 1, label: 'Nombre', required: false },
  { index: 2, label: 'Razon social', required: false },
  { index: 3, label: 'NIF', required: false },
  { index: 4, label: 'Email', required: false },
  { index: 5, label: 'Telefono', required: false },
  { index: 6, label: 'Telefono secundario', required: false },
  { index: 7, label: 'Direccion', required: false },
  { index: 8, label: 'Ciudad', required: false },
  { index: 9, label: 'Codigo postal', required: false },
  { index: 10, label: 'Provincia', required: false },
  { index: 11, label: 'Pais', required: false },
  { index: 12, label: 'Persona contacto', required: false },
  { index: 13, label: 'Notas', required: false },
]

const heroCards = [
  { title: 'Modo manual', value: 'Manual', caption: 'Campos clave para crear un cliente.', delay: 0 },
  { title: 'Importacion', value: 'CSV', caption: 'Importa varios clientes desde un archivo CSV.', delay: 90 },
]

// Formulario manual para clientes
const manualForm = useForm({
  type: 'fisica' as 'fisica' | 'juridica',
  name: '',
  company_name: '',
  nif: '',
  email: '',
  phone: '',
  phone_secondary: '',
  address: '',
  city: '',
  postal_code: '',
  province: '',
  country: '',
  contact_person: '',
  notes: '',
})

const importForm = useForm({
  file: null as File | null,
})

const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function inputClass(error?: string) {
  return [
    inputBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}

function tabClass(mode: 'manual' | 'import') {
  return [
    'inline-flex min-h-11 flex-1 items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition duration-300',
    activeMode.value === mode
      ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white'
      : 'text-slate-500 hover:-translate-y-0.5 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white/90',
  ]
}

function submitManual() {
  manualForm.post('/clients')
}

function submitImport() {
  importForm.post('/clientes/import', {
    forceFormData: true,
    onSuccess: () => {
      importForm.reset('file')
      selectedImportFileName.value = ''
      activeMode.value = 'import'
    },
  })
}

function handleImportFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  importForm.file = file
  selectedImportFileName.value = file?.name ?? ''
}
</script>

<style scoped>
.panel-float {
  animation: panelFloat 720ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.field-reveal {
  animation: fieldReveal 520ms ease-out both;
}

.map-card:hover {
  transform: translateX(4px);
  background: rgb(226 232 240 / 0.9);
}

.animate-badge-float {
  animation: badgeFloat 5.5s ease-in-out infinite;
}

.hero-card-enter-active,
.hero-card-appear-active {
  transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-card-enter-from,
.hero-card-appear-from {
  opacity: 0;
  transform: translateY(24px) scale(0.98);
}

.flash-stack-enter-active,
.flash-stack-appear-active,
.flash-stack-leave-active {
  transition: opacity 340ms ease, transform 340ms ease;
}

.flash-stack-enter-from,
.flash-stack-appear-from,
.flash-stack-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.mode-panel-enter-active,
.mode-panel-leave-active {
  transition: opacity 260ms ease, transform 260ms ease;
}

.mode-panel-enter-from {
  opacity: 0;
  transform: translateY(16px);
}

.mode-panel-leave-to {
  opacity: 0;
  transform: translateY(-10px);
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
