<template>
  <div class="min-h-screen">
    <LoadingBar />
    <ImpersonateBanner />
    <main>
      <slot></slot>
    </main>
  </div>
</template>

<script setup lang="ts">
import LoadingBar from '@/components/common/LoadingBar.vue'
import ImpersonateBanner from './ImpersonateBanner.vue'
</script>
