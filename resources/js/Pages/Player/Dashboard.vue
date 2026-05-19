<template>
    <AuthenticatedLayout>
        <Head title="Tableau de Bord" />

        <div class="player-dashboard space-y-10 pb-20">
            <!-- HEADER / WELCOME -->
            <header class="relative py-10 overflow-hidden rounded-[2.5rem] bg-[#1a1a1a] border-2 border-white/5 shadow-2xl">
                <div class="absolute inset-0 opacity-20" :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, #FF9500 1px, transparent 0)', backgroundSize: '24px 24px' }"></div>
                <div class="relative z-10 px-8">
                    <h1 class="text-4xl font-black text-white uppercase tracking-tighter leading-none">
                        Bonjour, <span class="text-[#FF9500]">{{ $page.props.auth.user.name }}</span>
                    </h1>
                    <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.4em] mt-3">Prêt pour une nouvelle mission ?</p>
                </div>
            </header>

            <!-- ACTIVE MISSIONS -->
            <section class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em]">Missions en cours</h2>
                    <span class="px-3 py-1 bg-white/5 rounded-full text-[8px] font-black text-[#FF9500] border border-[#FF9500]/20 uppercase">
                        {{ parties.length }} active(s)
                    </span>
                </div>
                
                <div v-if="parties.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Link 
                        v-for="partie in parties" 
                        :key="partie.id"
                        :href="route('progression.enigme', partie.id)"
                        class="mission-card group relative bg-[#1a1a1a] rounded-[2rem] border border-white/5 overflow-hidden shadow-xl hover:border-[#FF9500]/30 transition-all active:scale-[0.98]"
                    >
                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div class="space-y-2">
                                    <div 
                                        class="inline-flex px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest border"
                                        :class="getStatutColor(partie.statut)"
                                    >
                                        {{ getStatutLabel(partie.statut) }}
                                    </div>
                                    <h3 class="text-2xl font-black text-white uppercase tracking-tighter group-hover:text-[#FF9500] transition-colors">
                                        {{ partie.environnement.nom }}
                                    </h3>
                                </div>
                                <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center group-hover:bg-[#FF9500] transition-all">
                                    <i class="pi pi-bolt text-[#FF9500] group-hover:text-black transition-colors"></i>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex -space-x-2">
                                        <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-[#1a1a1a] bg-white/10 flex items-center justify-center text-[8px] font-black text-white/40">
                                            <i class="pi pi-user text-[10px]"></i>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">{{ partie.progression?.score || 0 }} POINTS</span>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[8px] font-black text-white/20 uppercase tracking-[0.2em]">
                                        <span>Progression</span>
                                        <span>{{ Math.round(((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) || 0 }}%</span>
                                    </div>
                                    <div class="w-full h-3 bg-black/40 rounded-full overflow-hidden p-[2px] border border-white/5">
                                        <div 
                                            class="h-full bg-gradient-to-r from-[#FF9500] to-[#FF7B00] rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(255,149,0,0.4)]"
                                            :style="{ width: (((partie.progression?.lieux_decouverts?.length || 0) / ((partie.progression?.lieux_restants?.length || 0) + (partie.progression?.lieux_decouverts?.length || 0))) * 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="bg-[#1a1a1a] p-16 rounded-[2.5rem] border-2 border-dashed border-white/5 text-center space-y-6">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto border border-white/5">
                        <i class="pi pi-map-marker text-3xl text-white/10"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xl font-black text-white uppercase tracking-tight">Aucun déploiement</p>
                        <p class="text-[10px] text-white/40 font-bold uppercase tracking-[0.2em]">Sélectionnez un parcours pour commencer</p>
                    </div>
                    <Link :href="route('parties.web.create')">
                        <button class="px-8 py-4 bg-[#FF9500] text-black font-black uppercase tracking-widest text-xs rounded-2xl shadow-lg shadow-[#FF9500]/20 active:scale-95 transition-all">
                            Explorer les zones
                        </button>
                    </Link>
                </div>
            </section>

            <!-- EXPLORE ENVIRONMENTS -->
            <section class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em]">Zones d'exploration</h2>
                    <Link :href="route('parties.web.create')" class="text-[8px] font-black text-[#FF9500] uppercase tracking-widest hover:underline">Voir tout</Link>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div 
                        v-for="env in environnements" 
                        :key="env.id"
                        class="group relative bg-[#1a1a1a] rounded-3xl border border-white/5 overflow-hidden aspect-[4/5] cursor-pointer active:scale-95 transition-all shadow-xl"
                        @click="router.get(route('parties.web.create'))"
                    >
                        <img :src="env.image_url" class="w-full h-full object-cover opacity-40 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-transparent to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <p class="text-[8px] font-black text-[#FF9500] uppercase tracking-widest mb-1">{{ env.lieux_count || 0 }} ÉTAPES</p>
                            <p class="text-sm font-black text-white uppercase tracking-tighter leading-none">{{ env.nom }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    parties: Array,
    environnements: Array
});

const getStatutLabel = (statut) => {
    switch(statut) {
        case 'en_cours': return 'En mission';
        case 'terminee': return 'Terminée';
        case 'suspendue': return 'En pause';
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
.player-dashboard {
    background-color: #0f0f0f;
    min-height: 100vh;
    color: white;
}
</style>


<style scoped>


</style>
