<template>
  <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
    <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Resumen general</h2>
      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <InfoField label="Marca" :value="vehicle.brand" />
        <InfoField label="Modelo" :value="vehicle.model" />
        <InfoField label="Version" :value="vehicle.version || 'Sin definir'" />
        <InfoField label="Color" :value="vehicle.color || 'No registrado'" />
        <InfoField label="Matricula" :value="vehicle.plate" />
        <InfoField label="VIN" :value="vehicle.vin || 'No registrado'" />
        <InfoField label="Kilometraje" :value="formatNumber(vehicle.mileage_km) + ' km'" />
        <InfoField label="Fecha matriculacion" :value="formatDate(vehicle.registration_date)" />
        <InfoField label="Empresa" :value="vehicle.company?.name || 'No asignada'" />
        <InfoField label="Proveedor" :value="vehicle.provider?.name || 'No asignado'" />
        <InfoField label="Estado fisico" :value="getPhysicalStatusLabel(vehicle.physical_status)" />
        <InfoField label="Estado comercial" :value="getCommercialStatusLabel(vehicle.commercial_status)" />
      </div>
    </div>

    <div class="space-y-6">
      <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Operacion</h2>
        <div class="mt-5 space-y-4">
          <MetricTile label="PVP contado" :value="formatCurrency(vehicle.price_cash)" />
          <MetricTile label="PVP financiado" :value="formatCurrency(vehicle.price_financed)" />
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <StatusPill
              title="Estado fisico"
              :value="getPhysicalStatusLabel(vehicle.physical_status)"
              :class-name="getPhysicalStatusClass(vehicle.physical_status)"
            />
            <StatusPill
              title="Estado comercial"
              :value="getCommercialStatusLabel(vehicle.commercial_status)"
              :class-name="getCommercialStatusClass(vehicle.commercial_status)"
            />
          </div>
        </div>
      </div>

      <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Notas</h2>
        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
          {{ vehicle.notes || 'Este vehiculo todavia no tiene notas registradas.' }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { defineComponent, h } from 'vue'
import type { VehicleData } from './vehicleShow'
import {
  formatCurrency,
  formatDate,
  formatNumber,
  getCommercialStatusClass,
  getCommercialStatusLabel,
  getPhysicalStatusClass,
  getPhysicalStatusLabel,
} from './vehicleShow'

defineProps<{
  vehicle: VehicleData
}>()

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
      ])
  },
})

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
      ])
  },
})

const MetricTile = defineComponent({
  name: 'MetricTile',
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
  },
  setup(props) {
    return () =>
      h('div', { class: 'rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60' }, [
        h('p', { class: 'text-sm font-medium text-slate-500 dark:text-slate-400' }, props.label),
        h('p', { class: 'mt-1 text-xl font-semibold text-slate-900 dark:text-white' }, props.value),
      ])
  },
})
</script>
