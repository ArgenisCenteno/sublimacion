<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="'Detalle del Proveedor'" />

    <div class="space-y-6">
      <!-- HEADER -->
      <section class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-950 shadow-2xl shadow-slate-950/30">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(129,140,248,0.45),_transparent_35%),linear-gradient(135deg,_#667eea_0%,_#764ba2_100%)] p-5 sm:p-7 lg:p-8">
          <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex-1">
              <Link
                href="/providers"
                class="mb-5 inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur-sm transition hover:bg-white/15"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver
              </Link>

              <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl border border-white/15 bg-white/15 text-white shadow-lg shadow-indigo-950/20 backdrop-blur-md">
                  <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                  </svg>
                </div>

                <div class="min-w-0 space-y-3 text-white">
                  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    {{ provider.name }}
                  </h1>
                  <p class="text-sm sm:text-base">
                    Empresa: {{ provider.company.name || '-' }}
                  </p>
                  <p class="text-sm sm:text-base">
                    Tipo: {{ provider.type }}
                  </p>
                  <p class="text-sm sm:text-base">
                    Estado: 
                    <span :class="provider.active ? 'text-emerald-400' : 'text-rose-400'">
                      {{ provider.active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </p>
                </div>

               
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- RESUMEN -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <article class="rounded-2xl border border-white/10 bg-white/10 p-4  not-first:shadow-lg backdrop-blur-md">
          <p class="text-xs uppercase font-semibold opacity-80">Email</p>
          <p class="mt-1 text-lg font-medium">{{ provider.email || '-' }}</p>
        </article>
        <article class="rounded-2xl border border-white/10 bg-white/10 p-4  not-first: shadow-lg backdrop-blur-md">
          <p class="text-xs uppercase font-semibold opacity-80">Teléfono</p>
          <p class="mt-1 text-lg font-medium">{{ provider.phone || '-' }}</p>
        </article>
        <article class="rounded-2xl border border-white/10 bg-white/10 p-4   shadow-lg backdrop-blur-md">
          <p class="text-xs uppercase font-semibold opacity-80">CIF / NIF</p>
          <p class="mt-1 text-lg font-medium">{{ provider.tax_id || '-' }}</p>
        </article>
      </section>

      <!-- MAPA -->
      <section class="rounded-2xl border border-slate-200/80 bg-white p-2 shadow-lg shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold mb-2">Ubicación</h2>
        <div ref="mapRef" class="h-80 rounded-2xl border"></div>
        <p class="text-xs text-slate-500 mt-2">📍 {{ provider.latitude }}, {{ provider.longitude }}</p>
      </section>

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Link } from '@inertiajs/vue3'
declare global { interface Window { google: any } }

const props = defineProps<{
  provider: {
    name: string
    company: { name: string }
    type: string
    email?: string | null
    phone?: string | null
    tax_id?: string | null
    latitude: number
    longitude: number
    active: boolean
    logo_url?: string | null
  }
}>()

const provider = props.provider

const mapRef = ref<HTMLDivElement | null>(null)
let marker: any

async function initMap() {
  await nextTick()
  if (!mapRef.value) return
  const location = { lat: provider.latitude, lng: provider.longitude }

  const map = new google.maps.Map(mapRef.value, {
    center: location,
    zoom: 15,
    zoomControl: true,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
  })

  marker = new google.maps.Marker({
    position: location,
    map,
    draggable: true,
  })

  // Arrastrar marcador
  marker.addListener('dragend', () => {
    const pos = marker.getPosition()
    if (!pos) return
    provider.latitude = pos.lat()
    provider.longitude = pos.lng()
    map.setCenter(pos)
  })

  // Click en mapa
  map.addListener('click', (e: any) => {
    if (!e.latLng) return
    marker.setPosition(e.latLng)
    provider.latitude = e.latLng.lat()
    provider.longitude = e.latLng.lng()
  })
}

onMounted(() => initMap())
</script>