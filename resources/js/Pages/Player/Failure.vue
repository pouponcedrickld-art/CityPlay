<template>
    <Head title="Échec de mission" />

    <div class="play-mat min-h-screen text-[#f5e8c7] flex flex-col items-center justify-center p-6 relative overflow-hidden">
        
        <div class="gsap-fail-card max-w-md w-full bg-[#1f140e] p-10 rounded-[2.5rem] shadow-[0_20px_50px_rgba(220,38,38,0.3)] border-2 border-red-900/50 text-center space-y-10 relative z-10">
            <!-- ERROR ICON (Like a token) -->
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-red-600 rounded-full blur-2xl opacity-20"></div>
                <div class="w-24 h-24 bg-[#2a1c14] border-4 border-red-600 rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(220,38,38,0.4)] relative z-10">
                    <i class="pi pi-times text-5xl text-red-500 drop-shadow-[0_0_10px_red]"></i>
                </div>
            </div>

            <div class="space-y-3">
                <h1 class="card-title text-4xl text-red-500 uppercase tracking-tighter leading-none drop-shadow-md">
                    Mission Compromise
                </h1>
                <p class="card-badge text-[10px] text-red-400/80 uppercase tracking-[0.3em]">Code erroné • Malus appliqué</p>
            </div>

            <div class="bg-[#2a1c14] p-6 rounded-xl border border-red-900/30 italic text-[#f5e8c7]/80 text-lg font-serif shadow-inner">
                <i class="pi pi-exclamation-triangle text-red-500/50 mr-2 text-sm"></i>
                "{{ partie.environnement?.message_mauvaise_reponse || "Ce n'est pas la bonne réponse. Reprenez vos esprits, la vérité est peut-être juste sous vos yeux." }}"
            </div>

            <div class="space-y-4 pt-4">
                <button
                    @click="tryAgain"
                    class="w-full p-5 rounded-xl bg-[#5c4033] text-[#f5e8c7] card-badge hover:bg-red-900/80 hover:text-white active:scale-95 transition-all flex items-center justify-center gap-3 border border-[#3f2a1e] shadow-[0_6px_0_#1f140e] active:translate-y-[6px] active:shadow-none"
                >
                    Reprendre l'Épreuve
                    <i class="pi pi-refresh text-xl"></i>
                </button>

                <button
                    @click="skip"
                    class="w-full p-4 rounded-xl bg-transparent text-red-500/50 border border-red-900/30 card-badge text-[10px] hover:bg-red-900/20 hover:text-red-400 transition-all"
                >
                    Défausser cette carte
                </button>
            </div>

            <p class="card-badge text-[9px] text-[#f5e8c7]/20 pt-4">
                Chaque erreur vous rapproche de la vérité
            </p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';

const props = defineProps({
    partie: Object
});

const tryAgain = () => {
    router.get(route('progression.enigme', props.partie.id));
};

const skip = () => {
    if (confirm('Passer cette énigme ? Vous ne marquerez pas de points.')) {
        router.post(route('progression.next', props.partie.id));
    }
};

onMounted(() => {
    gsap.set('.gsap-fail-card', { y: -50, opacity: 0, rotationZ: 5 });

    const tl = gsap.timeline();
    tl.to('.gsap-fail-card', { y: 0, opacity: 1, rotationZ: 0, duration: 0.6, ease: 'bounce.out' })
      .to('.gsap-fail-card', { 
          x: [-5, 5, -5, 5, 0], 
          duration: 0.4, 
          ease: 'power1.inOut' 
      }, '+=0.2');
});
</script>

<style scoped>
/* Specific overrides if necessary */
</style>