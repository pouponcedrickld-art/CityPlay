<script setup>
/**
 * Layout principal pour l'interface Joueur (Thème "Cave Boy").
 *
 * Gère la sidebar escamotable, le loader de navigation, les animations de fond
 * et l'affichage responsive (Sidebar sur desktop, Bottom-nav sur mobile).
 */
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VideoLoader from '@/Components/VideoLoader.vue';

const props = defineProps({
    immersive: { type: Boolean, default: false }, // Si true, cache la navigation pour le jeu
    hideLogo: { type: Boolean, default: false },
    wide: { type: Boolean, default: false },      // Si true, utilise toute la largeur
});

const page = usePage();
const showLoader = ref(false);
const isSidebarVisible = ref(true); // État de la sidebar desktop (toggleable)
const isMobileMenuOpen = ref(false); // Pour le menu "Plus" mobile
let loaderDelayTimer = null;

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

/**
 * Bascule l'affichage de la sidebar sur Desktop.
 */
const toggleSidebar = () => {
    isSidebarVisible.value = !isSidebarVisible.value;
};

/**
 * Configuration des éléments de navigation.
 */
const navItems = [
    { icon: 'pi pi-home', route: 'dashboard', label: 'Accueil' },
    { icon: 'pi pi-play', route: 'parties.web.create', label: 'Jouer' },
    { icon: 'pi pi-trophy', route: 'player.classement', label: 'Classement' },
    { icon: 'pi pi-users', route: 'parties.web.create', query: { tab: 'join' }, label: 'Rejoindre' },
    { icon: 'pi pi-map', route: 'parties.web.create', label: 'Parcours' },
    { icon: 'pi pi-cog', route: 'profile.edit', label: 'Réglages' },
];

/**
 * Génère l'URL complète pour un item de navigation.
 */
const navHref = (item) => {
    const base = route(item.route);
    if (!item.query) return base;
    const q = new URLSearchParams(item.query).toString();
    return `${base}?${q}`;
};

/**
 * Vérifie si l'item de navigation est l'actuel.
 */
const isActive = (item) => {
    if (item.query?.tab === 'join') {
        return route().current('parties.web.create') && page.url.includes('tab=join');
    }
    return route().current(item.route) && !page.url.includes('tab=join');
};

/**
 * Gestion du Loader Global.
 * On affiche le loader seulement si la navigation prend plus de 350ms (Anti-flash UX).
 */
