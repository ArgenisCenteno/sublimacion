<template>
  <FullScreenLayout>
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(251,101,20,0.18),_transparent_38%),linear-gradient(180deg,_#091224_0%,_#0f172b_48%,_#172033_100%)] text-white">
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -left-16 top-20 h-48 w-48 rounded-full bg-orange-400/14 blur-3xl" />
        <div class="absolute right-[-3rem] top-[-1rem] h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl" />
      </div>

      <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col px-5 pb-8 pt-5 sm:px-6">
        <header class="animate-[slideDown_0.55s_ease-out]">
          <div class="flex items-center justify-between gap-3">
            <Link
              href="/operaciones"
              class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/85 transition hover:bg-white/12"
            >
              <span class="text-sm leading-none">&lt;</span>
              Acceso
            </Link>
            <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
              Chapa 1001
            </div>
          </div>

          <div class="mt-6">
            <p class="text-sm font-medium text-white/70">{{ provider?.name ?? 'Taller de chapa' }}</p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight text-white">
              {{ screenTitle }}
            </h1>
            <p class="mt-3 max-w-sm text-sm leading-6 text-slate-200/80">
              {{ screenDescription }}
            </p>
          </div>
        </header>

        <section v-if="screen === 'capture'" class="mt-7 space-y-5">
          <article
            v-if="!hasAnalysisResult"
            class="animate-[panelRise_0.65s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.4)] backdrop-blur-xl"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 1</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Foto frontal del vehiculo</h2>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
                Lectura de placa
              </div>
            </div>

            <div class="mt-4 rounded-[24px] border border-dashed border-white/18 bg-slate-950/35 p-4">
              <div v-if="imagePreviewUrl" class="overflow-hidden rounded-[20px] border border-white/10 bg-black/20">
                <img :src="imagePreviewUrl" alt="Vista previa del vehiculo" class="h-72 w-full object-cover" />
              </div>

              <div v-else class="flex aspect-[4/3] items-center justify-center rounded-[20px] border border-white/8 bg-[linear-gradient(180deg,_rgba(255,255,255,0.08),_rgba(255,255,255,0.02))]">
                <div class="text-center">
                  <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/8 text-white/90">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.3-2h7.4L17 7h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                      <circle cx="12" cy="13" r="3.5" />
                    </svg>
                  </div>
                  <p class="mt-4 text-sm font-semibold text-white">Adjunta la foto frontal</p>
                  <p class="mt-2 text-xs leading-5 text-slate-300/80">
                    Puedes hacerla ahora mismo o elegirla desde tu galeria.
                  </p>
                </div>
              </div>

              <OpsPhotoPicker
                class="mt-4"
                title="Adjuntar foto frontal"
                description="Puedes hacer la foto ahora mismo o elegir una imagen que ya tengas."
                @select="handleImageChange"
              />
            </div>

            <div v-if="analysisError" class="mt-4 rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
              {{ analysisError }}
            </div>

            <div v-if="isAnalyzing" class="mt-4 rounded-2xl border border-cyan-300/15 bg-cyan-300/8 px-4 py-3 text-sm leading-6 text-cyan-50/90">
              Estamos revisando la foto para localizar el vehiculo...
            </div>
          </article>

          <article
            v-else
            class="animate-[panelRise_0.8s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 1</p>
                <h2 class="mt-1 text-lg font-semibold text-white">
                  {{ canResumeStageTwo ? 'Vehiculo listo para continuar' : 'Vehiculo encontrado' }}
                </h2>
              </div>
              <button
                type="button"
                class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/75 transition hover:bg-white/12"
                @click="resetToCapture"
              >
                Repetir foto
              </button>
            </div>

            <div v-if="imagePreviewUrl" class="mt-4 overflow-hidden rounded-[24px] border border-white/10 bg-black/20">
              <img :src="imagePreviewUrl" alt="Vista previa del vehiculo" class="h-44 w-full object-cover" />
            </div>

            <div class="mt-4 rounded-[24px] border border-white/10 bg-white/6 px-4 py-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Placa detectada</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[0.18em] text-white">{{ detectedPlate || 'Sin lectura' }}</p>
                </div>
                <div v-if="ocrConfidence !== null" class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
                  Lectura {{ formatConfidence(ocrConfidence) }}
                </div>
              </div>
            </div>

            <div v-if="matchedVehicle" class="mt-4 rounded-[24px] border border-emerald-300/12 bg-emerald-400/8 px-4 py-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100/70">Vehiculo localizado</p>
                  <h3 class="mt-2 text-lg font-semibold text-white">
                    {{ matchedVehicle.brand }} {{ matchedVehicle.model }}
                  </h3>
                  <p class="mt-1 text-sm text-emerald-50/85">{{ matchedVehicle.version || 'Version pendiente' }}</p>
                </div>
                <div class="rounded-full border border-white/10 bg-black/15 px-3 py-1 text-xs font-medium text-white/75">
                  {{ matchedVehicle.plate }}
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2 text-xs text-white/80">
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ matchedVehicle.color || 'Color sin dato' }}</div>
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ prettyStatus(matchedVehicle.physical_status) }}</div>
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ prettyStatus(matchedVehicle.commercial_status) }}</div>
              </div>
            </div>

            <div v-else class="mt-4 rounded-[24px] border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/80">
              No pudimos encontrar este vehiculo. Puedes volver a hacer la foto.
            </div>

            <div v-if="matchedVehicle && !canResumeStageTwo" class="mt-4 space-y-4">
              <OpsVehicleBodyMap
                :damages="vehicleDamages"
                :can-create="stageAvailable"
                :is-submitting="isSubmittingIncident"
                :error="damageError"
                :success="damageSuccess"
                @create-damage="submitMappedDamage"
              />
              <div v-if="stageNotice" class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                {{ stageNotice }}
              </div>

              <div v-if="!stageAvailable" class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/85">
                Aqui puedes revisar las marcas que ya tiene el vehiculo, pero la llegada a chapa solo se puede confirmar cuando exista una orden preparada para esta etapa.
              </div>

              <template v-if="canStartReception">
              <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/85">
                Aqui puedes ver si el vehiculo ya tiene marcas registradas y, si hace falta, añadir una nueva antes de confirmar la llegada.
              </div>

              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-100">Observaciones de entrada</span>
                <textarea
                  v-model="receptionNotes"
                  rows="3"
                  placeholder="Ej. llega con aleta delantera pendiente de revisar"
                  class="w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 py-3 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-orange-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-orange-300/12"
                />
              </label>

              <div v-if="confirmError" class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                {{ confirmError }}
              </div>

              <button
                type="button"
                :disabled="isConfirming || !capturedImage || !matchedVehicle"
                class="flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#fb6514,_#f79009)] px-4 text-base font-semibold text-white shadow-[0_18px_40px_rgba(251,101,20,0.32)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(251,101,20,0.36)] disabled:translate-y-0 disabled:opacity-60"
                @click="confirmReception"
              >
                {{ isConfirming ? 'Confirmando llegada...' : 'Confirmar llegada' }}
              </button>
              </template>
            </div>

            <div v-if="matchedVehicle && canResumeStageTwo" class="mt-4 space-y-4">
              <div class="rounded-2xl border border-cyan-300/15 bg-cyan-300/8 px-4 py-4 text-sm leading-6 text-cyan-50/90">
                Este vehiculo ya entro en chapa. Puedes continuar el trabajo o cerrar la etapa cuando termines.
              </div>

              <div class="flex flex-wrap gap-2 text-xs text-white/80">
                <div v-if="receptionSummary?.workshop_order?.id" class="rounded-full border border-white/10 bg-white/8 px-3 py-2">
                  Orden #{{ receptionSummary.workshop_order.id }}
                </div>
                <div v-if="receptionSummary?.workshop_order?.status" class="rounded-full border border-white/10 bg-white/8 px-3 py-2">
                  Estado {{ prettyStatus(receptionSummary.workshop_order.status) }}
                </div>
              </div>

              <button
                type="button"
                class="flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#fb6514,_#f79009)] px-4 text-base font-semibold text-white shadow-[0_18px_40px_rgba(251,101,20,0.32)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(251,101,20,0.36)]"
                @click="resumeStageTwo"
              >
                Continuar trabajo
              </button>
            </div>
          </article>
        </section>

        <section v-else-if="screen === 'reception_ok'" class="mt-7 space-y-5">
          <article class="animate-[panelRise_0.7s_cubic-bezier(0.22,1,0.36,1)] rounded-[30px] border border-emerald-300/16 bg-emerald-400/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.32)]">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-100/70">Llegada confirmada</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ receptionSummary?.vehicle?.brand }} {{ receptionSummary?.vehicle?.model }}</h2>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                  El vehiculo ya quedo registrado en chapa. Ya puedes empezar el trabajo y, cuando termines, volver a entrar y hacer otra foto de la placa.
                </p>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/80">
                {{ receptionSummary?.vehicle?.plate }}
              </div>
            </div>
          </article>

          <article class="animate-[panelRise_0.82s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Que sigue</p>
            <div class="mt-4 space-y-3">
              <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/85">
                Cuando termines el trabajo, vuelve a entrar y haz otra foto de la placa para informar una incidencia o cerrar la etapa.
              </div>
              <div class="flex flex-wrap gap-2 text-xs text-white/80">
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">Orden #{{ receptionSummary?.workshop_order?.id }}</div>
                <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">Estado {{ prettyStatus(receptionSummary?.workshop_order?.status ?? '') }}</div>
              </div>
            </div>
          </article>

          <Link href="/operaciones" class="mt-auto flex h-14 items-center justify-center rounded-2xl border border-white/12 bg-white/8 px-4 text-base font-semibold text-white transition hover:bg-white/12">
            Volver al acceso
          </Link>
        </section>

        <section v-else-if="screen === 'workshop'" class="mt-7 space-y-5">
          <article class="animate-[panelRise_0.7s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-emerald-300/14 bg-emerald-400/8 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.32)] backdrop-blur-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-100/70">Vehiculo en trabajo</p>
            <h2 class="mt-2 text-xl font-semibold text-white">
              {{ receptionSummary?.vehicle?.brand }} {{ receptionSummary?.vehicle?.model }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-emerald-50/85">
              El vehiculo ya esta en chapa. Desde aqui puedes marcar una incidencia o cerrar la reparacion.
            </p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs text-white/80">
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">{{ receptionSummary?.vehicle?.plate }}</div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">Orden #{{ receptionSummary?.workshop_order?.id }}</div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-2">Estado {{ prettyStatus(receptionSummary?.workshop_order?.status ?? receptionSummary?.vehicle?.physical_status ?? '') }}</div>
            </div>
          </article>

          <article v-if="stepTwoSuccessMessage" class="rounded-2xl border border-emerald-300/18 bg-emerald-400/10 px-4 py-3 text-sm leading-6 text-emerald-50/90">
            {{ stepTwoSuccessMessage }}
          </article>

          <article class="animate-[panelRise_0.8s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Paso 2</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Que quieres hacer</h2>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/70">
                Chapa activa
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <button type="button" :class="stepTwoActionClass('incident')" @click="selectedStageTwoAction = 'incident'">
                <span class="text-left">
                  <span class="block text-sm font-semibold text-white">Marcar incidencia</span>
                  <span class="mt-2 block text-xs leading-5 text-slate-200/75">Usa el mapa del vehiculo para indicar la zona donde viste el dano.</span>
                </span>
              </button>

              <button type="button" :class="stepTwoActionClass('finish')" @click="selectedStageTwoAction = 'finish'">
                <span class="text-left">
                  <span class="block text-sm font-semibold text-white">Finalizar reparacion</span>
                  <span class="mt-2 block text-xs leading-5 text-slate-200/75">Sube las fotos finales y cierra esta etapa.</span>
                </span>
              </button>
            </div>
          </article>

          <article v-if="selectedStageTwoAction === 'incident'" class="animate-[panelRise_0.9s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Mapa del vehiculo</p>
            <h3 class="mt-2 text-lg font-semibold text-white">Marca la zona desde arriba</h3>

            <div class="mt-4 space-y-4">
              <OpsVehicleBodyMap
                v-if="matchedVehicle"
                :damages="vehicleDamages"
                :can-create="true"
                :is-submitting="isSubmittingIncident"
                :error="damageError"
                :success="damageSuccess"
                @create-damage="submitMappedDamage"
              />

              <div class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-4 text-sm leading-6 text-orange-50/90">
                Para registrar una incidencia, toca una zona en el mapa del vehiculo, elige como se ve y guarda la marca.
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4 text-sm leading-6 text-slate-200/85">
                Asi todas las marcas quedan alineadas en el mismo formato y el siguiente taller puede verlas con claridad.
              </div>

              <div v-if="stepTwoError" class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                {{ stepTwoError }}
              </div>

              <button type="button" class="flex h-14 w-full items-center justify-center rounded-2xl border border-white/12 bg-white/8 px-4 text-base font-semibold text-white transition hover:bg-white/12" @click="selectedStageTwoAction = null">
                Volver
              </button>
            </div>
          </article>

          <article v-if="selectedStageTwoAction === 'finish'" class="animate-[panelRise_0.9s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Finalizar reparacion</p>
            <h3 class="mt-2 text-lg font-semibold text-white">Sube las evidencias finales</h3>

            <div class="mt-4 space-y-4">
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-100">Resumen del trabajo realizado</span>
                <textarea
                  v-model="finishForm.notes"
                  rows="4"
                  placeholder="Ej. reparacion completada y pintura terminada"
                  class="w-full rounded-2xl border border-white/14 bg-slate-950/45 px-4 py-3 text-sm text-white outline-hidden transition placeholder:text-slate-500 focus:border-orange-300/70 focus:bg-slate-950/65 focus:ring-4 focus:ring-orange-300/12"
                />
              </label>

              <OpsPhotoPicker
                title="Subir fotos finales del vehiculo"
                description="Estas fotos serviran como evidencia antes de cerrar la reparacion."
                multiple
                @select="handleFinishImagesChange"
              />

              <div v-if="finishForm.imageNames.length" class="flex flex-wrap gap-2">
                <div v-for="name in finishForm.imageNames" :key="name" class="rounded-full border border-white/10 bg-white/8 px-3 py-2 text-xs text-white/80">
                  {{ name }}
                </div>
              </div>

              <div v-if="stepTwoError" class="rounded-2xl border border-orange-300/20 bg-orange-400/10 px-4 py-3 text-sm leading-6 text-orange-100">
                {{ stepTwoError }}
              </div>

              <button type="button" :disabled="isSubmittingFinish" class="flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#fb6514,_#f79009)] px-4 text-base font-semibold text-white shadow-[0_18px_40px_rgba(251,101,20,0.32)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_48px_rgba(251,101,20,0.36)] disabled:translate-y-0 disabled:opacity-60" @click="submitFinishRepair">
                {{ isSubmittingFinish ? 'Finalizando reparacion...' : 'Finalizar reparacion' }}
              </button>
            </div>
          </article>
        </section>

        <section v-else class="mt-7 space-y-5">
          <article class="animate-[panelRise_0.72s_cubic-bezier(0.22,1,0.36,1)] rounded-[30px] border border-emerald-300/16 bg-emerald-400/10 p-5 shadow-[0_24px_80px_rgba(3,7,18,0.32)]">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-100/70">Trabajo completado</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ completedVehicle?.brand }} {{ completedVehicle?.model }}</h2>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">La reparacion se ha finalizado y toda la informacion del vehiculo quedo registrada.</p>
              </div>
              <div class="rounded-full border border-white/10 bg-white/8 px-3 py-1 text-xs font-medium text-white/80">{{ completedVehicle?.plate }}</div>
            </div>
          </article>

          <article class="animate-[panelRise_0.82s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-white/10 p-5 shadow-[0_18px_52px_rgba(3,7,18,0.35)]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Resumen del vehiculo</p>
            <div class="mt-4 grid gap-3">
              <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Vehiculo</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ completedVehicle?.brand }} {{ completedVehicle?.model }}</p>
                <p class="mt-1 text-sm text-slate-300">Estado actual: {{ prettyStatus(completedVehicle?.physical_status ?? '') }}</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Orden de taller</p>
                <p class="mt-2 text-lg font-semibold text-white">#{{ completedWorkshopOrder?.id }}</p>
                <p class="mt-1 text-sm text-slate-300">Estado: {{ prettyStatus(completedWorkshopOrder?.status ?? '') }}</p>
              </div>
            </div>
          </article>

          <article class="animate-[panelRise_0.92s_cubic-bezier(0.22,1,0.36,1)] rounded-[28px] border border-white/12 bg-slate-950/35 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Documentacion y evidencias</p>
            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
                <div>
                  <p class="text-sm font-semibold text-white">Foto de recepcion</p>
                  <p class="mt-1 text-xs text-slate-300">Capturada y asociada al vehiculo</p>
                </div>
                <span class="inline-flex rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-semibold text-emerald-200">OK</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/6 px-4 py-4">
                <div>
                  <p class="text-sm font-semibold text-white">Fotos finales</p>
                  <p class="mt-1 text-xs text-slate-300">{{ completedPhotoCount }} {{ completedPhotoCount === 1 ? 'imagen subida' : 'imagenes subidas' }}</p>
                </div>
                <span class="inline-flex rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-semibold text-emerald-200">OK</span>
              </div>
            </div>
          </article>

          <Link href="/operaciones" class="mt-auto flex h-14 items-center justify-center rounded-2xl border border-white/12 bg-white/8 px-4 text-base font-semibold text-white transition hover:bg-white/12">Volver al acceso</Link>
        </section>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import OpsVehicleBodyMap from '@/pages/Ops/components/OpsVehicleBodyMap.vue'
