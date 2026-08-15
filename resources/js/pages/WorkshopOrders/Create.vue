<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Nueva orden de taller'" />

    <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/20">
      <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.28),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#111827_45%,_#052e2b_100%)] p-5 sm:p-7">
        <div class="max-w-3xl">
          <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur-sm">Taller</span>
          <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Crear orden de taller</h1>
          <p class="mt-3 text-sm leading-6 text-white/75">Registra una orden manual y dejala lista para el seguimiento interno.</p>
        </div>
      </div>

      <div class="border-t border-white/10 bg-white p-6 dark:bg-slate-900 sm:p-8">
        <form class="space-y-8" @submit.prevent="submit">
          <div class="grid gap-6 xl:grid-cols-2">
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Vehiculo</span><select v-model="form.vehicle_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30"><option value="">Selecciona un vehiculo</option><option v-for="vehicle in vehicles" :key="vehicle.id" :value="String(vehicle.id)">{{ vehicle.label }}</option></select><p v-if="form.errors.vehicle_id" class="mt-2 text-sm text-rose-500">{{ form.errors.vehicle_id }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Proveedor</span><select v-model="form.provider_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30"><option value="">Sin proveedor</option><option v-for="provider in providers" :key="provider.id" :value="String(provider.id)">{{ provider.label }}</option></select><p v-if="form.errors.provider_id" class="mt-2 text-sm text-rose-500">{{ form.errors.provider_id }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de orden</span><select v-model="form.type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30"><option value="">Selecciona un tipo</option><option v-for="type in orderTypes" :key="type.value" :value="type.value">{{ type.label }}</option></select><p v-if="form.errors.type" class="mt-2 text-sm text-rose-500">{{ form.errors.type }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</span><select v-model="form.status" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30"><option value="">Selecciona un estado</option><option v-for="status in orderStatuses" :key="status.value" :value="status.value">{{ status.label }}</option></select><p v-if="form.errors.status" class="mt-2 text-sm text-rose-500">{{ form.errors.status }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Entrada</span><input v-model="form.entry_at" type="datetime-local" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30" /><p v-if="form.errors.entry_at" class="mt-2 text-sm text-rose-500">{{ form.errors.entry_at }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Salida</span><input v-model="form.exit_at" type="datetime-local" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30" /><p v-if="form.errors.exit_at" class="mt-2 text-sm text-rose-500">{{ form.errors.exit_at }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">URL foto de salida</span><input v-model="form.exit_photo_url" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30" placeholder="https://..." /><p v-if="form.errors.exit_photo_url" class="mt-2 text-sm text-rose-500">{{ form.errors.exit_photo_url }}</p></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Disco de la foto</span><input v-model="form.exit_photo_disk" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30" placeholder="local" /><p v-if="form.errors.exit_photo_disk" class="mt-2 text-sm text-rose-500">{{ form.errors.exit_photo_disk }}</p></label>
            <label class="block xl:col-span-2"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Creada por</span><select v-model="form.created_by_user_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-hidden transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/30"><option value="">Sin usuario</option><option v-for="user in users" :key="user.id" :value="String(user.id)">{{ user.label }}</option></select><p v-if="form.errors.created_by_user_id" class="mt-2 text-sm text-rose-500">{{ form.errors.created_by_user_id }}</p></label>
          </div>
          <label class="block"><span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Notas</span><textarea v-model="form.notes" rows="5" class="field min-h-[140px]" placeholder="Observaciones internas de la orden..."></textarea><p v-if="form.errors.notes" class="mt-2 text-sm text-rose-500">{{ form.errors.notes }}</p></label>
          <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-end"><Link href="/workshop/orders" class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</Link><button type="submit" :disabled="form.processing" class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60">{{ form.processing ? 'Guardando...' : 'Guardar orden' }}</button></div>
        </form>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

defineProps<{
  vehicles: Array<{ id: number; label: string }>
  providers: Array<{ id: number; label: string }>
  users: Array<{ id: number; label: string }>
  orderTypes: Array<{ value: string; label: string }>
  orderStatuses: Array<{ value: string; label: string }>
}>()

const form = useForm({ vehicle_id: '', provider_id: '', type: '', status: 'pendiente', entry_at: '', exit_at: '', exit_photo_url: '', exit_photo_disk: 'local', created_by_user_id: '', notes: '' })
const submit = () => form.post('/workshop/orders')
</script>


