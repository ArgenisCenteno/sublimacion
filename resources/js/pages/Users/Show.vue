<template>
      <AdminLayout>
        <PageBreadcrumb :pageTitle="'Usuarios'" />
  <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
    <!-- Info general del usuario -->
    <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Información del usuario</h2>
      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <InfoField label="Nombre" :value="user.name" />
        <InfoField label="Email" :value="user.email" />
        <InfoField label="Teléfono" :value="user.phone || '—'" />
        <InfoField label="Cédula/RIF" :value="user.cedula_rif || '—'" />
        <InfoField label="Estado" :value="user.active ? 'Activo' : 'Inactivo'" />
        <InfoField label="Roles" :value="user.roles.length ? user.roles.map(r => r.name).join(', ') : 'Sin roles'" />
        <InfoField label="Fecha de creación" :value="user.created_at" />
        <InfoField label="Última actualización" :value="user.updated_at" />
      </div>
    </div>

    <!-- Acciones / detalles adicionales -->
    <div class="space-y-6">
      <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Estado y permisos</h2>
        <div class="mt-5 space-y-4">
          <StatusPill
            title="Estado"
            :value="user.active ? 'Activo' : 'Inactivo'"
            :class-name="user.active ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400'"
          />
        </div>
      </div>

      <div v-if="user.notes" class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Notas</h2>
        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
          {{ user.notes }}
        </p>
      </div>
    </div>
  </section>
   </AdminLayout>
</template>

<script setup lang="ts">
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import { defineComponent, h } from 'vue';
 

interface Role {
  id: number;
  name: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  cedula_rif?: string | null;
  roles: Role[];
  active: boolean;
  created_at: string;
  updated_at: string;
  notes?: string;
}

defineProps<{
  user: User;
}>();

// ================================
// COMPONENTES INTERNOS
// ================================
const InfoField = defineComponent({
  name: 'InfoField',
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
  },
  setup(props) {
    return () =>
      h('div', { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' }, [
        h('p', { class: 'text-sm font-medium text-slate-500 dark:text-slate-400' }, props.label),
        h('p', { class: 'mt-1 text-base font-semibold text-slate-900 dark:text-white' }, props.value),
      ]);
  },
});

const StatusPill = defineComponent({
  name: 'StatusPill',
  props: {
    title: { type: String, required: true },
    value: { type: String, required: true },
    className: { type: String, required: true },
  },
  setup(props) {
    return () =>
      h('div', { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' }, [
        h('p', { class: 'text-sm font-medium text-slate-500 dark:text-slate-400' }, props.title),
        h('span', { class: `mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold ${props.className}` }, props.value),
      ]);
  },
});
</script>