import OpsPhotoPicker from '@/pages/Ops/components/OpsPhotoPicker.vue'

type StageTwoAction = 'incident' | 'finish' | null
type ScreenState = 'capture' | 'reception_ok' | 'workshop' | 'completed'
type WorkflowStage = 'reception' | 'work_in_progress' | 'unavailable'

interface ProviderSummary {
  name: string
  type: string
}

interface VehicleSummary {
  id: number
  plate: string
  brand: string
  model: string
  version: string | null
  color: string | null
  physical_status: string
  commercial_status: string
}

interface WorkshopOrderSummary {
  id: number
  status: string
  entry_at?: string | null
  exit_at?: string | null
}

interface ReceptionSummary {
  vehicle: {
    id: number
    plate: string
    brand: string
    model: string
    physical_status: string
  }
  workshop_order: WorkshopOrderSummary
}

interface AnalyzePlateResponse {
  plate: string
  confidence?: number
  vehicle: VehicleSummary | null
  damages?: DamageSummary[]
  workflow_stage?: WorkflowStage
  stage_available?: boolean
  workshop_order?: WorkshopOrderSummary | null
  message?: string
}

defineProps<{
  provider?: ProviderSummary | null
}>()

interface DamageSummary {
  id: number
  x_coord: number
  y_coord: number
  body_part: string | null
  body_part_label: string
  description: string
  severity: string
  status: string
  notes?: string | null
  stage: string
  provider?: string | null
  created_at?: string | null
}

