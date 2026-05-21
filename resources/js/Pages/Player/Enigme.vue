<script setup>
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    partie: Object,
    progression: Object,
    enigme: Object,
});

const page = usePage();

const form = useForm({
    reponse: '',
    latitude: null,
    longitude: null,
    precision: null,
});

const showHint = ref(false);
const showSolution = ref(false);
const showTeam = ref(false);
const showSkipConfirm = ref(false);
const showAbandonConfirm = ref(false);
const showTimeExpired = ref(false);
const revealedSolution = ref(null);
const isRequestingSolution = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);
const gpsMessage = ref(null);
const lastGpsCheck = ref(null);

const lastKnownCoords = ref(null);
const lastKnownTime = ref(null);

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

    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                const latlng = [latitude, longitude];

                // Sauvegarder pour réutilisation immédiate
                lastKnownCoords.value = { latitude, longitude, accuracy };
                lastKnownTime.value = Date.now();

                if (!playerMarker.value) {
                    playerMarker.value = L.marker(latlng).addTo(map.value);
                    map.value.setView(latlng, 16);
                } else {
                    playerMarker.value.setLatLng(latlng);
                }
            },
            (error) => console.warn("Erreur GPS Radar:", error),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
        );
    }
};

const recenterMap = () => {
    if (playerMarker.value && map.value) {
        map.value.setView(playerMarker.value.getLatLng(), 16);
    }
};

const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
};

const gpsDisponible = computed(() => props.enigme?.lieu?.gps_disponible === true);
const pointsEnigme = computed(() => (props.enigme?.points > 0 ? props.enigme.points : 10));
const scoreActuel = computed(() => props.progression?.score ?? 0);

const applyFlash = () => {
    const flash = page.props.flash;
    if (flash?.error) {
        gpsMessage.value = { type: 'error', text: flash.error };
        // Si c'est une erreur de solution, on ferme quand même le chargement
        isRequestingSolution.value = false;
        showSolution.value = false;
    }
    if (flash?.gps_validation) {
        lastGpsCheck.value = flash.gps_validation;
    }
    if (flash?.solution_revelee) {
        revealedSolution.value = flash.solution_revelee;
        showTimeExpired.value = false;
        showSolution.value = false;
    }
    if (flash?.solution_revelee === null && revealedSolution.value !== null) {
        // Optionnel : reset si le flash est explicitement null après navigation
    }
};

watch(() => page.props.flash, applyFlash, { deep: true });

watch(
    () => props.enigme?.id,
    (newId, oldId) => {
        if (newId && newId !== oldId) {
            showTimeExpired.value = false;
            revealedSolution.value = null;
        }
    },
);

watch(
    () => props.progression?.temps_restant,
    (newTemps) => {
        if (newTemps != null) {
            timeLeft.value = newTemps;
        }
    }
);

const checkLocation = () => {
    if (isOffline.value) {
        alert('Vous semblez être hors-ligne. La vérification nécessite une connexion internet.');
        return;
    }
    if (!gpsDisponible.value) {
        gpsMessage.value = {
            type: 'error',
            text: 'La vérification GPS n\'est pas disponible pour cette énigme. Contactez l\'organisateur.',
        };
        return;
    }

    isLocating.value = true;
    gpsMessage.value = null;

    // STRATÉGIE 1 : Réutiliser la position du radar si elle est récente (< 10s)
    if (lastKnownCoords.value && lastKnownTime.value && (Date.now() - lastKnownTime.value < 10000)) {
        console.log("Utilisation de la position en cache (Radar)");
        form.latitude = lastKnownCoords.value.latitude;
        form.longitude = lastKnownCoords.value.longitude;
        form.precision = lastKnownCoords.value.accuracy;
        submitLocationAnswer();
        return;
    }

    if (!navigator.geolocation) {
        isLocating.value = false;
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }

    // STRATÉGIE 2 : Demander une nouvelle position avec un timeout plus long
    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log("Nouvelle position obtenue via getCurrentPosition:", position.coords);
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            form.precision = position.coords.accuracy ?? null;
            submitLocationAnswer();
        },
        (error) => {
            console.error("Erreur Geolocation:", error);

            // STRATÉGIE 3 : Fallback ultime - Utiliser la dernière position connue même si vieille
            if (lastKnownCoords.value) {
                console.warn("Échec GPS frais, utilisation de la dernière position connue (Fallback)");
                form.latitude = lastKnownCoords.value.latitude;
                form.longitude = lastKnownCoords.value.longitude;
                form.precision = lastKnownCoords.value.accuracy;
                submitLocationAnswer();
                return;
            }

            isLocating.value = false;
            let msg = 'Erreur de localisation. Réessayez en plein air.';

            if (error.code === 1) {
                msg = "Veuillez autoriser l'accès à votre position dans les réglages de votre navigateur.";
            } else if (error.code === 3) {
                msg = "Le signal GPS est trop faible pour une vérification précise. Rapprochez-vous d'une fenêtre ou sortez dehors.";
            }

            gpsMessage.value = {
                type: 'error',
                text: msg,
            };
        },
        { enableHighAccuracy: true, timeout: 20000, maximumAge: 10000 }
    );
};

