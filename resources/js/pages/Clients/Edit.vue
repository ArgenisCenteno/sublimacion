<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Editar Cliente'" />

    <div class="space-y-6">

      <!-- HEADER -->
      <section class="rounded-2xl border border-white/10 bg-slate-950 p-6 text-white shadow-xl">
        <h1 class="text-3xl font-semibold">
          Editar cliente
        </h1>
        <p class="mt-2 text-sm text-white/70">
          {{ clientDisplayName }}
        </p>
      </section>

      <!-- FORM -->
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        
        <ClientManualForm
          :form="form"
          submit-label="Actualizar cliente"
          processing-label="Guardando..."
          @submit="submit"
        />

      </section>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import ClientManualForm from '@/components/clients/ClientManualForm.vue'

const props = defineProps<{
  client: any
}>()

const client = props.client

// 🧠 FORM INICIALIZADO CON DATOS DEL CLIENTE
const form = useForm({
  type: client.type ?? 'fisica',
  name: client.name ?? '',
  company_name: client.company_name ?? '',
  nif: client.nif ?? '',
  email: client.email ?? '',
  phone: client.phone ?? '',
  phone_secondary: client.phone_secondary ?? '',
  address: client.address ?? '',
  city: client.city ?? '',
  postal_code: client.postal_code ?? '',
  province: client.province ?? '',
  country: client.country ?? '',
  contact_person: client.contact_person ?? '',
  notes: client.notes ?? '',
})

// 🧠 NOMBRE DINÁMICO
const clientDisplayName = computed(() => {
  return client.type === 'juridica'
    ? client.company_name
    : client.name
})

// 🚀 SUBMIT
function submit() {
  form.put(`/clients/${client.id}`)
}
</script>