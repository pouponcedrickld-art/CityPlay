<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    partie: Object
});

const copyCode = () => {
    navigator.clipboard.writeText(props.partie.code_liaison);
    // On pourrait ajouter un toast ici
};

const selectRole = (role) => {
    // Appel API pour mettre à jour le rôle dans le pivot
    router.post(route('parties.update-role', props.partie.id), { role }, {
        onSuccess: () => router.get(route('progression.enigme', props.partie.id))
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Configuration Équipe" />
        <div class="h-[calc(100vh-12rem)] flex flex-col md:flex-row overflow-hidden relative rounded-3xl shadow-2xl border border-white">
        <!-- CHALLENGER SIDE (LEFT) -->
        <div 
            @click="selectRole('challenger')"
            class="flex-1 bg-[#191970] (midnightblue) relative group cursor-pointer overflow-hidden transition-all duration-700 hover:flex-[1.2]"
            style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);"
        >
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent"></div>
            
            <div class="relative h-full flex flex-col items-center justify-center p-12 text-center space-y-6">
                <div class="w-24 h-24 bg-blue-500 rounded-3xl flex items-center justify-center shadow-2xl shadow-blue-400 rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <i class="pi pi-bolt text-4xl text-white animate-pulse"></i>
                </div>
                
                <div class="space-y-2">
                    <h2 class="text-4xl font-black text-white uppercase tracking-tighter">Challenger</h2>
                    <p class="text-blue-200/60 font-bold uppercase tracking-[0.3em] text-xs">Dominez le classement</p>
                </div>

                <p class="text-blue-100/40 text-sm max-w-xs italic leading-relaxed">
                    "Je suis ici pour gagner. Chaque énigme résolue me rapproche de la victoire finale contre mes amis."
                </p>

                <div class="pt-8 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button label="Choisir ce rôle" class="p-button-rounded bg-blue-500 border-none px-8 font-black uppercase tracking-widest shadow-lg shadow-blue-900/50" />
                </div>
            </div>
        </div>

        <!-- PARTICIPANT SIDE (RIGHT) -->
        <div 
            @click="selectRole('participant')"
            class="flex-1 bg-[#FDFCF0] relative group cursor-pointer overflow-hidden transition-all duration-700 hover:flex-[1.2]"
            style="margin-left: -15%;"
        >
            <div class="absolute inset-0 bg-gradient-to-bl from-orange-100/50 to-transparent"></div>
            
            <div class="relative h-full flex flex-col items-center justify-center p-12 text-center space-y-6">
                <div class="w-24 h-24 bg-orange-100 rounded-3xl flex items-center justify-center shadow-xl -rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <i class="pi pi-users text-4xl text-orange-500"></i>
                </div>
                
                <div class="space-y-2">
                    <h2 class="text-4xl font-black text-orange-950 uppercase tracking-tighter">Participant</h2>
                    <p class="text-orange-900/40 font-bold uppercase tracking-[0.3em] text-xs">Jouez ensemble</p>
                </div>

                <p class="text-orange-900/30 text-sm max-w-xs italic leading-relaxed">
                    "L'union fait la force. Je préfère collaborer et partager l'aventure avec mon équipe."
                </p>

                <div class="pt-8 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button label="Choisir ce rôle" class="p-button-rounded bg-orange-950 border-none px-8 font-black uppercase tracking-widest shadow-lg shadow-orange-900/20" />
                </div>
            </div>
        </div>

        <!-- CODE OVERLAY -->
        <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-50 w-full max-w-xs px-6">
            <div class="bg-white/80 backdrop-blur-xl p-4 rounded-3xl border border-white shadow-2xl space-y-3">
                <p class="text-[9px] font-black text-center text-orange-950/40 uppercase tracking-[0.2em]">Partagez ce code avec vos amis</p>
                <div class="flex gap-2">
                    <div class="flex-1 bg-orange-50 p-3 rounded-2xl border border-orange-100 text-center font-black text-xl tracking-[0.3em] text-orange-950">
                        {{ partie.code_liaison }}
                    </div>
                    <button @click="copyCode" class="w-12 bg-orange-950 text-white rounded-2xl flex items-center justify-center active:scale-90 transition-transform">
                        <i class="pi pi-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Effet de transition pour le split */
@media (max-width: 768px) {
    div[style*="clip-path"] {
        clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%) !important;
    }
    div[style*="margin-left"] {
        margin-left: 0 !important;
        margin-top: -15% !important;
    }
}
</style>
