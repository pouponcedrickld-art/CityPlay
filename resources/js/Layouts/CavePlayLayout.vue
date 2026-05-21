<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VideoLoader from '@/Components/VideoLoader.vue';

const props = defineProps({
    immersive: { type: Boolean, default: false },
    hideLogo: { type: Boolean, default: false },
    wide: { type: Boolean, default: false },
});

const page = usePage();
const showLoader = ref(false);
const isSidebarVisible = ref(true);
let loaderDelayTimer = null;

const toggleSidebar = () => {
    isSidebarVisible.value = !isSidebarVisible.value;
};

const navItems = [
    { icon: 'pi pi-home', route: 'dashboard', label: 'Accueil' },
    { icon: 'pi pi-play', route: 'parties.web.create', label: 'Jouer' },
    { icon: 'pi pi-trophy', route: 'player.classement', label: 'Classement' },
    { icon: 'pi pi-users', route: 'parties.web.create', query: { tab: 'join' }, label: 'Rejoindre' },
    { icon: 'pi pi-map', route: 'parties.web.create', label: 'Parcours' },
    { icon: 'pi pi-cog', route: 'profile.edit', label: 'Réglages' },
];

const navHref = (item) => {
    const base = route(item.route);
    if (!item.query) return base;
    const q = new URLSearchParams(item.query).toString();
    return `${base}?${q}`;
};

const isActive = (item) => {
    if (item.query?.tab === 'join') {
        return route().current('parties.web.create') && page.url.includes('tab=join');
    }
    return route().current(item.route) && !page.url.includes('tab=join');
};

onMounted(() => {
    router.on('start', () => {
        // Anti-flash UX: show only if navigation is slow
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = setTimeout(() => {
            showLoader.value = true;
        }, 350);
    });
    router.on('finish', () => {
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = null;
        showLoader.value = false;
    });
    router.on('error', () => {
        if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
        loaderDelayTimer = null;
        showLoader.value = false;
    });
});

onUnmounted(() => {
    if (loaderDelayTimer) clearTimeout(loaderDelayTimer);
    loaderDelayTimer = null;
});

const innerClass = computed(() => [
    'cave-play-inner',
    props.immersive && 'cave-play-inner--immersive',
    props.wide && 'cave-play-inner--wide',
]);
</script>

<template>
    <div class="cave-play-hub cave-play-hub--standard">
        <VideoLoader :show="showLoader" />

        <div class="cave-leaves" aria-hidden="true">
            <span v-for="n in 6" :key="n" class="cave-leaf" />
        </div>

        <!-- Sidebar desktop -->
        <aside v-if="!immersive"
            class="cave-desktop-sidebar transition-all duration-300"
            :class="{ '-ml-[280px]': !isSidebarVisible }"
            style="overflow: visible"
        >
            <button
                @click="toggleSidebar"
                class="absolute -right-5 top-10 w-10 h-10 bg-[var(--cave-rock-light)] border-3 border-[var(--cave-border-dark)] rounded-xl flex items-center justify-center shadow-lg lg:flex hidden hover:scale-110 active:scale-95 transition-all"
                style="z-index: 50"
            >
                <i class="pi" :class="isSidebarVisible ? 'pi-chevron-left' : 'pi-bars'" />
            </button>

            <header class="cave-logo-wrap">
                <div class="cave-logo-icon">🗿</div>
                <h1 class="cave-logo-title">CityPlay</h1>
                <p class="cave-logo-sub">Aventure urbaine</p>
            </header>
            <nav class="cave-desktop-nav-list">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="navHref(item)"
                    class="cave-desktop-nav-link"
                    :class="{ 'cave-desktop-nav-link--active': isActive(item) }"
                >
                    <i :class="item.icon" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
            <div class="cave-sidebar-footer">
                <!-- Bouton Admin -->
                <Link
                    v-if="page.props.auth.user?.is_admin"
                    :href="route('admin.dashboard')"
                    class="cave-desktop-nav-link mb-3 cave-hud__btn--admin"
                    style="justify-content: center"
                >
                    <i class="pi pi-cog" />
                    <span>Dashboard Admin</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="cave-desktop-nav-link cave-desktop-nav-link--logout"
                >
                    <i class="pi pi-sign-out" />
                    <span>Déconnexion</span>
                </Link>
            </div>
        </aside>

        <div class="cave-play-main transition-all duration-300 relative">
            <!-- Bouton pour réouvrir la sidebar si cachée -->
            <button
                v-if="!immersive && !isSidebarVisible"
                @click="toggleSidebar"
                class="fixed left-4 top-4 w-12 h-12 bg-[var(--cave-rock-light)] border-3 border-[var(--cave-border-dark)] rounded-xl flex items-center justify-center shadow-lg z-50 lg:flex hidden hover:scale-110 active:scale-95 transition-all"
            >
                <i class="pi pi-bars" />
            </button>
            <div :class="innerClass">
                <header v-if="!hideLogo && !immersive" class="cave-logo-wrap cave-logo-wrap--mobile-only">
                    <div class="cave-logo-icon">🗿</div>
                    <h1 class="cave-logo-title">CityPlay</h1>
                    <p class="cave-logo-sub">Aventure urbaine</p>
                </header>
                <slot />
            </div>
        </div>

        <!-- Nav mobile -->
        <nav v-if="!immersive" class="cave-bottom-nav lg:hidden" aria-label="Navigation jeu">
            <Link
                v-for="item in navItems"
                :key="'m-' + item.label"
                :href="navHref(item)"
                class="cave-nav-stone"
                :class="{ 'cave-nav-stone--active': isActive(item) }"
                :title="item.label"
            >
                <i :class="item.icon" />
            </Link>
        </nav>
    </div>
</template>
