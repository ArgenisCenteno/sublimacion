<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Apariencia y pagos" />

        <div
            v-if="flash.success"
            class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
        >
            {{ flash.success }}
        </div>

        <form class="mt-5 space-y-6" @submit.prevent="submit">
            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Apariencia</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Nombre de la tienda</label>
                        <input v-model="form.store_name" :class="inputClass(form.errors.store_name)" />
                        <p v-if="form.errors.store_name" class="mt-1 text-sm text-red-500">{{ form.errors.store_name }}</p>
                    </div>
                    <div>
                        <label class="label">Slogan</label>
                        <input v-model="form.store_slogan" :class="inputClass(form.errors.store_slogan)" />
                        <p v-if="form.errors.store_slogan" class="mt-1 text-sm text-red-500">{{ form.errors.store_slogan }}</p>
                    </div>
                    <div>
                        <label class="label">Color primario</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.color_primary" type="color" class="h-12 w-16 rounded-xl border border-slate-300 dark:border-slate-700" />
                            <input v-model="form.color_primary" :class="inputClass(form.errors.color_primary)" />
                        </div>
                        <p v-if="form.errors.color_primary" class="mt-1 text-sm text-red-500">{{ form.errors.color_primary }}</p>
                    </div>
                    <div>
                        <label class="label">Color secundario</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.color_secondary" type="color" class="h-12 w-16 rounded-xl border border-slate-300 dark:border-slate-700" />
                            <input v-model="form.color_secondary" :class="inputClass(form.errors.color_secondary)" />
                        </div>
                        <p v-if="form.errors.color_secondary" class="mt-1 text-sm text-red-500">{{ form.errors.color_secondary }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Logo</label>
                        <div class="flex items-center gap-4">
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo actual" class="h-16 w-16 rounded-2xl border border-slate-200 object-cover dark:border-slate-700" />
                            <input type="file" accept="image/*" class="text-sm text-slate-600 dark:text-slate-300" @change="onLogoChange" />
                        </div>
                        <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">{{ form.errors.logo }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Redes sociales</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Instagram</label>
                        <input v-model="form.instagram_url" placeholder="https://instagram.com/..." :class="inputClass(form.errors.instagram_url)" />
                        <p v-if="form.errors.instagram_url" class="mt-1 text-sm text-red-500">{{ form.errors.instagram_url }}</p>
                    </div>
                    <div>
                        <label class="label">Facebook</label>
                        <input v-model="form.facebook_url" placeholder="https://facebook.com/..." :class="inputClass(form.errors.facebook_url)" />
                        <p v-if="form.errors.facebook_url" class="mt-1 text-sm text-red-500">{{ form.errors.facebook_url }}</p>
                    </div>
                    <div>
                        <label class="label">TikTok</label>
                        <input v-model="form.tiktok_url" placeholder="https://tiktok.com/@..." :class="inputClass(form.errors.tiktok_url)" />
                        <p v-if="form.errors.tiktok_url" class="mt-1 text-sm text-red-500">{{ form.errors.tiktok_url }}</p>
                    </div>
                    <div>
                        <label class="label">WhatsApp (número, con código de país)</label>
                        <input v-model="form.whatsapp_number" placeholder="584121234567" :class="inputClass(form.errors.whatsapp_number)" />
                        <p v-if="form.errors.whatsapp_number" class="mt-1 text-sm text-red-500">{{ form.errors.whatsapp_number }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Contacto</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Correo de contacto</label>
                        <input v-model="form.contact_email" type="email" :class="inputClass(form.errors.contact_email)" />
                        <p v-if="form.errors.contact_email" class="mt-1 text-sm text-red-500">{{ form.errors.contact_email }}</p>
                    </div>
                    <div>
                        <label class="label">Teléfono de contacto</label>
                        <input v-model="form.contact_phone" :class="inputClass(form.errors.contact_phone)" />
                        <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-500">{{ form.errors.contact_phone }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Dirección (retiro en tienda)</label>
                        <input v-model="form.address" :class="inputClass(form.errors.address)" />
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Horario de atención</label>
                        <input v-model="form.business_hours" placeholder="Lun-Vie 9am-6pm, Sáb 9am-1pm" :class="inputClass(form.errors.business_hours)" />
                        <p v-if="form.errors.business_hours" class="mt-1 text-sm text-red-500">{{ form.errors.business_hours }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Cuentas de pago</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Se muestran al cliente en el checkout cuando elige ese método de pago.</p>

                <h3 class="mt-5 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pago móvil</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Banco</label>
                        <input v-model="form.pago_movil_bank" :class="inputClass(form.errors.pago_movil_bank)" />
                    </div>
                    <div>
                        <label class="label">Teléfono</label>
                        <input v-model="form.pago_movil_phone" :class="inputClass(form.errors.pago_movil_phone)" />
                    </div>
                    <div>
                        <label class="label">Cédula/RIF</label>
                        <input v-model="form.pago_movil_cedula_rif" :class="inputClass(form.errors.pago_movil_cedula_rif)" />
                    </div>
                    <div>
                        <label class="label">Titular</label>
                        <input v-model="form.pago_movil_holder_name" :class="inputClass(form.errors.pago_movil_holder_name)" />
                    </div>
                </div>

                <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Transferencia</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Banco</label>
                        <input v-model="form.transferencia_bank" :class="inputClass(form.errors.transferencia_bank)" />
                    </div>
                    <div>
                        <label class="label">Número de cuenta</label>
                        <input v-model="form.transferencia_account_number" :class="inputClass(form.errors.transferencia_account_number)" />
                    </div>
                    <div>
                        <label class="label">Cédula/RIF</label>
                        <input v-model="form.transferencia_cedula_rif" :class="inputClass(form.errors.transferencia_cedula_rif)" />
                    </div>
                    <div>
                        <label class="label">Titular</label>
                        <input v-model="form.transferencia_holder_name" :class="inputClass(form.errors.transferencia_holder_name)" />
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface StoreSettingData {
    store_name: string | null;
    store_slogan: string | null;
    logo_path: string | null;
    color_primary: string;
    color_secondary: string;
    instagram_url: string | null;
    facebook_url: string | null;
    tiktok_url: string | null;
    whatsapp_number: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    address: string | null;
    business_hours: string | null;
    pago_movil_bank: string | null;
    pago_movil_phone: string | null;
    pago_movil_cedula_rif: string | null;
    pago_movil_holder_name: string | null;
    transferencia_bank: string | null;
    transferencia_account_number: string | null;
    transferencia_cedula_rif: string | null;
    transferencia_holder_name: string | null;
}

const props = defineProps<{
    setting: StoreSettingData;
}>();

const page = usePage<{ flash?: { success?: string | null } }>();
const flash = computed(() => page.props.flash ?? {});

const form = useForm({
    store_name: props.setting.store_name ?? '',
    store_slogan: props.setting.store_slogan ?? '',
    color_primary: props.setting.color_primary,
    color_secondary: props.setting.color_secondary,
    instagram_url: props.setting.instagram_url ?? '',
    facebook_url: props.setting.facebook_url ?? '',
    tiktok_url: props.setting.tiktok_url ?? '',
    whatsapp_number: props.setting.whatsapp_number ?? '',
    contact_email: props.setting.contact_email ?? '',
    contact_phone: props.setting.contact_phone ?? '',
    address: props.setting.address ?? '',
    business_hours: props.setting.business_hours ?? '',
    pago_movil_bank: props.setting.pago_movil_bank ?? '',
    pago_movil_phone: props.setting.pago_movil_phone ?? '',
    pago_movil_cedula_rif: props.setting.pago_movil_cedula_rif ?? '',
    pago_movil_holder_name: props.setting.pago_movil_holder_name ?? '',
    transferencia_bank: props.setting.transferencia_bank ?? '',
    transferencia_account_number: props.setting.transferencia_account_number ?? '',
    transferencia_cedula_rif: props.setting.transferencia_cedula_rif ?? '',
    transferencia_holder_name: props.setting.transferencia_holder_name ?? '',
    logo: null as File | null,
});

const logoPreview = ref<string | null>(props.setting.logo_path ? `/storage/${props.setting.logo_path}` : null);

function onLogoChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.logo = file;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    form.post('/settings/tienda', { preserveScroll: true });
}

const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500 disabled:opacity-60';

function inputClass(error?: string) {
    return [
        inputBaseClass,
        error
            ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
    ];
}
</script>
