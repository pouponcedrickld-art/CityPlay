<template>
    <CaveGameLayout>
        <Head :title="'Énigme - ' + (partie?.environnement?.nom || 'En cours')" />

        <!-- MODAL : Temps écoulé -->
        <Dialog v-model:visible="showTimeExpired" modal :unstyled="true" header="Temps écoulé !"
            :closable="false"
            :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
            <div class="cave-btn-stack">
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
        </Dialog>

        <!-- MODAL : Solution révélée -->
        <Dialog v-model:visible="showSolutionRevealed" modal :unstyled="true" header="Solution"
            :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
            <div class="cave-btn-stack">
                <div class="cave-tablet p-4 bg-orange-50/50 border-orange-100 mb-4">
                    <p class="cave-panel__text text-center font-bold" style="margin:0">{{ revealedSolution }}</p>
                </div>
                <p class="cave-flash cave-flash--error" style="margin:0">Aucun point ne sera attribué pour cette énigme.</p>
                <button type="button" class="cave-btn w-full mt-4" @click="continueAfterSolution">
                    <i class="cave-btn__icon pi pi-arrow-right" />
                    <span class="cave-btn__label">Continuer la mission</span>
                </button>
            </div>
        </Dialog>

        <!-- MODAL : Pause -->
        <Dialog v-model:visible="isPaused" modal :unstyled="true" header="PARTIE EN PAUSE"
            :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
            <div class="cave-btn-stack">
                <p class="cave-hint text-center">{{ partie.environnement?.message_pause || 'Partie en pause. Vos progrès sont sauvegardés.' }}</p>
                <button type="button" class="cave-btn" @click="togglePause">
                    <i class="cave-btn__icon pi pi-play" /><span class="cave-btn__label">Reprendre</span>
                </button>
                <button type="button" class="cave-btn" @click="router.get(route('dashboard'))">
                    <i class="cave-btn__icon pi pi-home" /><span class="cave-btn__label">Retour au camp</span>
                </button>
                <button type="button" class="cave-btn cave-btn--survival" @click="showAbandonConfirm = true">
                    <i class="cave-btn__icon pi pi-flag" /><span class="cave-btn__label">Terminer la quête</span>
                </button>
            </div>
        </Dialog>

        <div
            class="transition-opacity duration-300 min-h-screen"
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
                    <div class="cave-hud__score" :title="scoreActuel + ' points au total'">
                        <i class="pi pi-star-fill" />
                        <span>{{ scoreActuel }}</span>
                    </div>
                    <div class="cave-hud__timer" :class="{ 'cave-hud__timer--urgent': timeLeft < 300 }">
                        <i class="pi pi-clock" />
                        <span>{{ formatTime(timeLeft) }}</span>
                    </div>
                    <button type="button" class="cave-hud__btn" @click="togglePause"><i class="pi pi-pause" /></button>
                </div>
            </header>

            <main class="cave-game-content px-4 py-6">
                <!-- Zone Image/Vidéo immersive -->
                <article class="cave-panel overflow-hidden mb-8">
                    <div v-if="enigme?.lieu?.photos?.length || enigme?.image_url" class="relative">
                        <img :src="enigme?.lieu?.photos?.length ? enigme.lieu.photos[0].url : enigme.image_url" class="cave-panel__img w-full h-64 object-cover" :alt="enigme?.titre">
                        <div class="cave-level-card__overlay absolute inset-0" />

                        <div class="absolute bottom-4 left-4 flex flex-wrap gap-2">
                            <span class="cave-badge cave-badge--gold">{{ enigme?.type }}</span>
                            <span class="cave-badge cave-badge--pts">+{{ pointsEnigme }} pts</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h2 class="cave-panel__title mb-4">{{ enigme?.titre || 'Défi secret' }}</h2>
                        <p class="cave-panel__text text-lg italic leading-relaxed" style="margin:0">
                            "{{ enigme?.texte || 'Pas de description pour cette énigme.' }}"
                        </p>
                    </div>
                </article>

                <!-- Zone d'action -->
                <div class="space-y-8">
                    <!-- Carte de position -->
                    <div class="cave-panel overflow-hidden">
                        <div class="p-4 border-b-2 border-[#5C3317]/10 bg-[#E8D4B0]/30 flex items-center justify-between">
                            <h3 class="cave-section-title mb-0" style="text-align: left; font-size: 0.85rem">Position de l'aventurier</h3>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#00e5ff] animate-pulse"></span>
                                <span class="text-[10px] font-bold uppercase text-[#5C3317]">Signal GPS Actif</span>
                            </div>
                        </div>
                        <div class="relative h-[250px] w-full">
                            <GameMap
                                :player-lat="playerPos.lat"
                                :player-lng="playerPos.lng"
                                :target-lat="enigme?.lieu?.latitude"
                                :target-lng="enigme?.lieu?.longitude"
                                :target-radius="enigme?.lieu?.rayon_validation || 30"
                                class="absolute inset-0 !border-0 !rounded-0"
                            />
                        </div>
                    </div>

                    <!-- Scanner GPS -->
                    <div class="cave-tablet p-6 relative overflow-hidden">
                        <div class="cave-radar-pulse absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 rounded-full" :class="{ 'cave-radar-pulse--active': isLocating }" />

                        <div class="relative z-10 flex flex-col items-center text-center gap-6">
                            <div class="cave-logo-icon" style="width:72px;height:72px;font-size:1.75rem">
                                <i class="pi pi-map-marker" :class="{ 'animate-bounce': isLocating }" />
                            </div>
                            <div>
                                <h3 class="cave-section-title mb-2">Scanner GPS</h3>
                                <p v-if="gpsDisponible" class="cave-hint text-sm mt-0">
                                    Rendez-vous sur la zone, puis scannez pour valider l'étape.
                                </p>
                                <p v-else class="cave-hint cave-hint--warn text-sm mt-0">Vérification GPS indisponible</p>
                            </div>

                            <div v-if="gpsMessage" class="cave-flash w-full" :class="gpsMessage.type === 'error' ? 'cave-flash--error' : 'cave-flash--info'">
                                {{ gpsMessage.text }}
                            </div>

                            <div v-if="lastGpsCheck && lastGpsCheck.distance != null" class="bg-white/50 p-4 rounded-xl border-2 border-dashed border-[#5C3317]/20 w-full">
                                <p class="text-sm font-bold text-[#5C3317]">
                                    Distance : {{ lastGpsCheck.distance }} m
                                </p>
                                <p class="text-xs text-[#5C3317]/60">Rapprochez-vous encore un peu !</p>
                            </div>

                            <button
                                type="button"
                                class="cave-btn w-full"
                                :disabled="isLocating || isOffline || !gpsDisponible || form.processing"
                                @click="checkLocation"
                            >
                                <i class="cave-btn__icon" :class="isLocating || form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-compass'" />
                                <span class="cave-btn__label">{{ isLocating || form.processing ? 'Scan...' : 'Lancer le scanner' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Réponse Texte -->
                    <div class="cave-tablet p-6">
                        <h3 class="cave-section-title mb-6">Réponse manuscrite</h3>
                        <form class="space-y-6" @submit.prevent="submitTextAnswer">
                            <div class="relative">
                                <InputText v-model="form.reponse" placeholder="Saisissez votre réponse ici..." class="cave-input w-full pr-12" :disabled="form.processing || isOffline" />
                                <button v-if="form.reponse" type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#FF9500]" :disabled="form.processing">
                                    <i :class="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-send'" class="text-xl" />
                                </button>
                            </div>
                            <button type="submit" class="cave-btn cave-btn--survival w-full" :disabled="!form.reponse || form.processing">
                                <span class="cave-btn__label">Envoyer la réponse</span>
                            </button>
                        </form>
                    </div>

                    <!-- Grille d'outils -->
                    <div class="cave-tool-grid grid grid-cols-2 gap-4">
                        <button type="button" class="cave-tool-btn h-24" @click="showHint = true">
                            <div class="icon-box"><i class="pi pi-question" /></div>
                            <span class="label">Indice</span>
                        </button>
                        <button type="button" class="cave-tool-btn h-24" @click="showSolution = true">
                            <div class="icon-box"><i class="pi pi-eye" /></div>
                            <span class="label">Solution</span>
                        </button>
                        <button type="button" class="cave-tool-btn cave-tool-btn--danger h-24" @click="showSkipConfirm = true">
                            <div class="icon-box"><i class="pi pi-fast-forward" /></div>
                            <span class="label">Passer</span>
                        </button>
                        <button type="button" class="cave-tool-btn cave-tool-btn--danger h-24" @click="showAbandonConfirm = true">
                            <div class="icon-box"><i class="pi pi-power-off" /></div>
                            <span class="label">Quitter</span>
                        </button>
                    </div>
                </div>
            </main>

            <Dialog v-model:visible="showHint" modal :unstyled="true" header="Besoin d'aide ?" :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
                <p class="text-orange-900 text-center font-medium leading-relaxed" style="margin:0">L'indice sera bientôt disponible pour vous aider dans votre quête.</p>
                <template #footer>
                    <button type="button" class="cave-btn w-full" @click="showHint = false">Fermer</button>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSolution" modal :unstyled="true" header="Révéler la solution" :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
                <p class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-bold mb-4 text-center border border-red-100">
                    <i class="pi pi-exclamation-triangle mr-2" /> Voir la solution annule vos points pour ce défi.
                </p>
                <p class="text-orange-900 text-center font-medium leading-relaxed" style="margin:0">
                    La réponse vous sera affichée. Vous pourrez ensuite passer à l'énigme suivante.
                </p>
                <template #footer>
                    <div class="flex gap-3">
                        <button type="button" class="cave-btn flex-1" @click="showSolution = false">Annuler</button>
                        <button
                            type="button"
                            class="cave-btn cave-btn--survival flex-1"
                            :disabled="isRequestingSolution"
                            @click="requestSolutionManual"
                        >
                            <span class="cave-btn__label">{{ isRequestingSolution ? '...' : 'Révéler' }}</span>
                        </button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSkipConfirm" modal :unstyled="true" header="Passer l'étape" :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
                <p class="text-orange-900 text-center font-medium leading-relaxed" style="margin:0">Passer ce défi sans gagner de points ? Votre mission continuera à la prochaine étape.</p>
                <template #footer>
                    <div class="flex gap-3">
                        <button type="button" class="cave-btn flex-1" @click="showSkipConfirm = false">Non</button>
                        <button type="button" class="cave-btn cave-btn--survival flex-1" @click="confirmSkip">Oui</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showAbandonConfirm" modal :unstyled="true" header="Abandonner ?" :style="{ width: '90vw', maxWidth: '420px' }" :pt="dialogPt">
                <p class="text-orange-900 text-center font-medium leading-relaxed" style="margin:0">Votre progression sera sauvegardée mais la mission s'arrête ici pour aujourd'hui.</p>
                <template #footer>
                    <div class="flex gap-3">
                        <button type="button" class="cave-btn flex-1" @click="showAbandonConfirm = false">Continuer</button>
                        <button type="button" class="cave-btn cave-btn--survival flex-1" @click="confirmAbandon">Quitter</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </CaveGameLayout>
</template>

<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import GameMap from '@/Components/Game/GameMap.vue';

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
const showSolutionRevealed = ref(false);
const showSkipConfirm = ref(false);
const showAbandonConfirm = ref(false);
const showTimeExpired = ref(false);
const revealedSolution = ref(null);
const isRequestingSolution = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);
const gpsMessage = ref(null);
const lastGpsCheck = ref(null);
const playerPos = ref({ lat: null, lng: null });

const isPaused = computed(() => props.progression?.statut === 'pause');

const dialogPt = {
    root: { class: 'cave-dialog-root' },
    mask: { class: 'cave-dialog-mask' },
    header: { class: 'cave-dialog-header' },
    title: { class: 'cave-dialog-title' },
    content: { class: 'cave-dialog-content' },
    footer: { class: 'cave-dialog-footer' },
    closeButton: { class: 'cave-dialog-close' },
    closeButtonIcon: { class: 'cave-dialog-close-icon' },
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
    }
    if (flash?.gps_validation) {
        lastGpsCheck.value = flash.gps_validation;
    }
    if (flash?.solution_revelee) {
        revealedSolution.value = flash.solution_revelee;
        showTimeExpired.value = false;
        showSolution.value = false;
        showSolutionRevealed.value = true;
    }
};

