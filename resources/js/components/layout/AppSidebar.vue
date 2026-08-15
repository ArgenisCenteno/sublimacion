<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-99999 mt-16 flex h-screen flex-col border-r border-gray-200 bg-white px-5 text-gray-900 transition-all duration-300 ease-in-out lg:mt-0 dark:border-gray-800 dark:bg-gray-900',
            {
                'lg:w-[290px]': isExpanded || isMobileOpen,
                'lg:w-[90px]': !isExpanded,
                'w-[290px] translate-x-0': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded"
        @mouseleave="isHovered = false"
    >
        <div class="flex w-full items-center py-8">
            <Link
                href="/dashboard"
                @click.prevent="isMobileOpen ? handleToggle() : null"
                class="flex items-center gap-2"
            >
                <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="hidden items-center gap-2 text-xl font-bold text-gray-900 sm:inline-flex dark:text-white"
                >
                    Biocerra
                </span>
            </Link>

            <button
                @click="handleToggle"
                class="ml-auto hidden h-10 w-10 items-center justify-center rounded-lg border-gray-200 text-gray-500 sm:flex lg:h-11 lg:w-11 lg:border dark:border-gray-800 dark:text-gray-400"
            >
                <svg v-if="isMobileOpen" class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" fill=""/>
                </svg>
                <svg v-else width="16" height="12" viewBox="0 0 16 12" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/>
                </svg>
            </button>
        </div>

        <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
                        <h2 :class="['mb-4 flex text-xs leading-[20px] text-gray-400 uppercase', !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start']">
                            <template v-if="isExpanded || isHovered || isMobileOpen">
                                {{ menuGroup.title }}
                            </template>
                            <HorizontalDots v-else />
                        </h2>

                        <ul class="flex flex-col gap-4">
                            <li v-for="(item, index) in menuGroup.items" :key="item.name">

                                <!-- Con submenú -->
                                <button
                                    v-if="item.subItems && item.subItems.length"
                                    @click="toggleSubmenu(groupIndex, index)"
                                    :class="['group menu-item w-full', isSubmenuOpen(groupIndex, index) ? 'menu-item-active' : 'menu-item-inactive', !isExpanded && !isHovered ? 'lg:justify-center' : 'lg:justify-start']"
                                >
                                    <span :class="isSubmenuOpen(groupIndex, index) ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <component :is="item.icon" />
                                    </span>
                                    <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">
                                        {{ item.name }}
                                    </span>
                                    <ChevronDownIcon
                                        v-if="isExpanded || isHovered || isMobileOpen"
                                        :class="['ml-auto h-5 w-5 transition-transform duration-200', { 'rotate-180 text-brand-500': isSubmenuOpen(groupIndex, index) }]"
                                    />
                                </button>

                                <!-- Sin submenú -->
                                <Link
                                    v-else-if="item.path"
                                    :href="item.path"
                                    :class="['group menu-item', isActive(item.path) ? 'menu-item-active' : 'menu-item-inactive']"
                                >
                                    <span :class="isActive(item.path) ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <component :is="item.icon" />
                                    </span>
                                    <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">
                                        {{ item.name }}
                                    </span>
                                </Link>

                                <!-- Submenú desplegable -->
                                <transition
                                    @enter="startTransition"
                                    @after-enter="endTransition"
                                    @before-leave="startTransition"
                                    @after-leave="endTransition"
                                >
                                    <div v-show="isSubmenuOpen(groupIndex, index) && (isExpanded || isHovered || isMobileOpen)">
                                        <ul class="mt-2 ml-9 space-y-1">
                                            <li v-for="subItem in item.subItems ?? []" :key="subItem.name">
                                                <Link
                                                    :href="subItem.path"
                                                    :class="['menu-dropdown-item', isActive(subItem.path) ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive']"
                                                >
                                                    {{ subItem.name }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </transition>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed, type Component, defineComponent, h } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ChevronDownIcon, HorizontalDots } from '../../icons';
import { useSidebar } from '@/composables/useSidebar';

interface SubItem { name: string; path: string; }
interface MenuItem { icon: Component; name: string; path?: string; subItems?: SubItem[]; }
interface MenuGroup { title: string; items: MenuItem[]; }

const page   = usePage();
const currentPath = page.url;

const {
    isExpanded,
    isMobileOpen,
    isHovered,
    openSubmenu,
    toggleSidebar,
    toggleMobileSidebar,
} = useSidebar();

