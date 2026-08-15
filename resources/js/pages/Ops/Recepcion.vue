<template>
  <FullScreenLayout>
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(250,204,21,0.18),_transparent_38%),linear-gradient(180deg,_#17130a_0%,_#21180a_48%,_#2d2110_100%)] text-white">
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -left-16 top-20 h-48 w-48 rounded-full bg-amber-300/14 blur-3xl" />
        <div class="absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-orange-300/10 blur-3xl" />
      </div>

      <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col px-5 pb-8 pt-5 sm:px-6">
        <header class="animate-[slideDown_0.55s_ease-out]">
          <div class="flex items-center justify-between gap-3">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/85 transition hover:bg-white/12"
              @click="goToAccess"
            >
              <span class="text-sm leading-none">&lt;</span>
              Acceso
            </button>
            <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
              Campa 5001
            </div>
          </div>

          <div class="mt-6">
            <p class="text-sm font-medium text-white/70">{{ provider?.name ?? 'Entrada de campa' }}</p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight text-white">
              {{ screenTitle }}
            </h1>
            <p class="mt-3 max-w-sm text-sm leading-6 text-slate-200/80">
              {{ screenDescription }}
            </p>
          </div>
        </header>

        <section v-if="screen === 'capture'" class="mt-7 space-y-5">
          <article
            v-if="!hasAnalysisResult"
            class="animate-[panelRise_0.65s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.4)] backdrop-blur-xl"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 1</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Foto frontal del vehiculo</h2>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
                Lectura de placa
              </div>
            </div>

            <div class="mt-4 rounded-[24px] border border-dashed border-white/18 bg-slate-950/35 p-4">
              <div v-if="imagePreviewUrl" class="overflow-hidden rounded-[20px] border border-white/10 bg-black/20">
                <img :src="imagePreviewUrl ?? undefined" alt="Vista previa del vehiculo" class="h-72 w-full object-cover" />
              </div>

              <div v-else class="flex aspect-[4/3] items-center justify-center rounded-[20px] border border-white/8 bg-[linear-gradient(180deg,_rgba(255,255,255,0.08),_rgba(255,255,255,0.02))]">
                <div class="text-center">
                  <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/8 text-white/90">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.3-2h7.4L17 7h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                      <circle cx="12" cy="13" r="3.5" />
                    </svg>
                  </div>
                  <p class="mt-4 text-sm font-semibold text-white">Adjunta la foto frontal</p>
                  <p class="mt-2 text-xs leading-5 text-slate-300/80">
                    Puedes hacerla ahora mismo o elegirla desde tu galeria.
                  </p>
                </div>
              </div>

              <OpsPhotoPicker
                class="mt-4"
                title="Adjuntar foto frontal"
                description="Puedes hacer la foto ahora mismo o elegir una imagen que ya tengas."
                @select="handleImageSelection"
              />
            </div>

            <div v-if="analysisError" class="mt-4 rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
              {{ analysisError }}
            </div>

            <div v-if="isAnalyzing" class="mt-4 rounded-2xl border border-amber-300/15 bg-amber-300/8 px-4 py-3 text-sm leading-6 text-amber-50/90">
              Estamos revisando la foto para localizar el vehiculo...
            </div>
          </article>

          <article
            v-else
            class="animate-[panelRise_0.8s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 1</p>
                <h2 class="mt-1 text-lg font-semibold text-white">
                  {{ workflowStage === 'final_reception' ? 'Vehiculo listo para volver a campa' : 'Vehiculo encontrado' }}
                </h2>
              </div>
              <button
                type="button"
                class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/75 transition hover:bg-white/12"
                @click="resetAnalysis"
              >
                Repetir foto
              </button>
            </div>

            <div v-if="imagePreviewUrl" class="mt-4 overflow-hidden rounded-[24px] border border-white/10 bg-black/20">
              <img :src="imagePreviewUrl ?? undefined" alt="Vista previa del vehiculo" class="h-44 w-full object-cover" />
            </div>

            <div class="mt-4 rounded-[24px] border border-white/10 bg-white/6 px-4 py-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Placa detectada</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[0.18em] text-white">{{ detectedPlate || 'Sin lectura' }}</p>
                </div>
                <div v-if="ocrConfidence !== null" class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
                  Lectura {{ formatConfidence(ocrConfidence) }}
                </div>
              </div>
            </div>

            <div v-if="matchedVehicle" class="mt-4 rounded-[24px] border border-emerald-300/12 bg-emerald-400/8 px-4 py-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100/70">Vehiculo localizado</p>
                  <h3 class="mt-2 text-lg font-semibold text-white">
                    {{ matchedVehicle.brand }} {{ matchedVehicle.model }}
                  </h3>
                  <p class="mt-1 text-sm text-emerald-50/85">{{ matchedVehicle.version || 'Version pendiente' }}</p>
                </div>
                <div class="rounded-full border border-white/10 bg-black/15 px-3 py-1 text-xs font-medium text-white/75">
                  {{ matchedVehicle.plate }}
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2 text-xs text-white/80">
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ matchedVehicle.color || 'Color sin dato' }}</div>
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ prettyStatus(matchedVehicle.physical_status) }}</div>
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ prettyStatus(matchedVehicle.commercial_status) }}</div>
              </div>
            </div>

            <OpsVehicleBodyMap
              v-if="matchedVehicle"
              class="mt-4"
              :damages="vehicleDamages"
              :can-create="true"
              :is-submitting="isSubmittingDamage"
              :error="damageError"
              :success="damageSuccess"
              @create-damage="submitMappedDamage"
            />

            <div v-else class="mt-4 space-y-4">
              <div class="rounded-[24px] border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/80">
                No encontramos este vehiculo. Si acaba de llegar a campa, puedes darlo de alta aqui mismo.
              </div>

              <div v-if="canCreateVehicle" class="rounded-[24px] border border-sky-300/15 bg-sky-300/8 px-4 py-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-100/70">Alta en el momento</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Crear vehiculo desde la foto</h3>
                    <p class="mt-2 text-xs leading-5 text-slate-300/80">
                      Hemos rellenado algunos datos automaticamente. Revisalos antes de guardar.
                    </p>
                  </div>
                  <div v-if="vehicleCreationSuggestion?.confidence !== null && vehicleCreationSuggestion?.confidence !== undefined" class="rounded-full border border-white/10 bg-black/15 px-3 py-1 text-xs font-medium text-white/75">
                    Sugerencia {{ formatConfidence(vehicleCreationSuggestion.confidence ?? 0) }}
                  </div>
                </div>

                <div class="mt-4 grid gap-3">
                  <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-100">Matricula</span>
                    <input
                      v-model="createVehicleForm.plate"
                      type="text"
                      inputmode="text"
                      class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm font-semibold uppercase tracking-[0.18em] text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                    />
                  </label>

                  <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                      <span class="mb-2 block text-sm font-medium text-slate-100">Marca</span>
                      <input
                        v-model="createVehicleForm.brand"
                        type="text"
                        class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                      />
                    </label>

                    <label class="block">
                      <span class="mb-2 block text-sm font-medium text-slate-100">Modelo</span>
                      <input
                        v-model="createVehicleForm.model"
                        type="text"
                        class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                      />
                    </label>
                  </div>

                  <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                      <span class="mb-2 block text-sm font-medium text-slate-100">Version</span>
                      <input
                        v-model="createVehicleForm.version"
                        type="text"
                        class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                      />
                    </label>

                    <label class="block">
                      <span class="mb-2 block text-sm font-medium text-slate-100">Color</span>
                      <input
                        v-model="createVehicleForm.color"
                        type="text"
                        class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                      />
                    </label>
                  </div>

                  <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-100">Notas</span>
                    <textarea
                      v-model="createVehicleForm.notes"
                      rows="2"
                      placeholder="Ej. creado en recepcion de campa"
                      class="w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 py-3 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-sky-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-sky-300/12"
                    />
                  </label>
                </div>

                <div v-if="createVehicleError" class="mt-4 rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                  {{ createVehicleError }}
                </div>

                <button
                  type="button"
                  :disabled="isCreatingVehicle || !createVehicleForm.plate.trim() || !createVehicleForm.brand.trim() || !createVehicleForm.model.trim()"
                  class="mt-4 flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#38bdf8,_#22d3ee)] px-4 text-base font-semibold text-slate-950 shadow-[0_18px_40px_rgba(34,211,238,0.28)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(34,211,238,0.32)] disabled:translate-y-0 disabled:opacity-60"
                  @click="createVehicleFromPhoto"
                >
                  {{ createVehicleButtonLabel }}
                </button>
              </div>
            </div>

            <div v-if="matchedVehicle && canProcessVehicle" class="mt-4 space-y-4">
              <div v-if="workflowStage === 'initial_reception'" class="rounded-[24px] border border-white/10 bg-white/6 px-4 py-4">
                <p class="text-sm font-semibold text-white">Siguientes etapas</p>
                <p class="mt-2 text-xs leading-5 text-slate-300/80">
                  Marca por donde debe pasar este vehiculo antes de volver a campa.
                </p>

                <div class="mt-4 space-y-3">
                  <label
                    v-for="stage in workflowStageOptions"
                    :key="stage.value"
                    class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/6 px-4 py-4 transition hover:bg-white/10"
                  >
                    <input
                      v-model="selectedWorkflowStages"
                      type="checkbox"
                      :value="stage.value"
                      class="mt-1 h-4 w-4 rounded border-white/20 bg-slate-900 text-amber-400 focus:ring-amber-300/40"
                    />
                    <span class="block">
                      <span class="block text-sm font-semibold text-white">{{ stage.label }}</span>
                      <span class="mt-1 block text-xs leading-5 text-slate-300/75">{{ stage.description }}</span>
                    </span>
                  </label>
                </div>

                <div class="mt-4 rounded-2xl border border-amber-300/15 bg-amber-300/8 px-4 py-4 text-xs leading-5 text-amber-50/90">
                  Si el vehiculo no necesita pasar por ninguno de estos 3 procesos, puedes continuar. En ese caso no se activara el seguro de flota.
                </div>
              </div>

              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-100">{{ notesLabel }}</span>
                <textarea
                  v-model="receptionNotes"
                  rows="3"
                  :placeholder="notesPlaceholder"
                  class="w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 py-3 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-amber-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-amber-300/12"
                />
              </label>

              <div v-if="workflowStage === 'initial_reception'" class="space-y-3">
                <OpsPhotoPicker
                  title="Subir fotos de salida del vehiculo"
                  description="Estas fotos serviran como evidencia del estado en el que sale hacia talleres."
                  multiple
                  @select="handleReleasePhotosChange"
                />

                <div v-if="releasePhotoNames.length" class="flex flex-wrap gap-2">
                  <div v-for="name in releasePhotoNames" :key="name" class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-xs text-white/80">
                    {{ name }}
                  </div>
                </div>
              </div>

              <div v-if="confirmError" class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                {{ confirmError }}
              </div>

              <button
                type="button"
                :disabled="isConfirming || !matchedVehicle"
                class="flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#f59e0b,_#facc15)] px-4 text-base font-semibold text-slate-950 shadow-[0_18px_40px_rgba(250,204,21,0.32)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(250,204,21,0.36)] disabled:translate-y-0 disabled:opacity-60"
                @click="processVehicle"
              >
                {{ isConfirming ? processingLabel : actionLabel }}
              </button>
            </div>

            <div v-if="matchedVehicle && !canProcessVehicle" class="mt-4 rounded-2xl border border-amber-300/15 bg-amber-300/8 px-4 py-4 text-sm leading-6 text-amber-50/90">
              {{ unsupportedMessage }}
            </div>
          </article>
        </section>

        <section v-else class="mt-7 space-y-5">
          <article class="animate-[panelRise_0.7s_cubic-bezier(0.22,1,0.36,1)] rounded-[30px] border border-emerald-300/16 bg-emerald-400/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.32)]">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-100/70">{{ completedTag }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ completedVehicle?.brand }} {{ completedVehicle?.model }}</h2>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">{{ completedDescription }}</p>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/80">{{ completedVehicle?.plate }}</div>
            </div>
          </article>

          <article class="animate-[panelRise_0.82s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Estado actual</p>
            <div class="mt-4 rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
              <p class="text-lg font-semibold text-white">{{ completedVehicle ? prettyStatus(completedVehicle.physical_status) : 'Sin estado' }}</p>
              <p class="mt-2 text-sm text-slate-300">{{ completedSecondaryDescription }}</p>
            </div>
          </article>

          <article v-if="lastProcessedStage === 'initial_reception'" class="animate-[panelRise_0.9s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Evidencias guardadas</p>
            <div class="mt-4 rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
              <p class="text-sm font-semibold text-white">Fotos de salida</p>
              <p class="mt-1 text-xs text-slate-300">{{ completedPhotoCount }} {{ completedPhotoCount === 1 ? 'imagen subida' : 'imagenes subidas' }}</p>
            </div>
          </article>

          <button
            type="button"
            class="mt-auto flex h-14 w-full items-center justify-center rounded-2xl border border-white/12 bg-white/8 px-4 text-base font-semibold text-white transition hover:bg-white/12"
            @click="goToAccess"
          >
            Volver al acceso
          </button>
        </section>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import OpsVehicleBodyMap from '@/pages/Ops/components/OpsVehicleBodyMap.vue'