const submitLocationAnswer = () => {
    form.post(route('progression.submit', props.partie.id), {
        preserveScroll: true,
        onFinish: () => {
            isLocating.value = false;
        },
    });
};

const submitTextAnswer = () => {
    if (form.reponse) {
        form.post(route('progression.submit', props.partie.id));
    }
};

const confirmSkip = () => {
    showSkipConfirm.value = false;
    showSolution.value = false;
    showTimeExpired.value = false;
    revealedSolution.value = null;
    router.post(route('progression.next', props.partie.id));
};

const skipAfterTimeout = () => {
    showTimeExpired.value = false;
    confirmSkip();
};

const requestSolutionAfterTimeout = () => {
    isRequestingSolution.value = true;
    router.post(route('progression.solution', props.partie.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            isRequestingSolution.value = false;
        },
    });
};

const continueAfterSolution = () => {
    revealedSolution.value = null;
    router.post(route('progression.next', props.partie.id));
};

const requestSolutionManual = () => {
    isRequestingSolution.value = true;
    router.post(route('progression.solution', props.partie.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            isRequestingSolution.value = false;
            showSolution.value = false;
        },
    });
};

const togglePause = () => {
    router.post(route('parties.web.pause', props.partie.id));
};

const confirmAbandon = () => {
    showAbandonConfirm.value = false;
    router.post(route('parties.web.abandon', props.partie.id));
};

const timeLeft = ref(props.progression?.temps_restant || 3600);
let timerInterval = null;

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const onTimerExpired = () => {
    if (showTimeExpired.value || revealedSolution.value || props.progression?.statut !== 'en_cours') {
        return;
    }
    showTimeExpired.value = true;
    timeLeft.value = 0;
    window.axios.post(route('progression.store', props.partie.id), {
        temps_restant: 0,
    }).catch(() => {});
};

const startTimer = () => {
    if (timerInterval) return;
    timerInterval = setInterval(() => {
        if (props.progression?.statut !== 'en_cours') {
            return;
        }
        if (timeLeft.value > 0) {
            timeLeft.value--;
            if (timeLeft.value > 0 && timeLeft.value % 30 === 0) {
                window.axios.post(route('progression.store', props.partie.id), {
                    temps_restant: timeLeft.value,
                }).catch(() => {});
            }
        } else if (!showTimeExpired.value && !revealedSolution.value) {
            onTimerExpired();
        }
    }, 1000);
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    applyFlash();
    startTimer();
    initMap();
    if (props.progression?.statut === 'en_cours' && timeLeft.value <= 0) {
        onTimerExpired();
    }
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    if (timerInterval) clearInterval(timerInterval);
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (map.value) {
        map.value.remove();
    }
});
</script>

