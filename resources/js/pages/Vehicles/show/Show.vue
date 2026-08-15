<template>
  <component :is="currentComponent" :vehicle="vehicle" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { VehicleData } from './vehicleShow'
import ShowGeneralTab from './ShowGeneralTab.vue'
import ShowPhotosTab from './ShowPhotosTab.vue'
import ShowDocumentsTab from './ShowDocumentsTab.vue'
import ShowCostsTab from './ShowCostsTab.vue'
import ShowHistoryTab from './ShowHistoryTab.vue'

const props = defineProps<{
  vehicle: VehicleData
  tab: 'general' | 'photos' | 'documents' | 'costs' | 'history'
}>()

const currentComponent = computed(() => {
  const components = {
    general: ShowGeneralTab,
    photos: ShowPhotosTab,
    documents: ShowDocumentsTab,
    costs: ShowCostsTab,
    history: ShowHistoryTab,
  } as const

  return components[props.tab]
})
</script>
