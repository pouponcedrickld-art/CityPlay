<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SelectButton from 'primevue/selectbutton';
import Slider from 'primevue/slider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    environnements: Array
});

const showConfig = ref(false);
const selectedEnv = ref(null);

const form = useForm({
    environnement_id: null,
    mode: 'solo',
    duree: 60,
    locomotion: 'pied',
    difficulte: 2,
    nb_joueurs: 1
});

const modes = [
    { label: 'Solo', value: 'solo', icon: 'pi pi-user' },
    { label: 'Multi', value: 'team', icon: 'pi pi-users' }
];

const difficulties = [
    { label: 'Facile', value: 1 },
    { label: 'Normal', value: 2 },
    { label: 'Expert', value: 3 }
];

const locomotions = [
    { label: 'À pied', value: 'pied', icon: 'pi pi-directions-walk', factor: 1 },
    { label: 'Vélo', value: 'velo', icon: 'pi pi-bicycle', factor: 0.4 },
    { label: 'Voiture', value: 'voiture', icon: 'pi pi-car', factor: 0.2 }
];

const openConfig = (env) => {
    selectedEnv.value = env;
    form.environnement_id = env.id;
    form.difficulte = env.difficulte || 2;
    showConfig.value = true;
};

const estimatedTime = computed(() => {
    if (!selectedEnv.value) return 60;
    const baseTime = selectedEnv.value.duree_estimee || 60;
    const locomotion = locomotions.find(l => l.value === form.locomotion);
    return Math.round(baseTime * (locomotion?.factor || 1));
});

watch(() => form.locomotion, (newLoc) => {
    form.duree = estimatedTime.value;
});

const submit = () => {
    form.post(route('parties.web.store'), {
        onSuccess: () => {
            showConfig.value = false;
        }
    });
};

