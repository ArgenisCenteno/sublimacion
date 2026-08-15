<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Exámenes'" />

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
                                Catálogo
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Exámenes de laboratorio
                            </h1>
                        </div>

                        <button
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="navigateToCreate"
                            class="animate-pop inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-lg shadow-indigo-950/10 transition hover:bg-indigo-50"
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
                                    : 'Nuevo Examen'
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
                                class="grid gap-4 xl:grid-cols-[1fr_200px_200px_auto] xl:items-end"
                            >
                                <!-- Búsqueda -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Buscar</label
                                    >
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Nombre, código, categoría..."
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

                                <!-- Filtro categoría -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Categoría</label
                                    >
                                    <select
                                        v-model="selectedCategoria"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todas
                                        </option>
                                        <option
                                            v-for="cat in props.categorias"
                                            :key="cat"
                                            :value="cat"
                                            class="text-slate-900"
                                        >
                                            {{ cat }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filtro activo -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-white/85"
                                        >Estado</label
                                    >
                                    <select
                                        v-model="selectedActivo"
                                        class="h-12 w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white shadow-theme-xs focus:border-white/30 focus:ring-4 focus:ring-white/10 focus:outline-hidden"
                                    >
                                        <option value="" class="text-slate-900">
                                            Todos
                                        </option>
                                        <option
                                            value="true"
                                            class="text-slate-900"
                                        >
                                            Activos
                                        </option>
                                        <option
                                            value="false"
                                            class="text-slate-900"
                                        >
                                            Inactivos
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
                                Catálogo de exámenes
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
                            {{ props.examenes.total }} registros
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
                                        Examen
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Categoría
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p
                                        class="text-theme-xs font-medium tracking-[0.18em] text-slate-400 uppercase dark:text-slate-500"
                                    >
                                        Muestra
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
                                        Estado
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
                            name="examen"
                            appear
                            class="divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="examen in displayedExamenes"
                                :key="examen.id"
                                class="group border-t border-slate-100 transition hover:bg-[linear-gradient(90deg,_rgba(99,102,241,0.04),_rgba(139,92,246,0.03))] dark:border-slate-800 dark:hover:bg-slate-800/40"
                            >
                                <!-- Examen -->
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#ede9fe_0%,_#ddd6fe_100%)] text-indigo-700 shadow-sm dark:bg-[linear-gradient(135deg,_rgba(99,102,241,0.18)_0%,_rgba(139,92,246,0.18)_100%)] dark:text-indigo-300"
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
                                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                            >
                                                {{ examen.nombre }}
                                            </span>
                                            <span
                                                class="font-mono text-theme-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ examen.codigo }}
                                                <span
                                                    v-if="examen.nombre_corto"
                                                >
                                                    ·
                                                    {{
                                                        examen.nombre_corto
                                                    }}</span
                                                >
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Categoría -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span
                                        class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"
                                    >
                                        {{ examen.categoria }}
                                    </span>
                                </td>

                                <!-- Muestra -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-theme-sm text-slate-700 dark:text-slate-200"
                                            >{{ examen.muestra }}</span
                                        >
                                        <span
                                            v-if="examen.tubo"
                                            class="text-theme-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Tubo {{ examen.tubo }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Precio -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <span
                                        class="text-theme-sm font-semibold text-slate-800 dark:text-white/90"
                                    >
                                        ${{ Number(examen.precio).toFixed(2) }}
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                                    <button
                                        type="button"
                                        :disabled="Boolean(activeAction)"
                                        @click="toggleActivo(examen)"
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-xs font-semibold transition hover:opacity-80',
                                            examen.activo
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                                        ]"
                                    >
                                        {{
                                            activeAction ===
                                            `toggle:${examen.id}`
                                                ? '...'
                                                : examen.activo
                                                  ? 'Activo'
                                                  : 'Inactivo'
                                        }}
                                    </button>
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
                                                    `/catalogo/examenes/${examen.id}`,
                                                    `show:${examen.id}`,
                                                )
                                            "
                                            title="Ver examen"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `show:${examen.id}`
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
                                                    `/catalogo/examenes/${examen.id}/edit`,
                                                    `edit:${examen.id}`,
                                                )
                                            "
                                            title="Editar examen"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `edit:${examen.id}`
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
                                            @click="deleteExamen(examen)"
                                            title="Eliminar examen"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                        >
                                            <svg
                                                v-if="
                                                    activeAction ===
                                                    `delete:${examen.id}`
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
                    v-if="!displayedExamenes.length"
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
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            No encontramos exámenes
                        </h3>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Ajusta los filtros o registra un nuevo examen.
                        </p>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ props.examenes.from }} a
                        {{ props.examenes.to }} de
                        {{ props.examenes.total }} resultados
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="props.examenes.prev_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.examenes.prev_page_url!,
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
                            Página {{ props.examenes.current_page }} de
                            {{ props.examenes.last_page }}
                        </span>
                        <button
                            v-if="props.examenes.next_page_url"
                            type="button"
                            :disabled="Boolean(activeAction)"
                            @click="
                                navigateTo(
                                    props.examenes.next_page_url!,
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

interface Examen {
    id: number;
    codigo: string;
    nombre: string;
    nombre_corto?: string;
    categoria: string;
    muestra: string;
    tubo?: string;
    tiempo_resultado: number;
    unidad?: string;
    precio: string;
    activo: boolean;
    valores_referencia_count: number;
}

interface Props {
    examenes: {
        data: Examen[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    categorias: string[];
    filters: {
        search?: string;
        categoria?: string;
        activo?: string;
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

const displayedExamenes = ref<Examen[]>([]);
const search = ref(props.filters.search ?? '');
const selectedCategoria = ref(props.filters.categoria ?? '');
const selectedActivo = ref(props.filters.activo ?? '');
const activeAction = ref<string | null>(null);
let searchDebounce: number | null = null;

const DEBOUNCE_MS = 320;

watch(
    () => props.examenes.data,
    (data) => {
        displayedExamenes.value = [...data];
    },
    { immediate: true },
);

const hayFiltrosActivos = computed(
    () =>
        search.value.trim() !== '' ||
        selectedCategoria.value !== '' ||
        selectedActivo.value !== '',
);

const activeFilterChips = computed(() => {
    const chips: string[] = [];
    if (search.value.trim()) chips.push(`Búsqueda: ${search.value.trim()}`);
    if (selectedCategoria.value)
        chips.push(`Categoría: ${selectedCategoria.value}`);
    if (selectedActivo.value)
        chips.push(selectedActivo.value === 'true' ? 'Activos' : 'Inactivos');
    return chips;
});

function submitFilters() {
    if (activeAction.value) return;

    const params: Record<string, string> = {};
    const trimmed = search.value.trim();
    if (trimmed.length > 1) params.search = trimmed;
    if (selectedCategoria.value) params.categoria = selectedCategoria.value;
    if (selectedActivo.value) params.activo = selectedActivo.value;

    router.get('/catalogo/examenes', params, {
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

watch(selectedCategoria, () => {
    clearDebounce();
    submitFilters();
});
watch(selectedActivo, () => {
    clearDebounce();
    submitFilters();
});

onBeforeUnmount(clearDebounce);

function limpiarFiltros() {
    search.value = '';
    selectedCategoria.value = '';
    selectedActivo.value = '';
}

function clearDebounce() {
    if (searchDebounce !== null) {
        window.clearTimeout(searchDebounce);
        searchDebounce = null;
    }
}

const summaryCards = computed(() => [
    {
        title: 'Total exámenes',
        value: String(props.examenes.total),
        caption: 'En el catálogo completo',
        icon: icon(
            'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        ),
        iconClass: 'bg-white/15 text-indigo-300',
        delay: 0,
    },
    {
        title: 'Activos',
        value: String(props.examenes.data.filter((e) => e.activo).length),
        caption: 'Visibles en esta página',
        icon: icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'),
        iconClass: 'bg-white/15 text-emerald-300',
        delay: 90,
    },
    {
        title: 'Categorías',
        value: String(props.categorias.length),
        caption: 'Tipos de examen disponibles',
        icon: icon(
            'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
        ),
        iconClass: 'bg-white/15 text-violet-300',
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
        : 'Todos los exámenes registrados en el catálogo.',
);

function navigateToCreate() {
    navigateTo('/catalogo/examenes/create', 'create');
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

function toggleActivo(examen: Examen) {
    if (activeAction.value) return;
    activeAction.value = `toggle:${examen.id}`;
    router.patch(
        `/catalogo/examenes/${examen.id}/toggle-activo`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                activeAction.value = null;
            },
        },
    );
}

function deleteExamen(examen: Examen) {
    if (activeAction.value) return;
    if (!confirm(`¿Eliminar el examen "${examen.nombre}"?`)) return;

    activeAction.value = `delete:${examen.id}`;
    router.delete(`/catalogo/examenes/${examen.id}`, {
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
.examen-enter-from,
.examen-appear-from {
    opacity: 0;
    transform: translateY(16px);
}
.examen-enter-to,
.examen-appear-to {
    opacity: 1;
    transform: translateY(0);
}
.examen-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.examen-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.examen-enter-active,
.examen-appear-active,
.examen-leave-active {
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
