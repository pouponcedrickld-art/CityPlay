<template>
    <AuthenticatedLayout>
        <Head title="Plateau de Jeu" />

        <div class="player-dashboard py-10 px-4 min-h-screen play-mat">
            
            <div class="max-w-[1280px] mx-auto">
                <!-- HEADER / WELCOME -->
                <header class="gsap-header relative py-12 px-8 overflow-hidden rounded-3xl bg-[#1f140e] border-[4px] border-[#5c4033] shadow-2xl mb-12">
                    <div class="absolute inset-0 opacity-30" :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, #FF9500 1.5px, transparent 0)', backgroundSize: '32px 32px' }"></div>
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="mb-4 inline-block px-4 py-1.5 rounded-full border border-[#FF9500]/30 bg-[#FF9500]/10 text-[10px] font-black text-[#FF9500] uppercase tracking-widest shadow-[0_0_15px_rgba(255,149,0,0.2)]">
                            Zone de Commandement
                        </div>
                        <h1 class="text-4xl md:text-5xl font-black text-[#f5e8c7] uppercase tracking-tighter leading-none" style="font-family: 'Cinzel', serif;">
                            Aventurier <span class="text-[#FF9500]">{{ $page.props.auth.user.name }}</span>
                        </h1>
                        <p class="text-xs font-black text-white/50 uppercase tracking-[0.4em] mt-4" style="font-family: 'Share Tech Mono', monospace;">Prêt à jouer votre prochaine carte ?</p>
                    </div>
                </header>

                <div class="game-board gsap-board">
                    <!-- ACTIVE MISSIONS -->
                    <section class="mb-12">
                        <div class="flex items-center justify-between px-4 mb-6">
                            <h2 class="card-badge text-white/50">Missions en jeu</h2>
                            <span class="px-3 py-1 bg-white/5 rounded-full text-[9px] font-black text-[#FF9500] border border-[#FF9500]/30 uppercase">
                                {{ parties.length }} active(s)
                            </span>
                        </div>
                        
                        <div v-if="parties.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link 
                                v-for="partie in parties" 
                                :key="partie.id"
                                :href="route('progression.enigme', partie.id)"
                                class="board-card group block gsap-card"
                            >
                                <div class="p-6 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start mb-4">
                                            <div 
                                                class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                                :class="getStatutColor(partie.statut)"
                                                style="font-family: 'Share Tech Mono', monospace;"
                                            >
                                                {{ getStatutLabel(partie.statut) }}
                                            </div>
                                            <div class="w-10 h-10 bg-black/40 rounded-full flex items-center justify-center border border-white/10 group-hover:bg-[#FF9500] group-hover:border-[#FF9500] transition-colors shadow-inner">
                                                <i class="pi pi-bolt text-[#FF9500] group-hover:text-black"></i>
                                            </div>
                                        </div>
                                        <h3 class="card-title text-xl text-[#f5e8c7] group-hover:text-[#FF9500] transition-colors mb-6">
                                            {{ partie.environnement.nom }}
                                        </h3>
                                    </div>

                                    <div class="space-y-4 mt-auto">
                                        <div class="flex items-center justify-between border-t border-white/10 pt-4">
                                            <div class="flex -space-x-2">
                                                <div v-for="i in 3" :key="i" class="w-7 h-7 rounded-full border-2 border-[#2a1c14] bg-white/10 flex items-center justify-center shadow-sm">
                                                    <i class="pi pi-user text-[10px] text-white/50"></i>
                                                </div>
                                            </div>
                                            <span class="card-badge text-[#FF9500]">{{ partie.progression?.score || 0 }} PTS</span>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex justify-between card-badge text-[9px] text-white/40">
                                                <span>Avancement</span>
                                                <span>{{ Math.round(((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) || 0 }}%</span>
                                            </div>
                                            <div class="w-full h-2 bg-black/60 rounded-full overflow-hidden border border-[#5c4033]">
                                                <div 
                                                    class="h-full bg-gradient-to-r from-[#FF9500] to-[#FF7B00] rounded-full shadow-[0_0_10px_rgba(255,149,0,0.5)]"
                                                    :style="{ width: (((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="board-card p-12 text-center border-dashed border-2 flex flex-col items-center gsap-card">
                            <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center border border-white/10 mb-4">
                                <i class="pi pi-box text-2xl text-white/20"></i>
                            </div>
                            <p class="card-title text-lg text-white/60 mb-2">Aucune quête active</p>
                            <p class="card-badge text-[9px] text-white/40 mb-6">Piochez une nouvelle carte pour commencer</p>
                            <Link :href="route('parties.web.create')">
                                <button class="px-6 py-3 bg-[#FF9500] text-[#1f140e] font-black uppercase tracking-widest text-[10px] rounded-lg shadow-[0_4px_0_#cc7a00] active:translate-y-[4px] active:shadow-none transition-all hover:bg-[#ffaa33]">
                                    Piocher une carte
                                </button>
                            </Link>
                        </div>
                    </section>

                    <!-- EXPLORE ENVIRONMENTS -->
                    <section>
                        <div class="flex items-center justify-between px-4 mb-6">
                            <h2 class="card-badge text-white/50">Decks Disponibles</h2>
                            <Link :href="route('parties.web.create')" class="card-badge text-[9px] text-[#FF9500] hover:text-white transition-colors">Explorer tout →</Link>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div 
                                v-for="(env, idx) in environnements" 
                                :key="env.id"
                                class="board-card group cursor-pointer aspect-[3/4] p-2 flex flex-col gsap-card"
                                @click="router.get(route('parties.web.create'))"
                            >
                                <div class="relative flex-1 rounded-lg overflow-hidden border border-white/10 mb-3">
                                    <img :src="env.image_url" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-transform duration-700">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#1f140e] via-transparent to-transparent"></div>
                                </div>
                                <div class="px-2 pb-2 text-center">
                                    <p class="card-badge text-[8px] text-[#FF9500] mb-1">{{ env.lieux_count || 0 }} ÉTAPES</p>
                                    <p class="card-title text-xs text-[#f5e8c7] leading-tight">{{ env.nom }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    parties: Array,
    environnements: Array
});

onMounted(() => {
    // Initial state
    gsap.set('.gsap-header', { y: -30, opacity: 0 });
    gsap.set('.gsap-board', { opacity: 0, scale: 0.98 });
    gsap.set('.gsap-card', { y: 50, opacity: 0, rotationY: 15 });

    // Timeline for drawing the board
    const tl = gsap.timeline();
    
    tl.to('.gsap-header', { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.5)' })
      .to('.gsap-board', { opacity: 1, scale: 1, duration: 0.6, ease: 'power2.out' }, '-=0.4')
      .to('.gsap-card', { 
          y: 0, 
          opacity: 1, 
          rotationY: 0, 
          duration: 0.8, 
          stagger: 0.1, 
          ease: 'power3.out' 
      }, '-=0.2');
});

const getStatutLabel = (statut) => {
    switch(statut) {
        case 'en_cours': return 'En jeu';
        case 'terminee': return 'Validée';
        case 'suspendue': return 'Dans la défausse';
        default: return statut;
    }
};

const getStatutColor = (statut) => {
    switch(statut) {
        case 'en_cours': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'terminee': return 'bg-green-500/10 text-green-400 border-green-500/20';
        case 'suspendue': return 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20';
        default: return 'bg-white/5 text-white/40 border-white/10';
    }
};
</script>

<style scoped>
/* Specific overrides if needed, but mostly relying on app.css */
</style>