const screen = ref<ScreenState>('capture')
const selectedStageTwoAction = ref<StageTwoAction>(null)
const capturedImage = ref<File | null>(null)
const imagePreviewUrl = ref<string | null>(null)
const isAnalyzing = ref(false)
const isConfirming = ref(false)
const isSubmittingIncident = ref(false)
const isSubmittingFinish = ref(false)
const analysisError = ref('')
const stageNotice = ref('')
const confirmError = ref('')
const damageError = ref('')
const stepTwoError = ref('')
const damageSuccess = ref('')
const stepTwoSuccessMessage = ref('')
const detectedPlate = ref('')
const ocrConfidence = ref<number | null>(null)
const matchedVehicle = ref<VehicleSummary | null>(null)
const vehicleDamages = ref<DamageSummary[]>([])
const detectedWorkflowStage = ref<WorkflowStage>('reception')
const stageAvailable = ref(true)
const receptionNotes = ref('')
const receptionSummary = ref<ReceptionSummary | null>(null)
const completedVehicle = ref<ReceptionSummary['vehicle'] | null>(null)
const completedWorkshopOrder = ref<ReceptionSummary['workshop_order'] | null>(null)
const completedPhotoCount = ref(0)

const finishForm = ref({
  notes: '',
  images: [] as File[],
  imageNames: [] as string[],
})

