<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Crear Proveedor'" />

    <section class="panel-float rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900 mt-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">
        Alta de proveedor
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

          <!-- PIN -->
          <div>
            <label class="label">PIN *</label>
            <input 
              type="text"
              maxlength="6"
              v-model="form.pin" 
              :class="inputClass(form.errors.pin)" 
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
        <div class="md:col-span-2">
          <label class="label">Ubicación *</label>

         

          <div ref="mapRef" class="h-82 rounded-2xl border"></div>

          <p class="text-xs text-slate-500 mt-2">
            📍 {{ form.latitude }}, {{ form.longitude }}
          </p>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Crear proveedor' }}
          </button>
        </div>

      </form>
    </section>
  </AdminLayout>
</template>
<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const googleMapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
declare global { interface Window { google: any } }

// ─── PROPS ──────────────────────────────────────────
defineProps<{ companies: Array<{ id: number; name: string }> }>()

// ─── FORM ───────────────────────────────────────────
const form = useForm<{
  company_id: string | number
  name: string
  type: string
  pin: string
  pin_prefix: string
  email: string
  phone: string
  tax_id: string
  latitude: number
  longitude: number
  geofence_radius_meters: number
  active: boolean
}>({
  company_id: '',
  name: '',
  type: 'chapa',
  pin: '',
  pin_prefix: '',
  email: '',
  phone: '',
  tax_id: '',
  latitude: 40.4168,   // Madrid default
  longitude: -3.7038, // Madrid default
  geofence_radius_meters: 750,
  active: true,
})

// ─── SUBMIT ─────────────────────────────────────────
function submit() {
  form.post('/providers')
}

// ─── INPUT CLASS ────────────────────────────────────
const inputBaseClass = 'h-12 w-full rounded-2xl border px-4 text-sm'
function inputClass(error?: string) {
  return [inputBaseClass, error ? 'border-red-400' : 'border-slate-300']
}

// ─── MAP ────────────────────────────────────────────
const mapRef = ref<HTMLDivElement | null>(null)
const markerRef = ref<any>(null)

// Carga Google Maps
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

// Inicializa el mapa
async function initMap() {
  if (!mapRef.value) return
  const google = await loadGoogleMaps()
  const defaultLocation = { lat: form.latitude, lng: form.longitude }

  const map = new google.maps.Map(mapRef.value, {
    center: defaultLocation,
    zoom: 13,
  })

  const marker = new google.maps.Marker({
    position: defaultLocation,
    map,
    draggable: true,
  })
  markerRef.value = marker

  // Drag marker
  marker.addListener('dragend', () => {
    const pos = marker.getPosition()
    if (!pos) return
    form.latitude = pos.lat()
    form.longitude = pos.lng()
    map.setCenter(pos)
  })

  // Click en mapa
  map.addListener('click', (e: any) => {
    if (!e.latLng) return
    marker.setPosition(e.latLng)
    form.latitude = e.latLng.lat()
    form.longitude = e.latLng.lng()
  })
}

// ─── MONTADO ────────────────────────────────────────
onMounted(async () => {
  await nextTick()
  await initMap()
})

// ─── ACTUALIZAR AL NAVEGAR EN SPA ───────────────────
const page = usePage()
import { watch } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

watch(
  () => page.url,
  async () => {
    await nextTick()
    await initMap()
  }
)
</script>