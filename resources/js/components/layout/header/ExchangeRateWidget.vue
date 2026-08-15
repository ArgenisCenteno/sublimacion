<template>
  <div>
    <button
      type="button"
      @click="openModal"
      class="hidden items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-theme-xs font-medium text-gray-700 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] lg:flex"
      title="Actualizar tasas de cambio"
    >
      <span class="flex items-center gap-1">
        <span class="text-gray-400 dark:text-gray-500">Dólar</span>
        <span class="font-semibold text-gray-800 dark:text-white/90">{{ formattedBs }}</span>
      </span>
      <span class="h-3 w-px bg-gray-300 dark:bg-gray-700"></span>
      <span class="flex items-center gap-1">
        <span class="text-gray-400 dark:text-gray-500">Euro</span>
        <span class="font-semibold text-gray-800 dark:text-white/90">{{ formattedEur }}</span>
      </span>
    </button>

    <Modal v-if="isModalOpen" full-screen-backdrop @close="closeModal">
      <template #body>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
          <button
            type="button"
            @click="closeModal"
            class="absolute right-5 top-5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                fill="currentColor"
              />
            </svg>
          </button>

          <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Tasas de cambio</h4>
          <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Bolívares por cada dólar / euro.</p>

          <form @submit.prevent="submit">
            <div class="mb-4">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Tasa del dólar (Bs.)
              </label>
              <input
                v-model="form.bs"
                type="number"
                step="0.0001"
                min="0.0001"
                required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-300 dark:border-gray-700 dark:text-white/90"
              />
              <p v-if="errors.bs" class="mt-1.5 text-xs text-red-500">{{ errors.bs }}</p>
            </div>

            <div class="mb-6">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Tasa del euro (Bs.)
              </label>
              <input
                v-model="form.eur"
                type="number"
                step="0.0001"
                min="0.0001"
                required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-300 dark:border-gray-700 dark:text-white/90"
              />
              <p v-if="errors.eur" class="mt-1.5 text-xs text-red-500">{{ errors.eur }}</p>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button
                type="button"
                @click="closeModal"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-60"
              >
                {{ saving ? 'Guardando…' : 'Guardar' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import Modal from '../../ui/Modal.vue'

interface TasasCambio {
  BS: number | string | null
  EUR: number | string | null
}

const page = usePage<{ tasasCambio?: TasasCambio }>()

const tasas = computed<TasasCambio>(() => page.props.tasasCambio ?? { BS: null, EUR: null })

const formatRate = (value: number | string | null) => {
  if (value === null || value === undefined) {
return 'Sin definir'
}

  const numeric = typeof value === 'string' ? parseFloat(value) : value

  if (Number.isNaN(numeric)) {
return 'Sin definir'
}

  return numeric.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formattedBs = computed(() => formatRate(tasas.value.BS))
const formattedEur = computed(() => formatRate(tasas.value.EUR))

const isModalOpen = ref(false)
const saving = ref(false)
const errors = reactive<{ bs?: string; eur?: string }>({})
const form = reactive({ bs: '', eur: '' })

const openModal = () => {
  form.bs = tasas.value.BS !== null && tasas.value.BS !== undefined ? String(tasas.value.BS) : ''
  form.eur = tasas.value.EUR !== null && tasas.value.EUR !== undefined ? String(tasas.value.EUR) : ''
  errors.bs = undefined
  errors.eur = undefined
  isModalOpen.value = true
}

const closeModal = () => {
  if (saving.value) {
return
}

  isModalOpen.value = false
}

const putTasa = (divisa: 'BS' | 'EUR', tasa: string) =>
  new Promise<string | undefined>((resolve) => {
    router.put(
      `/configuracion/tasas/${divisa}`,
      { tasa },
      {
        preserveScroll: true,
        preserveState: true,
        onError: (formErrors) => resolve(formErrors.tasa as string | undefined),
        onSuccess: () => resolve(undefined),
      },
    )
  })

const submit = async () => {
  saving.value = true
  errors.bs = undefined
  errors.eur = undefined

  errors.bs = await putTasa('BS', form.bs)
  errors.eur = await putTasa('EUR', form.eur)

  saving.value = false

  if (!errors.bs && !errors.eur) {
    isModalOpen.value = false
  }
}
</script>
