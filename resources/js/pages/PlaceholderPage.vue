<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="title" />
    <div
      class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12"
    >
      <div class="mx-auto w-full max-w-[630px] text-center">
        <h3
          class="mb-4 font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl"
        >
          {{ title }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 sm:text-base">
          Esta página todavía no está implementada. Usa este espacio para mostrar el contenido real de <strong>{{ title }}</strong>.
        </p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

const page = usePage();
const title = computed(() => {
  return (page.props as any).title ?? "Placeholder";
});
</script>
