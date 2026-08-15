<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Cobro · ${orden.codigo}`" />

        <div class="space-y-6">
            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.22),_transparent_32%),linear-gradient(135deg,_#022c22_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Cobro
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                {{ orden.paciente?.nombres }} {{ orden.paciente?.apellidos }}
                            </h1>
                            <p class="mt-2 text-sm text-white/60">
                                Orden {{ orden.codigo }} · {{ orden.items?.length ?? 0 }} examen(es)
                            </p>
                        </div>
                        <button type="button" :disabled="isNavigatingBack" @click="goToShow"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15">
                            Volver a la orden
                        </button>
                    </div>

                    <transition-group tag="div" class="mt-6 grid gap-3 sm:grid-cols-3" name="hero-card" appear>
                        <article v-for="card in heroCards" :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            :class="['rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1', card.highlight ? 'border-emerald-400/40 bg-emerald-500/15' : '']">
                            <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            <p :class="['mt-3 text-2xl font-bold', card.highlight ? 'text-emerald-300' : 'text-white']">{{ card.value }}</p>
                            <p class="mt-1 text-xs text-white/60">{{ card.caption }}</p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <!-- Flash -->
            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo"  :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error"  :message="flash.error" />
            </transition-group>

            <!-- Liquidada -->
            <div v-if="orden.saldo_pendiente <= 0"
                class="rounded-[28px] border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">Orden completamente pagada</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400">El total ha sido cubierto.</p>
                    </div>
                    <button type="button" @click="imprimirRecibo"
                        class="ml-auto inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimir recibo
                    </button>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <div class="space-y-6">

                    <!-- ── Formulario de nuevo pago ── -->
                    <section v-if="orden.saldo_pendiente > 0"
                        class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="mb-5 text-base font-semibold text-slate-900 dark:text-white">Registrar pago</h2>

                        <form @submit.prevent="submit" class="space-y-6">

                            <!-- Divisa -->
                            <div class="field-reveal">
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Divisa <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="d in divisas" :key="d.value" type="button"
                                        @click="form.divisa = d.value"
                                        :class="['rounded-2xl border py-2.5 text-sm font-semibold transition duration-200',
                                            form.divisa === d.value
                                                ? 'border-indigo-400 bg-indigo-600 text-white shadow-sm'
                                                : 'border-slate-200 text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-indigo-500/10']">
                                        {{ d.label }}
                                    </button>
                                </div>
                                <!-- Aviso tasa no configurada -->
                                <div v-if="form.divisa !== 'USD' && !tasaActiva"
                                    class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                    ⚠️ No hay tasa configurada para {{ form.divisa }}. Configúrala antes de continuar.
                                </div>
                                <div v-else-if="form.divisa !== 'USD' && tasaActiva"
                                    class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    Tasa vigente: {{ tasaActiva }} {{ form.divisa }} = 1 USD
                                </div>
                            </div>

                            <!-- ── Métodos de pago ── -->
                            <div class="field-reveal" style="animation-delay:60ms">
                                <div class="mb-3 flex items-center justify-between">
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Métodos de pago <span class="text-red-500">*</span>
                                        <span class="ml-1.5 text-xs font-normal text-slate-400">(añade todos los que apliquen)</span>
                                    </label>
                                    <button type="button" @click="agregarMetodo"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Añadir método
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="(item, idx) in form.metodos" :key="idx"
                                        class="rounded-[20px] border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">

                                        <div class="mb-3 flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                                Método {{ idx + 1 }}
                                            </span>
                                            <button v-if="form.metodos.length > 1" type="button"
                                                @click="quitarMetodo(idx)"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <!-- Selector de método -->
                                            <div class="sm:col-span-3">
                                                <div class="grid grid-cols-3 gap-1.5 sm:grid-cols-6">
                                                    <button v-for="m in metodos" :key="m.value" type="button"
                                                        @click="item.metodo = m.value"
                                                        :class="['flex flex-col items-center gap-1 rounded-xl border px-2 py-2 text-xs font-medium transition duration-200',
                                                            item.metodo === m.value
                                                                ? 'border-indigo-400 bg-indigo-600 text-white'
                                                                : 'border-slate-200 text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-indigo-500/10']">
                                                        <span class="text-base">{{ m.icon }}</span>
                                                        <span>{{ m.label }}</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Monto -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    Monto ({{ form.divisa }}) <span class="text-red-500">*</span>
                                                </label>
                                                <input v-model="item.monto_divisa" type="number" min="0.01" step="0.01"
                                                    placeholder="0.00" :class="[inputSmClass]" />
                                            </div>

                                            <!-- Referencia -->
                                            <div class="sm:col-span-2">
                                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    Referencia
                                                </label>
                                                <input v-model="item.referencia" type="text"
                                                    placeholder="N° transacción, comprobante..."
                                                    :class="[inputSmClass]" />
                                            </div>

                                            <!-- Equivalente USD inline -->
                                            <div v-if="tasaActiva && item.monto_divisa"
                                                class="sm:col-span-3 rounded-xl bg-indigo-50 px-3 py-2 text-xs dark:bg-indigo-500/10">
                                                <span class="text-indigo-700 dark:text-indigo-300">
                                                    = <strong>${{ calcUsd(item.monto_divisa).toFixed(2) }} USD</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen total del pago en construcción -->
                                <div v-if="form.metodos.length && totalMetodosDivisa > 0"
                                    :class="['mt-4 rounded-2xl border px-4 py-3', saldoTrasEste <= 0 ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-500/40 dark:bg-emerald-500/10' : 'border-indigo-200 bg-indigo-50 dark:border-indigo-500/30 dark:bg-indigo-500/10']">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-slate-700 dark:text-slate-300">
                                            Total de este pago:
                                            <strong class="text-slate-900 dark:text-white">
                                                {{ divisaSimbol }}{{ totalMetodosDivisa.toFixed(2) }} → ${{ totalMetodosUsd.toFixed(2) }} USD
                                            </strong>
                                        </span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Saldo tras este pago:
                                        <strong :class="saldoTrasEste <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                            ${{ Math.max(0, saldoTrasEste).toFixed(2) }} USD
                                        </strong>
                                        <span v-if="saldoTrasEste <= 0" class="ml-1 font-semibold text-emerald-600 dark:text-emerald-400">✓ Quedaría liquidada</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones generales -->
                            <div class="field-reveal" style="animation-delay:90ms">
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Observaciones del pago
                                </label>
                                <textarea v-model="form.observaciones" rows="2"
                                    placeholder="Notas generales sobre este cobro..."
                                    :class="[inputBaseClass, 'h-auto resize-none py-3']" />
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                                <button type="submit"
                                    :disabled="form.processing || !puedeEnviar"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60">
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    {{ form.processing ? 'Registrando...' : 'Registrar pago' }}
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- ── Historial de pagos ── -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:60ms">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">
                            Historial de pagos
                            <span class="ml-2 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                                {{ orden.pagos?.length ?? 0 }}
                            </span>
                        </h2>

                        <div v-if="orden.pagos?.length" class="space-y-4">
                            <div v-for="pago in orden.pagos" :key="pago.id"
                                class="rounded-[20px] border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">

                                <!-- Cabecera del pago -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            ${{ Number(pago.monto_usd).toFixed(2) }} USD
                                        </span>
                                        <span class="ml-2 text-xs text-slate-500 dark:text-slate-400">
                                            en {{ divisaLabel(pago.divisa) }}
                                            · tasa {{ Number(pago.tasa_cambio).toFixed(4) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ pago.cajero?.name }} · {{ formatFecha(pago.created_at) }}
                                        </span>
                                        <button v-if="puedeEliminarPago" type="button"
                                            @click="confirmarEliminar(pago)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Detalle de métodos -->
                                <div class="mt-3 space-y-1.5">
                                    <div v-for="(met, midx) in pago.metodos" :key="midx"
                                        class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900">
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>{{ metodoIcon(met.metodo) }}</span>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ metodoLabel(met.metodo) }}</span>
                                            <span v-if="met.referencia" class="text-xs text-slate-400">· {{ met.referencia }}</span>
                                        </div>
                                        <div class="text-right text-sm">
                                            <span class="font-semibold text-slate-800 dark:text-white">
                                                {{ divisaSimbolFor(pago.divisa) }}{{ Number(met.monto_divisa).toFixed(2) }}
                                            </span>
                                            <span v-if="pago.divisa !== 'USD'" class="ml-1.5 text-xs text-slate-400">
                                                (${{ Number(met.monto_usd).toFixed(2) }})
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="pago.observaciones" class="mt-2 text-xs italic text-slate-400 dark:text-slate-500">
                                    {{ pago.observaciones }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sin pagos registrados aún.</p>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <!-- Resumen financiero -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Resumen de cobro</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Total orden</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">${{ Number(orden.total).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Pagado</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400">${{ Number(orden.monto_pagado_usd).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                                <dt class="font-semibold text-slate-900 dark:text-white">Saldo pendiente</dt>
                                <dd :class="['text-lg font-bold', orden.saldo_pendiente <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400']">
                                    ${{ Math.max(0, orden.saldo_pendiente).toFixed(2) }}
                                </dd>
                            </div>
                        </dl>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span>Progreso</span>
                                <span>{{ porcentajePagado }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                    :style="{ width: `${porcentajePagado}%` }" />
                            </div>
                        </div>
                    </section>

                    <!-- Tasas vigentes -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900" style="animation-delay:60ms">
                        <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">Tasas vigentes</h2>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">1 USD</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">= $1.00</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">1 EUR</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">
                                    {{ props.tasas.EUR ? `= $${(1 / props.tasas.EUR).toFixed(4)} USD` : '— Sin configurar' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">1 Bs.</dt>
                                <dd class="font-semibold text-slate-800 dark:text-white">
                                    {{ props.tasas.BS ? `= $${(1 / props.tasas.BS).toFixed(6)} USD` : '— Sin configurar' }}
                                </dd>
                            </div>
                        </dl>
                        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">
                            Las tasas se aplican en el momento del registro.
                        </p>
                    </section>

                    <!-- Recibo -->
                    <section class="panel-float rounded-[28px] border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-500/20 dark:bg-indigo-500/10" style="animation-delay:120ms">
                        <h2 class="text-base font-semibold text-indigo-900 dark:text-indigo-200">Recibo</h2>
                        <p class="mt-2 text-sm text-indigo-700 dark:text-indigo-300">
                            Disponible en cualquier momento, aunque el pago sea parcial.
                        </p>
                        <button type="button" @click="imprimirRecibo"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-indigo-300 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:bg-indigo-100 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimir recibo
                        </button>
                    </section>
                </aside>
            </div>
        </div>

        <!-- Modal eliminar pago -->
        <Teleport to="body">
            <transition name="modal">
                <div v-if="pagoAEliminar" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-sm rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">¿Eliminar este pago?</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            Se eliminará el pago de <strong>${{ Number(pagoAEliminar.monto_usd).toFixed(2) }} USD</strong>
                            registrado por {{ pagoAEliminar.cajero?.name }}.
                            Esta acción no se puede deshacer.
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="pagoAEliminar = null"
                                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">
                                Cancelar
                            </button>
                            <button type="button" @click="ejecutarEliminar"
                                class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-rose-500">
                                Eliminar pago
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>

        <!-- Recibo de impresión -->
        <div id="recibo-print" class="hidden print:block">
            <ReciboImpresion :orden="orden" />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import Alert from '@/components/ui/Alert.vue'
import ReciboImpresion from '@/components/Recepcion/ReciboImpresion.vue'

interface MetodoItem {
    metodo: string
    monto_divisa: number | string
    referencia: string
}

interface Props {
    orden: any
    tasas: { EUR: number | null; BS: number | null }
}

const props     = defineProps<Props>()
const page      = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash     = computed(() => page.props.flash ?? {})
const isNavigatingBack = ref(false)
const pagoAEliminar    = ref<any>(null)

const divisas = [
    { value: 'USD', label: 'USD ($)' },
    { value: 'EUR', label: 'EUR (€)' },
    { value: 'BS',  label: 'Bs.'     },
]

const metodos = [
    { value: 'efectivo',       label: 'Efectivo',    icon: '💵' },
    { value: 'transferencia',  label: 'Transferencia', icon: '🏦' },
    { value: 'punto_de_venta', label: 'POS',          icon: '💳' },
    { value: 'zelle',          label: 'Zelle',        icon: '📲' },
    { value: 'pago_movil',     label: 'Pago móvil',   icon: '📱' },
    { value: 'otro',           label: 'Otro',         icon: '🔖' },
]

function nuevoMetodoItem(): MetodoItem {
    return { metodo: 'efectivo', monto_divisa: '', referencia: '' }
}

const form = useForm({
    divisa:        'USD' as 'USD' | 'EUR' | 'BS',
    observaciones: '',
    metodos:       [nuevoMetodoItem()] as MetodoItem[],
})

function agregarMetodo() {
    form.metodos.push(nuevoMetodoItem())
}

function quitarMetodo(idx: number) {
    form.metodos.splice(idx, 1)
}

/* ── Cálculos reactivos ─────────────────────────────────── */

const tasaActiva = computed<number | null>(() => {
    if (form.divisa === 'USD') return 1
    return props.tasas[form.divisa] ?? null
})

function calcUsd(montoDivisa: number | string): number {
    if (!tasaActiva.value || !montoDivisa) return 0
    return Math.round((Number(montoDivisa) / tasaActiva.value) * 10000) / 10000
}

const totalMetodosDivisa = computed(() =>
    form.metodos.reduce((acc, m) => acc + Number(m.monto_divisa || 0), 0)
)

const totalMetodosUsd = computed(() => calcUsd(totalMetodosDivisa.value))

const saldoTrasEste = computed(() =>
    props.orden.saldo_pendiente - totalMetodosUsd.value
)

const porcentajePagado = computed(() => {
    const total  = Number(props.orden.total)
    const pagado = Number(props.orden.monto_pagado_usd)
    if (!total) return 0
    return Math.min(100, Math.round((pagado / total) * 100))
})

const heroCards = computed(() => [
    { title: 'Total orden',     value: `$${Number(props.orden.total).toFixed(2)}`,            caption: 'en USD',                          delay: 0,   highlight: false },
    { title: 'Pagado',          value: `$${Number(props.orden.monto_pagado_usd).toFixed(2)}`, caption: `${porcentajePagado.value}% cubierto`, delay: 90,  highlight: false },
    { title: 'Saldo pendiente', value: `$${Math.max(0, props.orden.saldo_pendiente).toFixed(2)}`,
      caption: props.orden.saldo_pendiente <= 0 ? 'Liquidada ✓' : 'por cobrar',              delay: 180, highlight: props.orden.saldo_pendiente <= 0 },
])

const puedeEnviar = computed(() => {
    if (form.divisa !== 'USD' && !tasaActiva.value) return false
    return form.metodos.every(m => m.metodo && Number(m.monto_divisa) > 0)
})

const puedeEliminarPago = computed(() =>
    ['pendiente', 'parcial'].includes(props.orden.estado_pago)
)

/* ── Helpers de presentación ────────────────────────────── */

const divisaSimbol = computed(() =>
    ({ USD: '$', EUR: '€', BS: 'Bs.' })[form.divisa] ?? ''
)

function divisaSimbolFor(divisa: string): string {
    return ({ USD: '$', EUR: '€', BS: 'Bs.' } as Record<string, string>)[divisa] ?? ''
}

function divisaLabel(divisa: string): string {
    return ({ USD: 'USD ($)', EUR: 'EUR (€)', BS: 'Bs.' } as Record<string, string>)[divisa] ?? divisa
}

function metodoLabel(metodo: string): string {
    return metodos.find(m => m.value === metodo)?.label ?? metodo
}

function metodoIcon(metodo: string): string {
    return metodos.find(m => m.value === metodo)?.icon ?? '💰'
}

function formatFecha(fecha: string): string {
    return new Date(fecha).toLocaleString('es-ES', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

/* ── Acciones ───────────────────────────────────────────── */

function confirmarEliminar(pago: any) {
    pagoAEliminar.value = pago
}

function ejecutarEliminar() {
    if (!pagoAEliminar.value) return
    router.delete(`/recepcion/ordenes/${props.orden.id}/pagos/${pagoAEliminar.value.id}`, {
        onFinish: () => { pagoAEliminar.value = null },
    })
}

function imprimirRecibo() {
    window.print()
}

function goToShow() {
    if (isNavigatingBack.value) return
    isNavigatingBack.value = true
    router.visit(`/recepcion/ordenes/${props.orden.id}`, {
        onFinish: () => { isNavigatingBack.value = false },
    })
}

function submit() {
    if (form.processing || !puedeEnviar.value) return
    form.post(`/recepcion/ordenes/${props.orden.id}/pagos`)
}

/* ── Clases CSS ─────────────────────────────────────────── */

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-800'

const inputSmClass =
    'h-10 w-full rounded-xl border bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 transition duration-300 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 border-slate-300 focus:border-indigo-300 focus:ring-indigo-500/10 dark:border-slate-600 dark:bg-slate-900 dark:focus:border-indigo-800'
</script>

<style scoped>
.panel-float         { animation: panelFloat  720ms cubic-bezier(0.22,1,0.36,1) both; }
.field-reveal        { animation: fieldReveal 520ms ease-out both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from, .hero-card-appear-from     { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from, .modal-leave-to       { opacity: 0; }
@keyframes panelFloat  { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes fieldReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
@media print {
    body > *:not(#recibo-print) { display: none !important; }
    #recibo-print { display: block !important; }
}
</style>