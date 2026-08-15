<template>
  <section :class="panelClass">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Documentacion</h3>
        <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
          Estado del expediente y del flujo documental.
        </p>
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
      <button
        v-for="item in documentCards"
        :key="item.key"
        type="button"
        class="document-mini-card rounded-2xl border px-4 py-4 text-left transition hover:-translate-y-0.5"
        :class="item.className"
        @click="item.drilldownKey && emit('open-drilldown', item.drilldownKey)"
      >
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] opacity-70">
          {{ item.label }}
        </p>
        <p class="mt-3 text-2xl font-semibold leading-none">{{ item.value }}</p>
      </button>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
      <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Plan de operaciones previsto</p>
      <div class="mt-4 space-y-3">
        <div
          v-for="item in workflowRows"
          :key="item.key"
          class="flex items-center justify-between gap-3"
        >
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</span>
          <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ item.value }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  documents: {
    vehicles_pending: number
    missing_ficha_tecnica: number
    missing_permiso_circulacion: number
    vault_pending: number
    vault_downloaded: number
    vault_processed: number
  }
  workflowPlan: {
    chapa: number
    dab: number
    limpieza: number
  }
  embedded?: boolean
}>()

const emit = defineEmits<{
  (e: 'open-drilldown', key: string): void
}>()

const panelClass = computed(() => props.embedded
  ? 'h-full'
  : 'rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 sm:px-6 sm:pt-6')

const documentCards = computed(() => [
  { key: 'vehicles_pending', label: 'Vehiculos con faltas', value: props.documents.vehicles_pending, className: 'document-mini-card--rose', drilldownKey: 'documents_vehicles_pending' },
  { key: 'missing_ficha_tecnica', label: 'Falta ficha tecnica', value: props.documents.missing_ficha_tecnica, className: 'document-mini-card--amber', drilldownKey: 'documents_missing_ficha_tecnica' },
  { key: 'missing_permiso_circulacion', label: 'Falta permiso', value: props.documents.missing_permiso_circulacion, className: 'document-mini-card--amber', drilldownKey: 'documents_missing_permiso_circulacion' },
  { key: 'vault_pending', label: 'Documentos pendientes', value: props.documents.vault_pending, className: 'document-mini-card--slate', drilldownKey: 'documents_vault_pending' },
  { key: 'vault_downloaded', label: 'Descargados', value: props.documents.vault_downloaded, className: 'document-mini-card--sky', drilldownKey: 'documents_vault_downloaded' },
  { key: 'vault_processed', label: 'Procesados', value: props.documents.vault_processed, className: 'document-mini-card--emerald', drilldownKey: 'documents_vault_processed' },
])

const workflowRows = computed(() => [
  { key: 'chapa', label: 'Requieren chapa', value: props.workflowPlan.chapa },
  { key: 'dab', label: 'Requieren DAB', value: props.workflowPlan.dab },
  { key: 'limpieza', label: 'Requieren limpieza', value: props.workflowPlan.limpieza },
])
</script>

<style scoped>
.document-mini-card--slate {
  border-color: #e4e7ec;
  background: #f9fafb;
  color: #1d2939;
}

.document-mini-card--rose {
  border-color: #fecdd3;
  background: #fff1f3;
  color: #be123c;
}

.document-mini-card--amber {
  border-color: #fde68a;
  background: #fffbeb;
  color: #b45309;
}

.document-mini-card--emerald {
  border-color: #a7f3d0;
  background: #ecfdf3;
  color: #047857;
}

.document-mini-card--sky {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #465fff;
}

.dark .document-mini-card--slate {
  border-color: #344054;
  background: #101828;
  color: rgba(255, 255, 255, 0.9);
}

.dark .document-mini-card--rose {
  border-color: rgba(217, 45, 32, 0.18);
  background: #101828;
  color: #f97066;
}

.dark .document-mini-card--amber {
  border-color: rgba(245, 158, 11, 0.18);
  background: #101828;
  color: #e9b949;
}

.dark .document-mini-card--emerald {
  border-color: rgba(18, 183, 106, 0.18);
  background: #101828;
  color: #52d3a2;
}

.dark .document-mini-card--sky {
  border-color: rgba(70, 95, 255, 0.18);
  background: #101828;
  color: #8ea2ff;
}

.dark .document-mini-card {
  box-shadow: none;
}
</style>
