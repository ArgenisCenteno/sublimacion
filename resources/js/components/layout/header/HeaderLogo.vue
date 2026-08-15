<template>
    <img
        @click="handleToggle"
        src="/images/logo/dkm.webp"
        class="sm:hidden"
        alt="Logo"
        width="42"
        height="42"
    />
   
</template>

<script setup>
import { useSidebar } from '@/composables/useSidebar';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
const {
    isExpanded,
    isMobileOpen,
    isHovered,
    openSubmenu,
    toggleSidebar,
    toggleMobileSidebar,
} = useSidebar();
const handleToggle = () => {
    if (window.innerWidth >= 1024) {
        toggleSidebar();
    } else {
        toggleMobileSidebar();
    }
};

const isApplicationMenuOpen = ref(false);

const toggleApplicationMenu = () => {
    isApplicationMenuOpen.value = !isApplicationMenuOpen.value;
};
</script>
