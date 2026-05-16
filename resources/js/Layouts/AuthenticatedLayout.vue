<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import VideoLoader from '@/Components/VideoLoader.vue';

const showingNavigationDropdown = ref(false);
const showLoader = ref(false);

onMounted(() => {
    router.on('start', () => {
        showLoader.value = true;
    });
    router.on('finish', () => {
        // On laisse le loader un peu pour que la vidéo soit visible
        setTimeout(() => {
            showLoader.value = false;
        }, 1200); // Augmenté pour mieux voir la vidéo
    });
    router.on('error', () => {
        showLoader.value = false;
    });
});

const page = usePage();
const isMobile = ref(window.innerWidth < 1024);

window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 1024;
});

const menuItems = [
    { label: 'Dashboard', icon: 'pi pi-home', route: 'dashboard' },
    { label: 'Parcours', icon: 'pi pi-map', route: 'parties.create' },
    { label: 'Mon Profil', icon: 'pi pi-user', route: 'profile.edit' },
];
</script>

<template>
    <div class="min-h-screen bg-[#FDFCF0] font-sans selection:bg-orange-100 selection:text-orange-950">
        <VideoLoader :show="showLoader" />

        <!-- DESKTOP SIDEBAR -->
        <aside v-if="!isMobile" class="fixed left-0 top-0 bottom-0 w-72 bg-white border-r border-orange-100 z-40 p-8 flex flex-col">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-12 h-12 bg-orange-950 rounded-2xl flex items-center justify-center shadow-lg shadow-orange-900/20">
                    <ApplicationLogo class="w-7 h-7 fill-white" />
                </div>
                <span class="text-xl font-black text-orange-950 uppercase tracking-tighter">CityPlay</span>
            </div>

            <nav class="flex-1 space-y-2">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.route"
                    :href="route(item.route)"
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

        <!-- MOBILE HEADER -->
        <header v-if="isMobile" class="fixed top-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-xl border-b border-orange-100 z-40 px-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-6 h-6 fill-orange-950" />
                <span class="text-sm font-black text-orange-950 uppercase tracking-tighter">CityPlay</span>
            </div>
            
            <div class="flex items-center gap-3">
                <div v-if="$page.props.auth.user.is_admin" class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="pi pi-shield text-orange-600 text-xs"></i>
                </div>
                <Link :href="route('profile.edit')" class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center border border-orange-100 overflow-hidden">
                    <img v-if="$page.props.auth.user.avatar" :src="$page.props.auth.user.avatar" class="w-full h-full object-cover">
                    <i v-else class="pi pi-user text-orange-300"></i>
                </Link>
            </div>
        </header>

        <!-- CONTENT AREA -->
        <main 
            class="transition-all duration-500"
            :class="[
                isMobile ? 'pt-20 pb-24 px-4' : 'pl-80 pr-8 py-8',
            ]"
        >
            <div class="max-w-5xl mx-auto">
                <slot />
            </div>
        </main>

        <!-- MOBILE BOTTOM NAV (GAME STYLE) -->
        <nav v-if="isMobile" class="fixed bottom-6 left-6 right-6 h-16 bg-orange-950 rounded-2xl z-40 px-4 flex items-center justify-around shadow-2xl shadow-orange-900/40 border border-white/10">
            <Link 
                v-for="item in menuItems" 
                :key="item.route"
                :href="route(item.route)"
                class="flex flex-col items-center justify-center w-12 h-12 rounded-xl transition-all"
                :class="route().current(item.route) ? 'bg-orange-500 text-white shadow-lg -translate-y-2' : 'text-white/40'"
            >
                <i :class="item.icon" class="text-lg"></i>
                <span v-if="route().current(item.route)" class="text-[8px] font-black uppercase mt-1">OK</span>
            </Link>
            
            <Link 
                :href="route('logout')" 
                method="post" 
                as="button"
                class="flex flex-col items-center justify-center w-12 h-12 rounded-xl text-white/40"
            >
                <i class="pi pi-power-off text-lg"></i>
            </Link>
        </nav>

        <!-- GLOBAL MODALS (TOASTS ETC) -->
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
