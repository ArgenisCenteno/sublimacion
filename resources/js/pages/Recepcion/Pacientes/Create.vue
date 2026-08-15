<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Registrar Paciente'" />

        <div class="space-y-6">
            <section
                class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20"
            >
                <div
                    class="bg-[radial-gradient(circle_at_top_left,_rgba(52,211,153,0.28),_transparent_32%),linear-gradient(135deg,_#042f2e_0%,_#0f172a_58%,_#111827_100%)] p-6 sm:p-8"
                >
                    <div
                        class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <div class="max-w-3xl">
                            <span
                                class="animate-badge-float inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/80 uppercase backdrop-blur-sm"
                            >
                                Recepción
                            </span>
                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Registrar nuevo paciente
                            </h1>
                        </div>
                        <button
                            type="button"
                            :disabled="isNavigatingBack || form.processing"
                            @click="goToIndex"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{
                                isNavigatingBack
                                    ? 'Cargando...'
                                    : 'Volver al listado'
                            }}
                        </button>
                    </div>

                    <transition-group
                        tag="div"
                        class="mt-6 grid gap-3 md:grid-cols-3"
                        name="hero-card"
                        appear
                    >
                        <article
                            v-for="card in heroCards"
                            :key="card.title"
                            :style="{ transitionDelay: `${card.delay}ms` }"
                            class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1"
                        >
                            <p
                                class="text-[11px] font-semibold tracking-[0.2em] text-white/55 uppercase"
                            >
                                {{ card.title }}
                            </p>
                            <p class="mt-3 text-2xl font-semibold">
                                {{ card.value }}
                            </p>
                            <p class="mt-2 text-sm text-white/70">
                                {{ card.caption }}
                            </p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <transition-group
                v-if="flash.success || flash.error"
                tag="div"
                class="space-y-3"
                name="flash-stack"
                appear
            >
                <Alert
                    key="success"
                    v-if="flash.success"
                    variant="success"
                    title="Listo"
                    :message="flash.success"
                />
                <Alert
                    key="error"
                    v-if="flash.error"
                    variant="error"
                    title="Error"
                    :message="flash.error"
                />
            </transition-group>

            <div
                class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]"
            >
                <!-- Formulario principal -->
                <section
                    class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
                >
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Datos personales -->
                        <div>
                            <h2
                                class="mb-4 text-base font-semibold text-slate-900 dark:text-white"
                            >
                                Datos personales
                            </h2>
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Tipo + Cédula -->
                                <div class="field-reveal md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Cédula
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <!-- DESPUÉS -->
                                    <div class="flex gap-2">
                                        <select
                                            v-model="form.tipo_documento"
                                            :class="[
                                                'h-12 w-20 min-w-[5rem] flex-shrink-0 rounded-2xl border bg-transparent px-3 py-2.5 text-sm text-slate-800 shadow-theme-xs transition duration-300 focus:-translate-y-0.5 focus:ring-4 focus:outline-hidden dark:text-white/90',
                                                errorClass(
                                                    form.errors.tipo_documento,
                                                ),
                                            ]"
                                        >
                                            <option value="V">V</option>
                                            <option value="E">E</option>
                                            <option value="P">P</option>
                                            <option value="J">J</option>
                                        </select>
                                        <input
                                            v-model="form.cedula"
                                            type="text"
                                            maxlength="10"
                                            inputmode="numeric"
                                            placeholder="12345678"
                                            :class="[inputBaseClass, 'flex-1', errorClass(form.errors.cedula)]"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.cedula"
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ form.errors.cedula }}
                                    </p>
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 60ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Nombres
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.nombres"
                                        @input="
                                            form.nombres =
                                                form.nombres.toUpperCase()
                                        "
                                        type="text"
                                        placeholder="José Luis"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.nombres),
                                        ]"
                                    />
                                    <p
                                        v-if="form.errors.nombres"
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ form.errors.nombres }}
                                    </p>
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 90ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Apellidos
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.apellidos"
                                        @input="
                                            form.apellidos =
                                                form.apellidos.toUpperCase()
                                        "
                                        type="text"
                                        placeholder="González Pérez"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.apellidos),
                                        ]"
                                    />
                                    <p
                                        v-if="form.errors.apellidos"
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ form.errors.apellidos }}
                                    </p>
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 120ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Fecha de nacimiento
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.fecha_nacimiento"
                                        type="date"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(
                                                form.errors.fecha_nacimiento,
                                            ),
                                        ]"
                                    />
                                    <p
                                        v-if="form.errors.fecha_nacimiento"
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ form.errors.fecha_nacimiento }}
                                    </p>
                                    <!-- Badge de menor en tiempo real -->
                                    <transition name="badge-pop">
                                        <span
                                            v-if="esMenor"
                                            class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"
                                        >
                                            Menor de edad — se requieren datos
                                            del representante
                                        </span>
                                    </transition>
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 150ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Sexo
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="form.sexo"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.sexo),
                                        ]"
                                    >
                                        <option value="">Seleccionar</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    <p
                                        v-if="form.errors.sexo"
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ form.errors.sexo }}
                                    </p>
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 180ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Teléfono</label
                                    >
                                    <input
                                        v-model="form.telefono"
                                        maxlength="12"
                                        inputmode="numeric"
                                        type="text"
                                        placeholder="0412-1234567"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.telefono),
                                        ]"
                                    />
                                </div>

                                <div
                                    class="field-reveal"
                                    style="animation-delay: 210ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Correo electrónico</label
                                    >
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="paciente@correo.com"
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.email),
                                        ]"
                                    />
                                </div>

                                <div
                                    class="field-reveal md:col-span-2"
                                    style="animation-delay: 240ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Dirección</label
                                    >
                                    <input
                                        v-model="form.direccion"
                                        type="text"
                                        placeholder="Av. Principal, Casa 5..."
                                        @input="
                                            form.direccion =
                                                form.direccion.toUpperCase()
                                        "
                                        :class="[
                                            inputBaseClass,
                                            errorClass(form.errors.direccion),
                                        ]"
                                    />
                                </div>

                                <div
                                    class="field-reveal md:col-span-2"
                                    style="animation-delay: 270ms"
                                >
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >Médico referente</label
                                    >
                                    <input
                                        v-model="form.medico_referente"
                                        type="text"
                                        placeholder="Dr. Martínez"
                                        @input="
                                            form.medico_referente =
                                                form.medico_referente.toUpperCase()
                                        "
                                        :class="[
                                            inputBaseClass,
                                            errorClass(
                                                form.errors.medico_referente,
                                            ),
                                        ]"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Representante (solo si es menor) -->
                        <transition name="rep-block">
                            <div
                                v-if="esMenor"
                                class="rounded-[20px] border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/25 dark:bg-amber-500/10"
                            >
                                <h2
                                    class="mb-4 text-base font-semibold text-amber-900 dark:text-amber-200"
                                >
                                    Representante o tutor
                                </h2>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300"
                                            >Nombres
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.rep_nombres"
                                            type="text"
                                            placeholder="María"
                                                 @input="form.rep_nombres = (form.rep_nombres || '').toUpperCase()"

                                            :class="[
                                                inputBaseClass,
                                                errorClass(
                                                    form.errors.rep_nombres,
                                                ),
                                            ]"
                                        />
                                        <p
                                            v-if="form.errors.rep_nombres"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.rep_nombres }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300"
                                            >Apellidos
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.rep_apellidos"
                                            type="text"
                                                @input="form.rep_apellidos = (form.rep_apellidos || '').toUpperCase()"

                                            placeholder="López"
                                            :class="[
                                                inputBaseClass,
                                                errorClass(
                                                    form.errors.rep_apellidos,
                                                ),
                                            ]"
                                        />
                                        <p
                                            v-if="form.errors.rep_apellidos"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.rep_apellidos }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300"
                                            >Cédula
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.rep_cedula"
                                            maxlength="10"
                                            inputmode="numeric"
                                            type="text"
                                            placeholder="V-10234567"
                                            :class="[
                                                inputBaseClass,
                                                errorClass(
                                                    form.errors.rep_cedula,
                                                ),
                                            ]"
                                        />
                                        <p
                                            v-if="form.errors.rep_cedula"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.rep_cedula }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300"
                                            >Teléfono
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.rep_telefono"
                                            type="text"
                                            placeholder="0414-9876543"
                                            :class="[
                                                inputBaseClass,
                                                errorClass(
                                                    form.errors.rep_telefono,
                                                ),
                                            ]"
                                        />
                                        <p
                                            v-if="form.errors.rep_telefono"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.rep_telefono }}
                                        </p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-amber-800 dark:text-amber-300"
                                            >Parentesco
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="form.rep_parentesco"
                                            :class="[
                                                inputBaseClass,
                                                errorClass(
                                                    form.errors.rep_parentesco,
                                                ),
                                            ]"
                                        >
                                            <option value="">
                                                Seleccionar
                                            </option>
                                            <option value="madre">Madre</option>
                                            <option value="padre">Padre</option>
                                            <option value="abuelo">
                                                Abuelo
                                            </option>
                                            <option value="abuela">
                                                Abuela
                                            </option>
                                            <option value="tutor_legal">
                                                Tutor legal
                                            </option>
                                            <option value="otro">Otro</option>
                                        </select>
                                        <p
                                            v-if="form.errors.rep_parentesco"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.rep_parentesco }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <!-- Submit -->
                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="form.processing || isNavigatingBack"
                                @click="goToIndex"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing || isNavigatingBack"
                                class="inline-flex items-center gap-2 rounded-2xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-teal-600 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
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
                                {{
                                    form.processing
                                        ? 'Guardando...'
                                        : 'Registrar paciente'
                                }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Sidebar -->
                <aside class="space-y-6">
                    <section
                        class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                        style="animation-delay: 90ms"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900 dark:text-white"
                        >
                            Resumen del registro
                        </h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">
                                    Nombre completo
                                </dt>
                                <dd
                                    class="font-medium text-slate-800 dark:text-white"
                                >
                                    {{
                                        form.nombres || form.apellidos
                                            ? `${form.apellidos}, ${form.nombres}`
                                            : '—'
                                    }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">
                                    Cédula
                                </dt>
                                <dd
                                    class="font-mono font-medium text-slate-800 dark:text-white"
                                >
                                    {{
                                        form.cedula
                                            ? `${form.tipo_documento}-${form.cedula}`
                                            : '—'
                                    }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500 dark:text-slate-400">
                                    Edad
                                </dt>
                                <dd
                                    class="font-medium text-slate-800 dark:text-white"
                                >
                                    {{
                                        edadCalculada !== null
                                            ? `${edadCalculada} años`
                                            : '—'
                                    }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="dark:text-slate:400 text-slate-500">
                                    Clasificación
                                </dt>
                                <dd>
                                    <span
                                        v-if="esMenor"
                                        class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"
                                        >Menor</span
                                    >
                                    <span
                                        v-else-if="edadCalculada !== null"
                                        class="rounded-full bg-teal-100 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/20 dark:text-teal-300"
                                        >Adulto</span
                                    >
                                    <span v-else class="text-slate-400">—</span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section
                        class="panel-float rounded-[28px] border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/20 dark:bg-amber-500/10"
                        style="animation-delay: 180ms"
                    >
                        <h2
                            class="text-base font-semibold text-amber-900 dark:text-amber-200"
                        >
                            Notas
                        </h2>
                        <ul
                            class="mt-4 space-y-2 text-sm leading-6 text-amber-800 dark:text-amber-100/80"
                        >
                            <li>La cédula debe ser única en el sistema.</li>
                            <li>
                                Si el paciente es menor de 18 años, los datos
                                del representante son obligatorios.
                            </li>
                            <li>
                                Al guardar, serás redirigido a crear la orden de
                                trabajo.
                            </li>
                        </ul>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Alert from '@/components/ui/Alert.vue';

const page = usePage<{
    flash?: { success?: string | null; error?: string | null };
}>();
const flash = computed(() => page.props.flash ?? {});
const isNavigatingBack = ref(false);

const form = useForm({
    tipo_documento: 'V',
    cedula: '',
    nombres: '',
    apellidos: '',
    sexo: '',
    fecha_nacimiento: '',
    telefono: '',
    email: '',
    direccion: '',
    medico_referente: '',
    rep_nombres: '',
    rep_apellidos: '',
    rep_cedula: '',
    rep_telefono: '',
    rep_parentesco: '',
});

const heroCards = [
    {
        title: 'Paso 1',
        value: 'Paciente',
        caption: 'Completa los datos personales del paciente.',
        delay: 0,
    },
    {
        title: 'Paso 2',
        value: 'Representante',
        caption: 'Requerido automáticamente si es menor de edad.',
        delay: 90,
    },
    {
        title: 'Paso 3',
        value: 'Orden',
        caption: 'Al guardar se creará la orden de trabajo.',
        delay: 180,
    },
];

const edadCalculada = computed<number | null>(() => {
    if (!form.fecha_nacimiento) return null;
    const hoy = new Date();
    const nac = new Date(form.fecha_nacimiento);
    const edad = hoy.getFullYear() - nac.getFullYear();
    const cumplioEsteAnio =
        hoy.getMonth() > nac.getMonth() ||
        (hoy.getMonth() === nac.getMonth() && hoy.getDate() >= nac.getDate());
    return cumplioEsteAnio ? edad : edad - 1;
});

const esMenor = computed(
    () => edadCalculada.value !== null && edadCalculada.value < 18,
);

const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function errorClass(error?: string) {
    return error
        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
        : 'border-slate-300 focus:border-teal-300 focus:ring-teal-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-teal-800';
}

function goToIndex() {
    if (isNavigatingBack.value || form.processing) return;
    isNavigatingBack.value = true;
    router.visit('/recepcion/pacientes', {
        onFinish: () => {
            isNavigatingBack.value = false;
        },
    });
}

function submit() {
    if (form.processing || isNavigatingBack.value) return;
    form.post('/recepcion/pacientes');
}
</script>

<style scoped>
.panel-float {
    animation: panelFloat 720ms cubic-bezier(0.22, 1, 0.36, 1) both;
}
.field-reveal {
    animation: fieldReveal 520ms ease-out both;
}
.animate-badge-float {
    animation: badgeFloat 5.5s ease-in-out infinite;
}

.rep-block-enter-active {
    transition:
        opacity 400ms ease,
        transform 400ms cubic-bezier(0.22, 1, 0.36, 1),
        max-height 400ms ease;
}
.rep-block-leave-active {
    transition:
        opacity 280ms ease,
        transform 280ms ease,
        max-height 280ms ease;
}
.rep-block-enter-from,
.rep-block-leave-to {
    opacity: 0;
    transform: translateY(12px);
}

.badge-pop-enter-active {
    transition:
        opacity 300ms ease,
        transform 300ms cubic-bezier(0.22, 1, 0.36, 1);
}
.badge-pop-enter-from {
    opacity: 0;
    transform: scale(0.85);
}

.hero-card-enter-active,
.hero-card-appear-active {
    transition:
        opacity 520ms ease,
        transform 520ms cubic-bezier(0.22, 1, 0.36, 1);
}
.hero-card-enter-from,
.hero-card-appear-from {
    opacity: 0;
    transform: translateY(24px) scale(0.98);
}

.flash-stack-enter-active,
.flash-stack-appear-active,
.flash-stack-leave-active {
    transition:
        opacity 340ms ease,
        transform 340ms ease;
}
.flash-stack-enter-from,
.flash-stack-appear-from,
.flash-stack-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

@keyframes panelFloat {
    from {
        opacity: 0;
        transform: translateY(28px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes fieldReveal {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes badgeFloat {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-2px);
    }
}
</style>
