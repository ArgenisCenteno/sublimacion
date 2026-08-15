<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Crear Empresa'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">
        Alta de empresa
      </h1>

      <form class="space-y-6" @submit.prevent="submit" enctype="multipart/form-data">
        <!-- GRID DOS COLUMNAS -->
        <div class="grid gap-6 md:grid-cols-2">
          <!-- NOMBRE -->
          <div class="field-reveal">
            <label class="label">Nombre de la empresa *</label>
            <input v-model="form.name" :class="inputClass(form.errors.name)" @input="form.name = form.name?.toUpperCase()" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
          </div>

          <!-- TAX ID -->
          <div class="field-reveal">
            <label class="label">CIF / NIF *</label>
            <input v-model="form.tax_id" :class="inputClass(form.errors.tax_id)" @input="form.tax_id = form.tax_id?.toUpperCase()" />
            <p v-if="form.errors.tax_id" class="mt-1 text-sm text-red-500">{{ form.errors.tax_id }}</p>
          </div>

          <!-- SERIE FACTURACION -->
          <div class="field-reveal">
            <label class="label">Serie de facturación</label>
            <input v-model="form.invoice_series" :class="inputClass(form.errors.invoice_series)" />
          </div>

          <!-- ACTIVO -->
          <div class="field-reveal flex items-center gap-2 mt-6 md:mt-0">
            <input type="checkbox" v-model="form.active" id="active" class="w-4 h-4 rounded border-slate-300 focus:ring-2 focus:ring-brand-500" />
            <label for="active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Activo</label>
          </div>
        </div>

        <!-- LOGO -->
        <div class="field-reveal">
          <label class="label">Logo (archivo .jpg, .jpeg, .png, .webp)</label>
          <input
            type="file"
            accept=".jpg,.jpeg,.png,.webp"
            @change="handleFileChange"
            :class="inputClass(form.errors.logo_url)"
          />
          <p v-if="selectedFileName" class="mt-1 text-sm text-slate-500">Archivo seleccionado: {{ selectedFileName }}</p>
          <p v-if="form.errors.logo_url" class="mt-1 text-sm text-red-500">{{ form.errors.logo_url }}</p>
        </div>

        <!-- SUBMIT -->
        <div class="flex justify-end">
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Guardando...' : 'Crear empresa' }}
          </button>
        </div>
      </form>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

interface CompanyForm {
  name: string
  tax_id: string
  invoice_series?: string
  logo_url?: File | null
  active: boolean
}

const form = useForm<CompanyForm>({
  name: '',
  tax_id: '',
  invoice_series: '',
  logo_url: null,
  active: true,
})

const selectedFileName = ref('')

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  form.logo_url = file
  selectedFileName.value = file?.name ?? ''
}

function submit() {
  form.post('/companies', {
    forceFormData: true, // necesario para enviar archivos
    onSuccess: () => {
      form.reset()
      selectedFileName.value = ''
    },
  })
}

// ================================
// 🧠 CLASES INPUT
// ================================
const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function inputClass(error?: string) {
  return [
    inputBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}
</script>

<style scoped>
.field-reveal {
  animation: fieldReveal 520ms ease-out both;
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
</style>