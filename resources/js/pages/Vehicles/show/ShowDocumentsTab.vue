<template>
  <section class="space-y-6">
    <div
      v-if="flash.success"
      class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
    >
      {{ flash.success }}
    </div>

    <div
      v-if="uploadForm.errors.file || uploadForm.errors.type"
      class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
    >
      {{ uploadForm.errors.file || uploadForm.errors.type }}
    </div>

    <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h4.19a1.5 1.5 0 011.06.44l1.12 1.12a1.5 1.5 0 001.06.44h8.07a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Expediente documental</h2>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ completedDocuments }} de {{ documentSlots.length }} documentos cargados
              </p>
            </div>
          </div>
        </div>

        <button
          type="button"
          :disabled="uploadForm.processing"
          class="inline-flex items-center gap-2 self-start rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-70 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200"
          @click="openPicker()"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          {{ uploadForm.processing ? 'Subiendo...' : 'Subir documento' }}
        </button>
      </div>

      <div class="mt-6">
        <div class="mb-2 flex items-center justify-between text-sm font-medium">
          <span class="text-slate-500 dark:text-slate-400">{{ progressPercent }}%</span>
          <span class="text-indigo-600 dark:text-indigo-300">{{ completedDocuments }} completados</span>
        </div>
        <div class="h-3 rounded-full bg-slate-100 dark:bg-slate-800">
          <div
            class="h-3 rounded-full bg-[linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] transition-all duration-500"
            :style="{ width: `${progressPercent}%` }"
          />
        </div>
      </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
      <article
        v-for="slot in documentSlots"
        :key="slot.key"
        :class="[
          'rounded-[24px] border p-5 shadow-sm transition',
          slot.document
            ? 'border-emerald-400/70 bg-white dark:border-emerald-500/30 dark:bg-slate-900'
            : 'border-rose-300 bg-rose-50/50 dark:border-rose-500/30 dark:bg-rose-500/5',
        ]"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-start gap-4">
            <div :class="['flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-sm', slot.document ? 'bg-emerald-400' : 'bg-rose-400']">
              <component :is="slot.icon" class="h-6 w-6" />
            </div>

            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ slot.label }}</h3>
                <span
                  v-if="slot.document"
                  class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]"
                  :class="statusClass(slot.document.status)"
                >
                  {{ statusLabel(slot.document.status) }}
                </span>
              </div>

              <p
                v-if="slot.document"
                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
              >
                {{ slot.document.name }}
              </p>
              <p
                v-if="slot.document"
                class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-400"
              >
                <span class="inline-flex items-center gap-1">
                  <FileIcon class="h-4 w-4" />
                  {{ slot.meta.format }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <StorageIcon class="h-4 w-4" />
                  {{ slot.meta.size }}
                </span>
              </p>
              <p
                v-else
                class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-rose-500"
              >
                <WarningIcon class="h-4 w-4" />
                Aun no se ha cargado
              </p>
            </div>
          </div>

          <div v-if="slot.document" class="flex items-center gap-2">
            <a
              v-if="slot.document.file_url"
              :href="slot.document.file_url"
              target="_blank"
              rel="noreferrer"
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
              <EyeIcon class="h-4 w-4" />
            </a>
            <a
              v-if="slot.document.download_url || slot.document.file_url"
              :href="slot.document.download_url || slot.document.file_url || '#'"
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
              <DownloadIcon class="h-4 w-4" />
            </a>
          </div>

          <button
            v-else
            type="button"
            :disabled="uploadForm.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-[linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-70"
            @click="selectSlotAndUpload(slot)"
          >
            <UploadIcon class="h-4 w-4" />
            {{ uploadForm.processing && selectedSlot?.key === slot.key ? 'Subiendo...' : 'Subir' }}
          </button>
        </div>

        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
          {{ slot.helper }}
        </p>
      </article>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept=".pdf,.jpg,.jpeg,.png,.webp"
      class="hidden"
      @change="handleFileChange"
    />

    <teleport to="body">
      <div
        v-if="pickerOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        @click.self="closePicker"
      >
        <div class="w-full max-w-3xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">
                Subir documento
              </p>
              <h3 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Elige el tipo de documento</h3>
              <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Selecciona donde quieres colocar este archivo dentro del expediente del vehiculo.
              </p>
            </div>

            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
              @click="closePicker"
            >
              x
            </button>
          </div>

          <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <button
              v-for="slot in documentSlots"
              :key="`picker-${slot.key}`"
              type="button"
              :disabled="uploadForm.processing"
              class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-indigo-200 hover:bg-indigo-50/60 disabled:cursor-not-allowed disabled:opacity-70 dark:border-slate-700 dark:bg-slate-800/40 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10"
              @click="selectSlotAndUpload(slot)"
            >
              <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ slot.label }}</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ slot.document ? 'Ya hay un archivo cargado. Si subes otro, se actualizara.' : 'Aun no hay ningun archivo cargado en esta seccion.' }}
              </p>
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </section>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, defineComponent, h, ref } from 'vue'
import type { VehicleData, VehicleDocument } from './vehicleShow'
import { normalizeDocumentText } from './vehicleShow'

