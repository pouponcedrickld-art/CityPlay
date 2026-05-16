<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import Toast from 'primevue/toast';
import VideoLoader from '@/Components/VideoLoader.vue';

// Import du logo
import logoWhite from "./logo_whrite.jpg";

const page = usePage();
const visible = ref(false);
const showLoader = ref(false);

onMounted(() => {
    router.on('start', () => {
        showLoader.value = true;
    });
    router.on('finish', () => {
        setTimeout(() => {
            showLoader.value = false;
        }, 1200);
    });
    router.on('error', () => {
        showLoader.value = false;
    });
});

// Menu adaptatif selon le rôle (Admin ou Player)
const menuItems = computed(() => {
    const isAdmin = page.props.auth.user?.is_admin;
    
    if (isAdmin) {
        return [
            { label: "Dashboard", icon: "pi pi-th-large", route: "admin.dashboard" },
            { label: "Lieux", icon: "pi pi-map-marker", route: "admin.lieux.index" },
            { label: "Énigmes", icon: "pi pi-box", route: "admin.enigmes.index" },
            { label: "Parcours", icon: "pi pi-map", route: "admin.environnements.index" },
            { label: "Paramètres", icon: "pi pi-cog", route: "admin.parametres" },
        ];
    }
    
    return [
        { label: "Mes Parties", icon: "pi pi-play", route: "player.dashboard" },
        { label: "Nouveau Parcours", icon: "pi pi-plus-circle", route: "parties.create" },
        { label: "Mon Profil", icon: "pi pi-user", route: "profile.edit" },
    ];
});

const isRouteActive = (routeName) => {
    return page.url.startsWith('/' + routeName.split('.')[0]);
};

const toggleSidebar = () => {
    visible.value = !visible.value;
};

const closeSidebar = () => {
    if (window.innerWidth < 1024) {
        visible.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col lg:flex-row">
        <VideoLoader :show="showLoader" />
        <Toast />

        <!-- HEADER MOBILE (Barre supérieure fixe) -->
        <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-orange-100 px-4 flex items-center justify-between z-50 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center p-1 border border-orange-100">
                    <img :src="logoWhite" alt="Logo" class="w-full h-full object-contain" />
                </div>
                <h1 class="font-black text-lg tracking-tight text-[#FF9500] uppercase">CityPlay</h1>
            </div>
            <button
                @click="toggleSidebar"
                class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-[#FF9500] active:scale-90 transition-transform"
            >
                <i :class="visible ? 'pi pi-times' : 'pi pi-bars'" class="text-lg"></i>
            </button>
        </header>

        <!-- Sidebar / Navigation Drawer -->
        <aside
            class="fixed inset-y-0 left-0 w-72 bg-white z-[60] lg:z-40 transition-transform duration-300 transform lg:translate-x-0 border-r border-orange-100 flex flex-col shadow-2xl lg:shadow-none"
            :class="visible ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Sidebar Background & Branding -->
            <div class="relative h-48 flex items-end p-6 overflow-hidden shrink-0">
                <div class="absolute inset-0 z-0 bg-cover bg-center opacity-10" :style="{ backgroundImage: `url(${logoWhite})` }"></div>
                <div class="absolute inset-0 z-10 bg-gradient-to-t from-white via-white/80 to-transparent"></div>
                
                <div class="relative z-20 flex items-center gap-4 w-full">
                    <div class="w-14 h-14 rounded-2xl bg-white shadow-xl flex items-center justify-center p-1 border border-orange-50">
                        <img :src="logoWhite" alt="Logo" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h2 class="font-black text-xl tracking-tighter text-[#FF9500] leading-none uppercase">CityPlay</h2>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-900/40 mt-1">
                            {{ page.props.auth.user?.is_admin ? 'Admin Space' : 'Explorer Mode' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
                <Link
                    v-for="item in menuItems"
                    :key="item.label"
                    :href="route(item.route)"
                    @click="closeSidebar"
                    class="flex items-center p-4 rounded-2xl transition-all duration-300 group"
                    :class="[
                        route().current(item.route + '*') || isRouteActive(item.route)
                            ? 'bg-gradient-to-r from-[#FF9500] to-[#FF7B00] text-white shadow-lg shadow-orange-200 active-glow'
                            : 'text-orange-900/60 hover:bg-orange-50 hover:text-orange-950'
                    ]"
                >
                    <i :class="[item.icon, 'text-lg mr-4 transition-transform group-hover:scale-110']"></i>
                    <span class="text-xs font-black uppercase tracking-widest">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Footer Sidebar (User Info) -->
            <div class="p-4 border-t border-orange-50 bg-orange-50/30">
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center p-3 rounded-xl text-red-500 hover:bg-red-50 transition-colors group">
                    <i class="pi pi-power-off mr-3 group-hover:rotate-12 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Déconnexion</span>
                </Link>
            </div>
        </aside>

        <!-- Overlay Mobile -->
        <div
            v-if="visible"
            @click="visible = false"
            class="fixed inset-0 bg-orange-950/20 backdrop-blur-sm z-[55] lg:hidden transition-opacity duration-300"
        ></div>

        <!-- Main Content Area -->
        <main class="flex-1 pt-16 lg:pt-0 lg:pl-72 min-h-screen relative z-0">
            <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.active-glow {
    filter: drop-shadow(0 4px 12px rgba(255, 149, 0, 0.3));
}

@media (max-width: 1023px) {
    aside {
        box-shadow: 20px 0 50px -10px rgba(0, 0, 0, 0.1);
    }
}

/* Suppression des anciens styles redondants */
.sidebar-main {
    font-family: ui-sans-serif, system-ui, sans-serif;
    backdrop-filter: blur(10px);
}
</style>