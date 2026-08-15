<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Ver Permiso'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Detalle del permiso</h1>

      <div class="space-y-4">
        <p><strong>Nombre:</strong> {{ permission.name }}</p>
      </div>

      
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
 

import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

interface Permission { id: number; name: string }
const { permission } = defineProps<{ permission: Permission }>()

</script>