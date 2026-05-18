<template>
    <AuthenticatedLayout>
        <Head title="Débriefing de mission" />

        <div class="game-summary min-h-screen bg-[#0f0f0f] text-white pb-12 overflow-hidden relative">
            <!-- BACKGROUND DECORATION -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_center,_#FF9500_0%,_transparent_70%)] opacity-20"></div>
            </div>

            <!-- HEADER -->
            <div class="hud-header bg-[#1a1a1a] border-b-2 border-[#FF9500]/30 p-10 text-center shadow-2xl relative z-10">
                <div class="relative inline-block mb-4">
                    <div class="absolute inset-0 bg-[#FF9500] rounded-full blur-2xl opacity-20 animate-pulse"></div>
                    <div class="w-24 h-24 bg-[#1a1a1a] border-4 border-[#FF9500] rounded-[2rem] flex items-center justify-center mx-auto relative z-10">
                        <i class="pi pi-flag-fill text-4xl text-[#FF9500]"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-black text-white uppercase tracking-tighter leading-none">Mission Accomplie</h1>
                <p class="text-[10px] font-black text-[#FF9500] uppercase tracking-[0.4em] mt-3">Débriefing des données d'exploration</p>
            </div>

            <main class="p-6 max-w-2xl mx-auto space-y-8 relative z-10 -mt-6">
                <!-- FINAL SCORE CARD -->
                <div class="bg-[#1a1a1a] p-10 rounded-[2.5rem] shadow-2xl border-2 border-[#FF9500]/20 text-center space-y-4 animate-fade-in-up">
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-white/40">Score de Performance</span>
                    <div class="text-8xl font-black text-white tracking-tighter drop-shadow-[0_0_15px_rgba(255,149,0,0.5)]">
                        {{ progression?.score || 0 }}
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <i class="pi pi-bolt text-[#FF9500]"></i>
                        <span class="text-sm font-black uppercase tracking-widest text-white">Points CityPlay</span>
                    </div>
                </div>

                <!-- STATS GRID -->
                <div class="grid grid-cols-2 gap-6 animate-fade-in-up delay-200">
                    <div class="bg-[#1a1a1a] p-8 rounded-[2rem] border border-white/5 shadow-xl text-center space-y-2">
                        <span class="text-[8px] font-black uppercase tracking-[0.2em] text-white/30">Résolues</span>
                        <div class="text-4xl font-black text-green-400">{{ progression.lieux_decouverts?.length || 0 }}</div>
                        <p class="text-[8px] font-black text-white/20 uppercase tracking-widest">Énigmes</p>
                    </div>
                    <div class="bg-[#1a1a1a] p-8 rounded-[2rem] border border-white/5 shadow-xl text-center space-y-2">
                        <span class="text-[8px] font-black uppercase tracking-[0.2em] text-white/30">Distance</span>
                        <div class="text-4xl font-black text-blue-400">
                            {{ (distanceTotale / 1000).toFixed(1) || '3.2' }}<span class="text-lg">km</span>
                        </div>
                        <p class="text-[8px] font-black text-white/20 uppercase tracking-widest">Parcourus</p>
                    </div>
                </div>

                <!-- ENIGMES NON RESOLUES -->
                <div v-if="enigmesNonResolues?.length" class="bg-[#1a1a1a] p-8 rounded-[2rem] border border-white/5 shadow-xl space-y-6 animate-fade-in-up delay-300">
                    <h3 class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] border-b border-white/5 pb-4 text-center">Énigmes non résolues</h3>
                    <div class="space-y-4">
                        <div v-for="enigme in enigmesNonResolues" :key="enigme.id" class="flex items-center gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
                            <div class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_8px_#ef4444]"></div>
                            <span class="text-sm text-white/70 font-black uppercase tracking-tight">{{ enigme.titre }}</span>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="space-y-4 pt-4 animate-fade-in-up delay-500">
                    <Link :href="route('dashboard')" class="block">
                        <button class="game-btn-primary w-full p-6 rounded-[1.5rem] bg-[#FF9500] text-black font-black uppercase tracking-[0.2em] shadow-[0_8px_0_#cc7a00] active:shadow-none active:translate-y-[8px] transition-all flex items-center justify-center gap-3">
                            <i class="pi pi-home text-xl"></i>
                            Quitter le débriefing
                        </button>
                    </Link>

                    <button
                        @click="showLogoutDialog = true"
                        class="w-full p-6 rounded-[1.5rem] bg-white/5 text-red-400 border border-red-400/20 font-black uppercase tracking-[0.2em] text-[10px] hover:bg-red-400/10 transition-all flex items-center justify-center gap-2"
                    >
                        <i class="pi pi-power-off"></i>
                        Terminer la session
                    </button>
                </div>
            </main>

            <!-- GAME DIALOGS -->
            <Dialog v-model:visible="showLogoutDialog" modal header="Déconnexion du système" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-8 space-y-6">
                    <p class="text-sm text-white/70 font-medium leading-relaxed">
                        Voulez-vous archiver votre profil pour une prochaine mission ou effacer toutes vos données de la base ?
                    </p>

                    <div class="space-y-4">
                        <button 
                            @click="handleLogout"
                            class="w-full p-6 bg-white/5 rounded-2xl border border-white/10 text-left hover:bg-white/10 transition-all group"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-[#FF9500] uppercase text-xs">Archiver le profil</span>
                                <i class="pi pi-chevron-right text-white/20 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                            <p class="text-[9px] text-white/40 font-bold uppercase tracking-widest leading-tight">
                                Conservez vos succès pour vos futures explorations.
                            </p>
                        </button>

                        <button 
                            @click="showDeleteConfirm = true; showLogoutDialog = false"
                            class="w-full p-6 bg-red-600/10 rounded-2xl border border-red-600/20 text-left hover:bg-red-600/20 transition-all group"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-red-500 uppercase text-xs">Auto-destruction</span>
                                <i class="pi pi-trash text-red-500/50"></i>
                            </div>
                            <p class="text-[9px] text-red-500/40 font-bold uppercase tracking-widest leading-tight">
                                Supprimez toutes vos traces du système immédiatement.
                            </p>
                        </button>
                    </div>
                </div>
            </Dialog>

            <Dialog v-model:visible="showDeleteConfirm" modal header="Confirmation d'effacement" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-8 space-y-4">
                    <div class="flex items-center gap-3 text-red-500">
                        <i class="pi pi-exclamation-triangle text-2xl"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Action Irréversible</span>
                    </div>
                    <p class="text-white/70 font-medium leading-relaxed italic">
                        "Toutes vos progressions CityPlay seront définitivement perdues. Êtes-vous certain ?"
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full p-6 pt-0">
                        <button @click="showDeleteConfirm = false" class="flex-1 p-4 rounded-xl bg-white/5 text-white/60 font-black uppercase tracking-widest text-[10px]">Annuler</button>
                        <button @click="handleDeleteProfile" class="flex-1 p-4 rounded-xl bg-red-600 text-white font-black uppercase tracking-widest text-[10px]">Confirmer</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
</script>

<style scoped>
.game-summary {
    background-color: #0f0f0f;
}

@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-500 { animation-delay: 0.5s; }

.game-btn-primary {
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
</style>

<style>
.custom-dialog .p-dialog-header { background: white; padding: 1.5rem; border-bottom: 1px solid #FFF7ED; }
.custom-dialog .p-dialog-title { font-weight: 900; text-transform: uppercase; letter-spacing: -0.025em; color: #431407; }
.custom-dialog .p-dialog-content { padding: 0 1.5rem 1.5rem 1.5rem; }
.custom-dialog .p-dialog-footer { padding: 1.5rem; border-top: 1px solid #FFF7ED; }
</style>