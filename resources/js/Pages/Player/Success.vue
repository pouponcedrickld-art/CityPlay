<template>
    <Head title="Victoire !" />

    <div class="game-success min-h-screen bg-[#0f0f0f] text-white flex flex-col overflow-hidden relative">
        <!-- BACKGROUND DECORATION -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_center,_#FF9500_0%,_transparent_70%)] opacity-20"></div>
            <div class="absolute top-0 left-0 w-full h-full" :style="{ backgroundImage: 'url(\'data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0_0_20_20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M0_0h20v20H0V0zm10_10l10-10M0_20l10-10\' stroke=\'%23FF9500\' fill=\'none\' fill-rule=\'evenodd\' stroke-opacity=\'.1\'/%3E%3C/svg%3E\')' }"></div>
        </div>

        <main class="flex-1 p-6 max-w-2xl mx-auto w-full flex flex-col items-center justify-center space-y-10 relative z-10 py-12">
            <!-- BRAVO SECTION -->
            <div class="text-center space-y-6 animate-fade-in-up">
                <div class="relative inline-block">
                    <div class="absolute inset-0 bg-[#FF9500] rounded-full blur-2xl opacity-20 animate-pulse"></div>
                    <div class="w-32 h-32 bg-[#1a1a1a] border-4 border-[#FF9500] rounded-full flex items-center justify-center mx-auto shadow-2xl relative z-10">
                        <i class="pi pi-star-fill text-6xl text-[#FF9500] animate-bounce"></i>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <h1 class="text-5xl font-black text-white uppercase tracking-tighter leading-none">
                        Excellent !
                    </h1>
                    <p class="text-[10px] font-black text-[#FF9500] uppercase tracking-[0.4em]">Objectif atteint • +{{ lieu.enigmes_count * 10 || 100 }} PTS</p>
                </div>
            </div>

            <!-- DISCOVERY CARD -->
            <div class="discovery-card w-full bg-[#1a1a1a] rounded-[2.5rem] overflow-hidden shadow-2xl border-2 border-white/10 animate-fade-in-up delay-200">
                <div class="relative h-72">
                    <img v-if="lieu.photos && lieu.photos.length > 0" :src="lieu.photos[0].url" class="w-full h-full object-cover">
                    <div v-else class="w-full h-full bg-black/40 flex items-center justify-center">
                        <i class="pi pi-map text-white/10 text-6xl"></i>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-8">
                        <span class="px-3 py-1 bg-[#FF9500] text-black text-[9px] font-black rounded-full uppercase tracking-widest mb-2 inline-block">Lieu découvert</span>
                        <h2 class="text-3xl font-black text-white uppercase tracking-tighter leading-none">{{ lieu.nom }}</h2>
                    </div>
                </div>

                <div class="p-8">
                    <p class="text-white/60 leading-relaxed italic text-lg">
                        "{{ partie.environnement?.message_bonne_reponse || lieu.description || 'Vous avez percé les secrets de cet endroit. La mission continue !' }}"
                    </p>
                </div>
            </div>

            <!-- NEXT STEP ACTION -->
            <div class="w-full space-y-4 animate-fade-in-up delay-500">
                <button
                    @click="nextStep"
                    class="game-btn-primary w-full p-6 rounded-[1.5rem] bg-[#FF9500] text-black font-black uppercase tracking-[0.2em] shadow-[0_8px_0_#cc7a00] active:shadow-none active:translate-y-[8px] transition-all flex items-center justify-center gap-3"
                >
                    Mission Suivante
                    <i class="pi pi-arrow-right text-xl"></i>
                </button>
                <p class="text-center text-[9px] font-black text-white/20 uppercase tracking-widest">Appuyez pour continuer votre aventure</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    partie: Object,
    lieu: Object
});

const nextStep = () => {
    router.post(route('progression.next', props.partie.id));
};
</script>

<style scoped>
.game-success {
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
.delay-500 { animation-delay: 0.5s; }

.game-btn-primary {
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
</style>

<style scoped>
:deep(.p-carousel-indicators) {
    padding: 1rem;
}
</style>