// ── Íconos SVG inline (sin dependencia de librerías externas) ──────────────

const icon = (path: string | string[], extraAttrs?: Record<string, string>) =>
    defineComponent({
        setup(_, { attrs }) {
            return () =>
                h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', width: '20', height: '20', ...attrs, ...extraAttrs },
                    (Array.isArray(path) ? path : [path]).map(d => h('path', { d }))
                );
        },
    });

// Recepción
const IconDashboard  = icon('M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6');
const IconPacientes  = icon(['M16 7a4 4 0 11-8 0 4 4 0 018 0z', 'M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z']);
const IconOrdenes    = icon(['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']);
const IconPagos      = icon('M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z');

// Laboratorio
const IconMuestras   = icon('M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z');
const IconResultados = icon('M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z');

// Catálogo
const IconExamenes   = icon(['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2', 'M9 5a2 2 0 002 2h2a2 2 0 002-2', 'M9 5a2 2 0 012-2h2a2 2 0 012 2', 'M9 12h6m-6 4h6']);
const IconPerfiles   = icon(['M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10']);
const IconMedicos    = icon(['M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804', 'M15 10a3 3 0 11-6 0 3 3 0 016 0z', 'M19.428 15.428a2 2 0 00-1 0 9 9 0 11-18 0 9 9 0 0118 0z'].join(' '));

// Admin
const IconUsuarios   = icon(['M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z']);
const IconRoles      = icon(['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z']);
const IconAjustes    = icon(['M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z']);

// ── Menú ────────────────────────────────────────────────────────────────────

const menuGroups: MenuGroup[] = [
    {
        title: 'General',
        items: [
            { icon: IconDashboard, name: 'Inicio',     path: '/dashboard' },
        ],
    },
    {
        title: 'Recepción',
        items: [
            { icon: IconPacientes, name: 'Pacientes',  path: '/recepcion/pacientes' },
            { icon: IconOrdenes,   name: 'Órdenes',    path: '/recepcion/ordenes' },
            { icon: IconPagos,     name: 'Pagos',      path: '/recepcion/pagos' },
        ],
    },
    {
        title: 'Laboratorio',
        items: [
            { icon: IconMuestras,   name: 'Muestras',   path: '/muestras' },
            { icon: IconResultados, name: 'Resultados', path: '/resultados' },
        ],
    },
    {
        title: 'Catálogo',
        items: [
            { icon: IconExamenes, name: 'Exámenes', path: '/catalogo/examenes' },
            { icon: IconPerfiles, name: 'Perfiles',  path: '/catalogo/perfiles' },
            { icon: IconMedicos,  name: 'Médicos',   path: '/catalogo/medicos' },
        ],
    },
    {
        title: 'Administración',
        items: [
            { icon: IconUsuarios, name: 'Usuarios', path: '/users' },
            { icon: IconRoles,    name: 'Roles',    path: '/roles' },
            {
                icon: IconAjustes,
                name: 'Ajustes',
                subItems: [
                    { name: 'Perfil',      path: '/settings/profile' },
                    { name: 'Apariencia',  path: '/settings/appearance' },
                    { name: 'Contraseña',  path: '/settings/password' },
                ],
            },
        ],
    },
];

// ── Helpers ─────────────────────────────────────────────────────────────────

const isActive = (path: string) => currentPath === path;

const toggleSubmenu = (groupIndex: number, itemIndex: number) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() =>
    menuGroups.some(group =>
        group.items.some(item =>
            item.subItems?.some(subItem => isActive(subItem.path))
        )
    )
);

const isSubmenuOpen = (groupIndex: number, itemIndex: number) => {
    const key = `${groupIndex}-${itemIndex}`;
    return (
        openSubmenu.value === key ||
        (isAnySubmenuRouteActive.value &&
            menuGroups[groupIndex].items[itemIndex].subItems?.some(subItem =>
                isActive(subItem.path)
            ))
    );
};

const startTransition = (el: Element) => {
    const htmlEl = el as HTMLElement;
    htmlEl.style.height = 'auto';
    const height = htmlEl.scrollHeight;
    htmlEl.style.height = '0px';
    void htmlEl.offsetHeight;
    htmlEl.style.height = height + 'px';
};

const endTransition = (el: Element) => {
    (el as HTMLElement).style.height = '';
};

const handleToggle = () => {
    if (window.innerWidth >= 1024) {
        toggleSidebar();
    } else {
        toggleMobileSidebar();
    }
};
</script>