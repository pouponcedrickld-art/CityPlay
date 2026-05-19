<template>
    <Head title="Victoire !" />

    <div class="play-mat min-h-screen text-[#f5e8c7] flex flex-col overflow-hidden relative">
        <main class="flex-1 p-6 max-w-2xl mx-auto w-full flex flex-col items-center justify-center space-y-10 relative z-10 py-12">
            <!-- BRAVO SECTION -->
            <div class="gsap-header text-center space-y-6">
                <div class="relative inline-block">
                    <div class="absolute inset-0 bg-[#FF9500] rounded-full blur-2xl opacity-20"></div>
                    <div class="w-32 h-32 bg-[#2a1c14] border-4 border-[#FF9500] rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(255,149,0,0.4)] relative z-10">
                        <i class="pi pi-star-fill text-6xl text-[#FF9500] drop-shadow-[0_0_15px_#FF9500]"></i>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <h1 class="card-title text-5xl text-[#FF9500] uppercase tracking-tighter leading-none drop-shadow-lg">
                        Épreuve Réussie !
                    </h1>
                    <p class="card-badge text-[10px] text-[#f5e8c7]/80 uppercase tracking-[0.4em]">Gloire Acquise • +{{ lieu.enigmes_count * 10 || 100 }} PTS</p>
                </div>
            </div>

            <!-- DISCOVERY CARD -->
            <div class="gsap-card board-card w-full bg-[#1f140e] rounded-[2.5rem] overflow-hidden shadow-2xl border-2 border-[#5c4033]">
                <div class="relative h-72 border-b-2 border-[#5c4033]">
                    <img v-if="lieu.photos && lieu.photos.length > 0" :src="lieu.photos[0].url" class="w-full h-full object-cover opacity-90">
                    <div v-else class="w-full h-full bg-[url('/img/pattern-wood.png')] bg-cover flex items-center justify-center">
                        <i class="pi pi-map text-black/30 text-6xl"></i>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1f140e] via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-8">
                        <span class="px-3 py-1 bg-[#FF9500] text-[#1f140e] text-[9px] card-badge rounded-md mb-2 inline-block shadow-md">Secret Dévoilé</span>
                        <h2 class="card-title text-3xl text-[#f5e8c7] uppercase leading-none drop-shadow-md">{{ lieu.nom }}</h2>
                    </div>
                </div>

                <div class="p-8 bg-[#2a1c14] border-t border-[#3f2a1e] shadow-inner">
                    <p class="text-[#f5e8c7]/90 leading-relaxed italic text-xl font-serif">
                        <i class="pi pi-quote-left text-[#FF9500]/50 mr-2 text-sm"></i>
                        {{ partie.environnement?.message_bonne_reponse || lieu.description || 'Vous avez percé les secrets de cet endroit. L\'aventure continue !' }}
                    </p>
                </div>
            </div>

            <!-- NEXT STEP ACTION -->
            <div class="gsap-action w-full space-y-4">
                <button
                    @click="nextStep"
                    class="w-full p-6 rounded-xl bg-[#5c4033] text-[#f5e8c7] card-badge text-sm hover:bg-[#FF9500] hover:text-[#1f140e] active:scale-95 transition-all flex items-center justify-center gap-3 border border-[#3f2a1e] shadow-[0_6px_0_#1f140e] active:translate-y-[6px] active:shadow-none"
                >
                    Tirer la Prochaine Carte
                    <i class="pi pi-arrow-right text-xl"></i>
                </button>
                <p class="text-center card-badge text-[9px] text-[#f5e8c7]/30">Le plateau vous attend...</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';

const props = defineProps({
    partie: Object,
    lieu: Object
});

const nextStep = () => {
    router.post(route('progression.next', props.partie.id));
};

onMounted(() => {
    gsap.set('.gsap-header', { y: -50, opacity: 0, scale: 0.9 });
    gsap.set('.gsap-card', { y: 100, opacity: 0, rotationY: -15 });
    gsap.set('.gsap-action', { y: 50, opacity: 0 });

    const tl = gsap.timeline();
    tl.to('.gsap-header', { y: 0, opacity: 1, scale: 1, duration: 0.8, ease: 'back.out(1.5)' })
      .to('.gsap-card', { y: 0, opacity: 1, rotationY: 0, duration: 0.8, ease: 'power3.out' }, '-=0.4')
      .to('.gsap-action', { y: 0, opacity: 1, duration: 0.6, ease: 'power2.out' }, '-=0.2');
});
</script>

<style scoped>
/* Specific overrides if necessary */
</style>