<template>
  <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-5 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Fotos</h2>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
        {{ photoCount }} registradas
      </span>
    </div>

    <div v-if="photoCount" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="photo in vehicle.photos"
        :key="photo.id"
        class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40"
      >
        <div class="flex aspect-[4/3] items-center justify-center overflow-hidden bg-[linear-gradient(135deg,_#e0e7ff_0%,_#ddd6fe_100%)] text-indigo-600 dark:bg-[linear-gradient(135deg,_rgba(99,102,241,0.18)_0%,_rgba(168,85,247,0.18)_100%)] dark:text-indigo-200">
          <img
            v-if="photoSource(photo)"
            :src="photoSource(photo) ?? undefined"
            :alt="photo.label || 'Foto del vehiculo'"
            class="h-full w-full object-cover"
          />
          <svg v-else class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 15.75l4.72-4.72a1.5 1.5 0 012.12 0l3.16 3.16 1.72-1.72a1.5 1.5 0 012.12 0l5.66 5.66M3.75 6.75h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5zm12 3.75h.008v.008h-.008z" />
          </svg>
        </div>

        <div class="space-y-1 p-4">
          <p class="font-medium text-slate-900 dark:text-white">{{ photo.label || 'Foto vehiculo' }}</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ photo.is_mandatory ? 'Obligatoria' : 'Adicional' }}
          </p>
          <p v-if="photo.stage || photo.type" class="text-xs text-slate-400 dark:text-slate-500">
            {{ [photo.stage, photo.type].filter(Boolean).join(' · ').replaceAll('_', ' ') }}
          </p>
        </div>
      </article>
    </div>

    <div
      v-else
      class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-800/40"
    >
      <p class="text-base font-semibold text-slate-900 dark:text-white">Sin fotos registradas</p>
      <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
        Cuando el vehiculo tenga fotos cargadas, apareceran aqui.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { VehicleData, VehiclePhoto } from './vehicleShow'

const props = defineProps<{
  vehicle: VehicleData
}>()

const photoCount = computed(() => props.vehicle.photos?.length ?? 0)

function photoSource(photo: VehiclePhoto) {
  if (!photo.url) {
    return null
  }

  if (/^https?:\/\//i.test(photo.url)) {
    return photo.url
  }

  return `/storage/${photo.url.replace(/^\/+/, '')}`
}
</script>