import OpsPhotoPicker from '@/pages/Ops/components/OpsPhotoPicker.vue'

type ScreenState = 'capture' | 'completed'
type WorkflowStage = 'initial_reception' | 'final_reception' | 'unsupported'
type WorkshopStage = 'chapa' | 'dab' | 'limpieza'

interface VehicleSummary {
  id: number
  plate: string
  brand: string
  model: string
  version?: string | null
  color?: string | null
  physical_status: string
  commercial_status: string
}

interface VehicleCreationSuggestion {
  plate: string
  brand: string
  model: string
  version?: string | null
  color?: string | null
  confidence?: number | null
}

interface DamageSummary {
  id: number
  x_coord: number
  y_coord: number
  body_part: string | null
  body_part_label: string
  description: string
  severity: string
  status: string
  notes?: string | null
  stage: string
  provider?: string | null
  created_at?: string | null
}

defineProps<{
  provider?: {
    name: string
    type: string
  } | null
}>()

const screen = ref<ScreenState>('capture')
const workflowStage = ref<WorkflowStage>('initial_reception')
const lastProcessedStage = ref<'initial_reception' | 'final_reception'>('initial_reception')
const imagePreviewUrl = ref<string | null>(null)
const capturedImage = ref<File | null>(null)
const matchedVehicle = ref<VehicleSummary | null>(null)
const vehicleDamages = ref<DamageSummary[]>([])
const completedVehicle = ref<VehicleSummary | null>(null)
const completedPhotoCount = ref(0)
const completedInsuranceSkipped = ref(false)
const detectedPlate = ref('')
const ocrConfidence = ref<number | null>(null)
const receptionNotes = ref('')
const selectedWorkflowStages = ref<WorkshopStage[]>([])
const releasePhotos = ref<File[]>([])
const releasePhotoNames = ref<string[]>([])
const vehicleCreationSuggestion = ref<VehicleCreationSuggestion | null>(null)
const createVehicleForm = ref({
  plate: '',
  brand: '',
  model: '',
  version: '',
  color: '',
  notes: '',
})
const analysisError = ref('')
const confirmError = ref('')
const createVehicleError = ref('')
const damageError = ref('')
const damageSuccess = ref('')
const isAnalyzing = ref(false)
const isConfirming = ref(false)
const isCreatingVehicle = ref(false)
const isSubmittingDamage = ref(false)

