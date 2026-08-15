<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Detalle de la Empresa'" />

    <div class="space-y-6">
      <!-- HEADER -->
      <section class="rounded-2xl border border-white/10 bg-slate-950 p-6 text-white shadow-xl flex items-center gap-6">
        <div>
          <h1 class="text-3xl font-semibold">
            {{ company.name }}
          </h1>
          <p class="mt-2 text-sm text-white/70">
            CIF/NIF: {{ company.tax_id || '-' }}
          </p>
          <p class="mt-1 text-sm text-white/70">
            Serie de facturación: {{ company.invoice_series || '-' }}
          </p>
          <p class="mt-1 text-sm text-white/70">
            Estado: <span :class="company.active ? 'text-emerald-400' : 'text-red-400'">{{ company.active ? 'Activo' : 'Inactivo' }}</span>
          </p>
        </div>

        <div v-if="company.logo_url" class="ml-auto">
          <img :src="logoUrl" alt="Logo" class="h-20 w-20 object-contain rounded-lg border border-white/20 bg-white/10" />
        </div>
      </section>

      <!-- DATOS PRINCIPALES -->
      <section class="grid gap-6 md:grid-cols-2">
        <div class="card">
          <h2 class="title">Información básica</h2>
          <div class="space-y-2 text-sm">
            <p><strong>Nombre:</strong> {{ company.name || '-' }}</p>
            <p><strong>CIF / NIF:</strong> {{ company.tax_id || '-' }}</p>
            <p><strong>Serie de facturación:</strong> {{ company.invoice_series || '-' }}</p>
          </div>
        </div>

        <div class="card">
          <h2 class="title">Relaciones</h2>
          <div class="space-y-2 text-sm">
            <p><strong>Usuarios:</strong> {{ company.users?.length || 0 }}</p>
            <p><strong>Proveedores:</strong> {{ company.providers?.length || 0 }}</p>
            <p><strong>Vehículos:</strong> {{ company.vehicles?.length || 0 }}</p>
          </div>
        </div>
      </section>

      <!-- LOGO -->
      <section class="card" v-if="company.logo_url">
        <h2 class="title">Logo</h2>
        <img :src="logoUrl" alt="Logo" class="h-32 w-32 object-contain rounded-lg border border-slate-300" />
      </section>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const props = defineProps<{
  company: any
}>()

const company = props.company

const logoUrl = computed(() => {
  if (!company.logo_url) return null
  // si el logo se guardó en storage/public
  return company.logo_url.startsWith('http')
    ? company.logo_url
    : `/storage/${company.logo_url}`
})
</script>