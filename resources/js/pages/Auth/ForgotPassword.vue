<template>
  <Head title="Recuperar contraseña" />
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
          <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
            <div>
              <div class="mb-5 sm:mb-8">
                <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                  ¿Olvidaste tu contraseña?
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Ingresa el correo electrónico de tu cuenta y te enviaremos un enlace para crear una nueva contraseña.
                </p>
              </div>

              <div
                v-if="status"
                class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
              >
                {{ status }}
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="space-y-5">
                  <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Correo electrónico<span class="text-error-500">*</span>
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      id="email"
                      name="email"
                      required
                      autofocus
                      autocomplete="email"
                      placeholder="info@ejemplo.com"
                      class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                  </div>

                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-[#42AAAB] shadow-theme-xs hover:bg-[#358C8D] disabled:opacity-60"
                  >
                    {{ form.processing ? 'Enviando...' : 'Enviar enlace de recuperación' }}
                  </button>

                  <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    ¿La recordaste?
                    <Link href="/login" class="font-medium text-[#358C8D] hover:underline dark:text-[#42AAAB]">Volver a iniciar sesión</Link>
                  </p>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="relative items-center hidden w-full h-full lg:w-1/2 bg-[#42AAAB] dark:bg-white/5 lg:grid">
          <div class="flex items-center justify-center z-1">
            <common-grid-shape />
            <div class="flex flex-col items-center max-w-xs">
              <Link href="/" class="block mb-4">
                <img width="231" height="48" src="/images/logo/auth-logo.svg" alt="Logo" />
              </Link>
              <p class="text-center text-gray-800 dark:text-white/60">
                Sistema de Gestión Administrativa
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import CommonGridShape from '@/components/common/CommonGridShape.vue'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'

defineProps<{
  status?: string | null
}>()

const form = useForm({
  email: '',
})

const handleSubmit = () => {
  form.post('/forgot-password')
}
</script>