const workflowStageOptions: Array<{ value: WorkshopStage; label: string; description: string }> = [
  {
    value: 'chapa',
    label: 'Chapa',
    description: 'Reparaciones de chapa y pintura antes de continuar el flujo.',
  },
  {
    value: 'dab',
    label: 'DAB',
    description: 'Revision tecnica interna y lista de chequeo mecanica.',
  },
  {
    value: 'limpieza',
    label: 'Limpieza',
    description: 'Preparacion final y fotos antes de volver a campa.',
  },
]

const hasAnalysisResult = computed(() => Boolean(detectedPlate.value || matchedVehicle.value))
const canProcessVehicle = computed(() => workflowStage.value !== 'unsupported')
const canCreateVehicle = computed(() => !matchedVehicle.value && detectedPlate.value !== '')

const screenTitle = computed(() => {
  if (screen.value === 'completed') {
    return 'Vehiculo registrado'
  }

  return workflowStage.value === 'final_reception'
    ? 'Marca el vehiculo como disponible'
    : 'Registrar entrada en campa'
})

const screenDescription = computed(() => {
  if (screen.value === 'completed') {
    return 'La entrada en campa ya quedo guardada. Aqui tienes el resumen antes de volver.'
  }

  return workflowStage.value === 'final_reception'
    ? 'Haz una foto frontal de la placa. Si el vehiculo vuelve de limpieza, podras dejarlo disponible.'
    : 'Haz una foto frontal de la placa. Si encontramos el vehiculo, podras confirmar su llegada y marcar por donde debe pasar.'
})

