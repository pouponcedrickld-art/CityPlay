<script setup>
/*
|--------------------------------------------------------------------------
| IMPORTS VUE + INERTIA
|--------------------------------------------------------------------------
*/
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputNumber from 'primevue/inputnumber';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/
const props = defineProps({
    lieux: Array,
    types: Array
});

/*
|--------------------------------------------------------------------------
| MAP SETUP
|--------------------------------------------------------------------------
*/
const mapContainer = ref(null);
let map = null;
let marker = null;

const initMap = () => {
    if (!mapContainer.value) return;

    map = L.map(mapContainer.value).setView([48.8566, 2.3522], 13); // Default Paris

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
};

const updateMarker = (lat, lng) => {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng]).addTo(map);
    }
    map.setView([lat, lng], 16);
};

onMounted(() => {
    initMap();
});

/*
|--------------------------------------------------------------------------
| FORMULAIRE
|--------------------------------------------------------------------------
*/
const form = useForm({
    lieu_id: null,
    type: 'force1',
    titre: '',
    texte: '',
    reponse: '',
    solution: '',
    points: 10,
    image_url: '',
    actif: true
});

// Quand on change de lieu, on centre la carte sur le lieu
watch(() => form.lieu_id, (newLieuId) => {
    const lieu = props.lieux.find(l => l.id === newLieuId);
    if (lieu && lieu.latitude && lieu.longitude) {
        updateMarker(lieu.latitude, lieu.longitude);
    }
});

const submit = () => {
    form.post(route('enigmes.store'));
};
</script>

<template>
    <Head title="Créer une Énigme" />

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
                    Nouvelle <span class="text-[#FF9500]">Énigme</span>
                </h1>
                <p class="text-orange-900/60 font-medium text-sm mt-1 uppercase tracking-widest">
                    Ajouter un nouveau défi au parcours
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

                    <!-- Titre & Points -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Titre de l'énigme</label>
                            <InputText
                                v-model="form.titre"
                                placeholder="Nom de l'énigme"
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                                :class="{ 'p-invalid': form.errors.titre }"
                            />
                            <small class="text-red-500" v-if="form.errors.titre">{{ form.errors.titre }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Points accordés</label>
                            <InputNumber
                                v-model="form.points"
                                :min="0"
                                placeholder="Nombre de points"
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                                :class="{ 'p-invalid': form.errors.points }"
                            />
                            <small class="text-red-500" v-if="form.errors.points">{{ form.errors.points }}</small>
                        </div>
                    </div>

                    <!-- Texte de l'énigme -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Texte de l'énigme (Question)</label>
                        <Textarea
                            v-model="form.texte"
                            rows="4"
                            placeholder="Écrivez ici l'énigme que les joueurs devront résoudre..."
                            class="w-full border-orange-50 focus:border-orange-300 rounded-xl p-4"
                            :class="{ 'p-invalid': form.errors.texte }"
                        />
                        <small class="text-red-500" v-if="form.errors.texte">{{ form.errors.texte }}</small>
                    </div>

                    <!-- Réponse & Solution -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Réponse attendue (Indice court)</label>
                            <InputText
                                v-model="form.reponse"
                                placeholder="Ex: La tour Eiffel"
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                                :class="{ 'p-invalid': form.errors.reponse }"
                            />
                            <small class="text-red-500" v-if="form.errors.reponse">{{ form.errors.reponse }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Solution détaillée</label>
                            <Textarea
                                v-model="form.solution"
                                rows="2"
                                placeholder="Explication complète de la solution..."
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl p-4"
                                :class="{ 'p-invalid': form.errors.solution }"
                            />
                            <small class="text-red-500" v-if="form.errors.solution">{{ form.errors.solution }}</small>
                        </div>
                    </div>

                    <!-- Géolocalisation -->
                    <div class="flex flex-col gap-4">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Localisation du lieu associé</label>
                        <div ref="mapContainer" class="w-full h-64 rounded-2xl border border-orange-100 overflow-hidden z-0"></div>
                        
                        <p class="text-[10px] text-orange-900/60 font-medium italic">
                            Note: La localisation est définie par le lieu sélectionné ci-dessus.
                        </p>
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
                            label="Enregistrer l'énigme"
                            icon="pi pi-check"
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