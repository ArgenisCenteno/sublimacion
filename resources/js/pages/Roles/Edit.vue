<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Editar Rol'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Editar rol</h1>

      <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
          <!-- NOMBRE -->
          <div class="field-reveal">
            <label class="label">Nombre del rol *</label>
            <input v-model="form.name" class="input-base" />
          </div>

          <!-- PERMISOS -->
          <div class="field-reveal md:col-span-2">
            <label class="label">Permisos</label>
            <div class="mt-2 grid gap-2 md:grid-cols-2">
              <label
                v-for="permission in normalizedPermissions"
                :key="permission.id"
                class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white/10 px-3 py-1 text-sm text-slate-900 hover:bg-indigo-50 dark:border-slate-700 dark:text-white/90 dark:hover:bg-slate-800"
              >
                <input
                  type="checkbox"
                  :value="permission.id"
                  v-model="form.permissions"
                  class="h-4 w-4 rounded border-slate-300 focus:ring-2 focus:ring-brand-500"
                />
                {{ permission.name }}
              </label>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Actualizando...' : 'Actualizar rol' }}
          </button>
        </div>
      </form>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

interface RoleForm {
  name: string
  permissions: number[]
}

interface Props {
  role: {
    id: number
    name: string
    permissions?: Array<{ id: number; name: string }>
  }
  allPermissions: Array<{ id: number | string; name: string }>
}

const { role, allPermissions } = defineProps<Props>()

// Normalizamos todos los IDs como números
const normalizedPermissions = allPermissions.map(p => ({
  id: Number(p.id),
  name: p.name,
}))

const form = useForm<RoleForm>({
  name: role.name,
  permissions: role.permissions?.map(p => Number(p.id)) ?? [],
})

function submit() {
  form.put(`/roles/${role.id}`, {
    onSuccess: () => console.log('Rol actualizado'),
  })
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