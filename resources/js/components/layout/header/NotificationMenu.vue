<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      @click="toggleDropdown"
    >
      <span
        v-if="hasNotifications"
        class="absolute right-0 top-0.5 z-1 flex h-2 w-2 rounded-full bg-orange-400"
      >
        <span class="absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75 -z-1 animate-ping"></span>
      </span>
      <svg
        class="fill-current"
        width="20"
        height="20"
        viewBox="0 0 20 20"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
          fill=""
        />
      </svg>
    </button>

    <div
      v-if="dropdownOpen"
      class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0"
    >
      <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
        <div>
          <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Avisos</h5>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ notificationCountLabel }}
          </p>
        </div>

        <button @click="closeDropdown" class="text-gray-500 dark:text-gray-400">
          <svg
            class="fill-current"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
              fill=""
            />
          </svg>
        </button>
      </div>

      <ul v-if="notifications.length" class="flex h-auto flex-col overflow-y-auto custom-scrollbar">
        <li v-for="notification in notifications" :key="notification.id">
          <Link
            :href="notification.href || '#'"
            class="flex gap-3 rounded-lg border-b border-gray-100 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5"
            @click="closeDropdown"
          >
            <span
              class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
              :class="iconWrapperClass(notification.tone)"
            >
              <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
              >
                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(notification.kind)" />
              </svg>
            </span>

            <span class="block min-w-0">
              <span class="mb-1.5 block text-theme-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium text-gray-800 dark:text-white/90">
                  {{ notification.title }}
                </span>
              </span>

              <span class="block text-sm leading-6 text-gray-500 dark:text-gray-400">
                {{ notification.message }}
              </span>

              <span class="mt-2 flex items-center gap-2 text-gray-500 text-theme-xs dark:text-gray-400">
                <span :class="badgeClass(notification.tone)" class="rounded-full px-2 py-0.5 font-semibold">
                  {{ notification.label }}
                </span>
                <span class="h-1 w-1 rounded-full bg-gray-400"></span>
                <span>{{ notification.time }}</span>
              </span>
            </span>
          </Link>
        </li>
      </ul>

      <div
        v-else
        class="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 px-6 text-center dark:border-gray-700"
      >
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sin avisos por ahora</p>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
          Cuando el sistema detecte actividad importante, la verás aquí.
        </p>
      </div>

      <Link
        href="/alerts"
        class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
        @click="closeDropdown"
      >
        Ver centro de alertas
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

interface NotificationItem {
  id: string
  kind: 'alert' | 'pickup' | 'damage' | 'available'
  tone: 'rose' | 'amber' | 'emerald' | 'sky'
  label: string
  title: string
  message: string
  time: string
  href: string | null
}

const page = usePage<{
  notifications?: {
    count: number
    items: NotificationItem[]
  }
}>()

const dropdownOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

const notificationState = computed(() => page.props.notifications ?? { count: 0, items: [] })
const notifications = computed(() => notificationState.value.items ?? [])
const hasNotifications = computed(() => notificationState.value.count > 0)
const notificationCountLabel = computed(() => {
  const count = notificationState.value.count ?? 0

  if (count === 0) {
    return 'No hay avisos pendientes.'
  }

  if (count === 1) {
    return '1 aviso reciente'
  }

  return `${count} avisos recientes`
})

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown()
  }
}

const iconPath = (kind: NotificationItem['kind']) => ({
  alert: 'M12 9v4.5m0 3h.008v.008H12v-.008zM10.29 3.86L1.82 18a1.875 1.875 0 001.61 2.81h16.94A1.875 1.875 0 0021.98 18L13.51 3.86a1.875 1.875 0 00-3.22 0z',
  pickup: 'M3.75 12h16.5M12 3.75l8.25 8.25L12 20.25 3.75 12 12 3.75z',
  damage: 'M14.25 9.75L9.75 14.25M9.75 9.75l4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  available: 'M5 13l4 4L19 7',
}[kind])

const iconWrapperClass = (tone: NotificationItem['tone']) => ({
  rose: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
  amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
  emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
  sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
}[tone])

const badgeClass = (tone: NotificationItem['tone']) => ({
  rose: 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
  amber: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
  emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
  sky: 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
}[tone])

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