const hasAnalysisResult = computed(() => Boolean(detectedPlate.value || matchedVehicle.value))
const canResumeStageTwo = computed(() => detectedWorkflowStage.value === 'work_in_progress' && Boolean(receptionSummary.value?.workshop_order))
const canStartReception = computed(() => stageAvailable.value && detectedWorkflowStage.value === 'reception' && Boolean(matchedVehicle.value))
const activeVehicleId = computed(() => receptionSummary.value?.vehicle?.id ?? matchedVehicle.value?.id ?? null)

const screenTitle = computed(() => {
  if (screen.value === 'reception_ok') {
return 'Vehiculo recepcionado'
}

  if (screen.value === 'workshop') {
return 'Trabajo en taller'
}

  if (screen.value === 'completed') {
return 'Vehiculo procesado'
}

  return 'Recepcion del vehiculo'
})

const screenDescription = computed(() => {
  if (screen.value === 'reception_ok') {
    return 'La llegada ya ha quedado registrada. Podras retomar el trabajo mas tarde entrando de nuevo con el PIN y otra foto de la placa.'
  }

  if (screen.value === 'workshop') {
    return 'El vehiculo ya fue recibido. Elige lo que vas a hacer para ver solo lo necesario.'
  }

  if (screen.value === 'completed') {
    return 'La reparacion ya fue cerrada. Aqui tienes el resumen final antes de volver al acceso.'
  }

  return 'Haz una foto frontal donde la placa se vea bien. Si encontramos el vehiculo, podras confirmar la llegada con la foto y tu ubicacion.'
})

