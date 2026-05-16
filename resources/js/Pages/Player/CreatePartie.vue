<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SelectButton from 'primevue/selectbutton';
import Slider from 'primevue/slider';
import InputNumber from 'primevue/inputnumber';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    environnements: Array
});

const showConfig = ref(false);
const selectedEnv = ref(null);

const form = useForm({
    environnement_id: null,
    mode: 'solo',
    duree_prevue: 60,
    locomotion: 'pied',
    nb_joueurs: 1
});

const modes = [
    { label: 'Solo', value: 'solo', icon: 'pi pi-user' },
    { label: 'Multi', value: 'team', icon: 'pi pi-users' }
];

const locomotions = [
    { label: 'À pied', value: 'pied', icon: 'pi pi-directions-walk', factor: 1 },
    { label: 'Vélo', value: 'velo', icon: 'pi pi-bicycle', factor: 0.4 },
    { label: 'Voiture', value: 'voiture', icon: 'pi pi-car', factor: 0.2 }
];

const openConfig = (env) => {
    selectedEnv.value = env;
    form.environnement_id = env.id;
    showConfig.value = true;
};

// Estimation du temps basée sur la locomotion
const estimatedTime = computed(() => {
    if (!selectedEnv.value) return 60;
    const baseTime = selectedEnv.value.duree_estimee || 60;
    const locomotion = locomotions.find(l => l.value === form.locomotion);
    return Math.round(baseTime * (locomotion?.factor || 1));
});

// Quand la locomotion change, on suggère une durée
watch(() => form.locomotion, (newLoc) => {
    form.duree_prevue = estimatedTime.value;
});

const submit = () => {
    form.post(route('parties.store'), {
        onSuccess: () => showConfig.value = false
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Nouvelle Partie" />

        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <main class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <span class="text-[9px] font-black text-orange-900/30 uppercase tracking-widest">Référence</span>
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
                        @click="openConfig(env)"
                        label="Configurer l'aventure"
                        icon="pi pi-cog"
                        class="w-full p-4 rounded-2xl bg-orange-950 border-none font-black uppercase tracking-widest shadow-lg active:scale-95 transition-all"
                    />
                </div>
            </div>
        </main>

        <!-- CONFIG MODAL -->
        <Dialog v-model:visible="showConfig" modal :header="'Configuration : ' + selectedEnv?.nom" :style="{ width: '90vw', maxWidth: '500px' }" class="custom-dialog">
            <div class="space-y-8 py-4">
                <!-- MODE CHOICE -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40 ml-1">Mode de jeu</label>
                    <SelectButton v-model="form.mode" :options="modes" optionLabel="label" optionValue="value" class="w-full custom-select-button">
                        <template #option="slotProps">
                            <div class="flex items-center gap-2 py-1">
                                <i :class="slotProps.option.icon"></i>
                                <span class="font-bold uppercase text-[10px]">{{ slotProps.option.label }}</span>
                            </div>
                        </template>
                    </SelectButton>
                </div>

                <!-- NB PLAYERS (TEAM ONLY) -->
                <div v-if="form.mode === 'team'" class="space-y-3 animate-fade-in">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40 ml-1">Nombre de joueurs (max 10)</label>
                        <span class="text-xs font-black text-orange-950">{{ form.nb_joueurs }}</span>
                    </div>
                    <Slider v-model="form.nb_joueurs" :min="2" :max="10" class="custom-slider" />
                </div>

                <!-- LOCOMOTION -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40 ml-1">Moyen de transport</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            v-for="loc in locomotions" 
                            :key="loc.value"
                            type="button"
                            @click="form.locomotion = loc.value"
                            class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 transition-all"
                            :class="form.locomotion === loc.value ? 'bg-orange-50 border-orange-500 shadow-inner' : 'bg-white border-orange-50 hover:border-orange-100'"
                        >
                            <i :class="[loc.icon, form.locomotion === loc.value ? 'text-orange-600' : 'text-orange-200']" class="text-xl"></i>
                            <span class="text-[9px] font-black uppercase tracking-tight" :class="form.locomotion === loc.value ? 'text-orange-950' : 'text-orange-300'">{{ loc.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- DURATION -->
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-900/40 ml-1">Temps de disponibilité</label>
                            <p class="text-[9px] text-orange-900/30 font-bold uppercase italic">Basé sur votre transport</p>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-orange-950">{{ form.duree_prevue }}</span>
                            <span class="text-[10px] font-black text-orange-900/40 uppercase">min</span>
                        </div>
                    </div>
                    <Slider v-model="form.duree_prevue" :min="15" :max="180" :step="5" class="custom-slider" />
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3 w-full pt-4">
                    <Button 
                        @click="submit"
                        :label="form.mode === 'solo' ? 'Lancer ma partie' : 'Créer le groupe'"
                        :icon="form.mode === 'solo' ? 'pi pi-play' : 'pi pi-users'"
                        class="w-full p-5 rounded-2xl bg-orange-950 border-none font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all"
                        :loading="form.processing"
                    />
                    <p class="text-center text-[9px] font-bold text-orange-900/30 uppercase tracking-widest">Vous pourrez mettre en pause à tout moment</p>
                </div>
            </template>
        </Dialog>

        <!-- EMPTY STATE -->
        <div v-if="!environnements.length" class="text-center py-20">
            <i class="pi pi-map text-4xl text-orange-200 mb-4"></i>
            <p class="text-orange-900/40 font-bold uppercase tracking-widest text-sm">Aucun parcours disponible pour le moment.</p>
        </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.custom-dialog .p-dialog-header {
    background: white;
    padding: 2rem 2rem 1rem 2rem;
}
.custom-dialog .p-dialog-title {
    font-size: 1.1rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.025em;
    color: #431407;
}
.custom-dialog .p-dialog-content {
    padding: 0 2rem 2rem 2rem;
}
.custom-dialog .p-dialog-footer {
    padding: 0 2rem 2rem 2rem;
}

.custom-select-button {
    display: flex;
    gap: 0.5rem;
    background: transparent !important;
    border: none !important;
}
.custom-select-button .p-button {
    flex: 1;
    background: white !important;
    border: 2px solid #FFF7ED !important;
    color: #9A3412 !important;
    border-radius: 1.25rem !important;
    transition: all 0.2s ease;
}
.custom-select-button .p-button.p-highlight {
    background: #FFF7ED !important;
    border-color: #FF9500 !important;
    color: #431407 !important;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.custom-slider .p-slider-handle {
    border: 3px solid #FF9500 !important;
    background: white !important;
}
.custom-slider .p-slider-range {
    background: #FF9500 !important;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease forwards;
}
</style>

