<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Pacientes'" />

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

            <!-- Hero header -->
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
                                Registro de pacientes
                            </h1>
                        </div>

                        <button
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="navigateToCreate"
                            class="animate-pop inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-teal-700 shadow-lg shadow-teal-950/10 transition hover:bg-teal-50"
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
                                    : 'Nuevo Paciente'
                            }}
                        </button>
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
                                class="grid gap-4 xl:grid-cols-[1fr_220px_220px_auto] xl:items-end"
                            >
                                <!-- Búsqueda -->
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
                                            placeholder="Cédula, nombre, apellido, teléfono..."
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

                                <!-- Filtro sexo -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Sexo</label
                                    >
                                    <select
                                        v-model="selectedSexo"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos
                                        </option>
                                        <option
                                            value="M"
                                            class="text-slate-900"
                                        >
                                            Masculino
                                        </option>
                                        <option
                                            value="F"
                                            class="text-slate-900"
                                        >
                                            Femenino
                                        </option>
                                    </select>
                                </div>

                                <!-- Filtro menor/adulto -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Edad</label
                                    >
                                    <select
                                        v-model="selectedEsMenor"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos
                                        </option>
                                        <option
                                            value="true"
                                            class="text-slate-900"
                                        >
                                            Menores de edad
                                        </option>
                                        <option
                                            value="false"
                                            class="text-slate-900"
                                        >
                                            Adultos
                                        </option>
                                    </select>
                                </div>

                                <button
                                    v-if="hayFiltrosActivos"
                                    type="button"
                                    @click="limpiarFiltros"
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
                                Listado de pacientes
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
                            {{ props.pacientes.total }} registros
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
                                        Paciente
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Cédula
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Edad
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Contacto
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Representante
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
                            name="paciente"
                            appear
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="paciente in displayedPacientes"
                                :key="paciente.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(20,184,166,0.04),_rgba(14,165,233,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <!-- Paciente -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#ccfbf1_0%,_#bae6fd_100%)] text-teal-700 shadow-sm dark:bg-[linear-gradient(135deg,_rgba(20,184,166,0.18)_0%,_rgba(14,165,233,0.18)_100%)] dark:text-teal-300"
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
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                            >
                                                {{ paciente.apellidos }},
                                                {{ paciente.nombres }}
                                            </span>
                                            <span
                                                class="text-theme-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    paciente.sexo === 'M'
                                                        ? 'Masculino'
                                                        : 'Femenino'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cédula -->
                                <td class="px-5 py-4 sm:px-6">
                                    <span
                                        class="font-mono text-theme-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{ paciente.tipo_documento }}-{{
                                            paciente.cedula
                                        }}
                                    </span>
                                </td>

                                <!-- Edad -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-theme-sm text-slate-700 dark:text-slate-200"
                                        >
                                            {{ paciente.edad }} años
                                        </span>
                                        <span
                                            v-if="paciente.es_menor"
                                            class="inline-flex w-fit items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400"
                                        >
                                            Menor de edad
                                        </span>
                                    </div>
                                </td>

                                <!-- Contacto -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-theme-sm text-slate-700 dark:text-slate-200"
                                        >
                                            {{ paciente.telefono ?? '—' }}
                                        </span>
                                        <span
                                            v-if="paciente.email"
                                            class="text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ paciente.email }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Representante -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div
                                        v-if="paciente.es_menor"
                                        class="flex flex-col"
                                    >
                                        <span
                                            class="text-theme-sm text-slate-700 dark:text-slate-200"
                                        >
                                            {{ paciente.rep_nombres }}
                                            {{ paciente.rep_apellidos }}
                                        </span>
                                        <span
                                            class="text-theme-xs text-slate-500 capitalize dark:text-slate-400"
                                        >
                                            {{
                                                paciente.rep_parentesco?.replace(
                                                    '_',
                                                    ' ',
                                                )
                                            }}
                                            · {{ paciente.rep_telefono }}
                                        </span>
                                    </div>
                                    <span
                                        v-else
                                        class="text-theme-xs text-slate-400 dark:text-slate-500"
                                        >—</span
                                    >
                                </td>

                                <!-- Acciones -->
                                <!-- TH de acciones - sin cambios en el header -->

                                <!-- TD acciones - reemplaza el actual -->
                                <td class="px-4 py-4 sm:px-5">
                                    <div class="flex items-center gap-1.5">
                                        <!-- Nueva orden -->
                                        <button
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateTo(
                                                    `/recepcion/ordenes/create?paciente_id=${paciente.id}`,
                                                    `orden:${paciente.id}`,
                                                )
                                            "
                                            :title="'Nueva orden'"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-teal-200 bg-teal-50 text-teal-700 transition hover:bg-teal-100 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `orden:${paciente.id}`
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
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </button>

                                        <!-- Ver -->
                                        <button
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateTo(
                                                    `/recepcion/pacientes/${paciente.id}`,
                                                    `show:${paciente.id}`,
                                                )
                                            "
                                            :title="'Ver paciente'"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `show:${paciente.id}`
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

                                        <!-- Editar -->
                                        <button
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="
                                                navigateTo(
                                                    `/recepcion/pacientes/${paciente.id}/edit`,
                                                    `edit:${paciente.id}`,
                                                )
                                            "
                                            :title="'Editar paciente'"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `edit:${paciente.id}`
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

                                        <!-- Eliminar -->
                                        <button
                                            type="button"
                                            :disabled="Boolean(activeAction)"
                                            @click="deletePaciente(paciente)"
                                            :title="'Eliminar paciente'"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `delete:${paciente.id}`
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
                    v-if="!displayedPacientes.length"
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
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos pacientes
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o registra un nuevo paciente.
                        </p>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.pacientes.from }} a
                        {{ props.pacientes.to }} de
                        {{ props.pacientes.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="props.pacientes.prev_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.pacientes.prev_page_url!,
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
                            Página {{ props.pacientes.current_page }} de
                            {{ props.pacientes.last_page }}
                        </span>
                        <button
                            v-if="props.pacientes.next_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.pacientes.next_page_url!,
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
    tipo_documento: string;
    cedula: string;
    nombres: string;
    apellidos: string;
    sexo: 'M' | 'F';
    fecha_nacimiento: string;
    edad: number;
    es_menor: boolean;
    telefono?: string;
    email?: string;
    rep_nombres?: string;
    rep_apellidos?: string;
    rep_cedula?: string;
    rep_telefono?: string;
    rep_parentesco?: string;
}

interface Props {
    pacientes: {
        data: Paciente[];
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
        es_menor?: string;
        sexo?: string;
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

// Estado
const displayedPacientes = ref<Paciente[]>([]);
const search = ref(props.filters.search ?? '');
const selectedSexo = ref(props.filters.sexo ?? '');
const selectedEsMenor = ref(props.filters.es_menor ?? '');
const activeAction = ref<string | null>(null);
let searchDebounce: number | null = null;

const DEBOUNCE_MS = 320;

watch(
    () => props.pacientes.data,
    (data) => {
        displayedPacientes.value = [...data];
    },
    { immediate: true },
);

// Filtros
const hayFiltrosActivos = computed(
    () =>
        search.value.trim() !== '' ||
        selectedSexo.value !== '' ||
        selectedEsMenor.value !== '',
);

const activeFilterChips = computed(() => {
    const chips: string[] = [];
    if (search.value.trim()) chips.push(`Búsqueda: ${search.value.trim()}`);
    if (selectedSexo.value)
        chips.push(selectedSexo.value === 'M' ? 'Masculino' : 'Femenino');
    if (selectedEsMenor.value)
        chips.push(
            selectedEsMenor.value === 'true' ? 'Menores de edad' : 'Adultos',
        );
    return chips;
});

function submitFilters() {
    if (activeAction.value) return;

    const params: Record<string, string> = {};
    const trimmed = search.value.trim();
    if (trimmed.length > 1) params.search = trimmed;
    if (selectedSexo.value) params.sexo = selectedSexo.value;
    if (selectedEsMenor.value) params.es_menor = selectedEsMenor.value;

    router.get('/recepcion/pacientes', params, {
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

watch(selectedSexo, () => {
    clearDebounce();
    submitFilters();
});
watch(selectedEsMenor, () => {
    clearDebounce();
    submitFilters();
});

onBeforeUnmount(clearDebounce);

function limpiarFiltros() {
    search.value = '';
    selectedSexo.value = '';
    selectedEsMenor.value = '';
}

function clearDebounce() {
    if (searchDebounce !== null) {
        window.clearTimeout(searchDebounce);
        searchDebounce = null;
    }
}

// Summary cards
const summaryCards = computed(() => [
    {
        title: 'En esta página',
        value: String(props.pacientes.data.length),
        caption: 'Pacientes visibles según filtros',
        icon: icon(
            'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ),
        iconClass: 'bg-white/15 text-teal-300',
        delay: 0,
    },
    {
        title: 'Menores de edad',
        value: String(props.pacientes.data.filter((p) => p.es_menor).length),
        caption: 'Pacientes menores en esta página',
        icon: icon(
            'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        ),
        iconClass: 'bg-white/15 text-amber-300',
        delay: 90,
    },
    {
        title: 'Masculino',
        value: String(
            props.pacientes.data.filter((p) => p.sexo === 'M').length,
        ),
        caption: 'Pacientes masculinos en esta página',
        icon: icon(
            'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ),
        iconClass: 'bg-white/15 text-sky-300',
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
        : 'Todos los pacientes registrados en el sistema.',
);

// Navegación
function navigateToCreate() {
    navigateTo('/recepcion/pacientes/create', 'create');
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

function deletePaciente(paciente: Paciente) {
    if (activeAction.value) return;
    if (
        !confirm(
            `¿Eliminar al paciente ${paciente.nombres} ${paciente.apellidos}?`,
        )
    )
        return;

    activeAction.value = `delete:${paciente.id}`;
    router.delete(`/recepcion/pacientes/${paciente.id}`, {
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
.paciente-enter-from,
.paciente-appear-from {
    opacity: 0;
    transform: translateY(16px);
}
.paciente-enter-to,
.paciente-appear-to {
    opacity: 1;
    transform: translateY(0);
}
.paciente-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.paciente-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.paciente-enter-active,
.paciente-appear-active,
.paciente-leave-active {
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
