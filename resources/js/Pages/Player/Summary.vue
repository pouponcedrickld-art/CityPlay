<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
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

<template>
    <AuthenticatedLayout>
        <Head title="Résumé de la partie" />

        <div class="min-h-screen bg-gray-50 pb-12">
        <!-- HEADER -->
        <div class="bg-white border-b border-orange-100 p-8 text-center shadow-sm">
            <div class="w-20 h-20 bg-orange-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-flag-fill text-3xl text-[#FF9500]"></i>
            </div>
            <h1 class="text-2xl font-black text-orange-950 uppercase tracking-tight">Partie Terminée</h1>
            <p class="text-orange-900/40 font-bold uppercase tracking-widest text-xs mt-1">Félicitations pour votre parcours !</p>
        </div>

        <main class="p-6 max-w-2xl mx-auto space-y-6 -mt-6">
            <!-- SCORE CARD -->
            <div class="bg-gradient-to-br from-[#FF9500] to-[#FF7B00] p-8 rounded-3xl shadow-xl text-white text-center space-y-2">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] opacity-70">Score Total</span>
                <div class="text-6xl font-black tabular-nums">
                    {{ progression?.score || 0 }}
                </div>
                <p class="text-sm font-bold uppercase tracking-widest opacity-90">Points CityPlay</p>
            </div>

            <!-- STATS GRID -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/40">Résolues</span>
                    <div class="text-3xl font-black text-green-500">{{ progression.nb_enigmes_resolues }}</div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/40">Distance</span>
                    <div class="text-3xl font-black text-blue-500">{{ (distanceTotale / 1000).toFixed(1) }}<span class="text-sm">km</span></div>
                </div>
            </div>

            <!-- ENIGMES NON RESOLUES (Optionnel) -->
            <div v-if="enigmesNonResolues?.length" class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm space-y-4">
                <h3 class="text-xs font-black text-orange-950 uppercase tracking-widest border-b border-orange-50 pb-2">Énigmes manquées</h3>
                <div class="space-y-3">
                    <div v-for="enigme in enigmesNonResolues" :key="enigme.id" class="flex items-center gap-3">
                        <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                        <span class="text-sm text-orange-900/70 font-medium">{{ enigme.titre }}</span>
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="space-y-3 pt-4">
                <Link :href="route('player.dashboard')" class="block">
                    <Button
                        label="Retour au tableau de bord"
                        icon="pi pi-home"
                        class="w-full p-4 rounded-2xl bg-orange-950 border-none font-bold uppercase tracking-widest shadow-lg"
                    />
                </Link>

                <Button
                    @click="showLogoutDialog = true"
                    label="Quitter & Déconnexion"
                    icon="pi pi-power-off"
                    class="w-full p-4 rounded-2xl bg-white text-red-500 border-2 border-red-100 font-bold uppercase tracking-widest hover:bg-red-50 transition-colors"
                />
            </div>
        </main>

        <!-- LOGOUT / PROFILE MGMT DIALOG -->
        <Dialog v-model:visible="showLogoutDialog" modal header="Déconnexion" :style="{ width: '90vw', maxWidth: '400px' }" class="custom-dialog">
            <div class="py-4 space-y-6">
                <p class="text-sm text-orange-900/80 font-medium">
                    Souhaitez-vous conserver votre profil pour votre prochaine visite ou le supprimer définitivement ?
                </p>

                <div class="space-y-3">
                    <button 
                        @click="handleLogout"
                        class="w-full p-4 bg-orange-50 rounded-2xl border border-orange-100 text-left hover:bg-orange-100 transition-colors group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-black text-orange-950 uppercase text-xs">Conserver mon profil</span>
                            <i class="pi pi-chevron-right text-orange-300 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                        <p class="text-[10px] text-orange-900/60 leading-tight">
                            Vos scores seront conservés pendant la durée légale définie par la mairie (2 ans).
                        </p>
                    </button>

                    <button 
                        @click="showDeleteConfirm = true; showLogoutDialog = false"
                        class="w-full p-4 bg-red-50 rounded-2xl border border-red-100 text-left hover:bg-red-100 transition-colors group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-black text-red-700 uppercase text-xs">Supprimer mon profil</span>
                            <i class="pi pi-trash text-red-300"></i>
                        </div>
                        <p class="text-[10px] text-red-900/60 leading-tight">
                            Toutes vos données et scores seront effacés immédiatement.
                        </p>
                    </button>
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="showDeleteConfirm" modal header="Confirmation" :style="{ width: '90vw', maxWidth: '400px' }" class="custom-dialog">
            <div class="py-4">
                <p class="text-sm text-red-600 font-bold uppercase tracking-tight mb-2">Attention !</p>
                <p class="text-sm text-orange-900/80 font-medium">
                    Cette action est irréversible. Toutes vos progressions CityPlay seront perdues.
                </p>
            </div>
            <template #footer>
                <div class="flex gap-2 w-full">
                    <Button label="Annuler" @click="showDeleteConfirm = false" class="p-button-text flex-1" />
                    <Button label="Supprimer" @click="handleDeleteProfile" class="p-button-danger flex-1" />
                </div>
            </template>
        </Dialog>
    </div>
    </AuthenticatedLayout>
</template>

<style>
.custom-dialog .p-dialog-header { background: white; padding: 1.5rem; border-bottom: 1px solid #FFF7ED; }
.custom-dialog .p-dialog-title { font-weight: 900; text-transform: uppercase; letter-spacing: -0.025em; color: #431407; }
.custom-dialog .p-dialog-content { padding: 0 1.5rem 1.5rem 1.5rem; }
.custom-dialog .p-dialog-footer { padding: 1.5rem; border-top: 1px solid #FFF7ED; }
</style>