const notesLabel = computed(() => 'Comentarios')
const notesPlaceholder = computed(() => workflowStage.value === 'final_reception'
  ? 'Ej. vehiculo recibido en campa y listo para poner disponible'
  : 'Ej. vehiculo recibido en campa y pendiente de pasar por las etapas marcadas')
const actionLabel = computed(() => {
  if (workflowStage.value === 'final_reception') {
    return 'Marcar como disponible'
  }

  return selectedWorkflowStages.value.length === 0 ? 'Continuar' : 'Confirmar llegada'
})
const processingLabel = computed(() => workflowStage.value === 'final_reception' ? 'Marcando disponible...' : 'Confirmando llegada...')
const unsupportedMessage = computed(() => 'Este vehiculo no necesita ninguna accion en campa en este momento.')
const completedTag = computed(() => lastProcessedStage.value === 'final_reception' ? 'Vehiculo disponible' : 'Llegada confirmada')
const completedDescription = computed(() => lastProcessedStage.value === 'final_reception'
  ? 'El vehiculo ya esta de vuelta en campa y ha quedado disponible.'
  : completedInsuranceSkipped.value
    ? 'El vehiculo ya fue recibido en campa y no necesita pasar por taller.'
    : 'El vehiculo ya fue recibido en campa y su paso por talleres ya quedo preparado.')
