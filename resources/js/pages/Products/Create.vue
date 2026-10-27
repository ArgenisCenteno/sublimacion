<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="'Crear producto'" />

        <section class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="mb-6 text-2xl font-semibold text-slate-900 dark:text-white">Nuevo producto o servicio</h1>

            <form class="space-y-8" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="label">Nombre *</label>
                        <input v-model="form.name" :class="inputClass(form.errors.name)" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label">Categoría</label>
                        <select v-model="form.category_id" :class="inputClass(form.errors.category_id)">
                            <option :value="null">— Sin categoría —</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Tipo *</label>
                        <select v-model="form.type" :class="inputClass(form.errors.type)">
                            <option value="producto">Producto</option>
                            <option value="servicio">Servicio</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Precio (USD) *</label>
                        <input v-model="form.price_usd" type="number" min="0" step="0.01" :class="inputClass(form.errors.price_usd)" />
                        <p v-if="form.errors.price_usd" class="mt-1 text-sm text-red-500">{{ form.errors.price_usd }}</p>
                    </div>

                    <div>
                        <label class="label">SKU</label>
                        <input v-model="form.sku" :class="inputClass(form.errors.sku)" />
                        <p v-if="form.errors.sku" class="mt-1 text-sm text-red-500">{{ form.errors.sku }}</p>
                    </div>

                    <div v-if="form.type === 'producto'">
                        <label class="label">Stock</label>
                        <input v-model="form.stock" type="number" min="0" :class="inputClass(form.errors.stock)" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Descripción</label>
                        <textarea v-model="form.description" rows="4" :class="textareaClass(form.errors.description)"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="active" v-model="form.active" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
                        <label for="active" class="text-sm text-slate-700 dark:text-slate-300">Activo</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="featured" v-model="form.featured" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
                        <label for="featured" class="text-sm text-slate-700 dark:text-slate-300">Destacado</label>
                    </div>
                </div>

                <!-- Variantes -->
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <label class="label">Variantes</label>
                        <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700" @click="addVariant">
                            + Agregar variante
                        </button>
                    </div>

                    <p v-if="!form.variants.length" class="text-sm text-slate-500 dark:text-slate-400">
                        Sin variantes. Útil para tallas, colores o planes/niveles del servicio.
                    </p>

                    <div v-for="(variant, index) in form.variants" :key="index" class="mb-3 grid gap-3 rounded-2xl border border-slate-200 p-4 md:grid-cols-[2fr_1fr_1fr_1fr_auto] dark:border-slate-700">
                        <div>
                            <label class="label text-xs">Nombre *</label>
                            <input v-model="variant.name" placeholder="Ej. Rojo - M" :class="inputClass()" />
                        </div>
                        <div>
                            <label class="label text-xs">Precio override</label>
                            <input v-model="variant.price_override" type="number" min="0" step="0.01" placeholder="Opcional" :class="inputClass()" />
                        </div>
                        <div v-if="form.type === 'producto'">
                            <label class="label text-xs">Stock</label>
                            <input v-model="variant.stock" type="number" min="0" :class="inputClass()" />
                        </div>
                        <div>
                            <label class="label text-xs">SKU</label>
                            <input v-model="variant.sku" placeholder="Opcional" :class="inputClass()" />
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="rounded-xl border border-rose-200 px-3 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10" @click="removeVariant(index)">
                                Quitar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Imágenes -->
                <div>
                    <label class="label">Imágenes</label>
                    <input ref="imagesInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" multiple class="hidden" @change="onImagesChange" />
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="flex h-24 w-24 items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 text-slate-400 transition hover:border-indigo-300 hover:text-indigo-500 dark:border-slate-700" @click="imagesInput?.click()">
                            + Agregar
                        </button>
                        <div v-for="(preview, index) in imagePreviews" :key="index" class="group relative h-24 w-24 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                            <img :src="preview" class="h-full w-full object-cover" />
                            <button
                                type="button"
                                class="absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition group-hover:opacity-100"
                                @click="removeImage(index)"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.images" class="mt-1 text-sm text-red-500">{{ form.errors.images }}</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        {{ form.processing ? 'Guardando...' : 'Crear producto' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

defineProps<{
    categories: Array<{ id: number; name: string }>;
}>();

interface VariantForm {
    name: string;
    price_override: string;
    stock: string;
    sku: string;
}

const form = useForm<{
    category_id: number | null;
    type: 'producto' | 'servicio';
    name: string;
    description: string;
    price_usd: string;
    sku: string;
    stock: string;
    active: boolean;
    featured: boolean;
    variants: VariantForm[];
    images: File[];
}>({
    category_id: null,
    type: 'producto',
    name: '',
    description: '',
    price_usd: '',
    sku: '',
    stock: '',
    active: true,
    featured: false,
    variants: [],
    images: [],
});

const addVariant = () => {
    form.variants.push({ name: '', price_override: '', stock: '', sku: '' });
};

const removeVariant = (index: number) => {
    form.variants.splice(index, 1);
};

const imagesInput = ref<HTMLInputElement | null>(null);
const imagePreviews = ref<string[]>([]);

function onImagesChange(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    for (const file of files) {
        form.images.push(file);
        imagePreviews.value.push(URL.createObjectURL(file));
    }
    if (imagesInput.value) imagesInput.value.value = '';
}

function removeImage(index: number) {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);
}

function submit() {
    form.post('/products');
}

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

function textareaClass(error?: string) {
    const base = inputBaseClass.replace('h-12', '');
    return [
        base,
        error
            ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
            : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
    ];
}
</script>