interface FlashProps {
  success?: string | null
}

interface DocumentSlotDefinition {
  key: string
  label: string
  helper: string
  synonyms: string[]
  icon: ReturnType<typeof icon>
}

const props = defineProps<{
  vehicle: VehicleData
}>()

const page = usePage<{ flash?: FlashProps }>()
const flash = computed(() => page.props.flash ?? {})
const fileInput = ref<HTMLInputElement | null>(null)
const pickerOpen = ref(false)
const selectedSlot = ref<DocumentSlotDefinition | null>(null)

const uploadForm = useForm({
  type: '',
  file: null as File | null,
  notes: '',
})

const FileIcon = icon('M7.5 3.75h6l3 3v13.5a1.5 1.5 0 01-1.5 1.5h-7.5A1.5 1.5 0 016 20.25V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75v3h3')
const StorageIcon = icon('M4.5 7.5h15M4.5 12h15M4.5 16.5h9')
const WarningIcon = icon('M12 9v4.5m0 3h.008v.008H12v-.008zM10.29 3.86L1.82 18a1.875 1.875 0 001.61 2.81h16.94A1.875 1.875 0 0021.98 18L13.51 3.86a1.875 1.875 0 00-3.22 0z')
const EyeIcon = icon('M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7zM12 15a3 3 0 100-6 3 3 0 000 6z')
const DownloadIcon = icon('M12 3v12m0 0l4-4m-4 4l-4-4M4.5 19.5h15')
const UploadIcon = icon('M12 21V9m0 0l4 4m-4-4l-4 4M4.5 4.5h15')

const requiredDocuments: DocumentSlotDefinition[] = [
  {
    key: 'ficha_tecnica',
    label: 'Ficha tecnica',
    helper: 'Documento base del vehiculo para identificar y validar la unidad.',
    synonyms: ['ficha tecnica', 'ficha_tecnica'],
    icon: icon('M7.5 3.75h6l3 3v13.5a1.5 1.5 0 01-1.5 1.5h-7.5A1.5 1.5 0 016 20.25V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75v3h3M9 12h6M9 15h4.5'),
  },
  {
    key: 'permiso_circulacion',
    label: 'Permiso de circulacion',
    helper: 'Permiso operativo para tener la documentacion principal completa.',
    synonyms: ['permiso de circulacion', 'permiso_circulacion'],
    icon: icon('M4.5 6.75h15A1.5 1.5 0 0121 8.25v7.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 15.75v-7.5a1.5 1.5 0 011.5-1.5zM7.5 10.5h3M7.5 13.5h6M16.5 12h.008v.008H16.5z'),
  },
  {
    key: 'factura_compra',
    label: 'Factura de compra',
    helper: 'Soporte de compra del vehiculo para control administrativo y financiero.',
    synonyms: ['factura de compra', 'factura_compra', 'factura'],
    icon: icon('M7.5 3.75h6l3 3v13.5a1.5 1.5 0 01-1.5 1.5h-7.5A1.5 1.5 0 016 20.25V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75v3h3M9 12h6M9 15h3'),
  },
  {
    key: 'informe_itv',
    label: 'Informe ITV',
    helper: 'Informe tecnico de la ITV para completar el expediente del vehiculo.',
    synonyms: ['informe itv', 'itv'],
    icon: icon('M7.5 4.5h9A1.5 1.5 0 0118 6v12a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 016 18V6A1.5 1.5 0 017.5 4.5zM9 12l1.5 1.5 4.5-4.5'),
  },
  {
    key: 'mandato_gestoria',
    label: 'Mandato gestoría',
    helper: 'Documento de apoyo para la gestoria y los tramites del vehiculo.',
    synonyms: ['mandato gestoria', 'mandato_gestoria', 'gestoria'],
    icon: icon('M13.5 6H6.75A2.25 2.25 0 004.5 8.25v9A2.25 2.25 0 006.75 19.5h9A2.25 2.25 0 0018 17.25V10.5L13.5 6zM13.5 6v4.5H18M9 15l1.5 1.5L15 12'),
  },
  {
    key: 'seguro',
    label: 'Seguro',
    helper: 'Poliza o justificante del seguro vinculado a esta unidad.',
    synonyms: ['seguro', 'poliza', 'poliza_seguro', 'insurance'],
    icon: icon('M12 3l7.5 3v5.25c0 4.664-3.19 8.905-7.5 9.75-4.31-.845-7.5-5.086-7.5-9.75V6L12 3z'),
  },
]

