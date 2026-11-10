<template>
  <div>
    <button
      type="button"
      class="hidden items-center gap-2.5 rounded-xl border border-gray-200 bg-gray-50 py-1.5 pl-2 pr-3 text-theme-xs font-medium text-gray-700 transition-colors dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 lg:flex"
      :class="canEdit ? 'hover:border-brand-300 hover:bg-brand-25 dark:hover:bg-white/[0.03]' : 'cursor-default'"
      :title="canEdit ? 'Actualizar tasa del dólar' : 'Tasa del dólar vigente'"
      @click="openModal"
    >
      <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-success-50 text-sm font-bold text-success-600 dark:bg-success-500/15">$</span>
      <span class="flex flex-col items-start leading-tight">
        <span class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Dólar</span>
        <span class="font-semibold text-gray-800 dark:text-white/90">{{ hasRate ? formatBs(rate) : 'Sin definir' }}</span>
      </span>
      <Pencil v-if="canEdit" class="h-3.5 w-3.5 text-gray-400" />
    </button>

    <Modal v-if="isModalOpen" full-screen-backdrop @close="closeModal">
      <template #body>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
          <button
            type="button"
            class="absolute right-5 top-5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
            aria-label="Cerrar"
            @click="closeModal"
          >
            <X class="h-5 w-5" />
          </button>

          <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Tasa del dólar</h4>
          <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            Bolívares por cada dólar. Se usa para calcular los pagos en Bs (pago móvil, transferencia y efectivo).
          </p>

          <form @submit.prevent="submit">
            <label class="label" for="rate-bs">Bs por 1 USD</label>
            <MoneyInput
              id="rate-bs"
              v-model="form.exchange_rate_bs"
              currency="BS"
              :decimals="2"
              required
              class="h-12 w-full rounded-xl border border-gray-300 bg-transparent px-4 text-base font-semibold text-gray-800 outline-none focus:border-brand-300 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
            />
            <p v-if="form.errors.exchange_rate_bs" class="mt-1.5 text-xs text-red-500">{{ form.errors.exchange_rate_bs }}</p>

            <div v-if="Number(form.exchange_rate_bs) > 0" class="mt-3 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
              Ejemplo: $10,00 = <strong class="text-gray-900 dark:text-white">{{ formatBs(Number(form.exchange_rate_bs) * 10) }}</strong>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
              <button type="button" class="btn-secondary" @click="closeModal">Cancelar</button>
              <button type="submit" :disabled="form.processing || !form.exchange_rate_bs" class="btn-primary">
                {{ form.processing ? 'Guardando…' : 'Guardar tasa' }}
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Pencil, X } from 'lucide-vue-next'
import MoneyInput from '@/components/forms/MoneyInput.vue'
import Modal from '../../ui/Modal.vue'
import { useExchangeRate } from '@/composables/useExchangeRate'

const { rate, hasRate, formatBs } = useExchangeRate()

const page = usePage<{ auth: { permissions: string[] } }>()
const canEdit = computed(() => (page.props.auth?.permissions ?? []).includes('settings.edit'))

const isModalOpen = ref(false)
const form = useForm<{ exchange_rate_bs: number | null }>({ exchange_rate_bs: null })

const openModal = () => {
  if (!canEdit.value) return
  form.clearErrors()
  form.exchange_rate_bs = hasRate.value ? rate.value : null
  isModalOpen.value = true
}

const closeModal = () => {
  if (!form.processing) isModalOpen.value = false
}

const submit = () => {
  form.put('/settings/exchange-rate', {
    preserveScroll: true,
    onSuccess: () => {
      isModalOpen.value = false
    },
  })
}
</script>
