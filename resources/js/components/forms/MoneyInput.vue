<template>
  <div class="relative">
    <span
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-gray-400 dark:text-gray-500"
    >
      {{ symbol }}
    </span>
    <input
      v-bind="$attrs"
      type="text"
      inputmode="numeric"
      autocomplete="off"
      :value="display"
      :placeholder="placeholder ?? format(0)"
      :class="[symbolPadding, 'text-right tabular-nums']"
      @input="onInput"
      @focus="moveCaretToEnd"
      @click="moveCaretToEnd"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'

defineOptions({ inheritAttrs: false })

/**
 * Input de montos con formato de moneda (es-VE: 1.234,56).
 * Se escribe como en un cajero: los dígitos entran por la derecha y los decimales se
 * completan solos. Emite un number (o null si está vacío) para que el backend reciba
 * el valor sin formato.
 */
const props = withDefaults(
  defineProps<{
    modelValue: string | number | null | undefined
    currency?: 'USD' | 'BS' | 'VES' | 'EUR'
    decimals?: number
    placeholder?: string
  }>(),
  { currency: 'USD', decimals: 2, placeholder: undefined },
)

const emit = defineEmits<{ 'update:modelValue': [value: number | null] }>()

const symbol = computed(() => ({ USD: '$', BS: 'Bs.', VES: 'Bs.', EUR: '€' })[props.currency])
const symbolPadding = computed(() => (symbol.value.length > 1 ? 'pl-11!' : 'pl-8!'))

const formatter = computed(
  () =>
    new Intl.NumberFormat('es-VE', {
      minimumFractionDigits: props.decimals,
      maximumFractionDigits: props.decimals,
    }),
)

const format = (value: number) => formatter.value.format(value)

const toDisplay = (value: typeof props.modelValue) => {
  if (value === null || value === undefined || value === '') return ''
  const n = Number(value)
  return Number.isFinite(n) ? format(n) : ''
}

const display = ref(toDisplay(props.modelValue))

watch(
  () => [props.modelValue, props.decimals] as const,
  ([value]) => {
    display.value = toDisplay(value)
  },
)

const onInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  const digits = input.value.replace(/\D/g, '').replace(/^0+/, '').slice(0, 15)

  if (!digits) {
    display.value = ''
    input.value = ''
    emit('update:modelValue', null)
    return
  }

  const value = Number(digits) / 10 ** props.decimals
  display.value = format(value)
  input.value = display.value
  emit('update:modelValue', value)
  moveCaretToEnd(event)
}

const moveCaretToEnd = (event: Event) => {
  const input = event.target as HTMLInputElement
  requestAnimationFrame(() => {
    const end = input.value.length
    input.setSelectionRange(end, end)
  })
}
</script>
