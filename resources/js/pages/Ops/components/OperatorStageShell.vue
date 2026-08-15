<template>
  <FullScreenLayout>
    <div :class="shellClass">
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div :class="orbOneClass" />
        <div :class="orbTwoClass" />
        <div class="absolute inset-x-6 top-24 h-px bg-white/10" />
      </div>

      <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col px-5 pb-8 pt-5 sm:px-6">
        <header class="animate-[slideDown_0.55s_ease-out]">
          <div class="flex items-center justify-between gap-3">
            <Link
              href="/operaciones"
              class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-3 py-2 text-xs font-semibold tracking-[0.2em] text-white/85 uppercase transition hover:bg-white/12"
            >
              <span class="text-sm leading-none">&lt;</span>
              Acceso
            </Link>
            <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
              {{ badge }}
            </div>
          </div>

          <div class="mt-6">
            <p class="text-sm font-medium text-white/70">
              {{ providerName ?? 'Flujo operativo' }}
            </p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight text-white">
              {{ title }}
            </h1>
            <p class="mt-3 max-w-sm text-sm leading-6 text-slate-200/80">
              {{ description }}
            </p>
          </div>
        </header>

        <section class="mt-7 animate-[panelRise_0.65s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.4)] backdrop-blur-xl">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 1</p>
              <h2 class="mt-1 text-lg font-semibold text-white">Identificar vehiculo</h2>
            </div>
            <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
              IA de placa
            </div>
          </div>

          <div class="mt-4 rounded-[24px] border border-dashed border-white/18 bg-slate-950/30 p-4">
            <div class="flex aspect-[4/3] items-center justify-center rounded-[20px] border border-white/8 bg-[linear-gradient(180deg,_rgba(255,255,255,0.08),_rgba(255,255,255,0.02))]">
              <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/8 text-white/90">
                  <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.3-2h7.4L17 7h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                    <circle cx="12" cy="13" r="3.5" />
                  </svg>
                </div>
                <p class="mt-4 text-sm font-semibold text-white">Fotografia la placa del vehiculo</p>
                <p class="mt-2 text-xs leading-5 text-slate-300/80">
                  La matricula se analizara automaticamente antes de continuar.
                </p>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
              <article
                v-for="hint in cameraHints"
                :key="hint.title"
                class="rounded-2xl border border-white/8 bg-white/6 px-3 py-3 text-center"
              >
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">{{ hint.step }}</p>
                <p class="mt-2 text-sm font-semibold text-white">{{ hint.title }}</p>
              </article>
            </div>
          </div>
        </section>

        <section class="mt-5 animate-[panelRise_0.75s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 2</p>
              <h2 class="mt-1 text-lg font-semibold text-white">Acciones de esta etapa</h2>
            </div>
            <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
              Sin logica aun
            </div>
          </div>

          <div class="mt-4 space-y-3">
            <article
              v-for="action in actions"
              :key="action.title"
              :class="actionCardClass(action.tone)"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="text-base font-semibold text-white">{{ action.title }}</h3>
                  <p class="mt-2 text-sm leading-6 text-slate-200/80">{{ action.description }}</p>
                </div>
                <div class="rounded-full border border-white/10 bg-black/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/70">
                  {{ action.badge }}
                </div>
              </div>
            </article>
          </div>
        </section>

        <section class="mt-5 grid gap-5 sm:grid-cols-2">
          <article class="animate-[fadeIn_0.95s_ease-out] rounded-[26px] border border-white/12 bg-white/10 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Flujo</p>
            <div class="mt-4 space-y-4">
              <div v-for="(step, index) in timeline" :key="step.title" class="flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-white/8 text-sm font-semibold text-white">
                    {{ index + 1 }}
                  </div>
                  <div v-if="index < timeline.length - 1" class="mt-2 h-full w-px bg-white/12" />
                </div>
                <div class="pb-3">
                  <p class="text-sm font-semibold text-white">{{ step.title }}</p>
                  <p class="mt-1 text-sm leading-6 text-slate-200/75">{{ step.description }}</p>
                </div>
              </div>
            </div>
          </article>

          <article class="animate-[fadeIn_1.05s_ease-out] rounded-[26px] border border-white/12 bg-slate-950/35 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Datos que quedaran ligados</p>
            <div class="mt-4 flex flex-wrap gap-2">
              <div
                v-for="item in linkedEntities"
                :key="item"
                class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-xs font-medium text-white/80"
              >
                {{ item }}
              </div>
            </div>
            <div class="mt-5 rounded-2xl border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/80">
              Cuando el vehiculo quede identificado, cualquier foto, checklist o incidencia se asociara a ese expediente.
            </div>
          </article>
        </section>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

