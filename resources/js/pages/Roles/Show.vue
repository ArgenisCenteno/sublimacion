<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Ver Rol'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">
        Detalle del rol
      </h1>

      <div class="space-y-4">
        <p><strong>Nombre:</strong> {{ role.name }}</p>
        <p><strong>Permisos:</strong> 
          <span v-if="role.permissions?.length">
            {{ role.permissions.map(p => p.name).join(', ') }}
          </span>
          <span v-else>-</span>
        </p>
      </div>

      <div class="mt-6 flex gap-3">
        <router-link :to="`/roles/${role.id}/edit`" class="btn-primary">Editar</router-link>
        <button @click="deleteRole" class="btn-danger">Eliminar</button>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
 
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

interface Role {
  id: number
  name: string
  permissions?: Array<{ id: number; name: string }>
}

const { role } = defineProps<{ role: Role }>()

const deleteRole = () => {
  if (confirm(`¿Deseas eliminar el rol ${role.name}?`)) {
    router.delete(`/roles/${role.id}`)
  }
}
</script>