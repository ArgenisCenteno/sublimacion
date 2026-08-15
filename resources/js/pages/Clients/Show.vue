<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Detalle del Cliente'" />

    <div class="space-y-6">
      
      <!-- HEADER -->
      <section class="rounded-2xl border border-white/10 bg-slate-950 p-6 text-white shadow-xl">
        <h1 class="text-3xl font-semibold">
          {{ clientDisplayName }}
        </h1>
        <p class="mt-2 text-sm text-white/70">
          Tipo: {{ client.type }}
        </p>
      </section>

      <!-- DATOS PRINCIPALES -->
      <section class="grid gap-6 md:grid-cols-2">
        
        <div class="card">
          <h2 class="title">Información básica</h2>

          <div class="space-y-2 text-sm">
            <p><strong>Nombre:</strong> {{ client.name || '-' }}</p>
            <p><strong>Empresa:</strong> {{ client.company_name || '-' }}</p>
            <p><strong>NIF:</strong> {{ client.nif || '-' }}</p>
            <p><strong>Email:</strong> {{ client.email || '-' }}</p>
          </div>
        </div>

        <div class="card">
          <h2 class="title">Contacto</h2>

          <div class="space-y-2 text-sm">
            <p><strong>Teléfono:</strong> {{ client.phone || '-' }}</p>
            <p><strong>Teléfono secundario:</strong> {{ client.phone_secondary || '-' }}</p>
            <p><strong>Persona de contacto:</strong> {{ client.contact_person || '-' }}</p>
          </div>
        </div>

      </section>

      <!-- DIRECCIÓN -->
      <section class="card">
        <h2 class="title">Dirección</h2>

        <div class="grid gap-2 text-sm md:grid-cols-2">
          <p><strong>Dirección:</strong> {{ client.address || '-' }}</p>
          <p><strong>Ciudad:</strong> {{ client.city || '-' }}</p>
          <p><strong>Código postal:</strong> {{ client.postal_code || '-' }}</p>
          <p><strong>Provincia:</strong> {{ client.province || '-' }}</p>
          <p><strong>País:</strong> {{ client.country || '-' }}</p>
        </div>
      </section>

      <!-- NOTAS -->
      <section class="card">
        <h2 class="title">Notas</h2>
        <p class="text-sm text-slate-600 dark:text-slate-300">
          {{ client.notes || 'Sin notas' }}
        </p>
      </section>

      <!-- CONTRATOS -->
      <section class="grid gap-6 md:grid-cols-2">
        
        <div class="card">
          <h2 class="title">Contratos de venta</h2>

          <ul v-if="client.sale_contracts?.length" class="space-y-2 text-sm">
            <li v-for="c in client.sale_contracts" :key="c.id">
              #{{ c.id }}
            </li>
          </ul>

          <p v-else class="text-sm text-slate-500">
            No hay contratos de venta
          </p>
        </div>

        <div class="card">
          <h2 class="title">Contratos de renting</h2>

          <ul v-if="client.renting_contracts?.length" class="space-y-2 text-sm">
            <li v-for="c in client.renting_contracts" :key="c.id">
              #{{ c.id }}
            </li>
          </ul>

          <p v-else class="text-sm text-slate-500">
            No hay contratos de renting
          </p>
        </div>

      </section>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const props = defineProps<{
  client: any
}>()

const client = props.client

const clientDisplayName = computed(() => {
  return client.type === 'juridica'
    ? client.company_name
    : client.name
})
</script>

 