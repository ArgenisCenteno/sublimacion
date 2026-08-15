<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Vehiculos'" />

        <div class="space-y-6">
            <div
                v-if="flash.success || flash.warning || flash.error"
                class="space-y-3"
            >
                <div
                    v-if="flash.success"
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash.warning"
                    class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
                >
                    {{ flash.warning }}
                </div>
                <div
                    v-if="flash.error"
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
                >
                    {{ flash.error }}
                </div>
            </div>

            <section
                class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
                <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.4),_transparent_30%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7"
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                    >
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Flota activa
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Vista operativa de vehiculos
                            </h1>
                        </div>

                        <button
                            type="button"
                            v-if="can('vehicles.create')"
                            :disabled="Boolean(activeAction)"
                            @click="navigateToCreate"
                            class="animate-pop inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            {{
                                activeAction === 'create'
                                    ? 'Cargando...'
                                    : 'Nuevo Vehiculo'
                            }}
                        </button>
                    </div>

                    <transition-group
                        tag="div"
                        class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4"
                        name="summary-card"
                        appear
                    >
                        <article
                            v-for="card in summaryCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md"
                        >
                            <div class="mb-3 flex items-center gap-3">
                                <div
                                    :class="[
                                        'flex h-10 w-10 items-center justify-center rounded-xl',
                                        card.iconClass,
                                    ]"
                                >
                                    <component
                                        :is="card.icon"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <p
                                    class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase"
                                >
                                    {{ card.title }}
                                </p>
                            </div>
                            <p class="text-2xl leading-none font-semibold">
                                {{ card.value }}
                            </p>
                            <p class="mt-2 text-sm text-white/65">
                                {{ card.caption }}
                            </p>
                        </article>
                    </transition-group>

                    <transition appear name="filter-panel">
                        <div
                            class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md"
                        >
                            <div
                                class="grid gap-4 xl:grid-cols-[1fr_320px_auto] xl:items-end"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                    >
                                        Buscar
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Buscar por placa, marca, modelo..."
                                            class="animate-fade-in h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 pl-11 text-sm text-white shadow-theme-xs placeholder:text-white/45 focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                        />
                                        <svg
                                            class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-white/45"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                    >
                                        Estado fisico
                                    </label>
                                    <select
                                        v-model="selectedPhysicalStatus"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos los estados
                                        </option>
                                        <option
                                            v-for="option in physicalStatusOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            class="text-slate-900"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <button
                                    v-if="selectedPhysicalStatus"
                                    type="button"
                                    @click="clearPhysicalStatusFilter"
                                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15"
                                >
                                    Limpiar filtros
                                </button>
                            </div>

                            <div
                                v-if="activeFilterChips.length"
                                class="mt-4 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="chip in activeFilterChips"
                                    :key="chip"
                                    class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-sm font-medium text-white/90"
                                >
                                    {{ chip }}
                                </span>
                            </div>
                        </div>
                    </transition>
                </div>
            </section>

            <div
                class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-xl font-semibold text-slate-900 dark:text-white"
                            >
                                Inventario de vehiculos
                            </h2>
                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ tableHeadline }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ props.vehicles.total }} registros
                        </span>
                    </div>
                </div>

                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-200 dark:border-slate-800"
                            >
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Placa
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Vehiculo
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Estado fisico
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Estado comercial
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Precio
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Acciones
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <transition-group
                            tag="tbody"
                            name="vehicle"
                            appear
                            enter-active-class="transition-opacity transition-transform duration-500 ease-in-out"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-opacity transition-transform duration-400 ease-in-out"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="vehicle in displayedVehicles"
                                :key="vehicle.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(168,85,247,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#e0e7ff_0%,_#ddd6fe_100%)] text-indigo-600 shadow-sm dark:bg-[linear-gradient(135deg,_rgba(99,102,241,0.18)_0%,_rgba(168,85,247,0.18)_100%)] dark:text-indigo-200"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                            >
                                                {{ vehicle.plate }}
                                            </span>
                                            <span
                                                v-if="vehicle.vin"
                                                class="text-theme-xs text-slate-500 dark:text-slate-400"
                                            >
                                                VIN: {{ vehicle.vin }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >
                                            {{ vehicle.brand }}
                                            {{ vehicle.model }}
                                        </span>
                                        <span
                                            v-if="vehicle.version"
                                            class="text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ vehicle.version }}
                                        </span>
                                        <span
                                            class="text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                vehicle.color ||
                                                'Color pendiente'
                                            }}
                                            - {{ vehicle.mileage_km }} km
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-theme-xs font-semibold',
                                            getPhysicalStatusClass(
                                                vehicle.physical_status,
                                            ),
                                        ]"
                                    >
                                        {{
                                            getPhysicalStatusLabel(
                                                vehicle.physical_status,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-theme-xs font-semibold',
                                            getCommercialStatusClass(
                                                vehicle.commercial_status,
                                            ),
                                        ]"
                                    >
                                        {{
                                            getCommercialStatusLabel(
                                                vehicle.commercial_status,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span
                                            v-if="vehicle.price_cash"
                                            class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >
                                            {{
                                                formatCurrency(
                                                    vehicle.price_cash,
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-if="vehicle.price_financed"
                                            class="text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Fin:
                                            {{
                                                formatCurrency(
                                                    vehicle.price_financed,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <button
                                            v-if="can('vehicles.view')"
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateToVehicle(
                                                    vehicle.id,
                                                    'show',
                                                )
                                            "
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            {{
                                                activeAction ===
                                                `show:${vehicle.id}`
                                                    ? 'Cargando...'
                                                    : 'Ver'
                                            }}
                                        </button>
                                        <button
                                            type="button"
                                            v-if="can('vehicles.edit')"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateToVehicle(
                                                    vehicle.id,
                                                    'edit',
                                                )
                                            "
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-theme-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            {{
                                                activeAction ===
                                                `edit:${vehicle.id}`
                                                    ? 'Cargando...'
                                                    : 'Editar'
                                            }}
                                        </button>
                                        <button
                                            type="button"
                                            v-if="can('vehicles.delete')"
                                            :disabled="Boolean(activeAction)"
                                            @click="deleteVehicle(vehicle)"
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-1.5 text-theme-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            {{
                                                activeAction ===
                                                `delete:${vehicle.id}`
                                                    ? 'Eliminando...'
                                                    : 'Eliminar'
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <div
                    v-if="!displayedVehicles.length"
                    class="px-6 py-16 text-center"
                >
                    <div class="mx-auto max-w-md">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <svg
                                class="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos vehiculos
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o limpia la busqueda para volver
                            a ver el inventario completo.
                        </p>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.vehicles.from }} a
                        {{ props.vehicles.to }} de
                        {{ props.vehicles.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="props.vehicles.prev_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateToPage(
                                    props.vehicles.prev_page_url,
                                    'prev-page',
                                )
                            "
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            {{
                                activeAction === 'prev-page'
                                    ? 'Cargando...'
                                    : 'Anterior'
                            }}
                        </button>
                        <span
                            class="text-sm text-slate-600 dark:text-slate-400"
                        >
                            Pagina {{ props.vehicles.current_page }} de
                            {{ props.vehicles.last_page }}
                        </span>
                        <button
                            v-if="props.vehicles.next_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateToPage(
                                    props.vehicles.next_page_url,
                                    'next-page',
                                )
                            "
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            {{
                                activeAction === 'next-page'
                                    ? 'Cargando...'
                                    : 'Siguiente'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, defineComponent, h, onBeforeUnmount, ref, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';

type PhysicalStatus =
    | 'en_transito'
    | 'en_campa'
    | 'taller_chapa'
    | 'taller_dab'
    | 'taller_limpieza'
    | 'pendiente_recogida'
    | 'disponible'
    | 'entregado';

const physicalStatusLabels: Record<PhysicalStatus, string> = {
    en_transito: 'En transito',
    en_campa: 'En campa',
    taller_chapa: 'Taller chapa',
    taller_dab: 'Taller DAB',
    taller_limpieza: 'Limpieza',
    pendiente_recogida: 'Pendiente recogida',
    disponible: 'Disponible',
    entregado: 'Entregado',
};

const physicalStatusOptions = (
    Object.entries(physicalStatusLabels) as Array<[PhysicalStatus, string]>
).map(([value, label]) => ({ value, label }));

interface Props {
    vehicles: {
        data: Array<{
            id: number;
            plate: string;
            vin?: string;
            brand: string;
            model: string;
            version?: string;
            color?: string;
            mileage_km: number;
            physical_status: string;
            commercial_status: string;
            price_cash?: number;
            price_financed?: number;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    filters: {
        search?: string;
        physical_status?: string;
    };
}

const props = defineProps<Props>();
const page = usePage<{
    auth: {
        user: any;
        permissions: string[];
        roles: string[];
    };
    flash?: {
        success?: string | null;
        warning?: string | null;
        error?: string | null;
    };
}>();
const flash = computed(() => page.props.flash ?? {});
const permissions = computed<string[]>(() => {
    return (page.props.auth?.permissions as string[]) ?? [];
});

const can = (permission: string): boolean => {
    return permissions.value.includes(permission);
};
type Vehicle = Props['vehicles']['data'][number];

const displayedVehicles = ref<Vehicle[]>([]);
const search = ref(props.filters.search ?? '');
const selectedPhysicalStatus = ref(props.filters.physical_status ?? '');
const activeAction = ref<string | null>(null);
let searchDebounceTimeout: number | null = null;

const SEARCH_DEBOUNCE_MS = 320;

watch(
    () => props.vehicles.data,
    (newVehicles) => {
        displayedVehicles.value = [...newVehicles];
    },
    { immediate: true },
);

const submitFilters = (nextSearch: string, nextPhysicalStatus: string) => {
    if (activeAction.value) {
        return;
    }

    const trimmedSearch = nextSearch.trim();
    const params: Record<string, string> = {};

    if (trimmedSearch.length > 2) {
        params.search = trimmedSearch;
    }

    if (nextPhysicalStatus) {
        params.physical_status = nextPhysicalStatus;
    }

    router.get('/vehicles', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(search, (newSearch) => {
    const trimmedSearch = newSearch.trim();

    if (trimmedSearch.length > 2 || trimmedSearch === '') {
        clearSearchDebounce();
        searchDebounceTimeout = window.setTimeout(() => {
            submitFilters(trimmedSearch, selectedPhysicalStatus.value);
        }, SEARCH_DEBOUNCE_MS);
    }
});

watch(selectedPhysicalStatus, (newPhysicalStatus) => {
    clearSearchDebounce();
    const trimmedSearch = search.value.trim();
    const nextSearch = trimmedSearch.length > 2 ? trimmedSearch : '';

    submitFilters(nextSearch, newPhysicalStatus);
});

onBeforeUnmount(() => {
    clearSearchDebounce();
});

const clearPhysicalStatusFilter = () => {
    selectedPhysicalStatus.value = '';
};

const activeFilterChips = computed(() => {
    const chips: string[] = [];

    if (search.value.trim()) {
        chips.push(`Busqueda: ${search.value.trim()}`);
    }

    if (selectedPhysicalStatus.value) {
        chips.push(
            `Estado: ${getPhysicalStatusLabel(selectedPhysicalStatus.value)}`,
        );
    }

    return chips;
});

const summaryCards = computed(() => [
    {
        title: 'En esta pagina',
        value: String(props.vehicles.data.length),
        caption: 'Vehiculos visibles segun filtros y paginacion',
        icon: icon(
            'M9 17h6m-7 0H6a1 1 0 01-1-1v-3l2.08-5.197A2 2 0 018.936 6h6.128a2 2 0 011.856 1.803L19 13v3a1 1 0 01-1 1h-2m-8 0v1a1 1 0 001 1h0a1 1 0 001-1v-1m4 0v1a1 1 0 001 1h0a1 1 0 001-1v-1M7 13h10',
        ),
        iconClass: 'bg-white/15 text-sky-300',
        delay: 0,
    },
    {
        title: 'Disponibles',
        value: String(
            props.vehicles.data.filter(
                (vehicle) => vehicle.physical_status === 'disponible',
            ).length,
        ),
        caption: 'Unidades listas para siguiente paso',
        icon: icon('M5 13l4 4L19 7'),
        iconClass: 'bg-white/15 text-emerald-300',
        delay: 90,
    },
    {
        title: 'En taller',
        value: String(
            props.vehicles.data.filter((vehicle) =>
                vehicle.physical_status.startsWith('taller_'),
            ).length,
        ),
        caption: 'Chapa, DAB o limpieza en curso',
        icon: icon(
            'M14.7 6.3a1 1 0 010 1.4l-6.99 6.99a2 2 0 01-.82.49l-2.54.85.85-2.54a2 2 0 01.49-.82l6.99-6.99a1 1 0 011.4 0l.62.62zm0 0l1.59-1.59a1 1 0 011.4 0l.61.62a1 1 0 010 1.4L16.7 8.3',
        ),
        iconClass: 'bg-white/15 text-amber-300',
        delay: 180,
    },
    {
        title: 'Filtros activos',
        value: String(activeFilterChips.value.length),
        caption: activeFilterChips.value.length
            ? activeFilterChips.value.join(' - ')
            : 'Sin restricciones activas',
        icon: icon('M3 4.5h18M6.75 12h10.5M10.5 19.5h3'),
        iconClass: 'bg-white/15 text-indigo-200',
        delay: 270,
    },
]);

const tableHeadline = computed(() => {
    if (activeFilterChips.value.length) {
        return `Vista filtrada por ${activeFilterChips.value.join(' - ')}`;
    }

    return 'Consulta general del inventario con acceso rapido a seguimiento y acciones.';
});

const getPhysicalStatusClass = (status: string) => {
    const classes = {
        en_transito:
            'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        en_campa:
            'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400',
        taller_chapa:
            'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        taller_dab:
            'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
        taller_limpieza:
            'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-400',
        pendiente_recogida:
            'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400',
        disponible:
            'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        entregado:
            'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400',
    } as const;

    return (
        classes[status as keyof typeof classes] ||
        'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400'
    );
};

const getPhysicalStatusLabel = (status: string) => {
    return physicalStatusLabels[status as PhysicalStatus] || status;
};

const getCommercialStatusClass = (status: string) => {
    const classes = {
        no_publicable:
            'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400',
        disponible:
            'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        reservado:
            'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400',
        vendido: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    } as const;

    return (
        classes[status as keyof typeof classes] ||
        'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400'
    );
};

const getCommercialStatusLabel = (status: string) => {
    const labels = {
        no_publicable: 'No publicable',
        disponible: 'Disponible',
        reservado: 'Reservado',
        vendido: 'Vendido',
    } as const;

    return labels[status as keyof typeof labels] || status;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const deleteVehicle = (vehicle: Vehicle) => {
    if (activeAction.value) {
        return;
    }

    if (
        confirm(
            `Estas seguro de que quieres eliminar el vehiculo ${vehicle.plate}?`,
        )
    ) {
        activeAction.value = `delete:${vehicle.id}`;
        router.delete(`/vehicles/${vehicle.id}`, {
            onFinish: () => {
                activeAction.value = null;
            },
        });
    }
};

function navigateToCreate() {
    navigate('/vehicles/create', 'create');
}

function navigateToVehicle(vehicleId: number, target: 'show' | 'edit') {
    navigate(
        target === 'show'
            ? `/vehicles/${vehicleId}`
            : `/vehicles/${vehicleId}/edit`,
        `${target}:${vehicleId}`,
    );
}

function navigateToPage(url: string, action: 'prev-page' | 'next-page') {
    navigate(url, action);
}

function navigate(url: string, action: string) {
    if (activeAction.value) {
        return;
    }

    activeAction.value = action;
    router.visit(url, {
        preserveScroll: true,
        onFinish: () => {
            activeAction.value = null;
        },
    });
}

function icon(path: string) {
    return defineComponent({
        name: 'SummaryIcon',
        setup(_, { attrs }) {
            return () =>
                h(
                    'svg',
                    {
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        'stroke-width': '1.8',
                        ...attrs,
                    },
                    [
                        h('path', {
                            d: path,
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        }),
                    ],
                );
        },
    });
}

function clearSearchDebounce() {
    if (searchDebounceTimeout !== null) {
        window.clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = null;
    }
}
</script>

<style scoped>
.vehicle-enter-from,
.vehicle-appear-from {
    opacity: 0;
    transform: translateY(16px);
}

.vehicle-enter-to,
.vehicle-appear-to {
    opacity: 1;
    transform: translateY(0);
}

.vehicle-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.vehicle-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pop {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }
    60% {
        transform: scale(1.02);
        opacity: 1;
    }
    100% {
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fadeIn 400ms ease-out;
}

.animate-pop {
    animation: pop 450ms ease-out;
}

.summary-card-enter-active,
.summary-card-appear-active {
    transition:
        opacity 520ms ease,
        transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}

.summary-card-enter-from,
.summary-card-appear-from {
    opacity: 0;
    transform: translateX(-28px);
}

.summary-card-enter-to,
.summary-card-appear-to {
    opacity: 1;
    transform: translateX(0);
}

.filter-panel-enter-active,
.filter-panel-appear-active {
    transition:
        opacity 540ms ease,
        transform 540ms cubic-bezier(0.22, 1, 0.36, 1);
}

.filter-panel-enter-from,
.filter-panel-appear-from {
    opacity: 0;
    transform: translateY(32px);
}

.filter-panel-enter-to,
.filter-panel-appear-to {
    opacity: 1;
    transform: translateY(0);
}
</style>
