<template>
    <div
        v-if="impersonating"
        class="fixed inset-x-0 top-0 z-[100000] flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-medium text-white"
    >
        <span>Estás impersonando a {{ impersonating.name }}</span>
        <button
            type="button"
            class="rounded-lg border border-white/40 px-3 py-1 text-xs font-semibold hover:bg-white/10"
            @click="leave"
        >
            Volver a mi cuenta
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage<{ impersonating: { name: string } | null }>();
const impersonating = computed(() => page.props.impersonating);

const leave = () => {
    router.post('/impersonate/leave');
};
</script>
