<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Mi perfil'" />

        <div class="space-y-6">
            <!-- ── Hero ──────────────────────────────────────────────────────── -->
            <section
                class="relative overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-[#0b1f1f] via-[#071516] to-[#050b0c] shadow-2xl shadow-slate-950/40"
            >
                <!-- Glow de marca -->
                <div class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute -top-24 -left-24 h-80 w-80 bg-[#42AAAB]/40 blur-[120px]"
                    ></div>

                    <div
                        class="absolute -right-24 -bottom-24 h-80 w-80 bg-[#358C8D]/40 blur-[120px]"
                    ></div>

                    <div
                        class="absolute top-1/2 left-1/2 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 bg-[#42AAAB]/10 blur-[140px]"
                    ></div>
                </div>

                <div class="relative p-6 sm:p-8">
                    <div
                        class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <!-- TEXTO -->
                        <div class="max-w-3xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold tracking-[0.18em] text-white/70 uppercase backdrop-blur-md"
                            >
                                Cuenta personal
                            </span>

                            <h1
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Hola, {{ user.name.split(' ')[0] }}
                            </h1>

                            <p class="mt-2 text-sm text-white/60">
                                Gestiona tu información de acceso y datos
                                personales.
                            </p>
                        </div>

                        <!-- AVATAR -->
                        <div class="flex-shrink-0">
                            <div
                                class="group relative h-20 w-20 cursor-pointer"
                                @click="avatarInput?.click()"
                            >
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    class="h-20 w-20 rounded-3xl border border-white/20 object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-20 w-20 items-center justify-center rounded-3xl border border-white/15 bg-white/5 text-3xl font-bold text-white backdrop-blur-md"
                                >
                                    {{ initials }}
                                </div>

                                <div
                                    class="absolute inset-0 flex items-center justify-center rounded-3xl bg-black/40 opacity-0 transition group-hover:opacity-100"
                                >
                                    <CameraIcon class="h-6 w-6 text-white" />
                                </div>

                                <div
                                    v-if="avatarUploading"
                                    class="absolute inset-0 flex items-center justify-center rounded-3xl bg-black/60"
                                >
                                    <div
                                        class="h-6 w-6 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                    />
                                </div>
                            </div>

                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onAvatarChange"
                            />
                        </div>
                    </div>

                    <!-- CARDS -->
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
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 text-white backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p
                                class="text-[11px] font-semibold tracking-[0.2em] text-white/50 uppercase"
                            >
                                {{ card.title }}
                            </p>

                            <p class="mt-3 truncate text-xl font-semibold">
                                {{ card.value }}
                            </p>

                            <p class="mt-2 text-sm text-white/60">
                                {{ card.caption }}
                            </p>
                        </article>
                    </transition-group>
                </div>
            </section>

            <!-- ── Formulario principal ───────────────────────────────────────── -->
            <transition appear name="panel-fade">
                <div class="panel-float grid gap-6 lg:grid-cols-[1fr_1fr]">
                    <!-- Datos personales -->
                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300"
                            >
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Datos personales
                                </h2>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Nombre y dirección de correo
                                </p>
                            </div>
                        </div>

                        <form @submit.prevent="submitProfile" class="space-y-5">
                            <!-- Nombre -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nombre completo
                                </label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    placeholder="Tu nombre"
                                    :class="inputClass(profileForm.errors.name)"
                                />
                                <p
                                    v-if="profileForm.errors.name"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ profileForm.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Correo electrónico
                                </label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="tu@correo.com"
                                    :class="
                                        inputClass(profileForm.errors.email)
                                    "
                                />
                                <p
                                    v-if="profileForm.errors.email"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>

                            <!-- Telefono -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Teléfono
                                </label>
                                <input
                                    v-model="profileForm.phone"
                                    type="text"
                                    :class="inputClass(profileForm.errors.phone)"
                                />
                                <p
                                    v-if="profileForm.errors.phone"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ profileForm.errors.phone }}
                                </p>
                            </div>

                            <!-- Cedula/RIF -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Cédula/RIF
                                </label>
                                <input
                                    v-model="profileForm.cedula_rif"
                                    type="text"
                                    placeholder="V-12345678"
                                    :class="inputClass(profileForm.errors.cedula_rif)"
                                />
                                <p
                                    v-if="profileForm.errors.cedula_rif"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ profileForm.errors.cedula_rif }}
                                </p>
                            </div>

                            <!-- Feedback -->
                            <transition name="fade">
                                <div
                                    v-if="profileSaved"
                                    class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                                >
                                    <CheckIcon class="h-4 w-4 flex-shrink-0" />
                                    Datos actualizados correctamente.
                                </div>
                            </transition>

                            <div class="pt-1">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-indigo-700 disabled:opacity-60 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                >
                                    <span v-if="profileForm.processing"
                                        >Guardando...</span
                                    >
                                    <span v-else>Guardar cambios</span>
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- Cambiar contraseña -->
                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-300"
                            >
                                <LockIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Cambiar contraseña
                                </h2>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Deja en blanco para no cambiarla
                                </p>
                            </div>
                        </div>

                        <form
                            @submit.prevent="submitPassword"
                            class="space-y-5"
                        >
                            <!-- Contraseña actual -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Contraseña actual
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="passwordForm.current_password"
                                        :type="
                                            showCurrent ? 'text' : 'password'
                                        "
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        :class="
                                            inputClass(
                                                passwordForm.errors
                                                    .current_password,
                                            )
                                        "
                                    />
                                    <button
                                        type="button"
                                        @click="showCurrent = !showCurrent"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    >
                                        <EyeIcon
                                            v-if="!showCurrent"
                                            class="h-4 w-4"
                                        />
                                        <EyeOffIcon v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p
                                    v-if="passwordForm.errors.current_password"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <!-- Nueva contraseña -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nueva contraseña
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="passwordForm.password"
                                        :type="showNew ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        :class="
                                            inputClass(
                                                passwordForm.errors.password,
                                            )
                                        "
                                    />
                                    <button
                                        type="button"
                                        @click="showNew = !showNew"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    >
                                        <EyeIcon
                                            v-if="!showNew"
                                            class="h-4 w-4"
                                        />
                                        <EyeOffIcon v-else class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Indicador de fuerza -->
                                <div
                                    v-if="passwordForm.password"
                                    class="mt-2 space-y-1"
                                >
                                    <div class="flex gap-1">
                                        <div
                                            v-for="i in 4"
                                            :key="i"
                                            :class="[
                                                'h-1 flex-1 rounded-full transition-all duration-300',
                                                i <= passwordStrength.score
                                                    ? passwordStrength.color
                                                    : 'bg-slate-200 dark:bg-slate-700',
                                            ]"
                                        />
                                    </div>
                                    <p
                                        class="text-xs"
                                        :class="passwordStrength.textColor"
                                    >
                                        {{ passwordStrength.label }}
                                    </p>
                                </div>

                                <p
                                    v-if="passwordForm.errors.password"
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{ passwordForm.errors.password }}
                                </p>
                            </div>

                            <!-- Confirmar contraseña -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Confirmar nueva contraseña
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="
                                            passwordForm.password_confirmation
                                        "
                                        :type="
                                            showConfirm ? 'text' : 'password'
                                        "
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        :class="
                                            inputClass(
                                                passwordForm.errors
                                                    .password_confirmation,
                                            )
                                        "
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirm = !showConfirm"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    >
                                        <EyeIcon
                                            v-if="!showConfirm"
                                            class="h-4 w-4"
                                        />
                                        <EyeOffIcon v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p
                                    v-if="
                                        passwordForm.errors
                                            .password_confirmation
                                    "
                                    class="mt-1.5 text-xs text-rose-500"
                                >
                                    {{
                                        passwordForm.errors
                                            .password_confirmation
                                    }}
                                </p>
                            </div>

                            <!-- Feedback -->
                            <transition name="fade">
                                <div
                                    v-if="passwordSaved"
                                    class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                                >
                                    <CheckIcon class="h-4 w-4 flex-shrink-0" />
                                    Contraseña actualizada correctamente.
                                </div>
                            </transition>

                            <div class="pt-1">
                                <button
                                    type="submit"
                                    :disabled="
                                        passwordForm.processing ||
                                        !passwordForm.current_password
                                    "
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:opacity-60 dark:bg-rose-500 dark:hover:bg-rose-600"
                                >
                                    <span v-if="passwordForm.processing"
                                        >Actualizando...</span
                                    >
                                    <span v-else>Cambiar contraseña</span>
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </transition>

            <!-- ── Avatar ───────────────────────────────────────────────────────── -->
            <transition appear name="panel-fade">
                <section
                    class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-500 dark:bg-sky-500/10 dark:text-sky-300"
                        >
                            <CameraIcon class="h-5 w-5" />
                        </div>
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Foto de perfil
                            </h2>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                JPG, PNG o WebP · máx. 2 MB
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-start gap-6 sm:flex-row sm:items-center"
                    >
                        <!-- Preview grande -->
                        <div class="relative flex-shrink-0">
                            <img
                                v-if="avatarPreview"
                                :src="avatarPreview"
                                alt="Vista previa"
                                class="h-24 w-24 rounded-3xl border border-slate-200 object-cover shadow-sm dark:border-slate-700"
                            />
                            <div
                                v-else
                                class="flex h-24 w-24 items-center justify-center rounded-3xl border border-slate-200 bg-slate-100 text-3xl font-bold text-slate-400 dark:border-slate-700 dark:bg-slate-800"
                            >
                                {{ initials }}
                            </div>
                            <div
                                v-if="avatarUploading"
                                class="absolute inset-0 flex items-center justify-center rounded-3xl bg-white/80 dark:bg-slate-900/80"
                            >
                                <div
                                    class="h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-sky-500"
                                />
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex-1 space-y-3">
                            <p
                                class="text-sm text-slate-600 dark:text-slate-400"
                            >
                                Haz clic en el botón o arrastra una imagen. Se
                                actualizará automáticamente al seleccionar.
                            </p>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    :disabled="avatarUploading"
                                    @click="avatarInput?.click()"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-100 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    <CameraIcon class="h-4 w-4" />
                                    {{
                                        avatarUploading
                                            ? 'Subiendo...'
                                            : 'Elegir imagen'
                                    }}
                                </button>

                                <button
                                    v-if="avatarPreview"
                                    type="button"
                                    :disabled="avatarUploading"
                                    @click="removeAvatar"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:-translate-y-0.5 hover:bg-rose-100 disabled:opacity-60 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                    Eliminar foto
                                </button>
                            </div>

                            <!-- Feedbacks -->
                            <transition name="fade">
                                <p
                                    v-if="avatarError"
                                    class="text-xs font-medium text-rose-500"
                                >
                                    {{ avatarError }}
                                </p>
                            </transition>
                            <transition name="fade">
                                <div
                                    v-if="avatarSaved"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                                >
                                    <CheckIcon class="h-4 w-4" />
                                    Foto actualizada correctamente.
                                </div>
                            </transition>
                        </div>
                    </div>

                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/jpg,image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onAvatarChange"
                    />
                </section>
            </transition>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

