<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    partie: Object,
    progression: Object,
});

const mapContainer = ref(null);
const map = ref(null);
const playerMarker = ref(null);
let watchId = null;

const initMap = () => {
    if (!mapContainer.value) return;

    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    map.value = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([44.841389, -0.569722], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map.value);

    // Afficher les lieux déjà découverts
    if (props.progression?.lieux_decouverts) {
        props.progression.lieux_decouverts.forEach(lieu => {
            if (lieu.latitude && lieu.longitude) {
                L.marker([lieu.latitude, lieu.longitude], {
                    icon: L.divIcon({
                        className: 'cave-marker-discovered',
                        html: '<div class="w-8 h-8 bg-green-500 border-2 border-white rounded-full flex items-center justify-center shadow-lg"><i class="pi pi-check text-white text-xs"></i></div>',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    })
                }).addTo(map.value).bindPopup(lieu.nom);
            }
        });
    }

    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                const latlng = [latitude, longitude];

                if (!playerMarker.value) {
                    playerMarker.value = L.marker(latlng).addTo(map.value);
                    map.value.setView(latlng, 16);
                } else {
                    playerMarker.value.setLatLng(latlng);
                }
            },
            (error) => console.warn("Erreur GPS:", error),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
};

const recenterMap = () => {
    if (playerMarker.value && map.value) {
        map.value.setView(playerMarker.value.getLatLng(), 16);
    }
};

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (map.value) map.value.remove();
});
</script>

<template>
    <CaveGameLayout>
        <Head title="Carte de la Quête" />

        <div class="h-screen w-full relative overflow-hidden flex flex-col">
            <!-- HUD Minimaliste pour la carte -->
            <header class="cave-hud z-50">
                <div class="flex items-center gap-3">
                    <Link :href="route('progression.show', partie.id)" class="cave-hud__btn">
                        <i class="pi pi-arrow-left" />
                    </Link>
                    <div>
                        <p class="cave-hud__mission">Exploration</p>
                        <p class="cave-hud__title">Carte de la zone</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="cave-hud__score">
                        <i class="pi pi-star-fill" />
                        <span>{{ progression?.score || 0 }}</span>
                    </div>
                </div>
            </header>

            <!-- Map Full Screen -->
            <div ref="mapContainer" class="flex-1 z-0" />

            <!-- Bouton de recentrage -->
            <div class="absolute bottom-6 right-6 z-10">
                <button
                    type="button"
                    class="cave-btn"
                    style="width:56px;height:56px;border-radius:50%;padding:0"
                    @click="recenterMap"
                >
                    <i class="pi pi-crosshairs text-xl" />
                </button>
            </div>
        </div>
    </CaveGameLayout>
</template>

<style scoped>
.cave-marker-discovered {
    background: transparent;
    border: none;
}
</style>
