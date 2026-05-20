<script setup>
import { onMounted, ref, watch, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Fix pour les icônes Leaflet avec Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const props = defineProps({
    playerLat: Number,
    playerLng: Number,
    targetLat: Number,
    targetLng: Number,
    targetRadius: { type: Number, default: 20 },
    zoom: { type: Number, default: 16 },
});

const mapContainer = ref(null);
let map = null;
let playerMarker = null;
let playerCircle = null;
let targetMarker = null;
let targetCircle = null;

const initMap = () => {
    if (!mapContainer.value) return;

    // Utiliser la position du joueur ou de la cible pour le centre initial
    const center = props.playerLat ? [props.playerLat, props.playerLng] : [props.targetLat, props.targetLng];

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView(center, props.zoom);

    // Style de carte sombre (CartoDB Dark Matter)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Icône personnalisée pour le joueur (Cyan Neon)
    const playerIcon = L.divIcon({
        className: 'player-location-icon',
        html: '<div class="player-marker-glow"></div><div class="player-marker-core"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    // Icône personnalisée pour la cible (Gold Neon)
    const targetIcon = L.divIcon({
        className: 'target-location-icon',
        html: '<div class="target-marker-glow"></div><div class="target-marker-core"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    // Ajouter les marqueurs
    if (props.playerLat) {
        playerMarker = L.marker([props.playerLat, props.playerLng], { icon: playerIcon }).addTo(map);
    }

    if (props.targetLat) {
        targetMarker = L.marker([props.targetLat, props.targetLng], { icon: targetIcon }).addTo(map);
        targetCircle = L.circle([props.targetLat, props.targetLng], {
            radius: props.targetRadius,
            color: '#f5c518',
            fillColor: '#f5c518',
            fillOpacity: 0.1,
            weight: 1,
            dashArray: '5, 10'
        }).addTo(map);
    }

    // Ajuster la vue pour voir les deux points si présents
    if (props.playerLat && props.targetLat) {
        const bounds = L.latLngBounds([props.playerLat, props.playerLng], [props.targetLat, props.targetLng]);
        map.fitBounds(bounds, { padding: [50, 50] });
    }
};

// Mettre à jour la position du joueur en temps réel
watch(() => [props.playerLat, props.playerLng], ([lat, lng]) => {
    if (map && lat && lng) {
        if (!playerMarker) {
            const playerIcon = L.divIcon({
                className: 'player-location-icon',
                html: '<div class="player-marker-glow"></div><div class="player-marker-core"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            playerMarker = L.marker([lat, lng], { icon: playerIcon }).addTo(map);
        } else {
            playerMarker.setLatLng([lat, lng]);
        }
    }
});

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (map) {
        map.remove();
    }
});
</script>

<template>
    <div class="game-map-wrapper relative h-full w-full overflow-hidden">
        <div ref="mapContainer" class="w-full h-full"></div>

        <!-- Map Overlay UI -->
        <div class="absolute top-4 left-4 z-[1000] flex flex-col gap-2">
            <div class="bg-black/60 backdrop-blur-md p-2 rounded-xl border border-white/10 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-[#00e5ff] shadow-[0_0_5px_#00e5ff]"></div>
                <span class="text-[8px] font-black text-white uppercase tracking-widest">Vous</span>
            </div>
            <div class="bg-black/60 backdrop-blur-md p-2 rounded-xl border border-white/10 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-[#f5c518] shadow-[0_0_5px_#f5c518]"></div>
                <span class="text-[8px] font-black text-white uppercase tracking-widest">Objectif</span>
            </div>
        </div>
    </div>
</template>

<style>
/* Styles pour les marqueurs Leaflet personnalisés */
.player-location-icon {
    position: relative;
}
.player-marker-core {
    width: 12px;
    height: 12px;
    background: #00e5ff;
    border: 2px solid white;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
}
.player-marker-glow {
    width: 30px;
    height: 30px;
    background: rgba(0, 229, 255, 0.3);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: map-pulse 2s infinite;
}

.target-location-icon {
    position: relative;
}
.target-marker-core {
    width: 16px;
    height: 16px;
    background: #f5c518;
    border: 2px solid white;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
}
.target-marker-glow {
    width: 40px;
    height: 40px;
    background: rgba(245, 197, 24, 0.2);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: map-pulse 3s infinite;
}

@keyframes map-pulse {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
}

/* Fix pour Leaflet en dark mode */
.leaflet-container {
    background: #0a0a0f !important;
}
</style>
