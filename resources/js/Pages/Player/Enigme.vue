<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import ProgressSpinner from 'primevue/progressspinner';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    partie: Object,
    progression: Object,
    enigme: Object,
    flash: Object
});

const form = useForm({
    reponse: '',
    latitude: null,
    longitude: null,
    distance_metres: null
});

const showHint = ref(false);
const showSolution = ref(false);
const showSkipConfirm = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);
const mapContainer = ref(null);
let map = null;
let userMarker = null;

// Gestion de l'état offline
const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
};

const initMap = () => {
    if (!mapContainer.value) return;

    // Coordonnées du lieu (point d'arrivée/cible)
    const targetLat = props.enigme.lieu?.latitude || 48.8566;
    const targetLng = props.enigme.lieu?.longitude || 2.3522;

    map = L.map(mapContainer.value, {
        zoomControl: false
    }).setView([targetLat, targetLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Marqueur de la cible (optionnel, on peut le rendre discret)
    L.circle([targetLat, targetLng], {
        color: '#FF9500',
        fillColor: '#FF9500',
        fillOpacity: 0.1,
        radius: 50 // Cercle indicatif
    }).addTo(map);
};

const checkLocation = () => {
    if (isOffline.value) {
        alert("Vous semblez être hors-ligne. La vérification nécessite une connexion internet.");
        return;
    }

    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }

    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            form.latitude = latitude;
            form.longitude = longitude;

            if (userMarker) {
                userMarker.setLatLng([latitude, longitude]);
            } else {
                const userIcon = L.divIcon({
                    className: 'user-location-marker',
                    html: '<div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-lg animate-pulse"></div>',
                    iconSize: [16, 16]
                });
                userMarker = L.marker([latitude, longitude], { icon: userIcon }).addTo(map);
            }
            
            map.flyTo([latitude, longitude], 17);

            if (props.enigme.lieu?.latitude && props.enigme.lieu?.longitude) {
                const target = L.latLng(props.enigme.lieu.latitude, props.enigme.lieu.longitude);
                const user = L.latLng(latitude, longitude);
                const distance = user.distanceTo(target);
                form.distance_metres = Math.round(distance);

                if (distance <= 10) {
                    submitLocationAnswer();
                }
            }
            isLocating.value = false;
        },
        (error) => {
            isLocating.value = false;
            let msg = "Erreur de localisation.";
            if (error.code === 1) msg = "Veuillez autoriser l'accès à votre position.";
            alert(msg);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const submitLocationAnswer = () => {
    form.post(route('progression.submit', props.partie.id));
};

const submitTextAnswer = () => {
    if (!form.reponse) return;
    form.post(route('progression.submit', props.partie.id));
};

const confirmSkip = () => {
    showSkipConfirm.value = false;
    router.post(route('progression.next', props.partie.id));
};

onMounted(() => {
    initMap();
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});
</script>

<template>
    <Head title="Énigme en cours" />

    <div class="min-h-screen bg-gray-50 pb-24 transition-opacity duration-300" :class="{ 'opacity-50 pointer-events-none': isOffline }">
        <!-- OFFLINE BANNER -->
        <div v-if="isOffline" class="bg-red-500 text-white text-[10px] font-bold uppercase tracking-widest p-2 text-center sticky top-0 z-50">
            Mode Hors-Ligne • Connexion perdue
        </div>

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

        <!-- FLASH MESSAGES -->
        <div v-if="$page.props.flash.error" class="mx-6 mt-4 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-xs font-bold animate-bounce">
            <i class="pi pi-exclamation-circle mr-2"></i>
            {{ $page.props.flash.error }}
        </div>

        <main class="p-6 max-w-2xl mx-auto space-y-6">
            <!-- IMAGE -->
            <div v-if="enigme.image_url" class="relative group rounded-3xl overflow-hidden shadow-lg border-4 border-white transition-transform hover:scale-[1.02]">
                <img :src="enigme.image_url" alt="Image de l'énigme" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            </div>

            <!-- QUESTION -->
            <div class="bg-white p-8 rounded-3xl border border-orange-100 shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full opacity-50"></div>
                <h2 class="text-xl font-black text-orange-950 uppercase tracking-tight mb-4 relative z-10">
                    {{ enigme.titre || 'Défi en cours' }}
                </h2>
                <p class="text-orange-900/80 leading-relaxed text-lg italic relative z-10">
                    "{{ enigme.texte }}"
                </p>
            </div>

            <!-- CARTE ET GEOLOCALISATION -->
            <div class="space-y-4">
                <div class="bg-white p-2 rounded-3xl border border-orange-100 shadow-sm overflow-hidden relative group">
                    <div ref="mapContainer" class="w-full h-48 rounded-2xl z-0 grayscale-[0.5] group-hover:grayscale-0 transition-all"></div>
                    <div v-if="isLocating" class="absolute inset-0 bg-white/60 flex items-center justify-center z-10 backdrop-blur-[2px]">
                        <ProgressSpinner style="width: 40px; height: 40px" strokeWidth="4" />
                    </div>
                </div>

                <Button
                    @click="checkLocation"
                    :label="isLocating ? 'Localisation...' : 'Je suis sur place !'"
                    :icon="isLocating ? 'pi pi-spin pi-spinner' : 'pi pi-map-marker'"
                    class="w-full p-5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 border-none font-black uppercase tracking-widest shadow-xl shadow-blue-200 active:scale-95 transition-all"
                    :disabled="isLocating || isOffline"
                />
            </div>

            <!-- FORMULAIRE -->
            <form @submit.prevent="submitTextAnswer" class="space-y-4 bg-white/40 p-4 rounded-3xl border border-dashed border-orange-200">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-orange-900/40 ml-1">Une idée ? Saisissez votre réponse</label>
                    <div class="relative">
                        <InputText
                            v-model="form.reponse"
                            placeholder="Votre réponse ici..."
                            class="w-full p-4 pr-12 rounded-2xl border-orange-100 focus:border-orange-400 focus:ring-4 focus:ring-orange-50 shadow-sm transition-all"
                            :disabled="form.processing || isOffline"
                        />
                        <button 
                            v-if="form.reponse"
                            type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-orange-500 text-white rounded-xl flex items-center justify-center shadow-md active:scale-90 transition-transform"
                            :disabled="form.processing"
                        >
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- ACTIONS SECONDAIRES -->
            <div class="grid grid-cols-3 gap-3">
                <button 
                    @click="showHint = true"
                    class="group flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:border-orange-300 hover:shadow-md transition-all active:scale-95"
                >
                    <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                        <i class="pi pi-info-circle text-[#FF9500]"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/60">Indice</span>
                </button>

                <button 
                    @click="showSolution = true"
                    class="group flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:border-orange-300 hover:shadow-md transition-all active:scale-95"
                >
                    <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                        <i class="pi pi-eye text-[#FF9500]"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/60">Solution</span>
                </button>

                <button 
                    @click="showSkipConfirm = true"
                    class="group flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-orange-100 shadow-sm hover:border-red-200 hover:shadow-md transition-all active:scale-95"
                >
                    <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center group-hover:bg-red-100 transition-colors">
                        <i class="pi pi-fast-forward text-red-400"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-red-900/60">Passer</span>
                </button>
            </div>
        </main>

        <!-- MODALS -->
        <Dialog v-model:visible="showHint" modal header="Besoin d'un indice ?" :style="{ width: '90vw', maxWidth: '400px' }" class="custom-dialog">
            <p class="text-orange-900/80 py-4 font-medium leading-relaxed">
                L'indice sera implémenté prochainement pour vous aider à résoudre ce mystère !
            </p>
            <template #footer>
                <Button label="Compris !" icon="pi pi-check" @click="showHint = false" class="p-button-orange w-full" />
            </template>
        </Dialog>

        <Dialog v-model:visible="showSolution" modal header="Voir la solution ?" :style="{ width: '90vw', maxWidth: '400px' }" class="custom-dialog">
            <div class="space-y-4 py-4">
                <div class="flex items-center gap-3 p-3 bg-red-50 rounded-xl border border-red-100">
                    <i class="pi pi-info-circle text-red-500"></i>
                    <p class="text-[11px] font-bold text-red-700 uppercase leading-tight">
                        Attention : Voir la solution annulera les points de cette énigme.
                    </p>
                </div>
                <div v-if="enigme.solution" class="bg-orange-50 p-5 rounded-2xl border border-orange-100 italic text-orange-950 shadow-inner">
                    {{ enigme.solution }}
                </div>
                <div v-else class="bg-orange-50 p-5 rounded-2xl border border-orange-100 italic text-orange-950 shadow-inner">
                    La réponse est : <span class="font-black uppercase tracking-tighter">{{ enigme.reponse }}</span>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 w-full">
                    <Button label="Annuler" icon="pi pi-times" @click="showSolution = false" class="p-button-text flex-1" />
                    <Button label="J'abandonne" icon="pi pi-check" @click="confirmSkip" class="p-button-orange flex-1" />
                </div>
            </template>
        </Dialog>

        <Dialog v-model:visible="showSkipConfirm" modal header="Passer cette énigme ?" :style="{ width: '90vw', maxWidth: '400px' }" class="custom-dialog">
            <p class="text-orange-900/80 py-4 font-medium">
                Voulez-vous vraiment passer ce défi ? Vous ne gagnerez aucun point pour cette étape.
            </p>
            <template #footer>
                <div class="flex gap-2 w-full">
                    <Button label="Non, je continue" @click="showSkipConfirm = false" class="p-button-text flex-1" />
                    <Button label="Oui, passer" @click="confirmSkip" class="p-button-danger p-button-outlined flex-1" />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<style>
.custom-dialog .p-dialog-header {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #FFF7ED;
}
.custom-dialog .p-dialog-title {
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.025em;
    color: #431407;
}
.custom-dialog .p-dialog-content {
    padding: 0 1.5rem 1.5rem 1.5rem;
}
.custom-dialog .p-dialog-footer {
    padding: 1.5rem;
    border-top: 1px solid #FFF7ED;
}
.p-button-orange {
    background: #FF9500 !important;
    border: none !important;
    border-radius: 1rem !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}
.user-location-marker {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