const completedSecondaryDescription = computed(() => lastProcessedStage.value === 'final_reception'
  ? 'Ya puedes volver al acceso para continuar con otro vehiculo.'
  : completedInsuranceSkipped.value
    ? 'El seguro de flota no se activara para este vehiculo.'
    : 'Ya puedes volver al acceso para registrar otro vehiculo.')
const createVehicleButtonLabel = computed(() => isCreatingVehicle.value ? 'Creando vehiculo...' : 'Crear vehiculo ahora')

const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '')

const formatConfidence = (value: number) => `${Math.round(value * 100)}%`

const prettyStatus = (status?: string | null) => {
  if (!status) {
    return 'Sin estado'
  }

  const labels: Record<string, string> = {
    en_transito: 'En transito',
    en_campa: 'En campa',
    taller_chapa: 'Taller de chapa',
    taller_dab: 'DAB',
    taller_limpieza: 'Limpieza',
    pendiente_recogida: 'Pendiente de recogida',
    disponible: 'Disponible',
    entregado: 'Entregado',
    no_publicable: 'No publicable',
    reservado: 'Reservado',
    vendido: 'Vendido',
  }

  return labels[status] ?? status.replaceAll('_', ' ')
}

const revokePreview = () => {
  if (imagePreviewUrl.value) {
    URL.revokeObjectURL(imagePreviewUrl.value)
    imagePreviewUrl.value = null
  }
}

const goToAccess = () => {
  router.visit('/operaciones')
}

const resetReleasePhotos = () => {
  releasePhotos.value = []
  releasePhotoNames.value = []
}

const resetCreateVehicleForm = () => {
  vehicleCreationSuggestion.value = null
  createVehicleError.value = ''
  createVehicleForm.value = {
    plate: '',
    brand: '',
    model: '',
    version: '',
    color: '',
    notes: '',
  }
}

const resetAnalysis = () => {
  analysisError.value = ''
  confirmError.value = ''
  damageError.value = ''
  damageSuccess.value = ''
  matchedVehicle.value = null
  vehicleDamages.value = []
  completedVehicle.value = null
  completedPhotoCount.value = 0
  completedInsuranceSkipped.value = false
  detectedPlate.value = ''
  ocrConfidence.value = null
  receptionNotes.value = ''
  selectedWorkflowStages.value = []
  resetReleasePhotos()
  resetCreateVehicleForm()
  workflowStage.value = 'initial_reception'
  screen.value = 'capture'
  capturedImage.value = null
  revokePreview()

}

const handleImageSelection = async (files: File[]) => {
  const file = files[0]

  if (!file) {
    return
  }

  capturedImage.value = file
  revokePreview()
  imagePreviewUrl.value = URL.createObjectURL(file)
  await analyzePlate()
}

const handleReleasePhotosChange = (files: File[]) => {
  releasePhotos.value = files
  releasePhotoNames.value = files.map((file) => file.name)
}

