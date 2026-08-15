<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Pagos'" />

        <div class="space-y-6">

            <!-- Flash -->
            <div v-if="flash.success || flash.warning || flash.error" class="space-y-3">
                <div v-if="flash.success"
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                    {{ flash.success }}
                </div>
                <div v-if="flash.warning"
                    class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                    {{ flash.warning }}
                </div>
                <div v-if="flash.error"
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                    {{ flash.error }}
                </div>
            </div>

            <!-- Hero -->
            <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
<div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(52,211,153,0.35),_transparent_30%),linear-gradient(135deg,_#0f766e_0%,_#0369a1_100%)] p-5 sm:p-7"
                >                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-2xl">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm">
                                Recepción
                            </span>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Registro de pagos
                            </h1>
                        </div>
                    </div>

                    <!-- Summary cards -->
                    <transition-group tag="div" class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4" name="summary-card" appear>
                        <article v-for="card in summaryCards" :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md">
                            <div class="mb-3 flex items-center gap-3">
                                <div :class="['flex h-10 w-10 items-center justify-center rounded-xl', card.iconClass]">
                                    <component :is="card.icon" class="h-5 w-5" />
                                </div>
                                <p class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase">{{ card.title }}</p>
                            </div>
                            <p class="text-2xl leading-none font-semibold">{{ card.value }}</p>
                            <p class="mt-2 text-sm text-white/65">{{ card.caption }}</p>
                        </article>
                    </transition-group>

                    <!-- Filtros -->
                    <transition appear name="filter-panel">
                        <div class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                            <div class="grid gap-4 xl:grid-cols-[1fr_160px_160px_160px_auto] xl:items-end">

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-white/85">Buscar</label>
                                    <div class="relative">
                                        <input v-model="search" type="text"
                                            placeholder="Orden, paciente, cédula..."
                                            class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 pl-11 text-sm text-white placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden" />
                                        <svg class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-white/45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-white/85">Divisa</label>
                                    <select v-model="selectedDivisa"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden">
                                        <option value="" class="text-slate-900">Todas</option>
                                        <option value="USD" class="text-slate-900">USD ($)</option>
                                        <option value="EUR" class="text-slate-900">EUR (€)</option>
                                        <option value="BS"  class="text-slate-900">Bs.</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-white/85">Estado</label>
                                    <select v-model="selectedEstado"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden">
                                        <option value="" class="text-slate-900">Todos</option>
                                        <option value="activo"  class="text-slate-900">Activos</option>
                                        <option value="anulado" class="text-slate-900">Anulados</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-white/85">Fecha</label>
                                    <input v-model="selectedFecha" type="date"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden" />
                                </div>

                                <button v-if="hayFiltrosActivos" type="button" @click="limpiarFiltros"
                                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Limpiar
                                </button>
                            </div>

                            <div v-if="activeFilterChips.length" class="mt-4 flex flex-wrap gap-2">
                                <span v-for="chip in activeFilterChips" :key="chip"
                                    class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-sm font-medium text-white/90">
                                    {{ chip }}
                                </span>
                            </div>
                        </div>
                    </transition>
                </div>
            </section>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Historial de pagos</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ tableHeadline }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ props.pagos.total }} registros
                        </span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Orden · Paciente</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Métodos</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Monto</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Cajero · Fecha</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Estado</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500">Acciones</p>
                                </th>
                            </tr>
                        </thead>

                        <transition-group tag="tbody" name="pago-row" appear
                            class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="pago in props.pagos.data" :key="pago.id"
                                :class="['group border-t border-slate-100 transition dark:border-slate-800',
                                    pago.estado === 'anulado'
                                        ? 'bg-slate-50/60 opacity-60 dark:bg-slate-800/20'
                                        : 'hover:bg-[linear-gradient(90deg,_rgba(16,185,129,0.03),_rgba(99,102,241,0.02))] dark:hover:bg-slate-800/40']">

                                <!-- Orden / Paciente -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-sm font-bold shadow-sm',
                                            pago.estado === 'anulado'
                                                ? 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500'
                                                : 'bg-[linear-gradient(135deg,_#d1fae5,_#a7f3d0)] text-emerald-700 dark:bg-[linear-gradient(135deg,_rgba(16,185,129,0.18),_rgba(5,150,105,0.18))] dark:text-emerald-300']">
                                            $
                                        </div>
                                        <div class="flex flex-col">
                                            <button type="button"
                                                @click="navigateTo(`/recepcion/ordenes/${pago.orden?.id}/cobrar`, `cobrar:${pago.id}`)"
                                                :disabled="Boolean(activeAction)"
                                                class="text-left text-theme-sm font-semibold text-slate-800 hover:text-indigo-600 dark:text-white/90 dark:hover:text-indigo-400">
                                                {{ pago.orden?.codigo ?? '—' }}
                                            </button>
                                            <span class="text-theme-xs text-slate-500 dark:text-slate-400">
                                                {{ pago.orden?.paciente?.nombres }} {{ pago.orden?.paciente?.apellidos }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Métodos -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="met in pago.metodos" :key="met.id"
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ metodoIcon(met.metodo) }} {{ metodoLabel(met.metodo) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Monto -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <div class="flex flex-col">
                                        <span :class="['text-theme-sm font-bold',
                                            pago.estado === 'anulado'
                                                ? 'text-slate-400 line-through dark:text-slate-500'
                                                : 'text-emerald-600 dark:text-emerald-400']">
                                            ${{ Number(pago.monto_usd).toFixed(2) }} USD
                                        </span>
                                        <span v-if="pago.divisa !== 'USD'" class="text-theme-xs text-slate-500 dark:text-slate-400">
                                            {{ divisaSimbol(pago.divisa) }}{{ Number(pago.monto_divisa).toFixed(2) }}
                                            · tasa {{ Number(pago.tasa_cambio).toFixed(4) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Cajero / Fecha -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <div class="flex flex-col">
                                        <span class="text-theme-sm text-slate-700 dark:text-slate-200">
                                            {{ pago.cajero?.name ?? '—' }}
                                        </span>
                                        <span class="text-theme-xs text-slate-500 dark:text-slate-400">
                                            {{ formatFecha(pago.created_at) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <div class="flex flex-col gap-1">
                                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold w-fit',
                                            pago.estado === 'activo'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ pago.estado === 'activo' ? 'Activo' : 'Anulado' }}
                                        </span>
                                        <span v-if="pago.estado === 'anulado' && pago.anulado_at"
                                            class="text-[10px] text-slate-400 dark:text-slate-500">
                                            {{ formatFecha(pago.anulado_at) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-4 sm:px-5">
                                    <div class="flex items-center gap-1.5">

                                        <!-- Ver orden -->
                                        <button type="button" :disabled="Boolean(activeAction)"
                                            @click="navigateTo(`/recepcion/pagos/${pago.id}`, `show:${pago.id}`)"
                                            title="Ver orden"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
                                            <svg v-if="activeAction === `show:${pago.id}`" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                            <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Anular (solo pagos activos) -->
                                        <button v-if="pago.estado === 'activo'"
                                            type="button" :disabled="Boolean(activeAction)"
                                            @click="solicitarAnulacion(pago)"
                                            title="Anular pago"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                            <svg v-if="activeAction === `anular:${pago.id}`" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                            <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>

                                        <!-- Badge anulado (no es botón) -->
                                        <span v-else
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-300 dark:border-slate-700 dark:text-slate-600"
                                            title="Pago anulado">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <!-- Estado vacío -->
                <div v-if="!props.pagos.data.length" class="px-6 py-16 text-center">
                    <div class="mx-auto max-w-md">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Sin pagos registrados</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ajusta los filtros o registra el cobro desde una orden.</p>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.pagos.from }} a {{ props.pagos.to }} de {{ props.pagos.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button v-if="props.pagos.prev_page_url" type="button"
                            :disabled="Boolean(activeAction)"
                            @click="navigateTo(props.pagos.prev_page_url, 'prev-page')"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ activeAction === 'prev-page' ? 'Cargando...' : 'Anterior' }}
                        </button>
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Página {{ props.pagos.current_page }} de {{ props.pagos.last_page }}
                        </span>
                        <button v-if="props.pagos.next_page_url" type="button"
                            :disabled="Boolean(activeAction)"
                            @click="navigateTo(props.pagos.next_page_url, 'next-page')"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ activeAction === 'next-page' ? 'Cargando...' : 'Siguiente' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación de anulación -->
        <Teleport to="body">
            <transition name="modal">
                <div v-if="pagoAAnular" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-sm rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-500/15">
                            <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">¿Anular este pago?</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Pago de <strong class="text-slate-800 dark:text-white">${{ Number(pagoAAnular.monto_usd).toFixed(2) }} USD</strong>
                                de la orden <strong class="text-slate-800 dark:text-white">{{ pagoAAnular.orden?.codigo }}</strong>.
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                La orden volverá a estado <strong>pendiente</strong> o <strong>pago parcial</strong> según el saldo restante. Esta acción queda registrada.
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="pagoAAnular = null"
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
import { computed, defineComponent, h, onBeforeUnmount, ref, watch } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

interface PagoMetodoItem {
    id: number
    metodo: string
    monto_divisa: string
    monto_usd: string
    referencia?: string
}

interface PagoItem {
    id: number
    orden?: { id: number; codigo: string; paciente?: { nombres: string; apellidos: string } }
    cajero?: { name: string }
    divisa: 'USD' | 'EUR' | 'BS'
    monto_divisa: string
    tasa_cambio: string
    monto_usd: string
    estado: 'activo' | 'anulado'
    referencia?: string
    observaciones?: string
    metodos: PagoMetodoItem[]
    created_at: string
    anulado_at?: string
}

interface Props {
    pagos: {
        data: PagoItem[]
        current_page: number
        last_page: number
        from: number
        to: number
        total: number
        prev_page_url?: string
        next_page_url?: string
    }
    totales: {
        total: number
        activos: number
        anulados: number
        suma_usd: number
    }
    filters: {
        search?: string
        divisa?: string
        estado?: string
        fecha?: string
    }
}

const props = defineProps<Props>()
const page  = usePage<{ flash?: { success?: string | null; warning?: string | null; error?: string | null } }>()
const flash = computed(() => page.props.flash ?? {})

const activeAction = ref<string | null>(null)
const pagoAAnular  = ref<PagoItem | null>(null)

const search         = ref(props.filters.search  ?? '')
const selectedDivisa = ref(props.filters.divisa  ?? '')
const selectedEstado = ref(props.filters.estado  ?? '')
const selectedFecha  = ref(props.filters.fecha   ?? '')

let searchDebounce: number | null = null
const DEBOUNCE_MS = 320

const hayFiltrosActivos = computed(() =>
    search.value.trim() !== '' || selectedDivisa.value !== '' ||
    selectedEstado.value !== '' || selectedFecha.value !== ''
)

const activeFilterChips = computed(() => {
    const chips: string[] = []
    if (search.value.trim())   chips.push(`Búsqueda: ${search.value.trim()}`)
    if (selectedDivisa.value)  chips.push(`Divisa: ${selectedDivisa.value}`)
    if (selectedEstado.value)  chips.push(selectedEstado.value === 'activo' ? 'Solo activos' : 'Solo anulados')
    if (selectedFecha.value)   chips.push(`Fecha: ${selectedFecha.value}`)
    return chips
})

function submitFilters() {
    if (activeAction.value) return
    const params: Record<string, string> = {}
    if (search.value.trim())   params.search = search.value.trim()
    if (selectedDivisa.value)  params.divisa  = selectedDivisa.value
    if (selectedEstado.value)  params.estado  = selectedEstado.value
    if (selectedFecha.value)   params.fecha   = selectedFecha.value
    router.get('/recepcion/pagos', params, { preserveState: true, preserveScroll: true, replace: true })
}

watch(search, (val) => {
    clearDebounce()
    if (val.trim().length > 1 || val.trim() === '') searchDebounce = window.setTimeout(submitFilters, DEBOUNCE_MS)
})
watch(selectedDivisa, () => { clearDebounce(); submitFilters() })
watch(selectedEstado, () => { clearDebounce(); submitFilters() })
watch(selectedFecha,  () => { clearDebounce(); submitFilters() })
onBeforeUnmount(clearDebounce)

function limpiarFiltros() {
    search.value = ''; selectedDivisa.value = ''; selectedEstado.value = ''; selectedFecha.value = ''
}
function clearDebounce() {
    if (searchDebounce !== null) { window.clearTimeout(searchDebounce); searchDebounce = null }
}

const tableHeadline = computed(() =>
    activeFilterChips.value.length
        ? `Vista filtrada por ${activeFilterChips.value.join(' · ')}`
        : 'Todos los pagos registrados en el sistema.'
)

const summaryCards = computed(() => [
    {
        title: 'Total cobrado',
        value: `$${Number(props.totales?.suma_usd ?? 0).toFixed(2)}`,
        caption: 'En pagos activos · USD',
        icon: icon('M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-emerald-300',
        delay: 0,
    },
    {
        title: 'Pagos activos',
        value: String(props.totales?.activos ?? 0),
        caption: 'Transacciones válidas',
        icon: icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-indigo-300',
        delay: 90,
    },
    {
        title: 'Anulados',
        value: String(props.totales?.anulados ?? 0),
        caption: 'No contabilizan en saldo',
        icon: icon('M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'),
        iconClass: 'bg-white/15 text-rose-300',
        delay: 180,
    },
    {
        title: 'Total registros',
        value: String(props.totales?.total ?? 0),
        caption: activeFilterChips.value.length ? 'Con filtros aplicados' : 'En el período completo',
        icon: icon('M3 4.5h18M6.75 12h10.5M10.5 19.5h3'),
        iconClass: 'bg-white/15 text-slate-300',
        delay: 270,
    },
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
function metodoLabel(m: string) { return metodosMap[m]?.label ?? m }
function metodoIcon(m: string)  { return metodosMap[m]?.icon  ?? '💰' }
function divisaSimbol(d: string) { return ({ USD: '$', EUR: '€', BS: 'Bs.' } as Record<string,string>)[d] ?? '' }

function formatFecha(fecha: string): string {
    return new Date(fecha).toLocaleString('es-ES', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

/* ── Navegación ──────────────────────────────────────────── */

function navigateTo(url: string, action: string) {
    if (activeAction.value) return
    activeAction.value = action
    router.visit(url, { preserveScroll: true, onFinish: () => { activeAction.value = null } })
}

/* ── Anulación ───────────────────────────────────────────── */

function solicitarAnulacion(pago: PagoItem) {
    if (activeAction.value) return
    pagoAAnular.value = pago
}

function ejecutarAnulacion() {
    if (!pagoAAnular.value) return
    const pago = pagoAAnular.value
    activeAction.value = `anular:${pago.id}`
    pagoAAnular.value  = null

    router.post(
        `/recepcion/ordenes/${pago.orden?.id}/pagos/${pago.id}/anular`,
        {},
        {
            preserveScroll: true,
            onFinish: () => { activeAction.value = null },
        }
    )
}

/* ── Icono SVG helper ────────────────────────────────────── */

function icon(path: string) {
    return defineComponent({
        setup(_, { attrs }) {
            return () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', ...attrs }, [
                h('path', { d: path, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }),
            ])
        },
    })
}
</script>

<style scoped>
.pago-row-enter-from,  .pago-row-appear-from  { opacity: 0; transform: translateY(14px); }
.pago-row-enter-to,    .pago-row-appear-to    { opacity: 1; transform: translateY(0); }
.pago-row-leave-from                          { opacity: 1; transform: translateY(0); }
.pago-row-leave-to                            { opacity: 0; transform: translateY(-8px); }
.pago-row-enter-active, .pago-row-appear-active, .pago-row-leave-active {
    transition: opacity 400ms ease, transform 400ms cubic-bezier(0.22,1,0.36,1);
}
.summary-card-enter-active, .summary-card-appear-active {
    transition: opacity 520ms ease, transform 520ms cubic-bezier(0.22,1,0.36,1);
}
.summary-card-enter-from, .summary-card-appear-from { opacity: 0; transform: translateX(-28px); }
.summary-card-enter-to,   .summary-card-appear-to   { opacity: 1; transform: translateX(0); }
.filter-panel-enter-active, .filter-panel-appear-active {
    transition: opacity 540ms ease, transform 540ms cubic-bezier(0.22,1,0.36,1);
}
.filter-panel-enter-from, .filter-panel-appear-from { opacity: 0; transform: translateY(32px); }
.filter-panel-enter-to,   .filter-panel-appear-to   { opacity: 1; transform: translateY(0); }
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from,   .modal-leave-to    { opacity: 0; }
</style>