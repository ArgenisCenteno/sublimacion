<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Pago #${pago.id}`" />

        <div class="space-y-6">

            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.28),_transparent_32%),linear-gradient(135deg,_#022c22_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <span class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Recepción · Pago
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                ${{ Number(pago.monto_usd).toFixed(2) }} USD
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="text-sm text-white/60">
                                    Orden <strong class="font-mono text-white/90">{{ pago.orden?.codigo }}</strong>
                                </span>
                                <span class="text-white/30">·</span>
                                <span class="text-sm text-white/60">
                                    {{ pago.orden?.paciente?.nombres }} {{ pago.orden?.paciente?.apellidos }}
                                </span>
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    pago.estado === 'activo'
                                        ? 'bg-emerald-400/20 text-emerald-300'
                                        : 'bg-rose-400/20 text-rose-300']">
                                    {{ pago.estado === 'activo' ? 'Activo' : 'Anulado' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="button" :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/recepcion/ordenes/${pago.orden?.id}/cobrar`, 'cobrar')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15">
                                {{ activeAction === 'cobrar' ? 'Cargando...' : 'Ver cobro de la orden' }}
                            </button>
                            <button type="button" :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/recepcion/pagos`, 'index')"
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15">
                                {{ activeAction === 'index' ? 'Cargando...' : 'Volver al listado' }}
                            </button>
                        </div>
                    </div>

                    <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-4" name="hero-card" appear>
                        <article v-for="card in statsCards" :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md">
                            <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            <p class="mt-3 text-2xl font-semibold">{{ card.value }}</p>
                            <p class="mt-1 text-sm text-white/65">{{ card.caption }}</p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <!-- Flash -->
            <transition-group v-if="flash.success || flash.error" tag="div" class="space-y-3" name="flash-stack" appear>
                <Alert key="success" v-if="flash.success" variant="success" title="Listo" :message="flash.success" />
                <Alert key="error"   v-if="flash.error"   variant="error"   title="Error" :message="flash.error" />
            </transition-group>

            <!-- Banner anulado -->
            <div v-if="pago.estado === 'anulado'"
                class="rounded-[28px] border border-rose-200 bg-rose-50 p-5 dark:border-rose-500/30 dark:bg-rose-500/10">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-rose-900 dark:text-rose-200">Pago anulado</p>
                        <p class="mt-0.5 text-xs text-rose-700 dark:text-rose-400">
                            Anulado el {{ formatDate(pago.anulado_at) }}
                            <template v-if="pago.anulado_por?.name"> por <strong>{{ pago.anulado_por.name }}</strong></template>.
                            Este pago no contabiliza en el saldo de la orden.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <div class="space-y-6">

                    <!-- Desglose de métodos -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="mb-5 text-base font-semibold text-slate-900 dark:text-white">Métodos de pago</h2>

                        <div class="space-y-3">
                            <div v-for="(met, idx) in pago.metodos" :key="met.id"
                                class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-xl shadow-sm dark:bg-slate-900">
                                        {{ metodoIcon(met.metodo) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ metodoLabel(met.metodo) }}</p>
                                        <p v-if="met.referencia" class="text-xs text-slate-500 dark:text-slate-400">
                                            Ref: {{ met.referencia }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ divisaSimbol(pago.divisa) }}{{ Number(met.monto_divisa).toFixed(2) }}
                                    </p>
                                    <p v-if="pago.divisa !== 'USD'" class="text-xs text-slate-500 dark:text-slate-400">
                                        = ${{ Number(met.monto_usd).toFixed(2) }} USD
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="mt-4 space-y-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                            <div v-if="pago.divisa !== 'USD'" class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Subtotal en {{ divisaLabel(pago.divisa) }}
                                </span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ divisaSimbol(pago.divisa) }}{{ Number(pago.monto_divisa).toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="pago.divisa !== 'USD'" class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Tasa aplicada
                                </span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ Number(pago.tasa_cambio).toFixed(4) }} {{ pago.divisa }}/USD
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base font-bold text-slate-900 dark:text-white">Total en USD</span>
                                <span :class="['text-lg font-bold', pago.estado === 'anulado' ? 'text-slate-400 line-through dark:text-slate-500' : 'text-emerald-600 dark:text-emerald-400']">
                                    ${{ Number(pago.monto_usd).toFixed(2) }} USD
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Observaciones -->
                    <section v-if="pago.observaciones"
                        class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay:60ms">
                        <h2 class="mb-3 text-base font-semibold text-slate-900 dark:text-white">Observaciones</h2>
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ pago.observaciones }}</p>
                    </section>

                    <!-- Resumen de la orden -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay:90ms">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Orden asociada</h2>
                            <button type="button" :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/recepcion/ordenes/${pago.orden?.id}`, `orden:${pago.orden?.id}`)"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                                <svg v-if="activeAction === `orden:${pago.orden?.id}`" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <template v-else>
                                    Ver orden
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </template>
                            </button>
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div v-for="field in datosOrden" :key="field.label"
                                class="rounded-2xl bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ field.label }}</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{{ field.value || '—' }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6">

                    <!-- Datos del registro -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Registro del pago</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Cajero</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ pago.cajero?.name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Fecha y hora</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ formatDate(pago.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Divisa</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ divisaLabel(pago.divisa) }}</dd>
                            </div>
                            <div v-if="pago.divisa !== 'USD'" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Tasa</dt>
                                <dd class="font-mono text-sm text-slate-800 dark:text-white">{{ Number(pago.tasa_cambio).toFixed(4) }}</dd>
                            </div>
                            <div v-if="pago.referencia" class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">Referencia</dt>
                                <dd class="font-medium text-slate-800 dark:text-white">{{ pago.referencia }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">ID interno</dt>
                                <dd class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ pago.id }}</dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Acciones -->
                    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay:60ms">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Acciones</h2>
                        <div class="mt-4 space-y-2">

                            <button type="button" :disabled="Boolean(activeAction)"
                                @click="navigateTo(`/recepcion/ordenes/${pago.orden?.id}/cobrar`, 'cobrar')"
                                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ activeAction === 'cobrar' ? 'Cargando...' : 'Ir al cobro de la orden' }}
                            </button>

                            <button type="button" @click="imprimirRecibo"
                                class="flex w-full items-center gap-3 rounded-2xl border border-indigo-200 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-500/30 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Imprimir recibo
                            </button>

                            <button v-if="pago.estado === 'activo'"
                                type="button" :disabled="Boolean(activeAction)"
                                @click="solicitarAnulacion"
                                class="flex w-full items-center gap-3 rounded-2xl border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                {{ activeAction === 'anular' ? 'Anulando...' : 'Anular este pago' }}
                            </button>
                        </div>
                    </section>

                    <!-- Info anulación -->
                    <section v-if="pago.estado === 'anulado'"
                        class="panel-float rounded-[28px] border border-rose-200 bg-rose-50 p-6 dark:border-rose-500/20 dark:bg-rose-500/10"
                        style="animation-delay:120ms">
                        <h2 class="text-base font-semibold text-rose-900 dark:text-rose-200">Datos de anulación</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-rose-600 dark:text-rose-400">Anulado por</dt>
                                <dd class="font-medium text-rose-800 dark:text-rose-200">{{ pago.anulado_por?.name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-rose-600 dark:text-rose-400">Fecha</dt>
                                <dd class="font-medium text-rose-800 dark:text-rose-200">{{ formatDate(pago.anulado_at) }}</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>

        <!-- Modal confirmación anulación -->
        <Teleport to="body">
            <transition name="modal">
                <div v-if="modalAnular" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-sm rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-500/15">
                            <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">¿Anular este pago?</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            Se anulará el pago de <strong class="text-slate-800 dark:text-white">${{ Number(pago.monto_usd).toFixed(2) }} USD</strong>
                            de la orden <strong class="text-slate-800 dark:text-white">{{ pago.orden?.codigo }}</strong>.
                            La orden volverá a estado pendiente o pago parcial. Esta acción queda registrada.
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="modalAnular = false"
                                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button type="button" @click="ejecutarAnulacion"
                                class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-rose-500">
                                Sí, anular pago
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import Alert from '@/components/ui/Alert.vue'

interface PagoMetodo {
    id: number
    metodo: string
    monto_divisa: string
    monto_usd: string
    referencia?: string
}

interface Pago {
    id: number
    divisa: 'USD' | 'EUR' | 'BS'
    monto_divisa: string
    tasa_cambio: string
    monto_usd: string
    estado: 'activo' | 'anulado'
    referencia?: string
    observaciones?: string
    created_at: string
    anulado_at?: string
    cajero?: { name: string }
    anulado_por?: { name: string }
    metodos: PagoMetodo[]
    orden?: {
        id: number
        codigo: string
        estado: string
        estado_pago: string
        estado_label: string
        estado_pago_label: string
        total: string
        monto_pagado_usd: number
        saldo_pendiente: number
        paciente?: { nombres: string; apellidos: string; cedula: string }
        medico?: { nombre_completo: string }
    }
}

const props       = defineProps<{ pago: Pago }>()
const page        = usePage<{ flash?: { success?: string | null; error?: string | null } }>()
const flash       = computed(() => page.props.flash ?? {})
const activeAction = ref<string | null>(null)
const modalAnular  = ref(false)

/* ── Stats cards ─────────────────────────────────────────── */

const statsCards = computed(() => [
    {
        title:   'Total USD',
        value:   `$${Number(props.pago.monto_usd).toFixed(2)}`,
        caption: props.pago.estado === 'anulado' ? 'Anulado — no contabiliza' : 'Acreditado a la orden',
        delay: 0,
    },
    {
        title:   'Métodos',
        value:   String(props.pago.metodos?.length ?? 0),
        caption: props.pago.metodos?.map(m => metodoLabel(m.metodo)).join(', ') || '—',
        delay: 90,
    },
    {
        title:   'Divisa',
        value:   divisaLabel(props.pago.divisa),
        caption: props.pago.divisa !== 'USD'
            ? `Tasa: ${Number(props.pago.tasa_cambio).toFixed(4)}`
            : 'Sin conversión aplicada',
        delay: 180,
    },
    {
        title:   'Estado',
        value:   props.pago.estado === 'activo' ? 'Activo' : 'Anulado',
        caption: props.pago.estado === 'activo'
            ? `Registrado el ${formatDate(props.pago.created_at)}`
            : `Anulado el ${formatDate(props.pago.anulado_at)}`,
        delay: 270,
    },
])

/* ── Datos de la orden ───────────────────────────────────── */

const datosOrden = computed(() => [
    { label: 'Código',        value: props.pago.orden?.codigo },
    { label: 'Paciente',      value: `${props.pago.orden?.paciente?.nombres ?? ''} ${props.pago.orden?.paciente?.apellidos ?? ''}`.trim() },
    { label: 'Cédula',        value: props.pago.orden?.paciente?.cedula },
    { label: 'Médico',        value: props.pago.orden?.medico?.nombre_completo },
    { label: 'Estado orden',  value: props.pago.orden?.estado_label },
    { label: 'Estado pago',   value: props.pago.orden?.estado_pago_label },
    { label: 'Total orden',   value: `$${Number(props.pago.orden?.total ?? 0).toFixed(2)} USD` },
    { label: 'Saldo pendiente', value: `$${Number(props.pago.orden?.saldo_pendiente ?? 0).toFixed(2)} USD` },
])

/* ── Helpers ─────────────────────────────────────────────── */

const metodosMap: Record<string, { label: string; icon: string }> = {
    efectivo:       { label: 'Efectivo',      icon: '💵' },
    transferencia:  { label: 'Transferencia', icon: '🏦' },
    punto_de_venta: { label: 'POS',           icon: '💳' },
    zelle:          { label: 'Zelle',         icon: '📲' },
    pago_movil:     { label: 'Pago móvil',    icon: '📱' },
    otro:           { label: 'Otro',          icon: '🔖' },
}
function metodoLabel(m: string)  { return metodosMap[m]?.label ?? m }
function metodoIcon(m: string)   { return metodosMap[m]?.icon  ?? '💰' }
function divisaLabel(d: string)  { return ({ USD: 'USD ($)', EUR: 'EUR (€)', BS: 'Bs.' } as Record<string, string>)[d] ?? d }
function divisaSimbol(d: string) { return ({ USD: '$', EUR: '€', BS: 'Bs.' } as Record<string, string>)[d] ?? '' }

function formatDate(iso?: string): string {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('es-VE', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

/* ── Acciones ────────────────────────────────────────────── */

function navigateTo(url: string, action: string) {
    if (activeAction.value) return
    activeAction.value = action
    router.visit(url, { onFinish: () => { activeAction.value = null } })
}

function imprimirRecibo() {
    window.print()
}

function solicitarAnulacion() {
    if (activeAction.value) return
    modalAnular.value = true
}

function ejecutarAnulacion() {
    modalAnular.value  = false
    activeAction.value = 'anular'
    router.post(
        `/recepcion/ordenes/${props.pago.orden?.id}/pagos/${props.pago.id}/anular`,
        {},
        {
            preserveScroll: true,
            onFinish: () => { activeAction.value = null },
        }
    )
}
</script>

<style scoped>
.panel-float         { animation: panelFloat  720ms cubic-bezier(0.22,1,0.36,1) both; }
.animate-badge-float { animation: badgeFloat 5.5s ease-in-out infinite; }
.hero-card-enter-active, .hero-card-appear-active { transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1); }
.hero-card-enter-from,  .hero-card-appear-from    { opacity:0; transform:translateY(24px) scale(0.98); }
.flash-stack-enter-active, .flash-stack-appear-active, .flash-stack-leave-active { transition: opacity 340ms ease, transform 340ms ease; }
.flash-stack-enter-from, .flash-stack-appear-from, .flash-stack-leave-to { opacity:0; transform:translateY(-10px); }
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from,   .modal-leave-to    { opacity: 0; }
@keyframes panelFloat { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
@keyframes badgeFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-2px); } }
</style>