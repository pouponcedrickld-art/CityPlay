<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

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
const showSkipConfirm = ref(false);
const showAbandonConfirm = ref(false);
const showTimeExpired = ref(false);
const revealedSolution = ref(null);
const isRequestingSolution = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);
const gpsMessage = ref(null);
const lastGpsCheck = ref(null);

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
    if (props.progression?.statut === 'en_cours' && timeLeft.value <= 0) {
        onTimerExpired();
    }
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    if (timerInterval) clearInterval(timerInterval);
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
                    <div class="cave-hud__score" :title="scoreActuel + ' points au total'">
                        <i class="pi pi-star-fill" />
                        <span>{{ scoreActuel }}</span>
                    </div>
                    <div class="cave-hud__timer" :class="{ 'cave-hud__timer--urgent': timeLeft < 300 }">
                        <span>{{ formatTime(timeLeft) }}</span>
                        <i class="pi pi-clock" />
                    </div>
                    <button type="button" class="cave-hud__btn" @click="togglePause"><i class="pi pi-pause" /></button>
                </div>
            </header>

            <main class="cave-game-content cave-game-content--split">
                <article class="cave-panel">
                    <div v-if="enigme?.lieu?.photos?.length || enigme?.image_url" class="relative">
                        <img :src="enigme?.lieu?.photos?.length ? enigme.lieu.photos[0].url : enigme.image_url" class="cave-panel__img" :alt="enigme?.titre">
                        <div class="cave-level-card__overlay absolute inset-0" />
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
                    <div class="cave-panel cave-radar-box">
                        <div class="cave-radar-pulse absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 rounded-full" :class="{ 'cave-radar-pulse--active': isLocating }" />
                        <div class="relative z-10 flex flex-col items-center text-center gap-3 py-4">
                            <div class="cave-tool-btn__icon" style="width:56px;height:56px;font-size:1.5rem">
                                <i class="pi pi-map-marker" :class="{ 'animate-ping': isLocating }" />
                            </div>
                            <h3 class="cave-section-title" style="font-size:0.75rem;margin:0">Scanner GPS</h3>
                            <p v-if="gpsDisponible" class="cave-hint">
                                Rendez-vous sur la zone de l'énigme, puis vérifiez votre position pour gagner les points.
                            </p>
                            <p v-else class="cave-hint cave-hint--warn">Vérification GPS indisponible</p>
                        </div>
                    </div>

                    <div v-if="gpsMessage" class="cave-flash" :class="gpsMessage.type === 'error' ? 'cave-flash--error' : 'cave-flash--info'">
                        {{ gpsMessage.text }}
                    </div>

                    <div v-if="lastGpsCheck && lastGpsCheck.distance != null" class="cave-gps-result">
                        <p>
                            Vous êtes à environ <strong>{{ lastGpsCheck.distance }} m</strong> de la zone.
                            Rapprochez-vous et réessayez.
                        </p>
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

            <Dialog v-model:visible="showHint" modal header="Indice" :style="{ width: '90vw', maxWidth: '420px' }" class="cave-game-dialog">
                <p class="cave-panel__text" style="margin:0">L'indice sera bientôt disponible pour vous aider.</p>
                <template #footer>
                    <button type="button" class="cave-btn w-full" @click="showHint = false">Fermer</button>
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