const csrfToken = computed(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  return token ?? ''
})

const formatConfidence = (value: number) => `${Math.round(value * 100)}%`

const prettyStatus = (value: string) => {
  if (!value) {
return 'Sin dato'
}

  return value.replaceAll('_', ' ')
}

const stepTwoActionClass = (action: Exclude<StageTwoAction, null>) => {
  const isActive = selectedStageTwoAction.value === action

  return [
    'rounded-[24px] border px-4 py-4 text-left transition duration-200',
    isActive
      ? 'border-orange-300/35 bg-orange-400/14 shadow-[0_14px_30px_rgba(251,101,20,0.14)]'
      : 'border-white/10 bg-white/6 hover:-translate-y-0.5 hover:bg-white/10',
  ]
}

const resetStepTwoFeedback = () => {
  stepTwoError.value = ''
  stepTwoSuccessMessage.value = ''
}

const resetFinishForm = () => {
  finishForm.value = {
    notes: '',
    images: [],
    imageNames: [],
  }
}

const clearCurrentMatch = () => {
  confirmError.value = ''
  stageNotice.value = ''
  damageError.value = ''
  damageSuccess.value = ''
  resetStepTwoFeedback()
  detectedPlate.value = ''
  ocrConfidence.value = null
  matchedVehicle.value = null
  vehicleDamages.value = []
  detectedWorkflowStage.value = 'reception'
  stageAvailable.value = true
  receptionNotes.value = ''
  receptionSummary.value = null
  selectedStageTwoAction.value = null
  resetFinishForm()
}

