<template>
  <Head title="Nueva contraseña" />
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
          <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
            <div>
              <div class="mb-5 sm:mb-8">
                <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                  Crear nueva contraseña
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Escribe y confirma tu nueva contraseña para recuperar el acceso a tu cuenta.
                </p>
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="space-y-5">
                  <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Correo electrónico
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      id="email"
                      readonly
                      class="h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white/70"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                  </div>

                  <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Nueva contraseña<span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="Nueva contraseña"
                        :class="inputClass"
                      />
                      <button
                        type="button"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400"
                        @click="showPassword = !showPassword"
                      >
                        {{ showPassword ? 'Ocultar' : 'Mostrar' }}
                      </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                  </div>

                  <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                      Confirmar contraseña<span class="text-error-500">*</span>
                    </label>
                    <input
                      v-model="form.password_confirmation"
                      :type="showPassword ? 'text' : 'password'"
                      id="password_confirmation"
                      required
                      autocomplete="new-password"
                      placeholder="Repite la contraseña"
                      :class="inputClass"
                    />
                  </div>

                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-[#42AAAB] shadow-theme-xs hover:bg-[#358C8D] disabled:opacity-60"
                  >
                    {{ form.processing ? 'Guardando...' : 'Guardar contraseña' }}
                  </button>

                  <p class="text-center text-sm">
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
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import CommonGridShape from '@/components/common/CommonGridShape.vue'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'

const props = defineProps<{
  email: string
  token: string
}>()

const showPassword = ref(false)

const inputClass =
  'dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-20 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800'

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const handleSubmit = () => {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
