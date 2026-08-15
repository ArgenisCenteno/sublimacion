<template>
  <FullScreenLayout>
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(251,101,20,0.18),_transparent_38%),linear-gradient(180deg,_#091224_0%,_#0f172b_48%,_#172033_100%)] text-white">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-16 top-20 h-48 w-48 rounded-full bg-orange-400/12 blur-3xl" />
        <div class="absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl" />
        <div class="absolute bottom-0 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-blue-light-500/10 blur-3xl" />
      </div>

      <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col px-5 pb-8 pt-6 sm:px-6">
        <header class="animate-[slideDown_0.55s_ease-out]">
          <div class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-200/95">
            Acceso a operaciones
          </div>
          <div class="mt-6">
            <p class="text-sm font-medium text-blue-light-100/80">DKM0 Core</p>
            <h1 class="mt-2 max-w-xs text-3xl font-semibold leading-tight text-white">
              Introduce tu PIN
            </h1>
          </div>
        </header>

        <section class="mt-8 animate-[panelRise_0.65s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.42)] backdrop-blur-xl">
          

          <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div>
              <label for="pin" class="mb-2 block text-sm font-medium text-slate-100">
                Tu PIN
              </label>
              <input
                id="pin"
                v-model="form.pin"
                type="password"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="Ej. 1234"
                maxlength="20"
                class="h-15 w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 text-center text-2xl font-semibold tracking-[0.35em] text-white shadow-inner outline-hidden transition placeholder:text-slate-500 focus:border-orange-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-orange-300/12"
              />
              <p class="mt-2 text-xs leading-5 text-slate-400">
                Usa el PIN asignado a tu area. No necesitas usuario ni contrasena.
              </p>
              <p v-if="form.errors.pin" class="mt-2 text-sm font-medium text-orange-200">
                {{ form.errors.pin }}
              </p>
            </div>

            <div class="rounded-2xl border border-cyan-300/15 bg-cyan-300/6 px-4 py-3 text-sm leading-6 text-cyan-50/90">
              Despues de validar tu PIN veras tu area de trabajo.
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#fb6514,_#f79009)] px-4 text-base font-semibold text-white shadow-[0_18px_40px_rgba(251,101,20,0.32)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(251,101,20,0.36)] disabled:translate-y-0 disabled:opacity-60"
            >
              {{ form.processing ? 'Validando PIN...' : 'Entrar' }}
            </button>
          </form>
        </section>

        <footer class="mt-auto animate-[fadeIn_0.9s_ease-out] pt-6 text-center text-xs leading-5 text-slate-400">
          Si el PIN no funciona, consulta con coordinacion.
        </footer>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'

const form = useForm({
  pin: '',
})

const submit = () => {
  form.post('/operaciones/access', {
    preserveScroll: true,
    onSuccess: () => form.reset('pin'),
  })
}
</script>

<style scoped>
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-18px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes panelRise {
  from {
    opacity: 0;
    transform: translateY(26px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}
</style>
