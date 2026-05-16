<script setup>
/*
|--------------------------------------------------------------------------
| IMPORTS VUE + INERTIA
|--------------------------------------------------------------------------
*/
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/
const props = defineProps({
    enigme: Object,
    lieux: Array,
    types: Array
});

/*
|--------------------------------------------------------------------------
| FORMULAIRE
|--------------------------------------------------------------------------
*/
const form = useForm({
    lieu_id: props.enigme.lieu_id,
    type: props.enigme.type,
    texte: props.enigme.texte,
    image_url: props.enigme.image_url || '',
    actif: props.enigme.actif
});

const submit = () => {
    form.put(route('enigmes.update', props.enigme.id));
};
</script>

<template>
    <Head title="Modifier l'Énigme" />

    <SidebarLayout>
        <div class="p-4 sm:p-8 min-h-screen bg-gray-50/50">
            
            <!-- HEADER -->
            <div class="mb-8">
                <button 
                    @click="$window.history.back()" 
                    class="flex items-center gap-2 text-orange-900/40 hover:text-[#FF9500] font-bold text-[10px] uppercase tracking-widest transition-colors mb-4"
                >
                    <i class="pi pi-arrow-left" />
                    Retour à la liste
                </button>
                <h1 class="text-3xl font-black text-orange-950 uppercase tracking-tight">
                    Modifier <span class="text-[#FF9500]">l'Énigme</span>
                </h1>
                <p class="text-orange-900/60 font-medium text-sm mt-1 uppercase tracking-widest">
                    Édition du défi #{{ enigme.id }}
                </p>
            </div>

            <!-- FORMULAIRE -->
            <form @submit.prevent="submit" class="max-w-3xl">
                <div class="bg-white p-8 rounded-3xl border border-orange-100 shadow-sm space-y-6">
                    
                    <!-- Lieu & Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Lieu associé</label>
                            <Select
                                v-model="form.lieu_id"
                                :options="lieux"
                                optionLabel="nom"
                                optionValue="id"
                                placeholder="Sélectionner un lieu"
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                                :class="{ 'p-invalid': form.errors.lieu_id }"
                            />
                            <small class="text-red-500" v-if="form.errors.lieu_id">{{ form.errors.lieu_id }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Difficulté / Type</label>
                            <Select
                                v-model="form.type"
                                :options="types"
                                placeholder="Type d'énigme"
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                                :class="{ 'p-invalid': form.errors.type }"
                            />
                            <small class="text-red-500" v-if="form.errors.type">{{ form.errors.type }}</small>
                        </div>
                    </div>

                    <!-- Texte de l'énigme -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Texte de l'énigme</label>
                        <Textarea
                            v-model="form.texte"
                            rows="5"
                            placeholder="Écrivez ici l'énigme que les joueurs devront résoudre..."
                            class="w-full border-orange-50 focus:border-orange-300 rounded-xl p-4"
                            :class="{ 'p-invalid': form.errors.texte }"
                        />
                        <small class="text-red-500" v-if="form.errors.texte">{{ form.errors.texte }}</small>
                    </div>

                    <!-- Image URL -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">URL de l'image (Optionnel)</label>
                        <IconField>
                            <InputIcon class="pi pi-image text-orange-300" />
                            <InputText
                                v-model="form.image_url"
                                placeholder="https://..."
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                            />
                        </IconField>
                        <small class="text-red-500" v-if="form.errors.image_url">{{ form.errors.image_url }}</small>
                    </div>

                    <!-- Statut -->
                    <div class="flex items-center justify-between p-4 bg-orange-50/50 rounded-2xl border border-orange-100">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-950 uppercase tracking-tight">Activer l'énigme</span>
                            <span class="text-[10px] text-orange-900/60 uppercase font-medium">L'énigme sera visible immédiatement</span>
                        </div>
                        <ToggleSwitch v-model="form.actif" />
                    </div>

                    <!-- Bouton de validation -->
                    <div class="flex justify-end pt-4">
                        <Button
                            type="submit"
                            label="Mettre à jour"
                            icon="pi pi-save"
                            :loading="form.processing"
                            class="p-button-orange shadow-lg shadow-orange-200"
                        />
                    </div>

                </div>
            </form>

        </div>
    </SidebarLayout>
</template>

<style>
/* Réutilisation des styles globaux */
.p-button-orange {
    background: linear-gradient(135deg, #FF9500 0%, #FF7B00 100%) !important;
    border: none !important;
    border-radius: 16px !important;
    padding: 0.75rem 1.5rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-size: 0.8rem !important;
}

.p-textarea:focus {
    box-shadow: 0 0 0 2px rgba(255, 149, 0, 0.1) !important;
    border-color: #FF9500 !important;
}

.p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-slider {
    background: #FF9500 !important;
}
</style>