type ActionTone = 'orange' | 'cyan' | 'emerald' | 'slate'

interface StageAction {
  title: string
  description: string
  badge: string
  tone: ActionTone
}

interface StageTimelineItem {
  title: string
  description: string
}

const props = defineProps<{
  badge: string
  title: string
  description: string
  providerName?: string | null
  theme: ActionTone
  actions: StageAction[]
  timeline: StageTimelineItem[]
  linkedEntities: string[]
}>()

const shellClass = computed(() => {
  const classes: Record<ActionTone, string> = {
    orange: 'relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(251,101,20,0.18),_transparent_38%),linear-gradient(180deg,_#091224_0%,_#0f172b_48%,_#172033_100%)] text-white',
    cyan: 'relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(11,165,236,0.18),_transparent_38%),linear-gradient(180deg,_#071424_0%,_#0d1f31_48%,_#15293a_100%)] text-white',
    emerald: 'relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(18,183,106,0.18),_transparent_38%),linear-gradient(180deg,_#071b18_0%,_#0d2622_48%,_#173730_100%)] text-white',
    slate: 'relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.14),_transparent_38%),linear-gradient(180deg,_#111827_0%,_#172033_50%,_#1d2a3f_100%)] text-white',
  }

  return classes[props.theme]
})

const orbOneClass = computed(() => {
  const classes: Record<ActionTone, string> = {
    orange: 'absolute -left-16 top-20 h-48 w-48 rounded-full bg-orange-400/14 blur-3xl',
    cyan: 'absolute -left-16 top-20 h-48 w-48 rounded-full bg-cyan-400/14 blur-3xl',
    emerald: 'absolute -left-16 top-20 h-48 w-48 rounded-full bg-emerald-400/14 blur-3xl',
    slate: 'absolute -left-16 top-20 h-48 w-48 rounded-full bg-slate-300/10 blur-3xl',
  }

  return classes[props.theme]
})

const orbTwoClass = computed(() => {
  const classes: Record<ActionTone, string> = {
    orange: 'absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl',
    cyan: 'absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-blue-light-400/12 blur-3xl',
    emerald: 'absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-lime-300/10 blur-3xl',
    slate: 'absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-white/8 blur-3xl',
  }

  return classes[props.theme]
})

const cameraHints = [
  { step: '01', title: 'Captura' },
  { step: '02', title: 'Lectura IA' },
  { step: '03', title: 'Vehiculo' },
]

const actionCardClass = (tone: ActionTone) => {
  const classes: Record<ActionTone, string> = {
    orange: 'rounded-[24px] border border-orange-300/14 bg-orange-400/8 px-4 py-4 transition duration-200 hover:-translate-y-0.5 hover:bg-orange-400/12',
    cyan: 'rounded-[24px] border border-cyan-300/14 bg-cyan-400/8 px-4 py-4 transition duration-200 hover:-translate-y-0.5 hover:bg-cyan-400/12',
    emerald: 'rounded-[24px] border border-emerald-300/14 bg-emerald-400/8 px-4 py-4 transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-400/12',
    slate: 'rounded-[24px] border border-white/10 bg-white/6 px-4 py-4 transition duration-200 hover:-translate-y-0.5 hover:bg-white/10',
  }

  return classes[tone]
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
    transform: translateY(24px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(12px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
