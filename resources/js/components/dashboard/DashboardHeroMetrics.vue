<template>
  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
    <button
      v-for="card in cards"
      :key="card.label"
      type="button"
      class="hero-metric-card rounded-2xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30 md:p-6"
      :style="{ animationDelay: `${card.delay}ms` }"
      @click="emit('open-drilldown', card.drilldownKey)"
    >
      <div class="flex h-12 w-12 items-center justify-center rounded-xl" :class="card.iconTone">
        <component :is="card.icon" class="h-6 w-6" />
      </div>

      <div class="mt-5 flex items-end justify-between gap-3">
        <div>
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</span>
          <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ card.value }}</h4>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ card.caption }}</p>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium" :class="card.badgeTone">
          {{ card.badge }}
        </span>
      </div>
    </button>
  </section>
</template>

<script setup lang="ts">
import { computed, defineComponent, h } from 'vue'

const props = defineProps<{
  hero: {
    ordenes_hoy: number
    muestras_pendientes: number
    resultados_pendientes: number
    ordenes_listas: number
  }
}>()

const emit = defineEmits<{
  (e: 'open-drilldown', key: string): void
}>()

const icon = (paths: string | string[]) =>
  defineComponent({
    setup(_, { attrs }) {
      return () =>
        h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', ...attrs },
          (Array.isArray(paths) ? paths : [paths]).map(d =>
            h('path', { d, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' })
          )
        )
    },
  })

const IconOrdenes    = icon('M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z')
const IconMuestras   = icon('M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z')
const IconResultados = icon('M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z')
const IconListas     = icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z')

const cards = computed(() => [
  {
    label:        'Órdenes hoy',
    value:        props.hero.ordenes_hoy,
    caption:      'Creadas en el día actual',
    badge:        'Recepción',
    drilldownKey: 'ordenes_hoy',
    delay:        40,
    icon:         IconOrdenes,
    iconTone:     'bg-blue-light-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400',
    badgeTone:    'bg-blue-light-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-300',
  },
  {
    label:        'Muestras pendientes',
    value:        props.hero.muestras_pendientes,
    caption:      'Sin tomar en cola activa',
    badge:        'Laboratorio',
    drilldownKey: 'muestras_pendientes',
    delay:        110,
    icon:         IconMuestras,
    iconTone:     'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
    badgeTone:    'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
  },
  {
    label:        'Resultados pendientes',
    value:        props.hero.resultados_pendientes,
    caption:      'Exámenes sin resultado cargado',
    badge:        'Análisis',
    drilldownKey: 'resultados_pendientes',
    delay:        180,
    icon:         IconResultados,
    iconTone:     'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400',
    badgeTone:    'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
  },
  {
    label:        'Órdenes listas',
    value:        props.hero.ordenes_listas,
    caption:      'Resultados validados por entregar',
    badge:        'Por entregar',
    drilldownKey: 'ordenes_listas',
    delay:        250,
    icon:         IconListas,
    iconTone:     'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
    badgeTone:    'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-300',
  },
])
</script>

<style scoped>
.hero-metric-card {
  animation: hero-metric-fade-in 380ms ease-out both;
  will-change: opacity, transform;
}

@keyframes hero-metric-fade-in {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
  .hero-metric-card { animation: none; }
}
</style>