const resetToCapture = () => {
  clearCurrentMatch()
}

const revokePreview = () => {
  if (imagePreviewUrl.value) {
    URL.revokeObjectURL(imagePreviewUrl.value)
  }
}

const handleImageChange = async (files: File[]) => {
  const file = files[0]

  if (!file) {
    return
  }

  revokePreview()
  capturedImage.value = file
  imagePreviewUrl.value = URL.createObjectURL(file)
  analysisError.value = ''
  clearCurrentMatch()
  screen.value = 'capture'

  await analyzeImage()
}

const handleFinishImagesChange = (files: File[]) => {
  finishForm.value.images = files
  finishForm.value.imageNames = files.map((file) => file.name)
}

const setReceptionSummary = (vehicle: VehicleSummary, workshopOrder: WorkshopOrderSummary) => {
  receptionSummary.value = {
    vehicle: {
      id: vehicle.id,
      plate: vehicle.plate,
      brand: vehicle.brand,
      model: vehicle.model,
      physical_status: vehicle.physical_status,
    },
    workshop_order: workshopOrder,
  }
}

const analyzeImage = async () => {
  if (!capturedImage.value) {
    return
  }

  isAnalyzing.value = true
  analysisError.value = ''

  const formData = new FormData()
  formData.append('image', capturedImage.value)

  try {
    const response = await fetch('/operaciones/chapa/analyze-plate', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: formData,
      credentials: 'same-origin',
    })

    const payload = await response.json() as AnalyzePlateResponse

    if (!response.ok) {
      analysisError.value = payload?.message || 'No pudimos analizar la imagen. Intenta de nuevo.'

      return
    }

    detectedPlate.value = payload.plate || ''
    ocrConfidence.value = Number(payload.confidence ?? 0)
    matchedVehicle.value = payload.vehicle ?? null
    vehicleDamages.value = payload.damages ?? []
    stageAvailable.value = payload.stage_available !== false
    stageNotice.value = stageAvailable.value ? '' : (payload.message || 'Esta etapa todavia no esta lista para este vehiculo.')
    detectedWorkflowStage.value = payload.workflow_stage === 'work_in_progress'
      ? 'work_in_progress'
      : payload.workflow_stage === 'unavailable'
        ? 'unavailable'
        : 'reception'

    if (payload.vehicle && payload.workshop_order && detectedWorkflowStage.value === 'work_in_progress') {
      setReceptionSummary(payload.vehicle, payload.workshop_order)
    }
  } catch {
    analysisError.value = 'Ocurrio un problema al analizar la foto. Intenta de nuevo.'
  } finally {
    isAnalyzing.value = false
  }
}

