<template>
  <div :class="wrapperClass">
    <div v-if="!compact">
      <span class="block text-sm font-semibold text-white">{{ title }}</span>
      <span v-if="description" class="mt-2 block text-xs leading-5 text-slate-300/80">{{ description }}</span>
    </div>

    <div :class="actionsClass">
      <button
        type="button"
        :disabled="disabled"
        :class="cameraButtonClass"
        @click="openCamera"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.3-2h7.4L17 7h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        <span v-if="!compact">{{ cameraLabel }}</span>
      </button>

      <button
        type="button"
        :disabled="disabled"
        :class="galleryButtonClass"
        @click="openGallery"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.6-4.6a1 1 0 011.4 0L13 14.4l3.6-3.6a1 1 0 011.4 0L20 13" />
          <rect x="3" y="5" width="18" height="14" rx="2" />
          <circle cx="8.5" cy="9.5" r="1.2" />
        </svg>
        <span v-if="!compact">{{ galleryLabel }}</span>
      </button>
    </div>

    <input
      ref="cameraInput"
      class="hidden"
      type="file"
      :accept="accept"
      :multiple="multiple"
      capture="environment"
      @change="handleChange"
    />

    <input
      ref="galleryInput"
      class="hidden"
      type="file"
      :accept="accept"
      :multiple="multiple"
      @change="handleChange"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

const props = withDefaults(defineProps<{
  title?: string
  description?: string
  multiple?: boolean
  compact?: boolean
  disabled?: boolean
  accept?: string
  cameraLabel?: string
  galleryLabel?: string
}>(), {
  title: 'Adjuntar imagen',
  description: '',
  multiple: false,
  compact: false,
  disabled: false,
  accept: 'image/*',
  cameraLabel: 'Tomar foto',
  galleryLabel: 'Elegir archivo',
})

const emit = defineEmits<{
  (e: 'select', files: File[]): void
}>()

const cameraInput = ref<HTMLInputElement | null>(null)
const galleryInput = ref<HTMLInputElement | null>(null)

const wrapperClass = computed(() => props.compact
  ? 'flex items-center gap-2'
  : 'rounded-[24px] border border-dashed border-white/18 bg-white/6 p-4 transition hover:bg-white/10',
)

const actionsClass = computed(() => props.compact
  ? 'flex items-center gap-2'
  : 'mt-4 flex flex-wrap gap-2',
)

const compactButtonClass = 'inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/14 bg-white/8 text-white/85 transition hover:bg-white/12 disabled:opacity-60'
const fullButtonBaseClass = 'inline-flex h-11 items-center justify-center gap-2 rounded-2xl border px-4 text-sm font-semibold transition disabled:opacity-60'

const cameraButtonClass = computed(() => props.compact
  ? compactButtonClass
  : `${fullButtonBaseClass} border-cyan-300/25 bg-cyan-300/10 text-cyan-50 hover:bg-cyan-300/16`,
)

const galleryButtonClass = computed(() => props.compact
  ? compactButtonClass
  : `${fullButtonBaseClass} border-white/14 bg-white/8 text-white/85 hover:bg-white/12`,
)

const resetInputs = () => {
  if (cameraInput.value) {
    cameraInput.value.value = ''
  }

  if (galleryInput.value) {
    galleryInput.value.value = ''
  }
}

const handleChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? [])

  if (files.length) {
    emit('select', files)
  }

  resetInputs()
}

const openCamera = () => {
  if (!props.disabled) {
    cameraInput.value?.click()
  }
}

const openGallery = () => {
  if (!props.disabled) {
    galleryInput.value?.click()
  }
}
</script>
