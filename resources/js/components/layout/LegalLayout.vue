<template>
  <FullScreenLayout>
    <Head :title="`${title} · Biocerra`" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <header class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
          <Link href="/" class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#42AAAB] text-white">
              <HeartPulse class="h-5 w-5" />
            </span>
            <span class="text-lg font-semibold text-gray-800 dark:text-white">Biocerra</span>
          </Link>
          <div class="flex items-center gap-4">
            <Link href="/" class="text-sm font-medium text-gray-500 transition hover:text-[#358C8D] dark:text-gray-400 dark:hover:text-[#42AAAB]">
              Volver al inicio
            </Link>
            <ThemeToggler />
          </div>
        </div>
      </header>

      <main class="mx-auto max-w-3xl px-6 py-16">
        <span class="inline-flex items-center gap-2 rounded-full bg-[#42AAAB]/10 px-4 py-1.5 text-sm font-medium text-[#358C8D] dark:bg-[#42AAAB]/15 dark:text-[#42AAAB]">
          <Lock class="h-4 w-4" />
          Legal
        </span>
        <h1 class="mt-5 text-title-sm font-semibold text-gray-800 dark:text-white">{{ title }}</h1>
        <p class="mt-2 text-sm text-gray-400">Última actualización: {{ lastUpdated }}</p>

        <div class="mt-10 space-y-8">
          <slot />
        </div>

        <div class="mt-14 flex flex-wrap gap-x-6 gap-y-2 border-t border-gray-100 pt-8 text-sm dark:border-gray-800">
          <Link href="/privacidad" class="text-gray-500 transition hover:text-[#358C8D] dark:text-gray-400 dark:hover:text-[#42AAAB]">Política de Privacidad</Link>
          <Link href="/cookies" class="text-gray-500 transition hover:text-[#358C8D] dark:text-gray-400 dark:hover:text-[#42AAAB]">Política de Cookies</Link>
          <Link href="/terminos" class="text-gray-500 transition hover:text-[#358C8D] dark:text-gray-400 dark:hover:text-[#42AAAB]">Términos y Condiciones</Link>
        </div>
      </main>

      <footer class="border-t border-gray-100 py-8 text-center text-xs text-gray-400 dark:border-gray-800">
        &copy; {{ year }} Biocerra. Todos los derechos reservados.
      </footer>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import ThemeToggler from '@/components/common/ThemeToggler.vue'
import { HeartPulse, Lock } from 'lucide-vue-next'

defineProps<{ title: string; lastUpdated: string }>()

const year = new Date().getFullYear()
</script>
