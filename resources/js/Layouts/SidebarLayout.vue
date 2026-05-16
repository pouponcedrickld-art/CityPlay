<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import Toast from 'primevue/toast';
// import Button from "primevue/button";

// Import du logo
import logoWhite from "./logo_whrite.jpg";

const visible = ref(false);
const activeLabel = ref("Tableau de bord");

const menuItems = [
    { label: "Tableau de bord", icon: "pi pi-th-large", route: "dashboard" },
    { label: "Chasses au trésor", icon: "pi pi-map-marker", route: "dashboard" },
    { label: "Énigmes", icon: "pi pi-box", route: "enigmes.index" },
    { label: "Joueurs", icon: "pi pi-users", route: "dashboard" },
    { label: "Statistiques", icon: "pi pi-chart-bar", route: "dashboard" },
    { label: "Paramètres", icon: "pi pi-cog", route: "dashboard" },
];

const setActive = (item) => {
    activeLabel.value = item.label;

    if (window.innerWidth < 1024) {
        visible.value = false;
    }
};

const toggleSidebar = () => {
    visible.value = !visible.value;
};
</script>

<template>
    <Toast />

    <!-- Bouton Mobile -->
    <button
        @click="toggleSidebar"
        class="lg:hidden fixed top-4 left-4 z-50 w-12 h-12 rounded-2xl bg-white shadow-xl border border-orange-100 flex items-center justify-center text-[#FF9500]"
    >
        <i class="pi pi-bars text-xl"></i>
    </button>

    <!-- Overlay Mobile -->
    <div
        v-if="visible"
        @click="visible = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden transition-all duration-300"
    ></div>

    <!-- Sidebar -->
    <aside
        class="fixed top-0 left-0 h-screen w-[85%] max-w-72 lg:w-72 flex flex-col z-40 transition-all duration-500 border-r sidebar-main overflow-hidden border-orange-100 text-[#FF9500]"
        :class="[
            visible
                ? 'translate-x-0'
                : '-translate-x-full lg:translate-x-0'
        ]"
    >

        <!-- Background -->
        <div
            class="absolute inset-0 z-0 bg-cover bg-center"
            :style="{ backgroundImage: `url(${logoWhite})` }"
        ></div>

        <!-- Overlay -->
        <div
            class="absolute inset-0 z-10 bg-gradient-to-b from-white/95 via-white/40 to-white/95"
        ></div>

        <!-- Content -->
        <div class="relative z-20 flex flex-col h-full">

            <!-- Header -->
            <div
                class="p-5 sm:p-6 border-b border-orange-200/40 h-40 sm:h-48 flex items-end"
            >
                <div class="w-full flex items-end justify-between">

                    <!-- Logo -->
                    <div
                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden shadow-xl bg-white flex items-center justify-center p-1 border border-white/40 backdrop-blur-md shrink-0"
                    >
                        <img
                            :src="logoWhite"
                            alt="Logo"
                            class="w-full h-full object-contain"
                        />
                    </div>

                    <!-- Texte -->
                    <div class="flex flex-col items-end pb-1">
                        <h2
                            class="font-black text-xl sm:text-2xl tracking-tight uppercase text-[#FF9500] leading-none active-glow"
                        >
                            CITYPLAY
                        </h2>

                        <p
                            class="mt-1 text-[8px] sm:text-[9px] uppercase font-extrabold tracking-[0.25em] sm:tracking-[0.30em] text-orange-900/70"
                        >
                            Admin Space
                        </p>
                    </div>

                </div>
            </div>

            <!-- Navigation -->
            <nav
                class="flex-1 px-3 sm:px-4 py-6 sm:py-8 space-y-2 overflow-y-auto custom-scrollbar"
            >
                <Link
                    v-for="item in menuItems"
                    :key="item.label"
                    :href="route(item.route)"
                    @click="setActive(item)"
                    class="nav-item group"
                    :class="[
                        activeLabel === item.label
                            ? 'nav-active-light active-glow'
                            : 'text-orange-950 hover:bg-orange-100/60'
                    ]"
                >
                    <i
                        :class="[
                            item.icon,
                            'text-lg sm:text-xl mr-3 sm:mr-4 transition-transform duration-300 group-hover:scale-110'
                        ]"
                    ></i>

                    <span
                        class="text-[11px] sm:text-[12px] font-bold uppercase tracking-wide text-inherit"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

        </div>
    </aside>
</template>

<style scoped>

/* Typography */
.sidebar-main {
    font-family:
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;

    backdrop-filter: blur(10px);
}

/* Navigation Item */
.nav-item {
    display: flex;
    align-items: center;

    padding: 0.9rem 1rem;

    border-radius: 16px;

    cursor: pointer;

    transition:
        all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Desktop */
@media (min-width: 640px) {
    .nav-item {
        padding: 0.95rem 1.2rem;
    }
}

/* Hover */
.nav-item:hover {
    transform: translateX(4px);
}

/* Active */
.nav-active-light {
    background: linear-gradient(
        135deg,
        #ff9500,
        #ff7b00
    );

    color: white;

    box-shadow:
        0 12px 24px rgba(255, 149, 0, 0.25);
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

/* Glow */
.active-glow {
    filter: drop-shadow(
        0 0 8px rgba(255, 149, 0, 0.5)
    );
}
</style>