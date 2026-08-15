<template>
    <div class="relative" ref="dropdownRef">
        <button
            class="flex items-center text-gray-700 dark:text-gray-400"
            @click.prevent="toggleDropdown"
        >
            <span
                class="mr-3 block h-11 w-11 flex-shrink-0 overflow-hidden rounded-full"
            >
                <img
                    :src="userAvatar"
                    alt="User"
                    class="h-full w-full object-cover"
                />
            </span>

            <span class="mr-1 block text-theme-sm font-medium">{{
                userName
            }}</span>

            <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
        </button>

        <!-- Dropdown Start -->
        <div
            v-if="dropdownOpen"
            class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
        >
            <div>
                <span
                    class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    {{ userName }}
                </span>
                <span
                    class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400"
                >
                    {{ userEmail }}
                </span>
            </div>

            <ul
                class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800"
            >
                <li v-for="item in menuItems" :key="item.href">
                    <Link
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                    >
                        <!-- SVG icon would go here -->
                        <component
                            :is="item.icon"
                            class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                        />
                        {{ item.text }}
                    </Link>
                </li>
            </ul>
            <Link
                href="/signin"
                @click="signOut"
                class="group mt-3 flex items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <LogoutIcon
                    class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                />
                Sign out
            </Link>
        </div>
        <!-- Dropdown End -->
    </div>
</template>

<script setup>
import {
    UserCircleIcon,
    ChevronDownIcon,
    LogoutIcon,
    SettingsIcon,
    InfoCircleIcon,
} from '@/icons';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const dropdownOpen = ref(false);
const dropdownRef = ref(null);
const page = usePage();

const userName = computed(() => page.props.auth?.user?.name ?? 'Usuario');
const userEmail = computed(() => page.props.auth?.user?.email ?? '');
const userAvatar = computed(() =>
    page.props.auth?.user?.avatar_url
        ? `/storage/${page.props.auth?.user?.avatar_url}`
        : '/images/user/owner.jpg',
);

const menuItems = [
    { href: '/profile', icon: UserCircleIcon, text: 'Edit profile' },
    // { href: '/chat', icon: SettingsIcon, text: 'Account settings' },
    // { href: '/profile', icon: InfoCircleIcon, text: 'Support' },
];

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const signOut = () => {
    // Implement sign out logic here
    console.log('Signing out...');
    closeDropdown();
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
