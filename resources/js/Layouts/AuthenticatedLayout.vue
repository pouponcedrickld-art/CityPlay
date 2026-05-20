<script setup>
import { ref, onMounted, computed } from 'vue';

const isDark = ref(true);

const initTheme = () => {
  isDark.value = localStorage.getItem('theme') !== 'light';
  if (isDark.value) {
    document.documentElement.classList.remove('light-theme');
  } else {
    document.documentElement.classList.add('light-theme');
  }
};

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    localStorage.setItem('theme', 'dark');
    document.documentElement.classList.remove('light-theme');
  } else {
    localStorage.setItem('theme', 'light');
    document.documentElement.classList.add('light-theme');
  }
};
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import VideoLoader from '@/Components/VideoLoader.vue';

const page = usePage();
const showingNavigationDropdown = ref(false);
const showLoader = ref(false);

// Détecte si on est en mode "Jeu immersif"
const isGameMode = computed(() => {
    return page.component.startsWith('Player/Enigme') || 
           page.component.startsWith('Player/Success') || 
           page.component.startsWith('Player/Failure') ||
           page.component.startsWith('Player/Summary');
});

onMounted(() => {
    initTheme();

    router.on('start', () => {
        showLoader.value = true;
    });
    router.on('finish', () => {
        // On laisse le loader un peu pour que la vidéo soit visible
        setTimeout(() => {
            showLoader.value = false;
        }, 1200);
    });
    router.on('error', () => {
        showLoader.value = false;
    });
});

const isMobile = ref(window.innerWidth < 1024);

window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 1024;
});

const menuItems = [
    { label: 'Dashboard', icon: 'pi pi-home', route: 'dashboard' },
    { label: 'Parcours', icon: 'pi pi-map', route: 'parties.web.create' },
    { label: 'Rejoindre', icon: 'pi pi-link', route: 'parties.web.create', query: { tab: 'join' } },
    { label: 'Mon Profil', icon: 'pi pi-user', route: 'profile.edit' },
];

const menuItemHref = (item) => {
    const base = route(item.route);
    if (!item.query) {
        return base;
    }
    const params = Object.entries(item.query)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');
    return `${base}?${params}`;
};
</script>

<template>
    <div class="min-h-screen font-sans selection:bg-orange-100 selection:text-orange-950" 
         :class="isGameMode ? 'bg-[#0f0f0f]' : 'bg-[#FDFCF0]'">
        
        <VideoLoader :show="showLoader" />

        <!-- DESKTOP SIDEBAR (Cachée en mode jeu pour l'immersion) -->
        <aside v-if="!isMobile && !isGameMode" class="fixed left-0 top-0 bottom-0 w-72 bg-white border-r border-orange-100 z-40 p-8 flex flex-col">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-12 h-12 bg-orange-950 rounded-2xl flex items-center justify-center shadow-lg shadow-orange-900/20">
                    <ApplicationLogo class="w-7 h-7 fill-white" />
                </div>
                <span class="text-xl font-black text-orange-950 uppercase tracking-tighter">CityPlay</span>
            </div>

            <nav class="flex-1 space-y-2">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.label"
                    :href="menuItemHref(item)"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 group"
                    :class="route().current(item.route) ? 'bg-orange-50 text-orange-950 shadow-inner' : 'text-orange-900/40 hover:bg-orange-50/50 hover:text-orange-900'"
                >
                    <i :class="[item.icon, route().current(item.route) ? 'text-orange-600' : 'text-orange-300 group-hover:text-orange-400']" class="text-lg"></i>
                    <span class="font-black uppercase tracking-widest text-[11px]">{{ item.label }}</span>
                </Link>

                <div v-if="$page.props.auth.user.is_admin || $page.props.auth.user.roles?.includes('admin')" class="pt-8 space-y-2">
                    <p class="px-4 text-[9px] font-black text-orange-950/20 uppercase tracking-[0.3em] mb-4">Administration</p>
                    <Link 
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-4 p-4 rounded-2xl text-orange-900/40 hover:bg-orange-50/50 hover:text-orange-900 transition-all"
                    >
                        <i class="pi pi-shield text-lg text-orange-300"></i>
                        <span class="font-black uppercase tracking-widest text-[11px]">Panel Admin</span>
                    </Link>
                </div>
            </nav>

            <div class="pt-8 border-t border-orange-50">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="w-full flex items-center gap-4 p-4 rounded-2xl text-red-400 hover:bg-red-50 hover:text-red-600 transition-all group"
                >
                    <i class="pi pi-power-off text-lg group-hover:rotate-12 transition-transform"></i>
                    <span class="font-black uppercase tracking-widest text-[11px]">Déconnexion</span>
                </Link>
            </div>
        </aside>

        <!-- MOBILE HEADER (Caché en mode jeu) -->
        <header v-if="isMobile && !isGameMode" class="fixed top-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-xl border-b border-orange-100 z-40 px-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-6 h-6 fill-orange-950" />
                <span class="text-sm font-black text-orange-950 uppercase tracking-tighter">CityPlay</span>
            </div>
            
            <div class="flex items-center gap-3">
                <Link :href="route('profile.edit')" class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="pi pi-user text-orange-600 text-xs"></i>
                </Link>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1" :class="[!isGameMode && !isMobile ? 'pl-72' : '', !isGameMode && isMobile ? 'pt-16' : '']">
            <!-- Global Flash Messages -->
            <div v-if="$page.props.flash?.error" class="fixed top-20 right-6 z-[100] max-w-sm animate-fade-in">
                <div class="bg-red-600 text-white p-4 rounded-2xl shadow-2xl border border-white/20 flex items-center gap-3">
                    <i class="pi pi-exclamation-triangle text-xl"></i>
                    <p class="text-xs font-bold uppercase tracking-tight">{{ $page.props.flash.error }}</p>
                </div>
            </div>
            <div v-if="$page.props.flash?.success" class="fixed top-20 right-6 z-[100] max-w-sm animate-fade-in">
                <div class="bg-green-600 text-white p-4 rounded-2xl shadow-2xl border border-white/20 flex items-center gap-3">
                    <i class="pi pi-check-circle text-xl"></i>
                    <p class="text-xs font-bold uppercase tracking-tight">{{ $page.props.flash.success }}</p>
                </div>
            </div>

            <div :class="isGameMode ? '' : 'p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto'">
                <slot />
            </div>
        </main>

        <!-- MOBILE NAV BAR (Uniquement hors mode jeu) -->
        <nav v-if="isMobile && !isGameMode" class="fixed bottom-0 left-0 right-0 h-20 bg-white border-t border-orange-100 z-40 px-6 flex items-center justify-around pb-4">
            <Link 
                v-for="item in menuItems" 
                :key="item.label"
                :href="menuItemHref(item)"
                class="flex flex-col items-center gap-1"
                :class="route().current(item.route) ? 'text-orange-600' : 'text-orange-300'"
            >
                <i :class="item.icon" class="text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-widest">{{ item.label }}</span>
            </Link>
        </nav>

        <!-- Floating Theme Toggle Switch -->
        <button 
          class="theme-switch-float" 
          @click="toggleTheme" 
          aria-label="Toggle Theme"
          title="Changer de Thème"
        >
          <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'" />
        </button>
    </div>
</template>

<style>
/* Game-like background pattern */
body {
    background-image: radial-gradient(#FF950010 1px, transparent 1px);
    background-size: 32px 32px;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
