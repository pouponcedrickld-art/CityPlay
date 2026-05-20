<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { isPlaying, toggleAudio, playAudio } from '@/audioPlayer';

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

    // Auto-play attempt on first user interaction if they haven't explicitly paused it
    const handleFirstInteraction = () => {
        if (!isPlaying.value) {
            playAudio();
        }
        window.removeEventListener('click', handleFirstInteraction);
    };
    window.addEventListener('click', handleFirstInteraction);

    onUnmounted(() => {
        window.removeEventListener('click', handleFirstInteraction);
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
    <div class="min-h-screen font-sans selection:bg-emerald-100 selection:text-emerald-950" 
         :class="isGameMode ? 'bg-[#0f0f0f]' : (isDark ? 'bg-[#040508]' : 'bg-[#f5f0e8]')">
        
        <VideoLoader :show="showLoader" />

        <!-- DESKTOP SIDEBAR (Cachée en mode jeu pour l'immersion) -->
        <aside v-if="!isMobile && !isGameMode" 
               class="fixed left-0 top-0 bottom-0 w-72 z-40 p-8 flex flex-col transition-colors duration-300"
               :class="isDark 
                 ? 'bg-[#10121b] border-r border-white/5' 
                 : 'bg-[#faf6ee] border-r border-[#c8a96e]/40 shadow-[2px_0_20px_rgba(139,105,20,0.08)]'">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg"
                     :class="isDark ? 'bg-orange-950 shadow-orange-900/20' : 'bg-[#1a1208] shadow-[#8b6914]/20'">
                    <ApplicationLogo class="w-7 h-7 fill-white" />
                </div>
                <span class="text-xl font-black uppercase tracking-tighter"
                      :class="isDark ? 'text-orange-950' : 'text-[#1a1208]'">CityPlay</span>
            </div>

            <nav class="flex-1 space-y-2">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.label"
                    :href="menuItemHref(item)"
                    class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 group"
                    :class="route().current(item.route) 
                      ? (isDark ? 'bg-orange-50 text-orange-950 shadow-inner' : 'bg-[#059669]/10 text-[#1a1208] border border-[#059669]/30 shadow-inner') 
                      : (isDark ? 'text-orange-900/40 hover:bg-orange-50/50 hover:text-orange-900' : 'text-[#6b4c1e]/60 hover:bg-[#c8a96e]/10 hover:text-[#1a1208]')"
                >
                    <i :class="[item.icon, route().current(item.route) 
                      ? (isDark ? 'text-orange-600' : 'text-[#059669]') 
                      : (isDark ? 'text-orange-300 group-hover:text-orange-400' : 'text-[#c8a96e] group-hover:text-[#059669]')]" 
                       class="text-lg"></i>
                    <span class="font-black uppercase tracking-widest text-[11px]">{{ item.label }}</span>
                </Link>

                <div v-if="$page.props.auth.user.is_admin || $page.props.auth.user.roles?.includes('admin')" class="pt-8 space-y-2">
                    <p class="px-4 text-[9px] font-black uppercase tracking-[0.3em] mb-4"
                       :class="isDark ? 'text-orange-950/20' : 'text-[#8b6914]/40'">Administration</p>
                    <Link 
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-4 p-4 rounded-2xl transition-all"
                        :class="isDark ? 'text-orange-900/40 hover:bg-orange-50/50 hover:text-orange-900' : 'text-[#6b4c1e]/60 hover:bg-[#c8a96e]/10 hover:text-[#1a1208]'"
                    >
                        <i class="pi pi-shield text-lg" :class="isDark ? 'text-orange-300' : 'text-[#c8a96e]'"></i>
                        <span class="font-black uppercase tracking-widest text-[11px]">Panel Admin</span>
                    </Link>
                </div>
            </nav>

            <div class="pt-8" :class="isDark ? 'border-t border-orange-50' : 'border-t border-[#c8a96e]/30'">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="w-full flex items-center gap-4 p-4 rounded-2xl transition-all group"
                    :class="isDark ? 'text-red-400 hover:bg-red-50 hover:text-red-600' : 'text-red-500/70 hover:bg-red-50 hover:text-red-600'"
                >
                    <i class="pi pi-power-off text-lg group-hover:rotate-12 transition-transform"></i>
                    <span class="font-black uppercase tracking-widest text-[11px]">Déconnexion</span>
                </Link>
            </div>
        </aside>

        <!-- MOBILE HEADER (Caché en mode jeu) -->
        <header v-if="isMobile && !isGameMode" 
                class="fixed top-0 left-0 right-0 h-16 backdrop-blur-xl z-40 px-6 flex items-center justify-between transition-colors duration-300"
                :class="isDark 
                  ? 'bg-[#10121b]/90 border-b border-white/5' 
                  : 'bg-[#faf6ee]/90 border-b border-[#c8a96e]/40 shadow-sm'">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-6 h-6" :class="isDark ? 'fill-orange-950' : 'fill-[#1a1208]'" />
                <span class="text-sm font-black uppercase tracking-tighter"
                      :class="isDark ? 'text-orange-950' : 'text-[#1a1208]'">CityPlay</span>
            </div>
            
            <div class="flex items-center gap-3">
                <Link :href="route('profile.edit')" 
                      class="w-8 h-8 rounded-full flex items-center justify-center"
                      :class="isDark ? 'bg-orange-100' : 'bg-[#c8a96e]/20 border border-[#c8a96e]/40'">
                    <i class="pi pi-user text-xs" :class="isDark ? 'text-orange-600' : 'text-[#059669]'"></i>
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
        <nav v-if="isMobile && !isGameMode" 
             class="fixed bottom-0 left-0 right-0 h-20 z-40 px-6 flex items-center justify-around pb-4 transition-colors duration-300"
             :class="isDark 
               ? 'bg-[#10121b] border-t border-white/5' 
               : 'bg-[#faf6ee] border-t border-[#c8a96e]/40 shadow-[0_-2px_15px_rgba(139,105,20,0.08)]'">
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

        <!-- Floating Controls -->
        <div class="fixed right-6 z-50 flex flex-col gap-4" :class="(isMobile && !isGameMode) ? 'bottom-[100px]' : 'bottom-8'">
            <!-- Floating Music Switch -->
            <button 
              class="w-14 h-14 rounded-full flex items-center justify-center text-xl cursor-pointer transition-all duration-300 shadow-xl backdrop-blur-md border hover:scale-110 hover:rotate-12"
              :class="isDark ? 'bg-[#10121b]/90 border-orange-500/30 text-orange-500 hover:bg-gradient-to-br hover:from-orange-400 hover:to-orange-600 hover:text-black hover:border-transparent hover:shadow-[0_0_20px_rgba(255,149,0,0.4)]' : 'bg-[#faf6ee]/95 border-[#c8a96e]/40 text-[#059669] hover:bg-gradient-to-br hover:from-emerald-400 hover:to-emerald-600 hover:text-white hover:border-transparent hover:shadow-[0_0_20px_rgba(5,150,105,0.4)]'"
              @click="toggleAudio" 
              aria-label="Toggle Music"
              title="Musique de fond"
            >
              <i :class="isPlaying ? 'pi pi-volume-up' : 'pi pi-volume-off'" />
            </button>
            
            <!-- Floating Theme Toggle Switch -->
            <button 
              class="w-14 h-14 rounded-full flex items-center justify-center text-xl cursor-pointer transition-all duration-300 shadow-xl backdrop-blur-md border hover:scale-110 hover:rotate-12"
              :class="isDark ? 'bg-[#10121b]/90 border-orange-500/30 text-orange-500 hover:bg-gradient-to-br hover:from-orange-400 hover:to-orange-600 hover:text-black hover:border-transparent hover:shadow-[0_0_20px_rgba(255,149,0,0.4)]' : 'bg-[#faf6ee]/95 border-[#c8a96e]/40 text-[#059669] hover:bg-gradient-to-br hover:from-emerald-400 hover:to-emerald-600 hover:text-white hover:border-transparent hover:shadow-[0_0_20px_rgba(5,150,105,0.4)]'"
              @click="toggleTheme" 
              aria-label="Toggle Theme"
              title="Changer de Thème"
            >
              <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'" />
            </button>
        </div>
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
