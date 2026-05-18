<template>
    <Head title="Échec de mission" />

    <div class="game-failure min-h-screen bg-[#0f0f0f] text-white flex flex-col items-center justify-center p-6 relative overflow-hidden">
        <!-- BACKGROUND DECORATION -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_center,_#ef4444_0%,_transparent_70%)] opacity-10"></div>
        </div>

        <div class="max-w-md w-full bg-[#1a1a1a] p-10 rounded-[2.5rem] shadow-2xl border-2 border-white/5 text-center space-y-10 relative z-10 animate-shake">
            <!-- ERROR ICON -->
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-red-600 rounded-full blur-2xl opacity-20"></div>
                <div class="w-24 h-24 bg-[#1a1a1a] border-4 border-red-600 rounded-full flex items-center justify-center mx-auto shadow-2xl relative z-10">
                    <i class="pi pi-times text-5xl text-red-600"></i>
                </div>
            </div>

            <div class="space-y-3">
                <h1 class="text-4xl font-black text-white uppercase tracking-tighter leading-none">
                    Mission Compromise
                </h1>
                <p class="text-[10px] font-black text-red-400 uppercase tracking-[0.3em]">Code erroné • Analyse en cours</p>
            </div>

            <div class="bg-black/40 p-6 rounded-2xl border border-white/5 italic text-white/60 text-sm">
                "Ce n'est pas la bonne réponse. Reprenez vos esprits, l'indice est peut-être juste sous vos yeux."
            </div>

            <div class="space-y-4">
                <button
                    @click="tryAgain"
                    class="game-btn-primary w-full p-5 rounded-[1.5rem] bg-red-600 text-white font-black uppercase tracking-[0.2em] shadow-[0_8px_0_#991b1b] active:shadow-none active:translate-y-[8px] transition-all flex items-center justify-center gap-3"
                >
                    Réessayer
                    <i class="pi pi-refresh text-xl"></i>
                </button>

                <button
                    @click="skip"
                    class="w-full p-5 rounded-[1.5rem] bg-white/5 text-white/40 border border-white/10 font-black uppercase tracking-[0.2em] text-[10px] hover:bg-white/10 transition-all"
                >
                    Passer l'énigme
                </button>
            </div>

            <p class="text-[9px] uppercase font-black tracking-widest text-white/20">
                Chaque erreur vous rapproche du but
            </p>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';

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
</script>

<style scoped>
.game-failure {
    background-color: #0f0f0f;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

.game-btn-primary {
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
</style>