const submitMappedDamage = async (payload: {
  body_part: string
  x_coord: number
  y_coord: number
  severity: string
  notes: string
  photos: File[]
}) => {
  if (!matchedVehicle.value || isSubmittingIncident.value || isSubmittingFinish.value) {
    return
  }

  damageError.value = ''
  damageSuccess.value = ''
  isSubmittingIncident.value = true

  try {
    const response = await fetch('/operaciones/chapa/report-mapped-damage', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: (() => {
        const formData = new FormData()
        formData.append('vehicle_id', String(matchedVehicle.value!.id))
        formData.append('body_part', payload.body_part)
        formData.append('x_coord', String(payload.x_coord))
        formData.append('y_coord', String(payload.y_coord))
        formData.append('severity', payload.severity)
        formData.append('notes', payload.notes)
        payload.photos.forEach((file) => formData.append('photos[]', file))
        return formData
      })(),
      credentials: 'same-origin',
    })

    const result = await response.json().catch(() => ({}))

    if (!response.ok) {
      damageError.value = result?.message
        || result?.errors?.body_part?.[0]
        || result?.errors?.severity?.[0]
        || 'No pudimos guardar la marca. Intenta de nuevo.'

      return
    }

    if (result?.damage) {
      vehicleDamages.value = [result.damage, ...vehicleDamages.value.filter((item) => item.id !== result.damage.id)]
    }

    damageSuccess.value = result?.message || 'La marca se guardo correctamente.'
  } catch {
    damageError.value = 'No pudimos guardar la marca en este momento. Intenta de nuevo.'
  } finally {
    isSubmittingIncident.value = false
  }
}

const getCurrentPosition = () => new Promise<GeolocationPosition>((resolve, reject) => {
  if (!navigator.geolocation) {
    reject(new Error('Geolocalizacion no disponible'))

    return
  }

  navigator.geolocation.getCurrentPosition(resolve, reject, {
    enableHighAccuracy: true,
    timeout: 15000,
    maximumAge: 0,
  })
})

