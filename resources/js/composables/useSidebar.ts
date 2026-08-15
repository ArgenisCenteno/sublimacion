import { ref, provide, inject } from 'vue'
import type { Ref } from 'vue'

interface SidebarState {
  isExpanded: Ref<boolean>
  isMobileOpen: Ref<boolean>
  isHovered: Ref<boolean>
  openSubmenu: Ref<string | null>
  toggleSidebar: () => void
  toggleMobileSidebar: () => void
}

const sidebarKey = Symbol('sidebar')

export function useSidebarProvider(): void {
  const isExpanded = ref(true)
  const isMobileOpen = ref(false)
  const isHovered = ref(true)
  const openSubmenu = ref<string | null>(null)

  const toggleSidebar = () => {
    isExpanded.value = !isExpanded.value
  }

  const toggleMobileSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value
  }

  provide(sidebarKey, {
    isExpanded,
    isMobileOpen,
    isHovered,
    openSubmenu,
    toggleSidebar,
    toggleMobileSidebar,
  })
}

export function useSidebar() {
  const state = inject<SidebarState>(sidebarKey)
  if (!state) {
    throw new Error('useSidebar must be used within SidebarProvider')
  }
  return state
}
