<template>
  <section class="rounded-[24px] border border-white/12 bg-white/6 px-4 py-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Mapa del vehiculo</p>
        <h3 class="mt-2 text-lg font-semibold text-white">Marcas del vehiculo</h3>
        <p class="mt-2 text-xs leading-5 text-slate-300/80">
          {{ canCreate
            ? 'Toca una zona para indicar donde has visto el detalle o dano.'
            : 'Aqui puedes ver las marcas que ya se registraron en este vehiculo.' }}
        </p>
      </div>
      <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/75">
        {{ damages.length }} {{ damages.length === 1 ? 'incidencia' : 'incidencias' }}
      </div>
    </div>

    <div class="mt-4 rounded-[24px] border border-white/10 bg-slate-950/45 p-4">
      <div class="relative mx-auto aspect-[0.74] w-full max-w-[260px]">
        <svg viewBox="0 0 220 300" class="h-full w-full text-white/90">
          <rect x="72" y="24" width="76" height="28" rx="14" class="fill-white/8 stroke-white/12" />
          <rect x="58" y="42" width="104" height="210" rx="44" class="fill-white/6 stroke-white/14" />
          <rect x="72" y="72" width="76" height="42" rx="18" class="fill-white/[0.03] stroke-white/10" />
          <rect x="72" y="120" width="76" height="60" rx="16" class="fill-white/[0.03] stroke-white/10" />
          <rect x="72" y="186" width="76" height="42" rx="18" class="fill-white/[0.03] stroke-white/10" />
          <rect x="72" y="246" width="76" height="28" rx="14" class="fill-white/8 stroke-white/12" />

          <rect x="44" y="86" width="18" height="46" rx="8" class="fill-white/7 stroke-white/12" />
          <rect x="158" y="86" width="18" height="46" rx="8" class="fill-white/7 stroke-white/12" />
          <rect x="44" y="146" width="18" height="46" rx="8" class="fill-white/7 stroke-white/12" />
          <rect x="158" y="146" width="18" height="46" rx="8" class="fill-white/7 stroke-white/12" />
          <rect x="44" y="206" width="18" height="34" rx="8" class="fill-white/7 stroke-white/12" />
          <rect x="158" y="206" width="18" height="34" rx="8" class="fill-white/7 stroke-white/12" />

          <g class="stroke-white/10">
            <line x1="110" y1="62" x2="110" y2="240" />
            <line x1="70" y1="150" x2="150" y2="150" />
          </g>
        </svg>

        <button
          v-for="part in bodyParts"
          :key="part.key"
          type="button"
          class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border border-cyan-300/18 bg-cyan-300/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-cyan-100 transition hover:scale-[1.04] hover:bg-cyan-300/18 disabled:cursor-not-allowed disabled:opacity-50"
          :class="{ 'ring-2 ring-cyan-300/40': selectedBodyPart === part.key }"
          :style="{ left: `${part.xPercent}%`, top: `${part.yPercent}%` }"
          :disabled="!canCreate || isSubmitting"
          @click="selectBodyPart(part)"
        >
          {{ part.shortLabel }}
        </button>

        <div
          v-for="damage in positionedDamages"
          :key="damage.id"
          class="absolute -translate-x-1/2 -translate-y-1/2"
          :style="{ left: `${damage.displayX}%`, top: `${damage.displayY}%` }"
        >
          <button
            type="button"
            class="group relative flex h-5 w-5 items-center justify-center rounded-full border border-orange-300/35 bg-orange-400/90 text-[11px] font-bold text-slate-950 shadow-[0_0_0_4px_rgba(251,146,60,0.18)]"
            :title="damage.body_part_label"
            @click="selectedDamageId = selectedDamageId === damage.id ? null : damage.id"
          >
            !
          </button>

          <div
            v-if="selectedDamageId === damage.id"
            class="absolute left-1/2 top-7 z-10 w-48 -translate-x-1/2 rounded-2xl border border-white/10 bg-slate-950/96 px-3 py-3 text-left shadow-xl"
          >
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-orange-200/80">{{ damage.body_part_label }}</p>
            <p class="mt-2 text-sm font-semibold text-white">{{ damage.description }}</p>
            <p class="mt-2 text-xs text-slate-300/85">Nivel: {{ severityLabel(damage.severity) }}</p>
            <p v-if="damage.notes" class="mt-2 text-xs leading-5 text-slate-300/80">{{ damage.notes }}</p>
            <p class="mt-2 text-[11px] text-slate-400">
              {{ damage.provider || 'Sin proveedor' }}<span v-if="damage.created_at"> - {{ damage.created_at }}</span>
            </p>
          </div>
        </div>
      </div>

      <div v-if="selectedPartMeta" class="mt-4 rounded-2xl border border-cyan-300/15 bg-cyan-300/8 px-4 py-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100/70">Nueva marca</p>
            <h4 class="mt-2 text-base font-semibold text-white">{{ selectedPartMeta.label }}</h4>
            <p class="mt-2 text-xs leading-5 text-slate-300/80">
              Esta marca quedara guardada para que el siguiente taller la vea al identificar el vehiculo.
            </p>
          </div>
          <button
            type="button"
            class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-[11px] font-medium text-white/75 transition hover:bg-white/12"
            @click="clearSelection"
          >
            Cancelar
          </button>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-100">Como se ve</span>
            <select
              v-model="severity"
              class="h-12 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-sm text-white outline-hidden focus:border-cyan-300/70 focus:ring-4 focus:ring-cyan-300/12"
            >
              <option value="leve" class="text-black">Leve</option>
              <option value="moderado" class="text-black">Media</option>
              <option value="grave" class="text-black">Fuerte</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-100">Zona</span>
            <input
              :value="selectedPartMeta.label"
              type="text"
              disabled
              class="h-12 w-full rounded-2xl border border-white/10 bg-white/8 px-4 text-sm text-white/85"
            />
          </label>
        </div>

        <label class="mt-3 block">
          <span class="mb-2 block text-sm font-medium text-slate-100">Comentario</span>
          <textarea
            v-model="notes"
            rows="3"
            placeholder="Ej. rayon largo, golpe leve, rueda pinchada..."
            class="w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 py-3 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-cyan-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-cyan-300/12"
          />
        </label>

        <div class="mt-3 flex items-center justify-between gap-3">
          <div>
            <span class="block text-sm font-medium text-slate-100">Fotos del dano</span>
            <span class="mt-1 block text-xs leading-5 text-slate-300/75">
              Puedes adjuntar una o varias imagenes si te ayuda a dejar mejor registrada la marca.
            </span>
          </div>

          <OpsPhotoPicker compact multiple @select="handleDamagePhotosChange" />
        </div>

        <div v-if="damagePhotoNames.length" class="mt-3 flex flex-wrap gap-2">
          <div
            v-for="name in damagePhotoNames"
            :key="name"
            class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-[11px] text-white/80"
          >
            {{ name }}
          </div>
        </div>

        <button
          type="button"
          :disabled="isSubmitting"
          class="mt-4 flex h-12 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#22d3ee,_#06b6d4)] px-4 text-sm font-semibold text-slate-950 shadow-[0_18px_40px_rgba(34,211,238,0.24)] transition duration-200 hover:-translate-y-0.5 disabled:translate-y-0 disabled:opacity-60"
          @click="submitDamage"
        >
          {{ isSubmitting ? 'Guardando marca...' : 'Guardar marca' }}
        </button>
      </div>
    </div>

    <div v-if="success" class="mt-4 rounded-2xl border border-emerald-300/18 bg-emerald-400/10 px-4 py-3 text-sm leading-6 text-emerald-50/90">
      {{ success }}
    </div>

    <div v-if="damages.length" class="mt-4 space-y-2">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Marcas guardadas</p>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="damage in damages"
          :key="`chip-${damage.id}`"
          class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-[11px] text-white/80"
        >
          {{ damage.body_part_label }} - {{ severityLabel(damage.severity) }}
        </div>
      </div>
      <p class="text-xs leading-5 text-slate-400/90">
        Puedes guardar varias marcas para el mismo vehiculo. Si varias caen en la misma zona, se mostraran separadas en el mapa.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import OpsPhotoPicker from '@/pages/Ops/components/OpsPhotoPicker.vue'

