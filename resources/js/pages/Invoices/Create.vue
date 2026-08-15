<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Cargar Factura'" />

        <section
            class="panel-float mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900"
        >
            <h1
                class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white"
            >
                Nueva factura
            </h1>

            <form
                class="space-y-6"
                @submit.prevent="submit"
                enctype="multipart/form-data"
            >
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- PROVEEDOR -->
                    <div class="field-reveal">
                        <label class="label">Proveedor</label>
                        <select
                            v-model="form.provider_id"
                            :class="inputClass(form.errors.provider_id)"
                        >
                            <option :value="null">— Sin proveedor —</option>
                            <option
                                v-for="provider in providers"
                                :key="provider.id"
                                :value="provider.id"
                            >
                                {{ provider.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.provider_id"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.provider_id }}
                        </p>
                    </div>

                    <!-- VEHÍCULO -->
                    <div class="field-reveal">
                        <label class="label">Vehículo</label>
                        <select
                            v-model="form.vehicle_id"
                            :class="inputClass(form.errors.vehicle_id)"
                        >
                            <option :value="null">— Sin vehículo —</option>
                            <option
                                v-for="vehicle in vehicles"
                                :key="vehicle.id"
                                :value="vehicle.id"
                            >
                                {{ vehicle.plate }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.vehicle_id"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.vehicle_id }}
                        </p>
                    </div>

                    <!-- ARCHIVO -->
                    <div class="field-reveal md:col-span-2">
                        <label class="label">Factura (Imagen) *</label>
                        <input
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            @change="handleFileChange"
                            :class="inputClass(form.errors.file)"
                        />
                        <p
                            v-if="selectedFileName"
                            class="mt-1 text-sm text-slate-500"
                        >
                            Archivo seleccionado: {{ selectedFileName }}
                        </p>
                        <p
                            v-if="form.errors.file"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.file }}
                        </p>

                        <!-- VISTA PREVIA DEL PDF -->
                        <div
                            v-if="previewUrl"
                            class="mt-4 overflow-hidden rounded-lg border"
                        >
                            <iframe
                                v-if="isPdf"
                                :src="previewUrl"
                                class="h-[400px] w-full"
                            ></iframe>
                            <img
                                v-else
                                :src="previewUrl"
                                alt="Vista previa"
                                class="max-h-[400px] w-full object-contain"
                            />
                        </div>
                    </div>
                    <!-- ALBARÁN -->
                    <div class="field-reveal md:col-span-2">
                        <label class="label">Albarán (Imagen)</label>
                        <input
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            @change="handleFileChange2"
                            :class="inputClass(form.errors.file2)"
                        />
                        <p
                            v-if="selectedFileName2"
                            class="mt-1 text-sm text-slate-500"
                        >
                            Archivo seleccionado: {{ selectedFileName2 }}
                        </p>
                        <p
                            v-if="form.errors.file2"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.file2 }}
                        </p>

                        <!-- VISTA PREVIA DEL ALBARÁN -->
                        <div
                            v-if="previewUrl2"
                            class="mt-4 overflow-hidden rounded-lg border"
                        >
                            <iframe
                                v-if="isPdf2"
                                :src="previewUrl2"
                                class="h-[400px] w-full"
                            ></iframe>
                            <img
                                v-else
                                :src="previewUrl2"
                                alt="Vista previa"
                                class="max-h-[400px] w-full object-contain"
                            />
                        </div>
                    </div>

                    <!-- NOTA -->
                    <div class="field-reveal md:col-span-2">
                        <label class="label">Nota</label>
                        <textarea
                            v-model="form.note"
                            placeholder="Escribe una nota opcional..."
                            class="h-24 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs transition duration-300 placeholder:text-slate-400 focus:-translate-y-0.5 focus:ring-4 focus:outline-hidden dark:text-white/90 dark:placeholder:text-slate-500"
                        ></textarea>
                        <p
                            v-if="form.errors.note"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.note }}
                        </p>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary"
                    >
                        {{ form.processing ? 'Subiendo...' : 'Cargar Factura' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';;
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

interface InvoiceForm {
    provider_id?: number | null;
    vehicle_id?: number | null;
    file?: File | null;
    note?: string;
    file2?: File | null;
}

const props = defineProps<{
    providers: { id: number; name: string }[];
    vehicles: { id: number; plate: string }[];
}>();

const providers = ref(props.providers);
const vehicles = ref(props.vehicles);

const form = useForm<InvoiceForm>({
    provider_id: null,
    vehicle_id: null,
    file: null,
    note: '',
    file2: null,
});

const selectedFileName = ref('');
const previewUrl = ref<string | null>(null);
const isPdf = ref(false);

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.file = file;
    selectedFileName.value = file?.name ?? '';

    if (file) {
        isPdf.value = file.type === 'application/pdf';
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = null;
    }
}
const selectedFileName2 = ref('');
const previewUrl2 = ref<string | null>(null);
const isPdf2 = ref(false);

function handleFileChange2(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.file2 = file;
    selectedFileName2.value = file?.name ?? '';

    if (file) {
        isPdf2.value = file.type === 'application/pdf';
        previewUrl2.value = URL.createObjectURL(file);
    } else {
        previewUrl2.value = null;
    }
}
function submit() {
    if (!form.file) {
        alert('Debes seleccionar un archivo');
        return;
    }

    form.post('/invoices', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            selectedFileName.value = '';
            previewUrl.value = null;
        },
    });
}

// ================================
// 🧠 CLASES INPUT
// ================================
const inputBaseClass =
    'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500';

function inputClass(error?: string) {
    return [
        inputBaseClass,
        error
            ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
    ];
}
</script>
