<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    partie: Object,
    progression: Object,
});

const page = usePage();

const mapContainer = ref(null);
const map = ref(null);
const playerMarker = ref(null);
const playerPosition = ref(null);
const tempsTrajet = ref(null);
const isLoadingTemps = ref(false);
let watchId = null;

// Calcule le temps de trajet depuis la position actuelle jusqu'au lieu cible
const calculerTempsTrajet = async () => {
    if (!playerPosition.value) return;

    isLoadingTemps.value = true;

    try {
        const response = await fetch(`/api/parties/${props.partie.id}/temps-trajet`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                lat: playerPosition.value.lat,
                lng: playerPosition.value.lng,
            }),
        });

        const data = await response.json();

        if (data.succes) {
            tempsTrajet.value = data.data;
        }
    } catch (error) {
        console.error('Erreur calcul temps trajet:', error);
    } finally {
        isLoadingTemps.value = false;
    }
};

// Watch la position pour recalculer automatiquement
watch(playerPosition, (newPos, oldPos) => {
    if (newPos && (!oldPos ||
        Math.abs(newPos.lat - oldPos.lat) > 0.001 ||
        Math.abs(newPos.lng - oldPos.lng) > 0.001)) {
        // Recalcule si la position a changé significativement (~100m)
        calculerTempsTrajet();
    }
}, { immediate: false });

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

                // Sauvegarde la position pour le calcul temps trajet
                playerPosition.value = { lat: latitude, lng: longitude };

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

// Retourne l'icône PrimeIcons correspondant au mode de locomotion
const getLocomotionIcon = (mode) => {
    const icons = {
        'pied': 'pi pi-walking',
        'velo': 'pi pi-bicycle',
        'trottinette': 'pi pi-arrow-right', // Pas d'icône trottinette spécifique
        'transports': 'pi pi-bus',
    };
    return icons[mode] || 'pi pi-map-marker';
};

onMounted(() => {
    initMap();

    // Écoute sur le canal privé de la partie pour synchroniser la progression
    window.Echo.private(`partie.${props.partie.id}`)
        .listen('.EnigmeResolue', (e) => {
            console.log('Progression mise à jour via WebSocket (Carte):', e);

            // Si l'action a été faite par quelqu'un d'autre
            if (e.resolu_par.id !== page.props.auth.user.id) {
                if (e.lieu_id) {
                    // Si résolue, on montre le succès
                    router.visit(route('progression.success', {
                        partie: props.partie.id,
                        lieu: e.lieu_id
                    }));
                } else {
                    // Sinon (passée), on va à la suivante
                    router.visit(route('progression.enigme', props.partie.id), {
                        replace: true
                    });
                }
            }
        });
});

onUnmounted(() => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
    window.Echo.leave(`partie.${props.partie.id}`);
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
                    <Link :href="route('progression.enigme', partie.id)" class="cave-hud__btn">
                        <i class="pi pi-arrow-left" />
                    </Link>
                    <div>
                        <p class="cave-hud__mission">Exploration</p>
                        <p class="cave-hud__title">Carte de la zone</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="cave-hud__score" title="Score de la mission">
                        <i class="pi pi-star" />
                        <span>{{ progression?.score || 0 }}</span>
                    </div>
                    <div class="cave-hud__score cave-hud__score--gold" title="Score global">
                        <i class="pi pi-star-fill" />
                        <span>{{ $page.props.auth.user.total_score || 0 }}</span>
                    </div>
                </div>
            </header>

            <!-- Map Full Screen -->
            <div ref="mapContainer" class="flex-1 z-0" />

            <!-- Panneau d'info temps de trajet -->
            <div v-if="tempsTrajet" class="absolute bottom-6 left-6 z-10 max-w-xs">
                <div class="cave-card p-4 rounded-xl shadow-lg bg-slate-800/90 backdrop-blur border border-slate-600">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="pi pi-clock text-yellow-400"></i>
                        <span class="text-sm font-semibold text-slate-200">Temps estimé</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-white">{{ tempsTrajet.temps_formate }}</span>
                        <span class="text-xs text-slate-400">vers {{ tempsTrajet.lieu_nom }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                        <i class="pi pi-map-marker"></i>
                        <span>{{ tempsTrajet.distance_km }} km</span>
                        <span class="mx-1">•</span>
                        <i :class="getLocomotionIcon(tempsTrajet.mode_locomotion)"></i>
                        <span class="capitalize">{{ tempsTrajet.mode_locomotion }}</span>
                    </div>
                </div>
            </div>

            <!-- Bouton de recentrage -->
            <div class="absolute bottom-6 right-6 z-10">
                <button type="button" class="cave-btn relative"
                    style="width:56px;height:56px;border-radius:50%;padding:0" @click="recenterMap"
                    title="Recentrer sur ma position">
                    <i class="pi pi-crosshairs text-xl" />
                    <span
                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                        Ma position
                    </span>
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
