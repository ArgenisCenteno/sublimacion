<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Editar Empresa'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">
        Editar empresa
      </h1>

      <form class="grid gap-6 md:grid-cols-2" @submit.prevent="submit">
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
        <div class="field-reveal flex items-center gap-2">
          <input type="checkbox" v-model="form.active" id="active" class="w-4 h-4 rounded border-slate-300 focus:ring-2 focus:ring-brand-500" />
          <label for="active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Activo</label>
        </div>

        <!-- LOGO -->
        <div class="field-reveal md:col-span-2">
          <label class="label">Logo (archivo .jpg, .png, .webp)</label>
          <input type="file" @change="handleLogoChange" class="block w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm text-slate-800 shadow-theme-xs dark:text-white/90" />
          <p v-if="form.errors.logo_url" class="mt-1 text-sm text-red-500">{{ form.errors.logo_url }}</p>
          <div v-if="previewLogoUrl" class="mt-4">
            <p class="text-sm text-slate-600 dark:text-slate-300">Logo actual:</p>
            <img :src="previewLogoUrl" class="h-24 w-24 object-contain rounded-lg border border-slate-300 mt-2" />
          </div>
        </div>

        <!-- SUBMIT -->
        <div class="md:col-span-2 flex justify-end mt-4">
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Actualizando...' : 'Actualizar empresa' }}
          </button>
        </div>
      </form>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import axios from 'axios'

interface CompanyForm {
  name: string
  tax_id: string
  invoice_series?: string
  logo_url?: File | null
  active: boolean
}

const props = defineProps<{
  company: any
}>()

// Inicializamos con los valores existentes
const form = useForm<CompanyForm>({
  name: props.company.name || '',
  tax_id: props.company.tax_id || '',
  invoice_series: props.company.invoice_series || '',
  logo_url: null,
  active: props.company.active ?? true,
})

// URL para mostrar el logo actual
const previewLogoUrl = computed(() => {
  if (form.logo_url) return URL.createObjectURL(form.logo_url)
  if (props.company.logo_url)
    return props.company.logo_url.startsWith('http')
      ? props.company.logo_url
      : `/storage/${props.company.logo_url}`
  return null
})

function handleLogoChange(event: Event) {
  const target = event.target as HTMLInputElement
  form.logo_url = target.files?.[0] ?? null
}


function submit() {
  const formData = new FormData()
  formData.append('name', form.name)
  formData.append('tax_id', form.tax_id)
  formData.append('invoice_series', form.invoice_series || '')
  formData.append('active', form.active ? '1' : '0')

  if (form.logo_url) formData.append('logo_url', form.logo_url)
  formData.append('_method', 'PUT') // Laravel lo trata como PUT

  axios.post(`/companies/${props.company.id}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  .then(res => {
    console.log('Empresa actualizada', res.data)
    //form.logo_url = null // reset archivo

    // 🔹 Redirigir al listado
    window.location.href = '/companies' // o la ruta de tu index
  })
  .catch(err => {
    console.error('Error', err.response?.data || err)
    if (err.response?.data?.errors) {
      form.errors = err.response.data.errors
    }
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