interface DamageItem {
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

interface BodyPartItem {
  key: string
  label: string
  shortLabel: string
  xPercent: number
  yPercent: number
}

interface PositionedDamage extends DamageItem {
  displayX: number
  displayY: number
}

const props = defineProps<{
  damages: DamageItem[]
  canCreate?: boolean
  isSubmitting?: boolean
  error?: string
  success?: string
}>()

const emit = defineEmits<{
  (e: 'create-damage', payload: {
    body_part: string
    x_coord: number
    y_coord: number
    severity: string
    notes: string
    photos: File[]
  }): void
}>()

const bodyParts: BodyPartItem[] = [
  { key: 'front_bumper', label: 'Parachoques delantero', shortLabel: 'Frente', xPercent: 50, yPercent: 8 },
  { key: 'hood', label: 'Capo', shortLabel: 'Capo', xPercent: 50, yPercent: 18 },
  { key: 'windshield', label: 'Parabrisas', shortLabel: 'Parab.', xPercent: 50, yPercent: 29 },
  { key: 'roof', label: 'Techo', shortLabel: 'Techo', xPercent: 50, yPercent: 42 },
  { key: 'rear_glass', label: 'Luna trasera', shortLabel: 'Luna', xPercent: 50, yPercent: 58 },
  { key: 'trunk', label: 'Porton trasero', shortLabel: 'Porton', xPercent: 50, yPercent: 70 },
  { key: 'rear_bumper', label: 'Parachoques trasero', shortLabel: 'Trasera', xPercent: 50, yPercent: 82 },
  { key: 'front_left_fender', label: 'Guardabarros delantero izquierdo', shortLabel: 'Guard. DI', xPercent: 24, yPercent: 21 },
  { key: 'front_right_fender', label: 'Guardabarros delantero derecho', shortLabel: 'Guard. DD', xPercent: 76, yPercent: 21 },
  { key: 'front_left_wheel', label: 'Rueda delantera izquierda', shortLabel: 'Rueda DI', xPercent: 16, yPercent: 30 },
  { key: 'front_right_wheel', label: 'Rueda delantera derecha', shortLabel: 'Rueda DD', xPercent: 84, yPercent: 30 },
  { key: 'left_front_door', label: 'Puerta delantera izquierda', shortLabel: 'Pta. DI', xPercent: 24, yPercent: 39 },
  { key: 'right_front_door', label: 'Puerta delantera derecha', shortLabel: 'Pta. DD', xPercent: 76, yPercent: 39 },
  { key: 'left_rear_door', label: 'Puerta trasera izquierda', shortLabel: 'Pta. TI', xPercent: 24, yPercent: 57 },
  { key: 'right_rear_door', label: 'Puerta trasera derecha', shortLabel: 'Pta. TD', xPercent: 76, yPercent: 57 },
  { key: 'rear_left_wheel', label: 'Rueda trasera izquierda', shortLabel: 'Rueda TI', xPercent: 16, yPercent: 68 },
  { key: 'rear_right_wheel', label: 'Rueda trasera derecha', shortLabel: 'Rueda TD', xPercent: 84, yPercent: 68 },
  { key: 'rear_left_panel', label: 'Lateral trasero izquierdo', shortLabel: 'Lat. TI', xPercent: 24, yPercent: 75 },
  { key: 'rear_right_panel', label: 'Lateral trasero derecho', shortLabel: 'Lat. TD', xPercent: 76, yPercent: 75 },
]

const selectedBodyPart = ref<string | null>(null)
const selectedDamageId = ref<number | null>(null)
const severity = ref<'leve' | 'moderado' | 'grave'>('leve')
const notes = ref('')
const damagePhotos = ref<File[]>([])
const damagePhotoNames = ref<string[]>([])

const selectedPartMeta = computed(() => bodyParts.find((part) => part.key === selectedBodyPart.value) ?? null)

const positionedDamages = computed<PositionedDamage[]>(() => {
  const groups = new Map<string, DamageItem[]>()

  for (const damage of props.damages) {
    const key = `${damage.x_coord.toFixed(4)}-${damage.y_coord.toFixed(4)}`
    const current = groups.get(key) ?? []
    current.push(damage)
    groups.set(key, current)
  }

  const positioned: PositionedDamage[] = []

  for (const group of groups.values()) {
    if (group.length === 1) {
      const damage = group[0]
      positioned.push({
        ...damage,
        displayX: damage.x_coord * 100,
        displayY: damage.y_coord * 100,
      })
      continue
    }

    group.forEach((damage, index) => {
      const angle = (Math.PI * 2 * index) / group.length
      const radius = 3.6
      const displayX = (damage.x_coord * 100) + (Math.cos(angle) * radius)
      const displayY = (damage.y_coord * 100) + (Math.sin(angle) * radius)

      positioned.push({
        ...damage,
        displayX: Math.min(95, Math.max(5, displayX)),
        displayY: Math.min(95, Math.max(5, displayY)),
      })
    })
  }

  return positioned
})

const severityLabel = (value: string) => {
  if (value === 'grave') {
    return 'Fuerte'
  }

  if (value === 'moderado') {
    return 'Media'
  }

  return 'Leve'
}

const selectBodyPart = (part: BodyPartItem) => {
  if (!props.canCreate || props.isSubmitting) {
    return
  }

  selectedBodyPart.value = part.key
}

const clearSelection = () => {
  selectedBodyPart.value = null
  severity.value = 'leve'
  notes.value = ''
  damagePhotos.value = []
  damagePhotoNames.value = []
}

const handleDamagePhotosChange = (files: File[]) => {
  damagePhotos.value = files
  damagePhotoNames.value = files.map((file) => file.name)
}

const submitDamage = () => {
  if (!selectedPartMeta.value) {
    return
  }

  emit('create-damage', {
    body_part: selectedPartMeta.value.key,
    x_coord: selectedPartMeta.value.xPercent / 100,
    y_coord: selectedPartMeta.value.yPercent / 100,
    severity: severity.value,
    notes: notes.value.trim(),
    photos: damagePhotos.value,
  })
}

watch(
  () => props.damages.length,
  (currentLength, previousLength) => {
    if (currentLength > previousLength) {
      clearSelection()
    }
  },
)
</script>
