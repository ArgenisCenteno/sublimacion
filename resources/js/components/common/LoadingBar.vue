<template>
  <div
    v-if="isVisible"
    class="fixed top-0 left-0 right-0 z-50 h-1 bg-brand-500 transition-all duration-300 ease-out"
    :style="{ width: progress + '%' }"
  />
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const isVisible = ref(false)
const progress = ref(0)

let progressInterval: number | null = null

const startProgress = () => {
  isVisible.value = true
  progress.value = 0

  // Simulate progress
  progressInterval = window.setInterval(() => {
    if (progress.value < 90) {
      progress.value += Math.random() * 10
    }
  }, 100)
}

const finishProgress = () => {
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }
  progress.value = 100
  setTimeout(() => {
    isVisible.value = false
    progress.value = 0
  }, 300)
}

let stopStart: (() => void) | null = null
let stopFinish: (() => void) | null = null
let stopError: (() => void) | null = null

onMounted(() => {
  stopStart = router.on('start', startProgress)
  stopFinish = router.on('finish', finishProgress)
  stopError = router.on('error', finishProgress)
})

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
  stopStart?.()
  stopFinish?.()
  stopError?.()
})
</script>