onMounted(() => {
    gsap.set('.gsap-header', { y: -30, opacity: 0 });
    gsap.set('.gsap-card', { y: 50, opacity: 0, scale: 0.95 });

    const tl = gsap.timeline();
    tl.to('.gsap-header', { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.5)' })
      .to('.gsap-card', { y: 0, opacity: 1, scale: 1, duration: 0.6, stagger: 0.15, ease: 'power3.out' }, '-=0.4');
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Choix du Scénario" />

        <div class="py-10 px-4 min-h-screen play-mat">
            <div class="max-w-[1280px] mx-auto">
                
                <header class="gsap-header relative py-8 px-8 overflow-hidden rounded-3xl bg-[#1f140e] border-[4px] border-[#5c4033] shadow-2xl mb-10 text-center">
                    <div class="absolute inset-0 opacity-20" :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, #FF9500 1.5px, transparent 0)', backgroundSize: '32px 32px' }"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl md:text-4xl font-black text-[#f5e8c7] uppercase tracking-tighter card-title">
                            Choisissez votre Quête
                        </h1>
                        <p class="card-badge text-[10px] text-white/50 mt-2">Piochez le deck d'exploration pour commencer</p>
                    </div>
                </header>

                <div v-if="form.errors.environnement_id" class="mb-6 p-4 bg-red-900/50 border border-red-500 rounded-xl text-red-200 text-xs font-bold animate-pulse text-center mx-auto max-w-lg shadow-[0_0_15px_rgba(239,68,68,0.3)]">
                    <i class="pi pi-exclamation-triangle mr-2"></i> Veuillez sélectionner un scénario valide.
                </div>

                <main class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="env in environnements" 
                        :key="env.id"
                        class="board-card group flex flex-col gsap-card"
                    >
                        <div class="relative h-56 border-b-2 border-[#5c4033] overflow-hidden">
                            <img 
                                :src="env.image_url || 'https://images.unsplash.com/photo-1519307212971-dd9561667ffb?auto=format&fit=crop&q=80&w=800'" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 opacity-80 group-hover:opacity-100"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-[#1f140e] to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="pi pi-map text-[#FF9500] text-xs"></i>
                                    <span class="card-badge text-[9px] text-[#FF9500]">{{ env.lieux_count || 0 }} étapes</span>
                                </div>
                                <h3 class="card-title text-xl text-[#f5e8c7] group-hover:text-[#FF9500] transition-colors drop-shadow-md">{{ env.nom }}</h3>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col justify-between space-y-6">
                            <p class="text-xs text-white/60 leading-relaxed italic" style="font-family: 'Share Tech Mono', monospace;">
                                "{{ env.description || 'Découvrez les secrets cachés de la ville à travers ce scénario immersif.' }}"
                            </p>

                            <div class="flex items-center justify-between py-4 border-t border-dashed border-white/10">
                                <div class="flex flex-col">
                                    <span class="card-badge text-[8px] text-white/30 mb-1">Durée Prévue</span>
                                    <span class="card-badge text-sm text-[#FF9500]">{{ env.duree_estimee || 60 }} min</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="card-badge text-[8px] text-white/30 mb-1">Difficulté</span>
                                    <div class="flex gap-1">
                                        <div v-for="i in 3" :key="i" class="w-2.5 h-2.5 rounded-sm transform rotate-45" :class="i <= (env.difficulte || 2) ? 'bg-[#FF9500] shadow-[0_0_5px_#FF9500]' : 'bg-white/10'"></div>
                                    </div>
                                </div>
                            </div>

                            <button 
                                @click="openConfig(env)"
                                class="w-full py-3.5 bg-gradient-to-r from-[#5c4033] to-[#3f2a1e] border border-[#FF9500]/50 hover:border-[#FF9500] text-[#f5e8c7] rounded-xl card-badge text-[10px] active:scale-95 transition-all shadow-[0_0_15px_rgba(0,0,0,0.5)] group-hover:shadow-[0_0_20px_rgba(255,149,0,0.2)]"
                            >
                                <i class="pi pi-cog mr-2"></i> Configurer
                            </button>
                        </div>
                    </div>
                </main>

                <div v-if="!environnements.length" class="game-board p-20 text-center mt-10">
                    <i class="pi pi-ban text-4xl text-white/20 mb-4"></i>
                    <p class="card-badge text-white/50 text-sm">Aucun deck disponible dans cette région.</p>
                </div>
            </div>
        </div>

        <!-- CONFIG MODAL -->
        <Dialog v-model:visible="showConfig" modal :header="'Règles : ' + selectedEnv?.nom" :style="{ width: '90vw', maxWidth: '550px' }" class="game-dialog">
            <div class="space-y-8 py-4 px-2">
                
                <div class="space-y-3">
                    <label class="card-badge text-[9px] text-white/40 ml-1">Type de Quête</label>
                    <SelectButton v-model="form.mode" :options="modes" optionLabel="label" optionValue="value" class="board-select-button">
                        <template #option="slotProps">
                            <div class="flex items-center gap-2 py-1.5">
                                <i :class="slotProps.option.icon"></i>
                                <span class="card-badge text-[10px]">{{ slotProps.option.label }}</span>
                            </div>
                        </template>
                    </SelectButton>
                </div>

                <div v-if="form.mode === 'team'" class="space-y-3 animate-fade-in p-4 bg-[#1f140e] border border-[#3f2a1e] rounded-xl">
                    <div class="flex justify-between items-center mb-2">
                        <label class="card-badge text-[9px] text-white/40">Membres de la Guilde (max 10)</label>
                        <span class="card-badge text-lg text-[#FF9500]">{{ form.nb_joueurs }}</span>
                    </div>
                    <Slider v-model="form.nb_joueurs" :min="2" :max="10" class="board-slider" />
                </div>

                <div class="space-y-3">
                    <label class="card-badge text-[9px] text-white/40 ml-1">Moyen de transport</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button 
                            v-for="loc in locomotions" 
                            :key="loc.value"
                            type="button"
                            @click="form.locomotion = loc.value"
                            class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all card-badge"
                            :class="form.locomotion === loc.value ? 'bg-[#3f2a1e] border-[#FF9500] text-[#FF9500] shadow-[0_0_15px_rgba(255,149,0,0.2)]' : 'bg-[#1f140e] border-white/10 text-white/40 hover:border-white/30 hover:text-white/80'"
                        >
                            <i :class="[loc.icon]" class="text-2xl mb-1"></i>
                            <span class="text-[9px]">{{ loc.label }}</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-4 bg-[#1f140e] border border-[#3f2a1e] rounded-xl">
                    <div class="flex justify-between items-end mb-2">
                        <div class="space-y-1">
                            <label class="card-badge text-[9px] text-white/40">Chronomètre</label>
                            <p class="card-badge text-[7px] text-[#FF9500]/60">Estimation auto activée</p>
                        </div>
                        <div class="flex items-baseline gap-1 text-[#FF9500]">
                            <i class="pi pi-clock text-sm mr-1"></i>
                            <span class="card-badge text-2xl">{{ form.duree }}</span>
                            <span class="card-badge text-[10px]">min</span>
                        </div>
                    </div>
                    <Slider v-model="form.duree" :min="15" :max="180" :step="5" class="board-slider" />
                </div>

                <div class="space-y-3">
                    <label class="card-badge text-[9px] text-white/40 ml-1">Niveau de défi</label>
                    <SelectButton v-model="form.difficulte" :options="difficulties" optionLabel="label" optionValue="value" class="board-select-button" />
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3 w-full pt-4 mt-2 border-t border-white/5">
                    <Button 
                        @click="submit"
                        :label="form.mode === 'solo' ? 'Lancer la Quête' : 'Former la Guilde'"
                        :icon="form.mode === 'solo' ? 'pi pi-play' : 'pi pi-users'"
                        class="w-full p-5 rounded-xl bg-[#FF9500] text-[#1f140e] border-none font-black uppercase tracking-widest text-xs shadow-[0_5px_0_#cc7a00] active:translate-y-[5px] active:shadow-none transition-all hover:bg-[#ffaa33]"
                        :loading="form.processing"
                    />
                </div>
            </template>
        </Dialog>

    </AuthenticatedLayout>
</template>

<style>
/* Game Dialog Overrides */
.game-dialog {
    background: #2a1c14 !important;
    border: 4px solid #5c4033 !important;
    border-radius: 20px !important;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.9) !important;
    color: #f5e8c7 !important;
}
.game-dialog .p-dialog-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding: 1.5rem !important;
    color: #FF9500 !important;
}
.game-dialog .p-dialog-title {
    font-family: 'Cinzel', serif !important;
    font-size: 1.25rem;
    font-weight: 900;
    text-transform: uppercase;
}
.game-dialog .p-dialog-content {
    background: transparent !important;
    color: #f5e8c7 !important;
}
.game-dialog .p-dialog-footer {
    background: transparent !important;
    padding: 1.5rem !important;
}
.game-dialog .p-dialog-header-icons .p-dialog-header-icon {
    color: rgba(255, 255, 255, 0.4) !important;
}

/* Board Select Button */
.board-select-button {
    display: flex;
    gap: 0.5rem;
}
.board-select-button .p-button {
    flex: 1;
    background: #1f140e !important;
    border: 2px solid #3f2a1e !important;
    color: rgba(255, 255, 255, 0.5) !important;
    border-radius: 12px !important;
    transition: all 0.3s;
}
.board-select-button .p-button:hover {
    border-color: rgba(255, 149, 0, 0.5) !important;
    color: rgba(255, 255, 255, 0.8) !important;
}
.board-select-button .p-button.p-highlight {
    background: #3f2a1e !important;
    border-color: #FF9500 !important;
    color: #FF9500 !important;
    box-shadow: 0 0 15px rgba(255, 149, 0, 0.15);
}

/* Board Slider */
.board-slider.p-slider {
    background: #1f140e !important;
    border: 1px solid #3f2a1e !important;
    height: 8px !important;
}
.board-slider .p-slider-range {
    background: linear-gradient(to right, #FF9500, #ffc266) !important;
}
.board-slider .p-slider-handle {
    border: 4px solid #1f140e !important;
    background: #FF9500 !important;
    box-shadow: 0 0 10px rgba(255, 149, 0, 0.5) !important;
    width: 20px !important;
    height: 20px !important;
    transition: transform 0.1s;
}
.board-slider .p-slider-handle:hover {
    transform: scale(1.2);
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease forwards;
}

:root.light-theme .game-dialog {
    background: #fcf8f2 !important;
    border-color: #d4b88a !important;
    color: #1f140e !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
}
:root.light-theme .game-dialog .p-dialog-header {
    border-color: rgba(0, 0, 0, 0.05);
    color: var(--accent-primary) !important;
}
:root.light-theme .game-dialog .p-dialog-content { color: #1f140e !important; }
:root.light-theme .game-dialog .p-dialog-header-icons .p-dialog-header-icon { color: rgba(0, 0, 0, 0.4) !important; }

:root.light-theme .board-select-button .p-button {
    background: #ffffff !important;
    border-color: #e5d3b3 !important;
    color: rgba(0, 0, 0, 0.5) !important;
}
:root.light-theme .board-select-button .p-button:hover {
    border-color: var(--accent-primary) !important;
}
:root.light-theme .board-select-button .p-button.p-highlight {
    background: #f0fdf4 !important;
    border-color: var(--accent-primary) !important;
    color: var(--accent-primary) !important;
}

:root.light-theme .board-slider.p-slider {
    background: #ffffff !important;
    border-color: #e5d3b3 !important;
}
:root.light-theme .board-slider .p-slider-range {
    background: linear-gradient(to right, var(--accent-primary), #34d399) !important;
}
:root.light-theme .board-slider .p-slider-handle {
    border-color: #ffffff !important;
    background: var(--accent-primary) !important;
}
</style>

