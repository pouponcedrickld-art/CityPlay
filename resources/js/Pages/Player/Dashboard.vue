<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    parties: Array,
    environnements: Array
});

const getStatutLabel = (statut) => {
    switch(statut) {
        case 'en_cours': return 'En cours';
        case 'terminee': return 'Terminée';
        case 'suspendue': return 'En pause';
        default: return statut;
    }
};

const getStatutColor = (statut) => {
    switch(statut) {
        case 'en_cours': return 'bg-blue-100 text-blue-700';
        case 'terminee': return 'bg-green-100 text-green-700';
        case 'suspendue': return 'bg-orange-100 text-orange-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tableau de Bord" />

        <div class="space-y-8">
            <!-- ACTIVE GAMES -->
            <section class="space-y-4">
                <h2 class="text-xs font-black text-orange-950 uppercase tracking-[0.2em] border-l-4 border-[#FF9500] pl-3">Mes Parties</h2>
                
                <div v-if="parties.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        v-for="partie in parties" 
                        :key="partie.id"
                        :href="route('parties.show', partie.id)"
                        class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span 
                                    class="text-[9px] font-black uppercase px-2 py-1 rounded-full"
                                    :class="getStatutColor(partie.statut)"
                                >
                                    {{ getStatutLabel(partie.statut) }}
                                </span>
                                <h3 class="text-lg font-black text-orange-950 uppercase tracking-tight mt-2">{{ partie.environnement.nom }}</h3>
                            </div>
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <i class="pi pi-arrow-right text-orange-400"></i>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-orange-900/40 font-bold uppercase">Progression</span>
                                <span class="text-orange-950 font-black">{{ partie.progression?.score || 0 }} pts</span>
                            </div>
                            <div class="w-full h-2 bg-orange-50 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-[#FF9500] rounded-full transition-all duration-1000"
                                    :style="{ width: (partie.progression?.nb_enigmes_resolues / (partie.progression?.lieux_restants?.length + partie.progression?.nb_enigmes_resolues) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="bg-white p-12 rounded-3xl border-2 border-dashed border-orange-100 text-center space-y-4">
                    <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto">
                        <i class="pi pi-map-marker text-2xl text-orange-200"></i>
                    </div>
                    <div>
                        <p class="text-orange-950 font-black uppercase tracking-tight">Aucune partie en cours</p>
                        <p class="text-orange-900/40 text-xs font-bold uppercase tracking-widest mt-1">Lancez votre première aventure !</p>
                    </div>
                    <Link :href="route('parties.create')">
                        <Button 
                            label="Voir les parcours" 
                            icon="pi pi-search" 
                            class="p-button-text p-button-orange font-black uppercase tracking-widest text-xs"
                        />
                    </Link>
                </div>
            </section>

            <!-- EXPLORE ENVIRONMENTS -->
            <section class="space-y-4">
                <h2 class="text-xs font-black text-orange-950 uppercase tracking-[0.2em] border-l-4 border-blue-400 pl-3">Découvrir</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div 
                        v-for="env in environnements" 
                        :key="env.id"
                        class="bg-white rounded-2xl border border-orange-100 shadow-sm overflow-hidden aspect-square relative group cursor-pointer"
                        @click="router.get(route('parties.create'))"
                    >
                        <img :src="env.image_url" class="w-full h-full object-cover grayscale-[0.5] group-hover:grayscale-0 transition-all duration-500">
                        <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors"></div>
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-70">{{ env.lieux_count || 0 }} étapes</p>
                            <p class="text-xs font-black uppercase leading-tight">{{ env.nom }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>


<style scoped>


</style>