watch(() => page.props.flash, applyFlash, { deep: true });

watch(
    () => [props.enigme?.id, props.progression?.temps_restant],
    ([enigmeId, temps]) => {
        if (temps != null) {
            timeLeft.value = temps;
        }
        if (enigmeId) {
            showTimeExpired.value = false;
            revealedSolution.value = null;
            showSolutionRevealed.value = false;
        }
    },
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
    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }

    isLocating.value = true;
    gpsMessage.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            form.precision = position.coords.accuracy ?? null;
            submitLocationAnswer();
        },
        (error) => {
            isLocating.value = false;
            gpsMessage.value = {
                type: 'error',
                text: error.code === 1
                    ? "Veuillez autoriser l'accès à votre position."
                    : 'Erreur de localisation. Réessayez en plein air.',
            };
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
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
    showSolutionRevealed.value = false;
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
    showSolutionRevealed.value = false;
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
    if (props.progression?.statut === 'en_cours' && timeLeft.value <= 0) {
        onTimerExpired();
    }

    if ("geolocation" in navigator) {
        navigator.geolocation.watchPosition((position) => {
            playerPos.value = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
        });
    }
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<style>
/* ── Styles Globaux pour les Dialogs PrimeVue (car ils sont téléportés) ── */
.cave-dialog-root {
    display: flex;
    align-items: center;
    justify-content: center;
}

.cave-dialog-root .p-dialog {
    border: 4px solid #5C3317 !important;
    border-radius: 24px !important;
    overflow: hidden !important;
    background: #FFF8EE !important;
    box-shadow: 0 12px 0 #3D2210, 0 20px 40px rgba(61, 34, 16, 0.7) !important;
    display: flex;
    flex-direction: column;
    height: auto !important;
    max-height: 90vh !important;
}

.cave-dialog-root .cave-dialog-header {
    background: linear-gradient(180deg, #E8D4B0, #D4B483) !important;
    border-bottom: 4px solid #5C3317 !important;
    padding: 1.25rem 1.5rem !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cave-dialog-root .cave-dialog-title {
    font-family: 'Bungee', cursive !important;
    font-size: 0.95rem !important;
    color: #5C3317 !important;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.cave-dialog-root .cave-dialog-close {
    color: #FFF5DC !important;
    border: 3px solid #5C3317 !important;
    border-radius: 12px !important;
    background: linear-gradient(180deg, #A84A3A, #6B2A1E) !important;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 0 #3D2210 !important;
    cursor: pointer;
}

.cave-dialog-root .cave-dialog-content {
    background: #FFF8EE !important;
    padding: 2rem 1.5rem !important;
    flex: none !important;
    color: #5C3317 !important;
    font-family: 'Fredoka', sans-serif !important;
}

.cave-dialog-root .cave-dialog-footer {
    background: #E8D4B0 !important;
    border-top: 4px solid #5C3317 !important;
    padding: 1.25rem 1.5rem 1.5rem !important;
}

.cave-dialog-root .cave-btn {
    color: #FFF5DC !important;
}

.cave-dialog-root .cave-hint {
    color: #5C3317 !important;
    opacity: 0.8;
}
</style>

<style scoped>
/* Force le style global pour cette page */
.cave-level-card__overlay {
    background: linear-gradient(to top, rgba(61, 34, 16, 0.75), transparent 55%);
}

.cave-step-ring svg circle:last-child {
    stroke: #FF9500;
}

.cave-radar-pulse--active {
    animation: cave-pulse 2s infinite;
}

@keyframes cave-pulse {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}
</style>
