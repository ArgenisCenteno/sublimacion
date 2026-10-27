<template>
    <header class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex items-center justify-between px-6 py-4 lg:px-8" :class="maxWidthClass">
            <Link href="/" class="flex items-center gap-2.5 text-lg font-semibold text-gray-800 dark:text-white">
                <img v-if="store.logoUrl" :src="store.logoUrl" :alt="store.name" class="h-8 w-8 rounded-full object-cover" />
                <Building2 v-else class="h-6 w-6" style="color: var(--color-primary)" />
                {{ store.name }}
            </Link>
            <div class="flex items-center gap-4">
                <Link
                    v-if="backHref"
                    :href="backHref"
                    class="text-sm font-medium text-gray-600 hover:text-[var(--color-secondary)] dark:text-gray-300 dark:hover:text-[var(--color-primary)]"
                >
                    {{ backLabel }}
                </Link>
                <CartLink />
                <Link
                    v-if="showSignIn"
                    href="/signin"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:border-[var(--color-primary)] hover:text-[var(--color-secondary)] dark:border-gray-700 dark:text-gray-300"
                >
                    Iniciar sesión
                </Link>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
import CartLink from '@/components/common/CartLink.vue';

withDefaults(
    defineProps<{
        backHref?: string;
        backLabel?: string;
        showSignIn?: boolean;
        maxWidthClass?: string;
    }>(),
    {
        backHref: undefined,
        backLabel: 'Volver',
        showSignIn: false,
        maxWidthClass: 'max-w-7xl',
    },
);

const page = usePage<{ store: { name: string; logoUrl: string | null } }>();
const store = computed(() => page.props.store);
</script>
