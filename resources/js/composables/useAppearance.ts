import { ref, watch } from 'vue'

type Theme = 'light' | 'dark'

const theme = ref<Theme>((localStorage.getItem('theme') as Theme | null) || 'light')

// Watch changes and apply to DOM
watch(theme, (newTheme) => {
  localStorage.setItem('theme', newTheme)
  if (newTheme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}, { immediate: true })

function applyTheme(newTheme: Theme): void {
  theme.value = newTheme
}

export function initializeTheme(): void {
  const savedTheme = (localStorage.getItem('theme') as Theme | null) || 'light'
  applyTheme(savedTheme)
}

export function useAppearance() {
  const isDarkMode = () => theme.value === 'dark'

  const toggleTheme = () => {
    applyTheme(theme.value === 'light' ? 'dark' : 'light')
  }

  return {
    isDarkMode,
    toggleTheme,
    theme,
  }
}