const analyzePlate = async () => {
  if (!capturedImage.value) {
    return
  }

  isAnalyzing.value = true
  analysisError.value = ''
  confirmError.value = ''
  createVehicleError.value = ''
  damageError.value = ''
  damageSuccess.value = ''
  matchedVehicle.value = null
  vehicleDamages.value = []
  completedVehicle.value = null

  try {
    const formData = new FormData()
    formData.append('image', capturedImage.value)

    const response = await fetch('/operaciones/recepcion/analyze-plate', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: formData,
      credentials: 'same-origin',
    })

    const payload = await response.json().catch(() => ({}))

    detectedPlate.value = payload.plate || ''
    ocrConfidence.value = typeof payload.confidence === 'number' ? payload.confidence : null

    if (!response.ok) {
      analysisError.value = payload?.message || payload?.errors?.image?.[0] || 'No pudimos analizar la foto. Intenta de nuevo.'

      return
    }

    if (!payload.vehicle && payload?.plate) {
      const suggestedVehicle = payload?.suggested_vehicle ?? {}
      vehicleCreationSuggestion.value = {
        plate: suggestedVehicle?.plate || payload.plate || '',
        brand: suggestedVehicle?.brand || '',
        model: suggestedVehicle?.model || '',
        version: suggestedVehicle?.version || '',
        color: suggestedVehicle?.color || '',
        confidence: typeof suggestedVehicle?.confidence === 'number' ? suggestedVehicle.confidence : null,
      }
      createVehicleForm.value = {
        plate: suggestedVehicle?.plate || payload.plate || '',
        brand: suggestedVehicle?.brand || '',
        model: suggestedVehicle?.model || '',
        version: suggestedVehicle?.version || '',
        color: suggestedVehicle?.color || '',
        notes: '',
      }
      selectedWorkflowStages.value = []
      resetReleasePhotos()
      workflowStage.value = 'initial_reception'
      analysisError.value = payload?.message || 'No encontramos este vehiculo.'
      vehicleDamages.value = payload?.damages ?? []

      return
    }

    resetCreateVehicleForm()
    matchedVehicle.value = payload.vehicle ?? null
    vehicleDamages.value = payload?.damages ?? []
    selectedWorkflowStages.value = Object.entries(payload.workflow_plan ?? {})
      .filter(([, enabled]) => Boolean(enabled))
      .map(([stage]) => stage as WorkshopStage)

    if (payload.workflow_stage === 'final_reception') {
      workflowStage.value = 'final_reception'
    } else if (payload.workflow_stage === 'unsupported') {
      workflowStage.value = 'unsupported'
    } else {
      workflowStage.value = 'initial_reception'
    }
  } catch {
    analysisError.value = 'Ocurrio un problema al analizar la foto. Intenta de nuevo.'
  } finally {
    isAnalyzing.value = false
  }
}

const createVehicleFromPhoto = async () => {
  if (!canCreateVehicle.value || isCreatingVehicle.value) {
    return
  }

  isCreatingVehicle.value = true
  createVehicleError.value = ''

  try {
    const response = await fetch('/operaciones/recepcion/create-vehicle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: JSON.stringify({
        plate: createVehicleForm.value.plate,
        brand: createVehicleForm.value.brand,
        model: createVehicleForm.value.model,
        version: createVehicleForm.value.version,
        color: createVehicleForm.value.color,
        notes: createVehicleForm.value.notes,
      }),
      credentials: 'same-origin',
    })

    const payload = await response.json().catch(() => ({}))

    if (!response.ok) {
      createVehicleError.value = payload?.message
        || payload?.errors?.plate?.[0]
        || payload?.errors?.brand?.[0]
        || payload?.errors?.model?.[0]
        || 'No pudimos guardar el vehiculo ahora mismo. Intenta de nuevo.'

      return
    }

    matchedVehicle.value = payload.vehicle ?? null
    vehicleDamages.value = payload?.damages ?? []
    detectedPlate.value = payload.plate || createVehicleForm.value.plate
    ocrConfidence.value = typeof payload.confidence === 'number' ? payload.confidence : ocrConfidence.value
    workflowStage.value = 'initial_reception'
    analysisError.value = ''
    resetReleasePhotos()
    resetCreateVehicleForm()
  } catch {
    createVehicleError.value = 'No pudimos guardar el vehiculo. Intenta de nuevo.'
  } finally {
    isCreatingVehicle.value = false
  }
}

