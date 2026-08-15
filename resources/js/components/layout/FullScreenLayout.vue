<template>
  <div class="min-h-screen">
    <LoadingBar />
    <main>
      <slot></slot>
    </main>
  </div>
</template>

<script setup lang="ts">
import LoadingBar from '@/components/common/LoadingBar.vue'
</script>