const resumeStageTwo = () => {
  if (!canResumeStageTwo.value) {
    return
  }

  resetStepTwoFeedback()
  selectedStageTwoAction.value = null
  screen.value = 'workshop'
}

const confirmReception = async () => {
  if (!capturedImage.value || !matchedVehicle.value) {
    return
  }

  isConfirming.value = true
  confirmError.value = ''

  try {
    const position = await getCurrentPosition()
    const formData = new FormData()
    formData.append('vehicle_id', String(matchedVehicle.value.id))
    formData.append('plate', detectedPlate.value)
    formData.append('reception_photo', capturedImage.value)
    formData.append('latitude', String(position.coords.latitude))
    formData.append('longitude', String(position.coords.longitude))
    formData.append('accuracy_meters', String(position.coords.accuracy))
    formData.append('notes', receptionNotes.value)

    const response = await fetch('/operaciones/chapa/confirm-reception', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: formData,
      credentials: 'same-origin',
    })

    const payload = await response.json()

    if (!response.ok) {
      confirmError.value = payload?.message || payload?.errors?.location?.[0] || payload?.errors?.plate?.[0] || 'No pudimos confirmar la recepcion. Intenta de nuevo.'

      return
    }

    receptionSummary.value = payload
    detectedWorkflowStage.value = 'work_in_progress'
    selectedStageTwoAction.value = null
    screen.value = 'reception_ok'
  } catch (error) {
    confirmError.value = error instanceof Error && error.message.includes('Geolocalizacion')
      ? 'Necesitamos acceso a la ubicacion para confirmar la recepcion. Activa la ubicacion y prueba de nuevo.'
      : 'Ocurrio un problema al confirmar la recepcion. Intenta de nuevo.'
  } finally {
    isConfirming.value = false
  }
}

const submitFinishRepair = async () => {
  if (!activeVehicleId.value) {
    stepTwoError.value = 'No encontramos el vehiculo activo para finalizar la reparacion.'

    return
  }

  if (!finishForm.value.images.length) {
    stepTwoError.value = 'Debes adjuntar al menos una foto final del vehiculo.'

    return
  }

  isSubmittingFinish.value = true
  resetStepTwoFeedback()

  try {
    const position = await getCurrentPosition()
    const formData = new FormData()
    formData.append('vehicle_id', String(activeVehicleId.value))
    formData.append('notes', finishForm.value.notes)
    finishForm.value.images.forEach((file) => formData.append('photos[]', file))
    formData.append('latitude', String(position.coords.latitude))
    formData.append('longitude', String(position.coords.longitude))
    formData.append('accuracy_meters', String(position.coords.accuracy))

    const response = await fetch('/operaciones/chapa/finish-repair', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        Accept: 'application/json',
      },
      body: formData,
      credentials: 'same-origin',
    })

    const payload = await response.json()

    if (!response.ok) {
      stepTwoError.value = payload?.message || payload?.errors?.photos?.[0] || payload?.errors?.['photos.0']?.[0] || 'No pudimos finalizar la reparacion. Intenta de nuevo.'

      return
    }

    if (payload?.vehicle) {
      receptionSummary.value = {
        vehicle: payload.vehicle,
        workshop_order: payload.workshop_order,
      }
    }

    completedVehicle.value = payload?.vehicle ?? receptionSummary.value?.vehicle ?? null
    completedWorkshopOrder.value = payload?.workshop_order ?? receptionSummary.value?.workshop_order ?? null
    completedPhotoCount.value = finishForm.value.images.length
    resetFinishForm()
    selectedStageTwoAction.value = null
    screen.value = 'completed'
  } catch (error) {
    stepTwoError.value = error instanceof Error && error.message.includes('Geolocalizacion')
      ? 'Necesitamos acceso a la ubicacion para finalizar la reparacion. Activa la ubicacion y prueba de nuevo.'
      : 'Ocurrio un problema al finalizar la reparacion. Intenta de nuevo.'
  } finally {
    isSubmittingFinish.value = false
  }
}

onBeforeUnmount(() => {
  revokePreview()
})
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
