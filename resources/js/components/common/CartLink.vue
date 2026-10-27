<template>
    <Link :href="href" class="relative inline-flex items-center" :class="linkClass || 'text-gray-600 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400'">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m-10 0a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z" />
        </svg>
        <span
            v-if="cartCount > 0"
            class="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-xs ring-2 ring-white dark:ring-gray-900 font-semibold text-white"
        >
            {{ cartCount }}
        </span>
    </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

withDefaults(defineProps<{ href?: string; linkClass?: string }>(), {
    href: '/cart',
    linkClass: '',
});

const page = usePage<{ cartCount?: number }>();
const cartCount = computed(() => page.props.cartCount ?? 0);
</script>
