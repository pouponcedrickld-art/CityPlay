<template>
    <AuthenticatedLayout>
        <Head title="Plateau de Jeu" />

        <div class="player-dashboard py-10 px-4 min-h-screen play-mat">
            
            <div class="max-w-[1280px] mx-auto">
                <!-- HEADER / WELCOME -->
                <header class="gsap-header player-section-header relative py-12 px-8 overflow-hidden rounded-3xl shadow-2xl mb-12 border-[4px]">
                    <div class="absolute inset-0 opacity-30" 
                         :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, var(--accent-primary) 1.5px, transparent 0)', backgroundSize: '32px 32px' }"></div>
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="mb-4 inline-block px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest player-badge-accent">
                            Zone de Commandement
                        </div>
                        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter leading-none player-title" style="font-family: 'Cinzel', serif;">
                            Aventurier <span class="player-accent-text">{{ $page.props.auth.user.name }}</span>
                        </h1>
                        <p class="text-xs font-black uppercase tracking-[0.4em] mt-4 player-muted-text" style="font-family: 'Share Tech Mono', monospace;">Prêt à jouer votre prochaine carte ?</p>
                    </div>
                </header>

                <div class="game-board gsap-board">
                    <section class="mb-12">
                        <div class="flex items-center justify-between px-4 mb-6">
                            <h2 class="card-badge player-muted-text">Missions en jeu</h2>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase player-badge-accent" style="background: color-mix(in srgb, var(--accent-primary) 8%, transparent); border: 1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent);">
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
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center border transition-colors shadow-inner player-icon-btn group-hover:border-[var(--accent-primary)]">
                                                <i class="pi pi-bolt player-accent-text group-hover:text-white"></i>
                                            </div>
                                        </div>
                                        <h3 class="card-title text-xl player-title group-hover:text-[var(--accent-primary)] transition-colors mb-6">
                                            {{ partie.environnement.nom }}
                                        </h3>
                                    </div>

                                    <div class="space-y-4 mt-auto">
                                        <div class="flex items-center justify-between border-t player-border-subtle pt-4">
                                            <div class="flex -space-x-2">
                                                <div v-for="i in 3" :key="i" class="w-7 h-7 rounded-full border-2 player-avatar flex items-center justify-center shadow-sm">
                                                    <i class="pi pi-user text-[10px] player-muted-text"></i>
                                                </div>
                                            </div>
                                            <span class="card-badge player-accent-text">{{ partie.progression?.score || 0 }} PTS</span>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex justify-between card-badge text-[9px] player-muted-text">
                                                <span>Avancement</span>
                                                <span>{{ Math.round(((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) || 0 }}%</span>
                                            </div>
                                            <div class="w-full h-2 rounded-full overflow-hidden player-progress-bar border">
                                                <div 
                                                    class="h-full rounded-full player-progress-fill"
                                                    :style="{ width: (((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="board-card p-12 text-center border-dashed border-2 flex flex-col items-center gsap-card">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center border mb-4 player-empty-icon">
                                <i class="pi pi-box text-2xl player-muted-text opacity-50"></i>
                            </div>
                            <p class="card-title text-lg player-muted-text mb-2">Aucune quête active</p>
                            <p class="card-badge text-[9px] player-muted-text opacity-60 mb-6">Piochez une nouvelle carte pour commencer</p>
                            <Link :href="route('parties.web.create')">
                                <button class="px-6 py-3 font-black uppercase tracking-widest text-[10px] rounded-lg transition-all player-btn-cta">
                                    Piocher une carte
                                </button>
                            </Link>
                        </div>
                    </section>

                    <!-- EXPLORE ENVIRONMENTS -->
                    <section>
                        <div class="flex items-center justify-between px-4 mb-6">
                            <h2 class="card-badge player-muted-text">Decks Disponibles</h2>
                            <Link :href="route('parties.web.create')" class="card-badge text-[9px] player-accent-text hover:opacity-70 transition-opacity">Explorer tout →</Link>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div 
                                v-for="(env, idx) in environnements" 
                                :key="env.id"
                                class="board-card group cursor-pointer aspect-[3/4] p-2 flex flex-col gsap-card"
                                @click="router.get(route('parties.web.create'))"
                            >
                                <div class="relative flex-1 rounded-lg overflow-hidden border player-border-subtle mb-3">
                                    <img :src="env.image_url" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-transform duration-700">
                                    <div class="absolute inset-0 player-img-overlay"></div>
                                </div>
                                <div class="px-2 pb-2 text-center">
                                    <p class="card-badge text-[8px] player-accent-text mb-1">{{ env.lieux_count || 0 }} ÉTAPES</p>
                                    <p class="card-title text-xs player-title leading-tight">{{ env.nom }}</p>
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
        case 'en_cours': return 'statut-en-cours bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'terminee': return 'statut-terminee bg-green-500/10 text-green-400 border-green-500/20';
        case 'suspendue': return 'statut-suspendue bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20';
        default: return 'bg-white/5 text-white/40 border-white/10';
    }
};
</script>

