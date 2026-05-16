<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

const props = defineProps({
    partie: Object,
    progression: Object,
    enigme: Object
});

const form = useForm({
    reponse: ''
});

const showHint = ref(false);
const showSolution = ref(false);

const submitAnswer = () => {
    form.post(route('progression.submit', props.partie.id));
};

const skipEnigme = () => {
    if (confirm('Voulez-vous vraiment passer cette énigme ? Vous ne gagnerez pas de points.')) {
        router.post(route('progression.next', props.partie.id));
    }
};
</script>

<template>
    <Head title="Énigme en cours" />

    <div class="min-h-screen bg-gray-50 pb-20">
        <!-- HEADER -->
        <div class="bg-white border-b border-orange-100 p-4 flex justify-between items-center sticky top-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="pi pi-box text-[#FF9500]"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-orange-950 uppercase tracking-tight">
                        Énigme {{ progression.nb_enigmes_resolues + 1 }}
                    </h1>
                    <p class="text-[10px] text-orange-900/40 font-bold uppercase tracking-widest">
                        {{ enigme.type }} • {{ enigme.points }} pts
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="px-3 py-1 bg-orange-50 rounded-lg border border-orange-100 flex items-center gap-2">
                    <i class="pi pi-clock text-[#FF9500] text-xs"></i>
                    <span class="text-xs font-bold text-orange-950">{{ progression.temps_restant_minutes }} min</span>
                </div>
            </div>
        </div>

        <main class="p-6 max-w-2xl mx-auto space-y-6">
            <!-- IMAGE (si disponible) -->
            <div v-if="enigme.image_url" class="rounded-3xl overflow-hidden shadow-lg border-4 border-white">
                <img :src="enigme.image_url" alt="Image de l'énigme" class="w-full h-48 object-cover">
            </div>

            <!-- QUESTION -->
            <div class="bg-white p-8 rounded-3xl border border-orange-100 shadow-sm">
                <h2 class="text-xl font-black text-orange-950 uppercase tracking-tight mb-4">
                    {{ enigme.titre || 'Défi en cours' }}
                </h2>
                <p class="text-orange-900/80 leading-relaxed text-lg italic">
                    "{{ enigme.texte }}"
                </p>
            </div>

            <!-- FORMULAIRE -->
            <form @submit.prevent="submitAnswer" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <InputText
                        v-model="form.reponse"
                        placeholder="Votre réponse ici..."
                        class="w-full p-4 rounded-2xl border-orange-100 focus:border-orange-300 focus:ring-orange-100 shadow-sm"
                        :disabled="form.processing"
                    />
                </div>
                
                <Button
                    type="submit"
                    label="Valider ma réponse"
                    icon="pi pi-check"
                    class="w-full p-4 rounded-2xl bg-gradient-to-r from-[#FF9500] to-[#FF7B00] border-none font-bold uppercase tracking-widest shadow-lg shadow-orange-200"
                    :loading="form.processing"
                />
            </form>

            <!-- ACTIONS SECONDAIRES -->
            <div class="grid grid-cols-3 gap-3">
                <button 
                    @click="showHint = true"
                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:bg-orange-50 transition-colors"
                >
                    <i class="pi pi-info-circle text-[#FF9500]"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-orange-900/60">Indice</span>
                </button>

                <button 
                    @click="showSolution = true"
                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:bg-orange-50 transition-colors"
                >
                    <i class="pi pi-eye text-[#FF9500]"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-orange-900/60">Solution</span>
                </button>

                <button 
                    @click="skipEnigme"
                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:bg-red-50 transition-colors"
                >
                    <i class="pi pi-fast-forward text-red-400"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-red-900/60">Passer</span>
                </button>
            </div>
        </main>

        <!-- MODALS -->
        <Dialog v-model:visible="showHint" modal header="Besoin d'un indice ?" :style="{ width: '90vw', maxWidth: '400px' }">
            <p class="text-orange-900/80">
                L'indice sera implémenté prochainement pour vous aider à résoudre ce mystère !
            </p>
            <template #footer>
                <Button label="OK" icon="pi pi-check" @click="showHint = false" class="p-button-text p-button-orange" />
            </template>
        </Dialog>

        <Dialog v-model:visible="showSolution" modal header="Découvrir la solution ?" :style="{ width: '90vw', maxWidth: '400px' }">
            <p class="text-orange-900/80 mb-4">
                Attention, voir la solution vous fera perdre les points de cette énigme.
            </p>
            <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 italic">
                La solution sera bientôt disponible.
            </div>
            <template #footer>
                <Button label="Retour" icon="pi pi-times" @click="showSolution = false" class="p-button-text" />
                <Button label="Voir la solution" icon="pi pi-eye" @click="showSolution = false" class="p-button-orange" />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.p-button-orange {
    color: #FF9500;
}
</style>