// ─── Props ────────────────────────────────────────────────────────────────

interface UserData {
    id: number;
    name: string;
    email: string;
    avatar_url?: string | null;
    phone?: string | null;
    cedula_rif?: string | null;
    active?: boolean;
}

interface Props {
    user: UserData;
}

const props = defineProps<Props>();

// ─── Hero cards ───────────────────────────────────────────────────────────

const initials = computed(() =>
    props.user.name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase() ?? '')
        .join(''),
);

const heroCards = [
    {
        title: 'Nombre',
        value: props.user.name,
        caption: 'Identificación en el sistema.',
        delay: 0,
    },
    {
        title: 'Correo',
        value: props.user.email,
        caption: 'Dirección de acceso y notificaciones.',
        delay: 90,
    },
];

// ─── Avatar ───────────────────────────────────────────────────────────────

const avatarPreview = ref<string | null>(
    props.user.avatar_url ? `/storage/${props.user.avatar_url}` : null,
);
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarSaved = ref(false);
const avatarError = ref<string | null>(null);
const avatarUploading = ref(false);

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'La imagen no puede superar 2 MB.';
        return;
    }

    avatarError.value = null;
    avatarPreview.value = URL.createObjectURL(file);
    submitAvatar(file);
}

function submitAvatar(file: File) {
    const form = useForm({ avatar: file });
    avatarUploading.value = true;

    form.post('/profile/avatar', {
        preserveScroll: true,
        onSuccess: () => {
            avatarSaved.value = true;
            avatarUploading.value = false;
            setTimeout(() => (avatarSaved.value = false), 3500);
        },
        onError: (errors) => {
            avatarError.value = errors.avatar ?? 'Error al subir la imagen.';
            avatarUploading.value = false;
        },
    });
}