<style scoped>
/* ── Dark Mode: variables player (défaut) ── */
.player-section-header {
  background: #1f140e;
  border-color: #5c4033;
}
.player-title { color: #f5e8c7; }
.player-accent-text { color: #FF9500; }
.player-muted-text { color: rgba(245, 232, 199, 0.5); }
.player-badge-accent {
  border: 1px solid rgba(255, 149, 0, 0.3);
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
}
.player-icon-btn {
  background: rgba(0,0,0,0.4);
  border-color: rgba(255,255,255,0.1);
}
.player-border-subtle { border-color: rgba(255,255,255,0.1); }
.player-avatar {
  border-color: #2a1c14;
  background: rgba(255,255,255,0.1);
}
.player-progress-bar {
  background: rgba(0,0,0,0.6);
  border-color: #5c4033;
}
.player-progress-fill {
  background: linear-gradient(to right, #FF9500, #FF7B00);
  box-shadow: 0 0 10px rgba(255,149,0,0.5);
}
.player-empty-icon {
  background: rgba(255,255,255,0.05);
  border-color: rgba(255,255,255,0.1);
}
.player-btn-cta {
  background: #FF9500;
  color: #1f140e;
  box-shadow: 0 4px 0 #cc7a00;
}
.player-btn-cta:hover { background: #ffaa33; }
.player-btn-cta:active { transform: translateY(4px); box-shadow: none; }
.player-img-overlay {
  background: linear-gradient(to top, #1f140e, transparent);
}

/* ── Light Mode: variables player (parchemin + émeraude) ── */
:global(:root.light-theme) .player-section-header {
  background: linear-gradient(135deg, #faf6ee 0%, #f0e8d0 100%) !important;
  border-color: #c8a96e !important;
  box-shadow: 0 10px 30px rgba(139, 105, 20, 0.12) !important;
}
:global(:root.light-theme) .player-title { color: #1a1208 !important; }
:global(:root.light-theme) .player-accent-text { color: #059669 !important; }
:global(:root.light-theme) .player-muted-text { color: #6b4c1e !important; }
:global(:root.light-theme) .player-badge-accent {
  border-color: rgba(5, 150, 105, 0.35) !important;
  background: rgba(5, 150, 105, 0.1) !important;
  color: #059669 !important;
}
:global(:root.light-theme) .player-icon-btn {
  background: rgba(200, 169, 110, 0.15) !important;
  border-color: #c8a96e !important;
}
:global(:root.light-theme) .player-border-subtle { border-color: rgba(200, 169, 110, 0.4) !important; }
:global(:root.light-theme) .player-avatar {
  border-color: #fffdf7 !important;
  background: rgba(200, 169, 110, 0.15) !important;
}
:global(:root.light-theme) .player-progress-bar {
  background: #e8dfc8 !important;
  border-color: #c8a96e !important;
}
:global(:root.light-theme) .player-progress-fill {
  background: linear-gradient(to right, #059669, #0891b2) !important;
  box-shadow: 0 0 8px rgba(5, 150, 105, 0.4) !important;
}
:global(:root.light-theme) .player-empty-icon {
  background: rgba(200, 169, 110, 0.1) !important;
  border-color: #c8a96e !important;
}
:global(:root.light-theme) .player-btn-cta {
  background: #059669 !important;
  color: #ffffff !important;
  box-shadow: 0 4px 0 #047857 !important;
}
:global(:root.light-theme) .player-btn-cta:hover { background: #047857 !important; }
:global(:root.light-theme) .player-img-overlay {
  background: linear-gradient(to top, #faf6ee, transparent) !important;
}
</style>
