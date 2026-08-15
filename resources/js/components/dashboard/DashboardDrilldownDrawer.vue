<template>
  <div v-if="open" class="fixed inset-0 z-[120]">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="$emit('close')"></div>

    <aside
      class="absolute right-0 top-0 flex h-full w-full max-w-2xl flex-col border-l border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
    >
      <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-5 dark:border-gray-800 sm:px-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ title }}</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
        </div>

        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white"
          @click="$emit('close')"
        >
          <span class="sr-only">Cerrar</span>
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none">
            <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-6">
        <div class="mb-5 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-[#101828]">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
            Estas viendo
          </p>
          <p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ title }}</p>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
          <p
            v-if="!loading && !error"
            class="mt-3 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/[0.04] dark:text-gray-300"
          >
            {{ items.length }} resultado<span v-if="items.length !== 1">s</span>
          </p>
        </div>

        <div v-if="loading" class="space-y-3">
          <div
            v-for="index in 6"
            :key="index"
            class="h-24 animate-pulse rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]"
          ></div>
        </div>

        <div
          v-else-if="error"
          class="rounded-2xl border border-error-200 bg-error-50 px-4 py-5 dark:border-error-500/20 dark:bg-error-500/10"
        >
          <p class="text-sm font-semibold text-error-700 dark:text-error-300">No pudimos cargar el detalle</p>
          <p class="mt-2 text-sm text-error-600 dark:text-error-200">{{ error }}</p>
        </div>

        <div
          v-else-if="!items.length"
          class="rounded-2xl border border-dashed border-gray-300 px-4 py-10 text-center dark:border-gray-700 dark:bg-[#101828]"
        >
          <p class="text-sm font-semibold text-gray-800 dark:text-white/90">No hay resultados</p>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ emptyMessage }}</p>
        </div>

        <div v-else class="space-y-3">
          <article
            v-for="item in items"
            :key="item.id"
            class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-[#101828]"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ item.title }}</p>
                  <span
                    v-if="item.badge"
                    class="inline-flex rounded-full border border-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300"
                  >
                    {{ item.badge }}
                  </span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ item.subtitle }}</p>
                <div v-if="item.meta?.length" class="mt-3 flex flex-wrap gap-2">
                  <span
                    v-for="metaItem in item.meta"
                    :key="metaItem"
                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/[0.04] dark:text-gray-300"
                  >
                    {{ metaItem }}
                  </span>
                </div>
              </div>

              <button
                v-if="item.href"
                type="button"
                class="inline-flex shrink-0 items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-wait disabled:opacity-70 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                :disabled="Boolean(navigatingTo)"
                @click="openItem(item)"
              >
                {{ navigatingTo === item.id ? 'Cargando...' : (item.hrefLabel || 'Abrir') }}
              </button>
            </div>
          </article>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

interface DrilldownItem {
  id: number | string
  title: string
  subtitle: string
  meta?: string[]
  badge?: string | null
  href?: string | null
  hrefLabel?: string | null
}

defineProps<{
  open: boolean
  loading: boolean
  title: string
  subtitle: string
  emptyMessage: string
  error: string | null
  items: DrilldownItem[]
}>()

defineEmits<{
  (e: 'close'): void
}>()

const navigatingTo = ref<number | string | null>(null)

const openItem = (item: DrilldownItem) => {
  if (!item.href || navigatingTo.value) {
    return
  }

  navigatingTo.value = item.id

  router.visit(item.href, {
    onFinish: () => {
      navigatingTo.value = null
    },
  })
}
</script>