const submitMappedDamage = async (payload: {
  body_part: string
  x_coord: number
  y_coord: number
  severity: string
  notes: string
  photos: File[]
}) => {
  if (!matchedVehicle.value || isSubmittingDamage.value) {
    return
  }

  isSubmittingDamage.value = true
  damageError.value = ''
  damageSuccess.value = ''

  try {
    const response = await fetch('/operaciones/recepcion/report-mapped-damage', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: (() => {
        const formData = new FormData()
        formData.append('vehicle_id', String(matchedVehicle.value!.id))
        formData.append('body_part', payload.body_part)
        formData.append('x_coord', String(payload.x_coord))
        formData.append('y_coord', String(payload.y_coord))
        formData.append('severity', payload.severity)
        formData.append('notes', payload.notes)
        payload.photos.forEach((file) => formData.append('photos[]', file))
        return formData
      })(),
      credentials: 'same-origin',
    })

    const result = await response.json().catch(() => ({}))

    if (!response.ok) {
      damageError.value = result?.message
        || result?.errors?.body_part?.[0]
        || result?.errors?.severity?.[0]
        || 'No pudimos guardar la marca. Intenta de nuevo.'

      return
    }

    if (result?.damage) {
      vehicleDamages.value = [result.damage, ...vehicleDamages.value.filter((item) => item.id !== result.damage.id)]
    }

    damageSuccess.value = result?.message || 'La marca se guardo correctamente.'
  } catch {
    damageError.value = 'No pudimos guardar la marca en este momento. Intenta de nuevo.'
  } finally {
    isSubmittingDamage.value = false
  }
}

const getCurrentPosition = () => new Promise<GeolocationPosition>((resolve, reject) => {
  if (!navigator.geolocation) {
    reject(new Error('Geolocalizacion no disponible'))

    return
  }

  navigator.geolocation.getCurrentPosition(resolve, reject, {
    enableHighAccuracy: true,
    timeout: 15000,
    maximumAge: 0,
  })
})

const processVehicle = async () => {
  if (!matchedVehicle.value || workflowStage.value === 'unsupported') {
    return
  }

  isConfirming.value = true
  confirmError.value = ''

  try {
    const position = await getCurrentPosition()
    const formData = new FormData()

    formData.append('vehicle_id', String(matchedVehicle.value.id))
    formData.append('plate', detectedPlate.value || matchedVehicle.value.plate)
    formData.append('latitude', String(position.coords.latitude))
    formData.append('longitude', String(position.coords.longitude))
    formData.append('accuracy_meters', String(position.coords.accuracy))

    if (receptionNotes.value.trim()) {
      formData.append('notes', receptionNotes.value.trim())
    }

    if (workflowStage.value === 'initial_reception') {
      selectedWorkflowStages.value.forEach((stage) => {
        formData.append('workflow_plan[]', stage)
      })

      releasePhotos.value.forEach((file) => {
        formData.append('photos[]', file)
      })
    }

    const response = await fetch('/operaciones/recepcion/release-vehicle', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: formData,
      credentials: 'same-origin',
    })

      const payload = await response.json().catch(() => ({}))

      if (!response.ok) {
      confirmError.value = payload?.message || payload?.errors?.location?.[0] || payload?.errors?.vehicle_id?.[0] || payload?.errors?.workflow_plan?.[0] || payload?.errors?.photos?.[0] || payload?.errors?.['photos.0']?.[0] || 'No pudimos completar la recepcion. Intenta de nuevo.'

        return
      }

    lastProcessedStage.value = workflowStage.value === 'final_reception' ? 'final_reception' : 'initial_reception'
    completedVehicle.value = payload.vehicle ?? matchedVehicle.value
    completedPhotoCount.value = Number(payload.photo_count ?? 0)
    completedInsuranceSkipped.value = Boolean(payload.insurance_skipped)
    matchedVehicle.value = payload.vehicle ?? matchedVehicle.value
    screen.value = 'completed'
  } catch (error) {
    confirmError.value = error instanceof Error && error.message.includes('Geolocalizacion')
      ? 'Necesitamos acceso a la ubicacion para completar la recepcion. Activa la ubicacion y prueba de nuevo.'
      : 'Ocurrio un problema al completar la recepcion. Intenta de nuevo.'
  } finally {
    isConfirming.value = false
  }
}

onBeforeUnmount(() => {
  revokePreview()
})
</script>

<style scoped>
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-18px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes panelRise {
  from {
    opacity: 0;
    transform: translateY(24px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>