<template>
    <CaveGameLayout>
        <Head :title="'Énigme - ' + (partie?.environnement?.nom || 'En cours')" />

        <div v-if="showTimeExpired && !revealedSolution" class="cave-overlay">
            <div class="cave-overlay__card">
                <div class="cave-overlay__header">
                    <div class="cave-logo-icon" style="width:64px;height:64px;margin:0 auto 12px"><i class="pi pi-clock text-2xl" /></div>
                    <h2 class="cave-result-title" style="font-size:1.25rem">Temps écoulé !</h2>
                </div>
                <div class="cave-overlay__body cave-btn-stack">
                    <p class="cave-hint text-center">
                        Le chrono est terminé pour cette énigme. Que souhaitez-vous faire ?
                    </p>
                    <button type="button" class="cave-btn" @click="skipAfterTimeout">
                        <i class="cave-btn__icon pi pi-fast-forward" />
                        <span class="cave-btn__label">Passer l'énigme</span>
                    </button>
                    <button
                        type="button"
                        class="cave-btn cave-btn--survival"
                        :disabled="isRequestingSolution"
                        @click="requestSolutionAfterTimeout"
                    >
                        <i class="cave-btn__icon" :class="isRequestingSolution ? 'pi pi-spin pi-spinner' : 'pi pi-eye'" />
                        <span class="cave-btn__label">{{ isRequestingSolution ? 'Chargement...' : 'Voir la solution' }}</span>
                    </button>
                    <p class="cave-hint text-center" style="font-size:0.7rem">
                        Passer ou voir la solution : aucun point pour ce défi.
                    </p>
                </div>
            </div>
        </div>

        <div v-if="revealedSolution" class="cave-overlay">
            <div class="cave-overlay__card">
                <div class="cave-overlay__header">
                    <div class="cave-logo-icon" style="width:64px;height:64px;margin:0 auto 12px"><i class="pi pi-eye text-2xl" /></div>
                    <h2 class="cave-result-title" style="font-size:1.25rem">Solution</h2>
                </div>
                <div class="cave-overlay__body cave-btn-stack">
                    <p class="cave-panel__text text-center" style="margin:0">{{ revealedSolution }}</p>
                    <p class="cave-flash cave-flash--error" style="margin:0">Aucun point ne sera attribué pour cette énigme.</p>
                    <button type="button" class="cave-btn w-full" @click="continueAfterSolution">
                        <i class="cave-btn__icon pi pi-arrow-right" />
                        <span class="cave-btn__label">Continuer la mission</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="progression?.statut === 'pause'" class="cave-overlay">
            <div class="cave-overlay__card">
                <div class="cave-overlay__header">
                    <div class="cave-logo-icon" style="width:64px;height:64px;margin:0 auto 12px"><i class="pi pi-pause text-2xl" /></div>
                    <h2 class="cave-result-title" style="font-size:1.25rem">Partie en pause</h2>
                </div>
                <div class="cave-overlay__body cave-btn-stack">
                    <p class="cave-hint text-center">{{ partie.environnement?.message_pause || 'Que souhaitez-vous faire ?' }}</p>
                    <button type="button" class="cave-btn" @click="togglePause">
                        <i class="cave-btn__icon pi pi-play" /><span class="cave-btn__label">Reprendre</span>
                    </button>
                    <button type="button" class="cave-btn cave-btn--ghost" @click="router.get(route('dashboard'))">
                        <i class="cave-btn__icon pi pi-home" /><span class="cave-btn__label">Retour au camp</span>
                    </button>
                    <button type="button" class="cave-btn cave-btn--danger" @click="showAbandonConfirm = true">
                        <i class="cave-btn__icon pi pi-flag" /><span class="cave-btn__label">Terminer la quête</span>
                    </button>
                </div>
            </div>
        </div>

        <div
            class="transition-opacity duration-300"
            :class="{ 'opacity-50 pointer-events-none': isOffline || progression?.statut === 'pause' || showTimeExpired || revealedSolution }"
        >
            <div v-if="isOffline" class="cave-offline-banner sticky top-0 z-50">
                <i class="pi pi-wifi mr-2" /> Connexion perdue
            </div>

            <header class="cave-hud">
                <div class="flex items-center gap-3">
                    <div class="cave-step-ring">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 48 48">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" opacity="0.2" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent"
                                :stroke-dasharray="125.6"
                                :stroke-dashoffset="125.6 * (1 - (progression?.lieux_decouverts?.length + 1) / ((progression?.lieux_restants?.length || 0) + (progression?.lieux_decouverts?.length || 0) + 1))" />
                        </svg>
                        <span class="cave-step-ring__label">{{ (progression?.lieux_decouverts?.length || 0) + 1 }}</span>
                    </div>
                    <div>
                        <p class="cave-hud__mission">Mission</p>
                        <p class="cave-hud__title truncate max-w-[120px] lg:max-w-xs">{{ partie?.environnement?.nom }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Bouton Dashboard (Mobile) -->
                    <Link :href="route('dashboard')" class="cave-hud__btn lg:hidden" title="Dashboard">
                        <i class="pi pi-home" />
                    </Link>

                    <div class="cave-hud__score" :title="'Score de la mission: ' + scoreActuel + ' pts'">
                        <i class="pi pi-star" />
                        <span>{{ scoreActuel }}</span>
                    </div>

                    <!-- Bouton Équipe (si mode team) -->
                    <button v-if="partie.mode === 'team'" type="button" class="cave-hud__btn" @click="showTeam = true" title="Membres de l'équipe">
                        <i class="pi pi-users" />
                    </button>

                    <div class="cave-hud__score cave-hud__score--gold" :title="'Score global: ' + ($page.props.auth.user.total_score || 0) + ' pts'">
                        <i class="pi pi-star-fill" />
                        <span>{{ $page.props.auth.user.total_score || 0 }}</span>
                    </div>
                    <div class="cave-hud__timer" :class="{ 'cave-hud__timer--urgent': timeLeft < 300 }">
                        <span>{{ formatTime(timeLeft) }}</span>
                        <i class="pi pi-clock" />
                    </div>
                    <Link :href="route('progression.carte', partie.id)" class="cave-hud__btn" title="Carte">
                        <i class="pi pi-map" />
                    </Link>
                    <button type="button" class="cave-hud__btn" @click="togglePause"><i class="pi pi-pause" /></button>

                    <!-- Bouton Admin -->
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('admin.dashboard')" class="cave-hud__btn cave-hud__btn--admin" title="Dashboard Admin">
                        <i class="pi pi-cog" />
                    </Link>
                </div>
            </header>

            <main class="cave-game-content cave-game-content--split">
                <article class="cave-panel">
                    <div v-if="enigme?.image_url" class="relative">
                        <img :src="enigme.image_url" class="cave-panel__img" :alt="enigme?.titre">
                        <div class="cave-level-card__overlay absolute inset-0" />
                    </div>
                    <div v-else class="cave-panel__img-placeholder flex items-center justify-center bg-[var(--cave-rock-dark)]" style="height: 200px;">
                        <i class="pi pi-question-circle text-4xl opacity-20" />
                    </div>
                    <div class="cave-panel__body">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="cave-badge cave-badge--gold">{{ enigme?.type }}</span>
                            <span class="cave-badge cave-badge--pts">+{{ pointsEnigme }} pts si réussi</span>
                        </div>
                        <h2 class="cave-panel__title">{{ enigme?.titre || 'Défi secret' }}</h2>
                        <p class="cave-panel__text">
                            <i class="pi pi-info-circle mr-2 opacity-50" />
                            "{{ enigme?.texte || 'Pas de description pour cette énigme.' }}"
                        </p>
                    </div>
                </article>

                <div class="cave-game-sidebar-col">
                    <div class="cave-panel cave-radar-box p-0 overflow-hidden relative" style="height: 200px;">
                        <div ref="mapContainer" class="absolute inset-0 z-0" />
                        <div class="cave-radar-pulse absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 rounded-full pointer-events-none" :class="{ 'cave-radar-pulse--active': isLocating }" />
                        <div class="relative z-10 flex flex-col items-center text-center gap-1 py-4 pointer-events-none bg-gradient-to-b from-white/20 to-transparent">
                            <div class="cave-tool-btn__icon" style="width:40px;height:40px;font-size:1.2rem;background:rgba(255,255,255,0.4)">
                                <i class="pi pi-map-marker" :class="{ 'animate-ping': isLocating }" />
                            </div>
                            <h3 class="cave-section-title" style="font-size:0.7rem;margin:0">Radar de zone</h3>
                        </div>
                        <button type="button" class="absolute bottom-2 right-2 z-10 cave-hud__btn" style="width:32px;height:32px;background:var(--cave-rock-light);color:var(--cave-border-dark);border:2px solid var(--cave-border-dark)" @click="recenterMap">
                            <i class="pi pi-crosshairs text-sm" />
                        </button>
                    </div>

                    <div v-if="gpsMessage" class="cave-flash" :class="gpsMessage.type === 'error' ? 'cave-flash--error' : 'cave-flash--info'">
                        {{ gpsMessage.text }}
                    </div>

                    <button
                        type="button"
                        class="cave-btn w-full"
                        :disabled="isLocating || isOffline || !gpsDisponible || form.processing"
                        @click="checkLocation"
                    >
                        <i class="cave-btn__icon" :class="isLocating || form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-compass'" />
                        <span class="cave-btn__label">{{ isLocating || form.processing ? 'Vérification...' : 'Vérifier ma position' }}</span>
                    </button>

                    <div class="cave-panel cave-panel__body space-y-3">
                        <h3 class="cave-section-title" style="font-size:0.75rem;margin:0">Réponse texte</h3>
                        <form class="relative" @submit.prevent="submitTextAnswer">
                            <InputText v-model="form.reponse" placeholder="Code ou réponse..." class="cave-game-input w-full" :disabled="form.processing || isOffline" />
                            <button v-if="form.reponse" type="submit" class="cave-copy-btn absolute right-2 top-1/2 -translate-y-1/2" :disabled="form.processing">
                                <i :class="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-send'" />
                            </button>
                        </form>
                    </div>

                    <div class="cave-tool-grid">
                        <button v-if="partie.mode === 'team'" type="button" class="cave-tool-btn" @click="showTeam = true">
                            <div class="icon-box"><i class="pi pi-users" /></div>
                            <span class="label">Équipe</span>
                        </button>
                        <button type="button" class="cave-tool-btn" @click="showHint = true">
                            <div class="icon-box"><i class="pi pi-question" /></div>
                            <span class="label">Indice</span>
                        </button>
                        <button type="button" class="cave-tool-btn" @click="showSolution = true">
                            <div class="icon-box"><i class="pi pi-eye" /></div>
                            <span class="label">Solution</span>
                        </button>
                        <button type="button" class="cave-tool-btn cave-tool-btn--danger" @click="showSkipConfirm = true">
                            <div class="icon-box"><i class="pi pi-fast-forward" /></div>
                            <span class="label">Passer</span>
                        </button>
                        <button type="button" class="cave-tool-btn cave-tool-btn--danger" @click="showAbandonConfirm = true">
                            <div class="icon-box"><i class="pi pi-power-off" /></div>
                            <span class="label">Quitter</span>
                        </button>
                    </div>
                </div>
            </main>

            <Dialog v-model:visible="showTeam" modal header="Membres de l'équipe" :style="{ width: '90vw', maxWidth: '480px' }" class="cave-game-dialog">
                <div class="space-y-3 py-2">
                    <div v-for="user in partie.team?.users" :key="user.id"
                        class="flex items-center justify-between p-3 rounded-2xl border-2 border-[var(--cave-border-dark)] bg-white shadow-[0_4px_0_var(--cave-border-darker)]"
                        :class="{ 'bg-[rgba(255,215,0,0.05)]': user.id === $page.props.auth.user.id }">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[var(--cave-rock-light)] border-2 border-[var(--cave-border-dark)] flex items-center justify-center font-black">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-black text-sm uppercase leading-none">{{ user.name }}</p>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border border-[var(--cave-border-dark)]"
                                    :class="user.pivot.role === 'challenger' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'">
                                    {{ user.pivot.role }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black uppercase opacity-50">Score Global</p>
                            <p class="text-sm font-black text-orange-600">{{ user.total_score || 0 }} <span class="text-[10px]">pts</span></p>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <button type="button" class="cave-btn w-full" @click="showTeam = false">Fermer</button>
                </template>
            </Dialog>

            <Dialog v-model:visible="showHint" modal header="Indice" :style="{ width: '90vw', maxWidth: '420px' }" class="cave-game-dialog">
                <div class="cave-overlay__body">
                    <p v-if="enigme?.indice" class="cave-panel__text text-center" style="margin:0">{{ enigme.indice }}</p>
                    <p v-else class="cave-hint text-center">Aucun indice n'est disponible pour cette énigme.</p>
                </div>
                <template #footer>
                    <button type="button" class="cave-btn w-full" @click="showHint = false">Compris</button>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSolution" modal header="Solution" :style="{ width: '90vw', maxWidth: '420px' }" class="cave-game-dialog">
                <p class="cave-flash cave-flash--error mb-3">Voir la solution annule vos points pour ce défi.</p>
                <p class="cave-panel__text text-center" style="margin:0">
                    La réponse vous sera affichée. Vous pourrez ensuite passer à l'énigme suivante.
                </p>
                <template #footer>
                    <div class="flex gap-2">
                        <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="showSolution = false">Annuler</button>
                        <button
                            type="button"
                            class="cave-btn cave-btn--danger flex-1"
                            :disabled="isRequestingSolution"
                            @click="requestSolutionManual"
                        >
                            {{ isRequestingSolution ? 'Chargement...' : 'Voir la solution' }}
                        </button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSkipConfirm" modal header="Passer ?" :style="{ width: '90vw', maxWidth: '420px' }" class="cave-game-dialog">
                <p class="cave-panel__text" style="margin:0">Passer ce défi sans gagner de points ?</p>
                <template #footer>
                    <div class="flex gap-2">
                        <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="showSkipConfirm = false">Non</button>
                        <button type="button" class="cave-btn flex-1" @click="confirmSkip">Oui</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showAbandonConfirm" modal header="Abandonner ?" :style="{ width: '90vw', maxWidth: '420px' }" class="cave-game-dialog">
                <p class="cave-panel__text" style="margin:0">Votre progression sera sauvegardée mais la mission s'arrête ici.</p>
                <template #footer>
                    <div class="flex gap-2">
                        <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="showAbandonConfirm = false">Continuer</button>
                        <button type="button" class="cave-btn cave-btn--danger flex-1" @click="confirmAbandon">Abandonner</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </CaveGameLayout>
</template>

<style scoped>
.cave-level-card__overlay {
    background: linear-gradient(to top, rgba(61, 34, 16, 0.75), transparent 55%);
}
.cave-step-ring svg circle:last-child {
    stroke: var(--cave-btn-primary);
}
</style>
