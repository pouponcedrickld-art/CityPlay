<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close']);

const page = usePage();
const retentionDays = page.props.retentionDays || 30;

const logout = () => {
    router.post(route('logout'));
};

const deleteAccount = () => {
    if (confirm("Êtes-vous sûr de vouloir supprimer votre profil définitivement ? Toutes vos statistiques et récompenses seront perdues.")) {
        router.delete(route('profile.destroy'), {
            onSuccess: () => {
                emit('close');
            }
        });
    }
};
</script>

<template>
    <Dialog 
        :visible="show" 
        @update:visible="$emit('close')" 
        modal 
        header="Déconnexion Tactique" 
        :style="{ width: '90vw', maxWidth: '450px' }"
        class="game-logout-modal"
    >
        <div class="space-y-6 py-4">
            <div class="text-center space-y-2">
                <i class="pi pi-power-off text-4xl text-[#FF9500] mb-2"></i>
                <p class="text-white font-bold uppercase tracking-widest text-sm">Que voulez-vous faire de votre profil ?</p>
            </div>

            <div class="space-y-3">
                <!-- OPTION 1: CONSERVER -->
                <button 
                    @click="logout"
                    class="w-full p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-all text-left group"
                >
                    <div class="w-12 h-12 bg-[#FF9500]/10 rounded-xl flex items-center justify-center group-hover:bg-[#FF9500]/20 transition-all">
                        <i class="pi pi-save text-[#FF9500] text-xl"></i>
                    </div>
                    <div>
                        <p class="text-white font-black uppercase tracking-tighter text-sm">Conserver mes données</p>
                        <p class="text-[9px] text-white/40 font-bold uppercase tracking-widest">
                            Rétention active pendant {{ retentionDays }} jours
                        </p>
                    </div>
                </button>

                <!-- OPTION 2: SUPPRIMER -->
                <button 
                    @click="deleteAccount"
                    class="w-full p-5 bg-red-600/5 border border-red-600/10 rounded-2xl flex items-center gap-4 hover:bg-red-600/10 transition-all text-left group"
                >
                    <div class="w-12 h-12 bg-red-600/10 rounded-xl flex items-center justify-center group-hover:bg-red-600/20 transition-all">
                        <i class="pi pi-trash text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-red-500 font-black uppercase tracking-tighter text-sm">Suppression définitive</p>
                        <p class="text-[9px] text-red-600/40 font-bold uppercase tracking-widest">
                            Effacer toutes mes traces de la zone
                        </p>
                    </div>
                </button>
            </div>
        </div>

        <template #footer>
            <div class="w-full flex justify-center pb-4">
                <button 
                    @click="$emit('close')"
                    class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] hover:text-white/60 transition-all"
                >
                    Annuler la déconnexion
                </button>
            </div>
        </template>
    </Dialog>
</template>

<style>
.game-logout-modal .p-dialog-header {
    background: #1a1a1a;
    color: white;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding: 2rem;
}
.game-logout-modal .p-dialog-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 1rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 2px;
}
.game-logout-modal .p-dialog-content {
    background: #1a1a1a;
    padding: 2rem;
}
.game-logout-modal .p-dialog-footer {
    background: #1a1a1a;
    border-top: none;
    padding: 0 2rem 2rem 2rem;
}
.game-logout-modal .p-dialog-header-icons .p-dialog-header-close {
    color: white;
}
</style>