const documentSlots = computed(() =>
  requiredDocuments.map((required) => {
    const document = findDocument(required.synonyms)

    return {
      ...required,
      document,
      meta: getDocumentMeta(document),
    }
  })
)

const completedDocuments = computed(() => documentSlots.value.filter((slot) => slot.document).length)
const progressPercent = computed(() => Math.round((completedDocuments.value / documentSlots.value.length) * 100))

function findDocument(synonyms: readonly string[]) {
  return props.vehicle.documents.find((document) => {
    const haystack = `${normalizeDocumentText(document.document_type)} ${normalizeDocumentText(document.name)}`
    return synonyms.some((synonym) => haystack.includes(normalizeDocumentText(synonym)))
  })
}

function getDocumentMeta(document?: VehicleDocument) {
  if (!document) {
    return {
      format: '--',
      size: '--',
    }
  }

  const extension = document.mime_type?.split('/').pop()?.toUpperCase()
    || document.name?.split('.').pop()?.toUpperCase()
    || 'PDF'

  return {
    format: extension,
    size: document.size_kb ? `${document.size_kb} KB` : '-- KB',
  }
}

function openPicker() {
  if (uploadForm.processing) {
    return
  }

  pickerOpen.value = true
}

function closePicker() {
  if (uploadForm.processing) {
    return
  }

  pickerOpen.value = false
}

function selectSlotAndUpload(slot: DocumentSlotDefinition) {
  if (uploadForm.processing) {
    return
  }

  selectedSlot.value = slot
  pickerOpen.value = false
  fileInput.value?.click()
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] ?? null

  if (!file || !selectedSlot.value) {
    resetFileInput()
    return
  }

  uploadForm.type = selectedSlot.value.key
  uploadForm.file = file

  uploadForm.post(`/vehicles/${props.vehicle.id}/documents`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      uploadForm.reset()
      selectedSlot.value = null
      resetFileInput()
    },
    onError: () => {
      resetFileInput()
    },
  })
}

function resetFileInput() {
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function statusLabel(status?: string | null) {
  return ({
    pendiente: 'Pendiente',
    descargado: 'Descargado',
    procesado: 'Procesado',
  }[status ?? ''] ?? 'Guardado')
}

function statusClass(status?: string | null) {
  return ({
    pendiente: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    descargado: 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
    procesado: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
  }[status ?? ''] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200')
}

function icon(path: string) {
  return defineComponent({
    name: 'DocumentIcon',
    setup(_, { attrs }) {
      return () =>
        h(
          'svg',
          {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '1.8',
            ...attrs,
          },
          [
            h('path', {
              d: path,
              'stroke-linecap': 'round',
              'stroke-linejoin': 'round',
            }),
          ]
        )
    },
  })
}
</script>