onMounted(() => {
    router.on('start', () => {
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

/**
 * Classes calculées pour le conteneur principal.
 */
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
                <header v-if="!hideLogo && !immersive" class="cave-logo-wrap cave-logo-wrap--mobile-only relative">
                    <div class="cave-logo-icon">🗿</div>
                    <h1 class="cave-logo-title">CityPlay</h1>
                    <p class="cave-logo-sub">Aventure urbaine</p>

                    <!-- Bouton Dashboard Admin (Mobile) -->
                    <Link
                        v-if="page.props.auth.user?.is_admin"
                        :href="route('admin.dashboard')"
                        class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-10 bg-[#C2410C] border-2 border-[#9A3412] rounded-xl flex items-center justify-center text-white shadow-lg active:scale-90 transition-transform"
                        title="Admin"
                    >
                        <i class="pi pi-cog" />
                    </Link>
                </header>
                <slot />
            </div>
        </div>

        <!-- Nav mobile (Admin Style) -->
        <nav v-if="!immersive" class="mobile-bottom-nav lg:hidden flex bg-[#D4C5B3]">
            <div class="nav-items-container">
                <!-- Home -->
                <Link :href="route('dashboard')" class="nav-item-bottom" :class="{ 'active': route().current('dashboard') }">
                    <i class="pi pi-home"></i>
                    <span>Home</span>
                </Link>

                <!-- Classement -->
                <Link :href="route('player.classement')" class="nav-item-bottom" :class="{ 'active': route().current('player.classement') }">
                    <i class="pi pi-trophy"></i>
                    <span>Elite</span>
                </Link>

                <!-- Jouer (Center FAB) -->
                <div class="nav-item-center-wrapper">
                    <Link :href="route('parties.web.create')" class="nav-item-center">
                        <div class="center-btn-inner bg-[#FCD116] border-[#4A3525]">
                            <i class="pi pi-play text-[#4A3525]"></i>
                        </div>
                        <span class="text-[#4A3525]">Jouer</span>
                    </Link>
                </div>

                <!-- Map/Parcours -->
                <Link :href="route('parties.web.create')" class="nav-item-bottom" :class="{ 'active': route().current('parties.web.create') && !page.url.includes('tab=join') }">
                    <i class="pi pi-map"></i>
                    <span>Quêtes</span>
                </Link>

                <!-- Plus Menu -->
                <button class="nav-item-bottom" :class="{ 'active': isMobileMenuOpen }" @click="toggleMobileMenu">
                    <i class="pi pi-ellipsis-h"></i>
                    <span>Plus</span>
                </button>
            </div>
        </nav>

        <!-- Mobile Action Sheet (Admin Style) -->
        <div v-if="isMobileMenuOpen" class="mobile-sheet-overlay" @click="closeMobileMenu"></div>
        <div class="mobile-action-sheet" :class="{ 'open': isMobileMenuOpen }">
            <div class="sheet-handle" @click="closeMobileMenu"></div>
            <div class="sheet-content">
                <h3 class="sheet-title">Aventure Menu</h3>
                <Link :href="route('profile.edit')" class="sheet-item" @click="closeMobileMenu">
                    <i class="pi pi-cog"></i>
                    <span>Réglages Profil</span>
                </Link>
                <Link :href="route('parties.web.create', { tab: 'join' })" class="sheet-item" @click="closeMobileMenu">
                    <i class="pi pi-users"></i>
                    <span>Rejoindre Guilde</span>
                </Link>
                <Link v-if="page.props.auth.user?.is_admin" :href="route('admin.dashboard')" class="sheet-item" @click="closeMobileMenu">
                    <i class="pi pi-shield"></i>
                    <span>Dashboard Admin</span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="sheet-item text-red-600" @click="closeMobileMenu">
                    <i class="pi pi-sign-out text-red-600"></i>
                    <span>Déconnexion</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Import gaming fonts */
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&family=Bungee&display=swap');

.font-bungee { font-family: 'Bungee', cursive; }
.font-fredoka { font-family: 'Fredoka', sans-serif; }

/* Existing CavePlay styles... */

/* --- MOBILE BOTTOM NAV (Admin Style) --- */
.mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 75px;
    border-radius: 25px 25px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    z-index: 100;
    align-items: center;
    padding: 0 10px;
    border-top: 2px solid rgba(74, 53, 37, 0.1);
}

.nav-items-container {
    display: flex;
    width: 100%;
    justify-content: space-around;
    align-items: flex-end;
    height: 100%;
    padding-bottom: 12px;
    position: relative;
}

.nav-item-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    color: rgba(74, 53, 37, 0.5);
    text-decoration: none;
    font-size: 10px;
    font-weight: 700;
    width: 20%;
    background: transparent;
    border: none;
    height: 100%;
    position: relative;
    font-family: 'Fredoka', sans-serif;
    text-transform: uppercase;
}

.nav-item-bottom i {
    font-size: 22px;
    transition: all 0.3s ease;
}

.nav-item-bottom.active {
    color: #4A3525;
}

.nav-item-bottom.active i {
    transform: translateY(-3px);
}

.nav-item-center-wrapper {
    width: 20%;
    display: flex;
    justify-content: center;
    position: relative;
    height: 100%;
}

.nav-item-center {
    position: absolute;
    bottom: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    font-family: 'Bungee', cursive;
    text-transform: uppercase;
}

.center-btn-inner {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: translateY(-15px);
    border-width: 3px;
    border-style: solid;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.center-btn-inner:active {
    transform: translateY(-12px) scale(0.9);
}

.center-btn-inner i {
    font-size: 24px;
}

/* Action Sheet */
.mobile-sheet-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.mobile-action-sheet {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #D4C5B3;
    border-radius: 30px 30px 0 0;
    z-index: 1001;
    transform: translateY(100%);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 1.5rem 1.5rem 3rem;
    border-top: 4px solid #4A3525;
}

.mobile-action-sheet.open {
    transform: translateY(0);
}

.sheet-handle {
    width: 50px;
    height: 5px;
    background: rgba(74, 53, 37, 0.2);
    border-radius: 3px;
    margin: 0 auto 2rem;
}

.sheet-title {
    font-family: 'Bungee', cursive;
    font-size: 0.85rem;
    font-weight: 700;
    color: #4A3525;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 1.5rem;
    text-align: center;
}

.sheet-item {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 1rem;
    color: #4A3525;
    text-decoration: none;
    font-weight: 700;
    font-family: 'Fredoka', sans-serif;
    border-bottom: 1px solid rgba(74, 53, 37, 0.1);
    border-radius: 12px;
    transition: background 0.2s;
}

.sheet-item:active {
    background: rgba(74, 53, 37, 0.05);
}

.sheet-item i {
    font-size: 1.4rem;
    color: #4A3525;
    width: 28px;
    text-align: center;
}

/* Rest of CavePlayLayout styles... */
</style>
