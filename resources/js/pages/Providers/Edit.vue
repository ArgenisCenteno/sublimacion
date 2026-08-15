<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Editar Proveedor'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">
        Editar proveedor
      </h1>

      <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">

          <!-- EMPRESA -->
          <div>
            <label class="label">Empresa *</label>
            <select v-model="form.company_id" :class="inputClass(form.errors.company_id)">
              <option value="">Seleccionar</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>

          <!-- NOMBRE -->
          <div>
            <label class="label">Nombre *</label>
            <input
              v-model="form.name"
              @input="form.name = form.name?.toUpperCase()"
              :class="inputClass(form.errors.name)"
            />
          </div>

          <!-- TIPO -->
          <div>
            <label class="label">Tipo *</label>
            <select v-model="form.type" :class="inputClass(form.errors.type)">
              <option value="chapa">Chapa</option>
              <option value="dab">DAB</option>
              <option value="limpieza">Limpieza</option>
              <option value="proveedor_vehiculos">Vehículos</option>
            </select>
          </div>

          <!-- PIN (solo lectura en edit) -->
          <div>
            <label class="label">PIN *</label>
            <input
              type="text"
              v-model="form.pin"
              maxlength="6"
              class="h-12 w-full rounded-2xl border px-4 text-sm bg-slate-100 dark:bg-slate-700"
              readonly
            />
          </div>

          <!-- PREFIX -->
          <div>
            <label class="label">Prefijo PIN</label>
            <input v-model="form.pin_prefix" :class="inputClass()" />
          </div>

          <!-- EMAIL -->
          <div>
            <label class="label">Email</label>
            <input v-model="form.email" :class="inputClass(form.errors.email)" />
          </div>

          <!-- PHONE -->
          <div>
            <label class="label">Teléfono</label>
            <input v-model="form.phone" :class="inputClass(form.errors.phone)" />
          </div>

          <!-- TAX -->
          <div>
            <label class="label">CIF</label>
            <input v-model="form.tax_id" :class="inputClass(form.errors.tax_id)" />
          </div>

          <!-- ACTIVO -->
          <div class="flex items-center gap-2 mt-6">
            <input type="checkbox" v-model="form.active" />
            <label>Activo</label>
          </div>

        </div>

        <!-- 🗺️ MAPA -->
        <div class="md:col-span-2 mt-4">
          <label class="label">Ubicación *</label>
          <div ref="mapRef" class="h-82 rounded-2xl border"></div>
          <p class="text-xs text-slate-500 mt-2">
            📍 {{ form.latitude }}, {{ form.longitude }}
          </p>
        </div>

        <div class="flex justify-end mt-4">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Actualizar proveedor' }}
          </button>
        </div>

      </form>
    </section>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const googleMapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
declare global { interface Window { google: any } }

const props = defineProps<{
  companies: Array<{ id: number; name: string }>
  provider: { 
    id: number
    company_id: number | string
    name: string
    type: string
    pin: string
    pin_prefix?: string
    email?: string
    phone?: string
    tax_id?: string
    latitude: number
    longitude: number
    active: boolean
  }
}>()

const form = useForm({
  company_id: props.provider.company_id,
  name: props.provider.name,
  type: props.provider.type,
  pin: props.provider.pin,
  pin_prefix: props.provider.pin_prefix || '',
  email: props.provider.email || '',
  phone: props.provider.phone || '',
  tax_id: props.provider.tax_id || '',
  latitude: props.provider.latitude,
  longitude: props.provider.longitude,
  active: props.provider.active,
})

// ─── SUBMIT ───────────────────────────────────────────
function submit() {
  form.put(`/providers/${props.provider.id}`)
}

// ─── INPUT CLASS ─────────────────────────────────────
const inputBaseClass = 'h-12 w-full rounded-2xl border px-4 text-sm'
function inputClass(error?: string) {
  return [
    inputBaseClass,
    error ? 'border-red-400' : 'border-slate-300',
  ]
}

// ─── MAP ──────────────────────────────────────────────
const mapRef = ref<HTMLDivElement | null>(null)

function loadGoogleMaps(): Promise<any> {
  return new Promise((resolve) => {
    if (window.google) resolve(window.google)
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&libraries=places`
    script.async = true
    script.defer = true
    script.onload = () => resolve(window.google)
    document.head.appendChild(script)
  })
}

onMounted(async () => {
  const google = await loadGoogleMaps()
  const location = { lat: form.latitude, lng: form.longitude }

  const map = new google.maps.Map(mapRef.value, {
    center: location,
    zoom: 15,
    zoomControl: true,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
  })

  const marker = new google.maps.Marker({
    position: location,
    map,
    draggable: true,
  })

  marker.addListener('dragend', () => {
    const pos = marker.getPosition()
    if (!pos) return
    form.latitude = pos.lat()
    form.longitude = pos.lng()
    map.setCenter(pos)
  })

  map.addListener('click', (e: any) => {
    if (!e.latLng) return
    marker.setPosition(e.latLng)
    form.latitude = e.latLng.lat()
    form.longitude = e.latLng.lng()
  })
})
</script>