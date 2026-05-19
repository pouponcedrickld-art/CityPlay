<script setup>
import { onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    partie: Object
});

const copyCode = () => {
    navigator.clipboard.writeText(props.partie.code_liaison);
    // On pourrait ajouter un toast PrimeVue ici
};

const selectRole = (role) => {
    router.post(route('parties.update-role', props.partie.id), { role }, {
        onSuccess: () => router.get(route('progression.enigme', props.partie.id))
    });
};

onMounted(() => {
    gsap.set('.gsap-header', { y: -30, opacity: 0 });
    gsap.set('.role-card', { y: 100, opacity: 0, rotationY: 45 });
    gsap.set('.code-overlay', { y: 20, opacity: 0 });

    const tl = gsap.timeline();
    tl.to('.gsap-header', { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.5)' })
      .to('.role-card', { y: 0, opacity: 1, rotationY: 0, duration: 0.8, stagger: 0.2, ease: 'power3.out' }, '-=0.4')
      .to('.code-overlay', { y: 0, opacity: 1, duration: 0.6, ease: 'power2.out' }, '-=0.2');
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Choix du Rôle" />
        
        <div class="py-10 px-4 min-h-screen play-mat">
            <div class="max-w-[1000px] mx-auto game-board p-8 md:p-12">
                
                <header class="gsap-header text-center mb-12">
                    <h1 class="text-3xl md:text-5xl font-black text-[#f5e8c7] uppercase tracking-tighter card-title mb-4 drop-shadow-md">
                        Tirez votre Carte de Rôle
                    </h1>
                    <p class="card-badge text-white/50">Rejoignez la Guilde et définissez votre destinée</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 relative mb-20">
                    <!-- CHALLENGER CARD -->
                    <div 
                        @click="selectRole('challenger')"
                        class="role-card board-card cursor-pointer group p-8 flex flex-col items-center text-center relative overflow-hidden"
                    >
                        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_#3b82f6_0%,_transparent_70%)]"></div>
                        <div class="w-24 h-24 rounded-full border-4 border-[#5c4033] bg-[#1f140e] flex items-center justify-center mb-6 shadow-inner group-hover:border-[#3b82f6] group-hover:shadow-[0_0_20px_rgba(59,130,246,0.5)] transition-all duration-500">
                            <i class="pi pi-bolt text-4xl text-[#3b82f6] drop-shadow-[0_0_8px_#3b82f6]"></i>
                        </div>
                        <h2 class="card-title text-3xl text-white mb-2 group-hover:text-[#3b82f6] transition-colors">Challenger</h2>
                        <p class="card-badge text-[10px] text-[#3b82f6] mb-6">Affrontement Solo</p>
                        <p class="text-sm text-white/50 italic leading-relaxed font-serif">
                            "Je joue pour la gloire. Chaque énigme résolue me rapproche du sommet du classement."
                        </p>
                        <button class="mt-8 px-8 py-3 bg-[#1f140e] border border-[#5c4033] text-[#3b82f6] rounded-xl card-badge text-[10px] group-hover:bg-[#3b82f6] group-hover:text-white transition-colors">
                            Sélectionner
                        </button>
                    </div>

                    <!-- PARTICIPANT CARD -->
                    <div 
                        @click="selectRole('participant')"
                        class="role-card board-card cursor-pointer group p-8 flex flex-col items-center text-center relative overflow-hidden"
                    >
                        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_#FF9500_0%,_transparent_70%)]"></div>
                        <div class="w-24 h-24 rounded-full border-4 border-[#5c4033] bg-[#1f140e] flex items-center justify-center mb-6 shadow-inner group-hover:border-[#FF9500] group-hover:shadow-[0_0_20px_rgba(255,149,0,0.5)] transition-all duration-500">
                            <i class="pi pi-users text-4xl text-[#FF9500] drop-shadow-[0_0_8px_#FF9500]"></i>
                        </div>
                        <h2 class="card-title text-3xl text-white mb-2 group-hover:text-[#FF9500] transition-colors">Coéquipier</h2>
                        <p class="card-badge text-[10px] text-[#FF9500] mb-6">Force Collective</p>
                        <p class="text-sm text-white/50 italic leading-relaxed font-serif">
                            "L'union fait la force. Je partage l'aventure et collabore avec ma guilde pour triompher."
                        </p>
                        <button class="mt-8 px-8 py-3 bg-[#1f140e] border border-[#5c4033] text-[#FF9500] rounded-xl card-badge text-[10px] group-hover:bg-[#FF9500] group-hover:text-[#1f140e] transition-colors">
                            Sélectionner
                        </button>
                    </div>
                </div>

                <!-- CODE OVERLAY (Like a token/plaque on the board) -->
                <div class="code-overlay bg-[#2a1c14] border-2 border-[#5c4033] p-6 rounded-2xl max-w-sm mx-auto text-center shadow-[0_10px_30px_rgba(0,0,0,0.8)] relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#1f140e] border border-[#5c4033] px-4 py-1 rounded-full card-badge text-[8px] text-[#FF9500]">
                        Sceau d'Invitation
                    </div>
                    <p class="card-badge text-[9px] text-white/40 mb-3 mt-2">Partagez ce code avec d'autres aventuriers</p>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-[#1f140e] p-3 rounded-xl border border-black text-center font-black text-2xl tracking-[0.3em] text-[#f5e8c7] shadow-inner" style="font-family: 'Share Tech Mono', monospace;">
                            {{ partie.code_liaison }}
                        </div>
                        <button @click="copyCode" class="w-14 bg-[#5c4033] text-[#f5e8c7] rounded-xl flex items-center justify-center hover:bg-[#FF9500] hover:text-[#1f140e] active:scale-95 transition-all shadow-[0_4px_0_#3f2a1e] active:translate-y-[4px] active:shadow-none">
                            <i class="pi pi-copy text-xl"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Specific overrides */
</style>
