<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Órdenes de Trabajo'" />

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

            <!-- Hero -->
            <section
                class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
              <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(52,211,153,0.35),_transparent_30%),linear-gradient(135deg,_#0f766e_0%,_#0369a1_100%)] p-5 sm:p-7"
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                    >
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Recepción
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Órdenes de trabajo
                            </h1>
                        </div>

                        
                    </div>

                    <!-- Summary cards -->
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

                    <!-- Filtros -->
                    <transition appear name="filter-panel">
                        <div
                            class="mt-6 rounded-[24px] border border-white/15 bg-white/10 p-4 backdrop-blur-md"
                        >
                            <div
                                class="grid gap-4 xl:grid-cols-[1fr_180px_180px_160px_auto] xl:items-end"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Buscar</label
                                    >
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Código, cédula, nombre del paciente..."
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
                                        >Estado</label
                                    >
                                    <select
                                        v-model="selectedEstado"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos
                                        </option>
                                        <option
                                            v-for="opt in estadoOpciones"
                                            :key="opt.value"
                                            :value="opt.value"
                                            class="text-slate-900"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Pago</label
                                    >
                                    <select
                                        v-model="selectedEstadoPago"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos
                                        </option>
                                        <option
                                            value="pendiente"
                                            class="text-slate-900"
                                        >
                                            Pendiente
                                        </option>
                                        <option
                                            value="pagado"
                                            class="text-slate-900"
                                        >
                                            Pagado
                                        </option>
                                        <option
                                            value="exonerado"
                                            class="text-slate-900"
                                        >
                                            Exonerado
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Fecha</label
                                    >
                                    <input
                                        v-model="selectedFecha"
                                        type="date"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    />
                                </div>

                                <button
                                    v-if="hayFiltrosActivos"
                                    type="button"
                                    @click="limpiarFiltros"
                                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15"
                                >
                                    Limpiar
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

            <!-- Tabla -->
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
                                Listado de órdenes
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
                            {{ props.ordenes.total }} registros
                        </span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-200 dark:border-slate-800"
                            >
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Orden
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Paciente
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Médico
                                    </p>
                                </th>
                               
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Total
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Estado
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Pago
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
                            name="orden"
                            appear
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="orden in displayedOrdenes"
                                :key="orden.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(251,191,36,0.04),_rgba(245,158,11,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <!-- Orden -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#fef3c7_0%,_#fde68a_100%)] text-amber-700 shadow-sm dark:bg-[linear-gradient(135deg,_rgba(251,191,36,0.18)_0%,_rgba(245,158,11,0.18)_100%)] dark:text-amber-300"
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
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-mono text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                            >
                                                {{ orden.codigo }}
                                            </span>
                                            <span
                                                class="text-theme-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    formatDate(orden.created_at)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Paciente -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                        >
                                            {{ orden.paciente?.apellidos }},
                                            {{ orden.paciente?.nombres }}
                                        </span>
                                        <span
                                            class="font-mono text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                orden.paciente?.tipo_documento
                                            }}-{{ orden.paciente?.cedula }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Médico -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span
                                        v-if="orden.medico"
                                        class="text-theme-sm text-slate-700 dark:text-slate-200"
                                    >
                                        {{ orden.medico.nombre_completo }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-theme-xs text-slate-400 dark:text-slate-500"
                                        >—</span
                                    >
                                </td>

                                

                                <!-- Total -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                    >
                                        ${{ Number(orden.total).toFixed(2) }}
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span :class="estadoClass(orden.estado)">
                                        {{ estadoLabel(orden.estado) }}
                                    </span>
                                </td>

                                <!-- Pago -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span
                                        :class="
                                            estadoPagoClass(orden.estado_pago)
                                        "
                                    >
                                        {{ estadoPagoLabel(orden.estado_pago) }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-4 sm:px-5">
                                    <div class="flex items-center gap-1.5">
                                        <!-- Ver -->
                                        <button
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateTo(
                                                    `/recepcion/ordenes/${orden.id}`,
                                                    `show:${orden.id}`,
                                                )
                                            "
                                            title="Ver orden"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `show:${orden.id}`
                                                "
                                                class="h-3.5 w-3.5 animate-spin"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                class="h-3.5 w-3.5"
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
                                        </button>

                                        <!-- Editar — solo si está pendiente -->
                                        <button
                                            v-if="orden.estado === 'pendiente'"
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateTo(
                                                    `/recepcion/ordenes/${orden.id}/edit`,
                                                    `edit:${orden.id}`,
                                                )
                                            "
                                            title="Editar orden"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `edit:${orden.id}`
                                                "
                                                class="h-3.5 w-3.5 animate-spin"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                class="h-3.5 w-3.5"
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
                                        </button>

                                        <!-- Marcar pagado — solo si pago pendiente -->
                                        <button
                                            v-if="
                                                [
                                                    'pendiente',
                                                    'parcial',
                                                ].includes(orden.estado_pago) &&
                                                orden.estado !== 'anulado'
                                            "
                                            type="button"
                                            @click="
                                                router.visit(
                                                    `/recepcion/ordenes/${orden.id}/cobrar`,
                                                )
                                            "
                                            title="Registrar cobro"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-emerald-200 text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-400 dark:hover:bg-emerald-500/10"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </button>

                                        <!-- Eliminar — solo pendiente o anulado -->
                                        <button
                                            v-if="
                                                [
                                                    'pendiente',
                                                    'anulado',
                                                ].includes(orden.estado)
                                            "
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="deleteOrden(orden)"
                                            title="Eliminar orden"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `delete:${orden.id}`
                                                "
                                                class="h-3.5 w-3.5 animate-spin"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                class="h-3.5 w-3.5"
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
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <!-- Estado vacío -->
                <div
                    v-if="!displayedOrdenes.length"
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
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos órdenes
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o crea una nueva orden de
                            trabajo.
                        </p>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.ordenes.from }} a
                        {{ props.ordenes.to }} de
                        {{ props.ordenes.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="props.ordenes.prev_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.ordenes.prev_page_url!,
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
                            Página {{ props.ordenes.current_page }} de
                            {{ props.ordenes.last_page }}
                        </span>
                        <button
                            v-if="props.ordenes.next_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.ordenes.next_page_url!,
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

interface Paciente {
    id: number;
    nombres: string;
    apellidos: string;
    cedula: string;
    tipo_documento: string;
}

interface Medico {
    id: number;
    nombre_completo: string;
}

interface Orden {
    id: number;
    codigo: string;
    estado: string;
    estado_pago: string;
    total: string;
    items_count: number;
    paciente?: Paciente;
    medico?: Medico;
    created_at: string;
}

interface Props {
    ordenes: {
        data: Orden[];
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
        estado?: string;
        estado_pago?: string;
        fecha?: string;
    };
}

const props = defineProps<Props>();

const page = usePage<{
    flash?: {
        success?: string | null;
        warning?: string | null;
        error?: string | null;
    };
}>();

const flash = computed(() => page.props.flash ?? {});

const displayedOrdenes = ref<Orden[]>([]);
const search = ref(props.filters.search ?? '');
const selectedEstado = ref(props.filters.estado ?? '');
const selectedEstadoPago = ref(props.filters.estado_pago ?? '');
const selectedFecha = ref(props.filters.fecha ?? '');
const activeAction = ref<string | null>(null);
let searchDebounce: number | null = null;

const DEBOUNCE_MS = 320;

const estadoOpciones = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'en_proceso', label: 'En proceso' },
    { value: 'listo', label: 'Listo' },
    { value: 'entregado', label: 'Entregado' },
    { value: 'anulado', label: 'Anulado' },
];

watch(
    () => props.ordenes.data,
    (data) => {
        displayedOrdenes.value = [...data];
    },
    { immediate: true },
);

const hayFiltrosActivos = computed(
    () =>
        search.value.trim() !== '' ||
        selectedEstado.value !== '' ||
        selectedEstadoPago.value !== '' ||
        selectedFecha.value !== '',
);

const activeFilterChips = computed(() => {
    const chips: string[] = [];
    if (search.value.trim()) chips.push(`Búsqueda: ${search.value.trim()}`);
    if (selectedEstado.value)
        chips.push(`Estado: ${estadoLabel(selectedEstado.value)}`);
    if (selectedEstadoPago.value)
        chips.push(`Pago: ${estadoPagoLabel(selectedEstadoPago.value)}`);
    if (selectedFecha.value) chips.push(`Fecha: ${selectedFecha.value}`);
    return chips;
});

function submitFilters() {
    if (activeAction.value) return;
    const params: Record<string, string> = {};
    const trimmed = search.value.trim();
    if (trimmed.length > 1) params.search = trimmed;
    if (selectedEstado.value) params.estado = selectedEstado.value;
    if (selectedEstadoPago.value) params.estado_pago = selectedEstadoPago.value;
    if (selectedFecha.value) params.fecha = selectedFecha.value;

    router.get('/recepcion/ordenes', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

watch(search, (val) => {
    clearDebounce();
    if (val.trim().length > 1 || val.trim() === '') {
        searchDebounce = window.setTimeout(submitFilters, DEBOUNCE_MS);
    }
});

watch(selectedEstado, () => {
    clearDebounce();
    submitFilters();
});
watch(selectedEstadoPago, () => {
    clearDebounce();
    submitFilters();
});
watch(selectedFecha, () => {
    clearDebounce();
    submitFilters();
});

onBeforeUnmount(clearDebounce);

function limpiarFiltros() {
    search.value = '';
    selectedEstado.value = '';
    selectedEstadoPago.value = '';
    selectedFecha.value = '';
}

function clearDebounce() {
    if (searchDebounce !== null) {
        window.clearTimeout(searchDebounce);
        searchDebounce = null;
    }
}

// Labels y clases
function estadoLabel(estado: string) {
    return (
        {
            pendiente: 'Pendiente',
            en_proceso: 'En proceso',
            listo: 'Listo',
            entregado: 'Entregado',
            anulado: 'Anulado',
        }[estado] ?? estado
    );
}

function estadoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:
            'rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        en_proceso:
            'rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        listo: 'rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
        entregado:
            'rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        anulado:
            'rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    };
    return map[estado] ?? map.pendiente;
}

function estadoPagoLabel(estado: string) {
    return (
        { pendiente: 'Pendiente', pagado: 'Pagado', exonerado: 'Exonerado' }[
            estado
        ] ?? estado
    );
}

function estadoPagoClass(estado: string) {
    const map: Record<string, string> = {
        pendiente:
            'rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        pagado: 'rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        exonerado:
            'rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    };
    return map[estado] ?? map.pendiente;
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('es-VE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

// Summary cards
const summaryCards = computed(() => [
    {
        title: 'Total órdenes',
        value: String(props.ordenes.total),
        caption: 'En el registro completo',
        icon: icon(
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ),
        iconClass: 'bg-white/15 text-amber-300',
        delay: 0,
    },
    {
        title: 'Pendientes',
        value: String(
            props.ordenes.data.filter((o) => o.estado === 'pendiente').length,
        ),
        caption: 'Sin procesar en esta página',
        icon: icon('M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-orange-300',
        delay: 90,
    },
    {
        title: 'Listos',
        value: String(
            props.ordenes.data.filter((o) => o.estado === 'listo').length,
        ),
        caption: 'Esperando entrega en esta página',
        icon: icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-teal-300',
        delay: 180,
    },
    {
        title: 'Filtros activos',
        value: String(activeFilterChips.value.length),
        caption: activeFilterChips.value.length
            ? activeFilterChips.value.join(' · ')
            : 'Sin restricciones activas',
        icon: icon('M3 4.5h18M6.75 12h10.5M10.5 19.5h3'),
        iconClass: 'bg-white/15 text-indigo-200',
        delay: 270,
    },
]);

const tableHeadline = computed(() =>
    activeFilterChips.value.length
        ? `Vista filtrada por ${activeFilterChips.value.join(' · ')}`
        : 'Todas las órdenes de trabajo registradas.',
);

// Navegación
function navigateToCreate() {
    navigateTo('/recepcion/ordenes/create', 'create');
}

function navigateTo(url: string, action: string) {
    if (activeAction.value) return;
    activeAction.value = action;
    router.visit(url, {
        preserveScroll: true,
        onFinish: () => {
            activeAction.value = null;
        },
    });
}

function marcarPagado(orden: Orden) {
    if (activeAction.value) return;
    if (!confirm(`¿Marcar la orden ${orden.codigo} como pagada?`)) return;
    activeAction.value = `pago:${orden.id}`;
    router.patch(
        `/recepcion/ordenes/${orden.id}/marcar-pagado`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                activeAction.value = null;
            },
        },
    );
}

function deleteOrden(orden: Orden) {
    if (activeAction.value) return;
    if (!confirm(`¿Eliminar la orden ${orden.codigo}?`)) return;
    activeAction.value = `delete:${orden.id}`;
    router.delete(`/recepcion/ordenes/${orden.id}`, {
        onFinish: () => {
            activeAction.value = null;
        },
    });
}

function icon(path: string) {
    return defineComponent({
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
</script>

<style scoped>
.orden-enter-from,
.orden-appear-from {
    opacity: 0;
    transform: translateY(16px);
}
.orden-enter-to,
.orden-appear-to {
    opacity: 1;
    transform: translateY(0);
}
.orden-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.orden-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.orden-enter-active,
.orden-appear-active,
.orden-leave-active {
    transition:
        opacity 500ms ease,
        transform 500ms cubic-bezier(0.22, 1, 0.36, 1);
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