function removeAvatar() {
    const form = useForm({});
    avatarUploading.value = true;

    form.delete('/profile/avatar', {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
            avatarUploading.value = false;
            avatarSaved.value = true;
            setTimeout(() => (avatarSaved.value = false), 3500);
        },
        onError: () => {
            avatarUploading.value = false;
        },
    });
}

// ─── Formulario: perfil ───────────────────────────────────────────────────

const profileSaved = ref(false);

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
    cedula_rif: props.user.cedula_rif ?? '',
});

function submitProfile() {
    profileForm.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            profileSaved.value = true;
            setTimeout(() => (profileSaved.value = false), 3500);
        },
    });
}

// ─── Formulario: contraseña ───────────────────────────────────────────────

const passwordSaved = ref(false);
const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submitPassword() {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordSaved.value = true;
            setTimeout(() => (passwordSaved.value = false), 3500);
        },
    });
}

// ─── Indicador de fuerza de contraseña ───────────────────────────────────

const passwordStrength = computed(() => {
    const p = passwordForm.password;
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;

    const map = [
        {
            label: 'Muy débil',
            color: 'bg-rose-500',
            textColor: 'text-rose-500',
        },
        {
            label: 'Débil',
            color: 'bg-orange-400',
            textColor: 'text-orange-500',
        },
        {
            label: 'Aceptable',
            color: 'bg-amber-400',
            textColor: 'text-amber-500',
        },
        {
            label: 'Fuerte',
            color: 'bg-emerald-500',
            textColor: 'text-emerald-600 dark:text-emerald-400',
        },
        {
            label: 'Muy fuerte',
            color: 'bg-emerald-600',
            textColor: 'text-emerald-700 dark:text-emerald-300',
        },
    ];

    return { score: Math.max(1, score), ...map[score] };
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function inputClass(error?: string) {
    const base =
        'w-full rounded-2xl border px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:outline-none focus:ring-2 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500';
    return error
        ? `${base} border-rose-400 bg-rose-50 focus:ring-rose-300 dark:border-rose-500/60 dark:bg-rose-500/10`
        : `${base} border-slate-200 bg-slate-50 focus:ring-indigo-300 focus:border-indigo-400 dark:border-slate-700 dark:focus:border-indigo-500`;
}

// ─── Iconos ───────────────────────────────────────────────────────────────

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

const UserIcon = icon(
    'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
);
const LockIcon = icon(
    'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
);
const CheckIcon = icon('M4.5 12.75l6 6 9-13.5');
const EyeIcon = icon(
    'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
);
const EyeOffIcon = icon(
    'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88',
);
const CameraIcon = icon(
    'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z',
);
const TrashIcon = icon(
    'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
);
</script>

<style scoped>
.panel-float {
    animation: panelFloat 720ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.animate-badge-float {
    animation: badgeFloat 5.5s ease-in-out infinite;
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

.panel-fade-enter-active,
.panel-fade-appear-active {
    transition:
        opacity 420ms ease,
        transform 420ms ease;
}
.panel-fade-enter-from,
.panel-fade-appear-from {
    opacity: 0;
    transform: translateY(18px);
}

.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 300ms ease,
        transform 300ms ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
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
