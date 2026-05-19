<template>
    <AuthenticatedLayout>
        <Head title="Débriefing de mission" />

        <div class="play-mat min-h-screen text-[#f5e8c7] pb-12 overflow-hidden relative">

            <!-- HEADER -->
            <div class="gsap-item bg-[#1f140e] border-b-[4px] border-[#5c4033] p-10 text-center shadow-[0_10px_20px_rgba(0,0,0,0.5)] relative z-10">
                <div class="relative inline-block mb-4">
                    <div class="absolute inset-0 bg-[#FF9500] rounded-full blur-2xl opacity-20"></div>
                    <div class="w-24 h-24 bg-[#2a1c14] border-4 border-[#FF9500] rounded-[2rem] flex items-center justify-center mx-auto relative z-10 shadow-inner">
                        <i class="pi pi-flag-fill text-4xl text-[#FF9500] drop-shadow-[0_0_8px_#FF9500]"></i>
                    </div>
                </div>
                <h1 class="card-title text-4xl text-[#f5e8c7] uppercase tracking-tighter leading-none">Quête Accomplie</h1>
                <p class="card-badge text-[10px] text-[#FF9500]/80 uppercase tracking-[0.4em] mt-3">Livre des Chroniques</p>
            </div>

            <main class="p-4 md:p-6 max-w-2xl mx-auto space-y-8 relative z-10 -mt-6">
                <!-- FINAL SCORE CARD -->
                <div class="gsap-item board-card bg-[#2a1c14] p-10 rounded-[2.5rem] shadow-2xl border-2 border-[#5c4033] text-center space-y-4">
                    <span class="card-badge text-[10px] uppercase tracking-[0.4em] text-[#FF9500]/50">Gloire Amassée</span>
                    <div class="text-8xl font-black text-[#f5e8c7] tracking-tighter drop-shadow-[0_0_15px_rgba(255,149,0,0.5)] font-serif">
                        {{ progression?.score || 0 }}
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <i class="pi pi-bolt text-[#FF9500]"></i>
                        <span class="card-badge text-sm text-[#f5e8c7]">Points d'Aventure</span>
                    </div>
                </div>

                <!-- MESSAGE FIN DE PARTIE -->
                <div v-if="partie.environnement?.message_fin" class="gsap-item bg-[#1f140e] p-8 rounded-[2rem] border-2 border-[#5c4033] shadow-inner text-center space-y-2">
                    <span class="card-badge text-[8px] uppercase tracking-[0.2em] text-[#FF9500]">Épilogue</span>
                    <p class="text-[#f5e8c7]/80 leading-relaxed italic text-xl font-serif">
                        <i class="pi pi-quote-left text-[#FF9500]/30 mr-2"></i>
                        "{{ partie.environnement.message_fin }}"
                    </p>
                </div>

                <!-- STATS GRID -->
                <div class="gsap-item grid grid-cols-2 gap-6">
                    <div class="bg-[#1f140e] p-8 rounded-[2rem] border border-[#5c4033] shadow-lg text-center space-y-2">
                        <span class="card-badge text-[8px] uppercase tracking-[0.2em] text-[#FF9500]/50">Épreuves</span>
                        <div class="text-4xl font-black text-green-500 drop-shadow-[0_0_10px_rgba(34,197,94,0.3)]">{{ progression.lieux_decouverts?.length || 0 }}</div>
                        <p class="card-badge text-[8px] text-[#f5e8c7]/50 uppercase">Surmontées</p>
                    </div>
                    <div class="bg-[#1f140e] p-8 rounded-[2rem] border border-[#5c4033] shadow-lg text-center space-y-2">
                        <span class="card-badge text-[8px] uppercase tracking-[0.2em] text-[#FF9500]/50">Parcours</span>
                        <div class="text-4xl font-black text-blue-400 drop-shadow-[0_0_10px_rgba(96,165,250,0.3)]">
                            {{ (distanceTotale / 1000).toFixed(1) || '3.2' }}<span class="text-lg">km</span>
                        </div>
                        <p class="card-badge text-[8px] text-[#f5e8c7]/50 uppercase">Explorés</p>
                    </div>
                </div>

                <!-- ENIGMES NON RESOLUES -->
                <div v-if="enigmesNonResolues?.length" class="gsap-item bg-[#2a1c14] p-8 rounded-[2rem] border-2 border-red-900/30 shadow-xl space-y-6">
                    <h3 class="card-badge text-[10px] text-red-500/50 uppercase tracking-[0.2em] border-b border-red-900/20 pb-4 text-center">Mystères Non Résolus</h3>
                    <div class="space-y-4">
                        <div v-for="enigme in enigmesNonResolues" :key="enigme.id" class="flex items-center gap-4 bg-[#1f140e] p-4 rounded-xl border border-red-900/10">
                            <div class="w-3 h-3 bg-red-600 rounded-sm rotate-45 shadow-[0_0_8px_#ef4444]"></div>
                            <span class="card-badge text-xs text-[#f5e8c7]/60">{{ enigme.titre }}</span>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="gsap-item space-y-4 pt-4">
                    <Link :href="route('dashboard')" class="block">
                        <button class="w-full p-6 rounded-xl bg-[#FF9500] text-[#1f140e] card-badge hover:bg-[#ffaa33] active:scale-95 transition-all flex items-center justify-center gap-3 border border-[#3f2a1e] shadow-[0_6px_0_#cc7a00] active:translate-y-[6px] active:shadow-none text-sm">
                            <i class="pi pi-home text-xl"></i>
                            Quitter la Table
                        </button>
                    </Link>

                    <button
                        @click="showLogoutDialog = true"
                        class="w-full p-6 rounded-xl bg-transparent text-red-500/70 border border-red-900/30 card-badge text-[10px] hover:bg-red-900/20 transition-all flex items-center justify-center gap-2"
                    >
                        <i class="pi pi-power-off"></i>
                        Quitter le Jeu (Déconnexion)
                    </button>
                </div>
            </main>

            <!-- GAME DIALOGS -->
            <Dialog v-model:visible="showLogoutDialog" modal header="Clôture de Session" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-8 space-y-6">
                    <p class="text-sm text-[#f5e8c7]/80 font-serif italic text-center">
                        Souhaitez-vous conserver vos chroniques pour vos futures aventures ou brûler ce livre à jamais ?
                    </p>

                    <div class="space-y-4">
                        <button 
                            @click="handleLogout"
                            class="w-full p-6 bg-[#1f140e] rounded-xl border border-[#5c4033] text-left hover:border-[#FF9500] transition-all group"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="card-badge text-[#FF9500] text-xs">Fermer le Livre</span>
                                <i class="pi pi-chevron-right text-[#FF9500]/50 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                            <p class="text-[9px] text-[#f5e8c7]/40 card-badge">
                                Conservez vos succès pour la suite.
                            </p>
                        </button>

                        <button 
                            @click="showDeleteConfirm = true; showLogoutDialog = false"
                            class="w-full p-6 bg-red-900/20 rounded-xl border border-red-900/50 text-left hover:bg-red-900/40 transition-all group"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="card-badge text-red-500 text-xs">Brûler le Livre</span>
                                <i class="pi pi-trash text-red-500/50"></i>
                            </div>
                            <p class="text-[9px] text-red-500/40 card-badge">
                                Effacez toutes traces de votre existence.
                            </p>
                        </button>
                    </div>
                </div>
            </Dialog>

            <Dialog v-model:visible="showDeleteConfirm" modal header="Rituel d'Oblitération" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-8 space-y-4 text-center">
                    <i class="pi pi-fire text-4xl text-red-500 mb-2 drop-shadow-[0_0_10px_red]"></i>
                    <p class="text-[#f5e8c7]/80 font-serif italic text-lg">
                        "Toutes vos progressions seront réduites en cendres. Êtes-vous certain de vouloir accomplir ce rituel ?"
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full px-6 pb-6">
                        <button @click="showDeleteConfirm = false" class="flex-1 p-3 rounded-xl bg-[#1f140e] text-[#f5e8c7]/50 card-badge border border-[#5c4033] hover:text-[#f5e8c7]">Annuler</button>
                        <button @click="handleDeleteProfile" class="flex-1 p-3 rounded-xl bg-red-900/80 text-red-100 card-badge shadow-inner hover:bg-red-700">Confirmer</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { gsap } from 'gsap';
import Dialog from 'primevue/dialog';

const props = defineProps({
    partie: Object,
    progression: Object,
    enigmesNonResolues: Array,
    distanceTotale: Number
});

const showLogoutDialog = ref(false);
const showDeleteConfirm = ref(false);

const logoutForm = useForm({});
const deleteProfileForm = useForm({});

const handleLogout = () => {
    logoutForm.post(route('logout'));
};

const handleDeleteProfile = () => {
    deleteProfileForm.delete(route('profile.destroy'));
};

onMounted(() => {
    gsap.set('.gsap-item', { y: 50, opacity: 0 });
    gsap.to('.gsap-item', { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: 'back.out(1.2)' });
});
</script>

<style scoped>
/* Resets and specific styling handled globally in app.css (.game-dialog, .play-mat) */
</style>