<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

const props = defineProps({
    environnements: Array
});

const form = useForm({
    environnement_id: null
});

const submit = (id) => {
    form.environnement_id = id;
    form.post(route('parties.store'));
};
</script>

<template>
    <Head title="Nouvelle Partie" />

    <div class="min-h-screen bg-gray-50 pb-12">
        <!-- HEADER -->
        <div class="bg-white border-b border-orange-100 p-8 text-center shadow-sm">
            <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-plus text-2xl text-[#FF9500]"></i>
            </div>
            <h1 class="text-2xl font-black text-orange-950 uppercase tracking-tight">Nouvelle Aventure</h1>
            <p class="text-orange-900/40 font-bold uppercase tracking-widest text-[10px] mt-1">Choisissez votre parcours</p>
        </div>

        <main class="p-6 max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div 
                v-for="env in environnements" 
                :key="env.id"
                class="bg-white rounded-3xl border border-orange-100 shadow-sm overflow-hidden hover:shadow-xl transition-all group flex flex-col"
            >
                <div class="relative h-48">
                    <img 
                        :src="env.image_url || 'https://images.unsplash.com/photo-1519307212971-dd9561667ffb?auto=format&fit=crop&q=80&w=800'" 
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="pi pi-map-marker text-xs"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ env.lieux?.length || 0 }} étapes</span>
                        </div>
                        <h3 class="text-lg font-black uppercase tracking-tight">{{ env.nom }}</h3>
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                    <p class="text-sm text-orange-900/70 leading-relaxed italic">
                        "{{ env.description || 'Découvrez les secrets cachés de la ville à travers ce parcours immersif.' }}"
                    </p>

                    <div class="flex items-center justify-between py-4 border-t border-dashed border-orange-100">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-orange-900/30 uppercase tracking-widest">Durée estimée</span>
                            <span class="text-sm font-bold text-orange-950">{{ env.duree_estimee || 60 }} min</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[9px] font-black text-orange-900/30 uppercase tracking-widest">Difficulté</span>
                            <div class="flex gap-1 mt-1">
                                <div v-for="i in 3" :key="i" class="w-2 h-2 rounded-full" :class="i <= (env.difficulte || 2) ? 'bg-orange-400' : 'bg-orange-100'"></div>
                            </div>
                        </div>
                    </div>

                    <Button 
                        @click="submit(env.id)"
                        label="Commencer l'aventure"
                        icon="pi pi-play"
                        class="w-full p-4 rounded-2xl bg-orange-950 border-none font-black uppercase tracking-widest shadow-lg active:scale-95 transition-all"
                        :loading="form.processing"
                    />
                </div>
            </div>
        </main>

        <!-- EMPTY STATE -->
        <div v-if="!environnements.length" class="text-center py-20">
            <i class="pi pi-map text-4xl text-orange-200 mb-4"></i>
            <p class="text-orange-900/40 font-bold uppercase tracking-widest text-sm">Aucun parcours disponible pour le moment.</p>
        </div>
    </div>
</template>
