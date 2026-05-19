<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

const props = defineProps({
    partie: Object,
    progression: Object,
    enigme: Object,
    flash: Object,
});

const form = useForm({
    reponse: '',
    latitude: null,
    longitude: null,
    distance_metres: null,
});

const showHint = ref(false);
const showSolution = ref(false);
const showSkipConfirm = ref(false);
const showAbandonConfirm = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);

const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
};

const checkLocation = () => {
    if (isOffline.value) {
        alert('Vous semblez être hors-ligne. La vérification nécessite une connexion internet.');
        return;
    }
    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }
    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            if (props.enigme?.lieu?.latitude && props.enigme?.lieu?.longitude) {
                submitLocationAnswer();
            }
            isLocating.value = false;
        },
        (error) => {
            isLocating.value = false;
            alert(error.code === 1 ? "Veuillez autoriser l'accès à votre position." : 'Erreur de localisation.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const submitLocationAnswer = () => form.post(route('progression.submit', props.partie.id));
const submitTextAnswer = () => { if (form.reponse) form.post(route('progression.submit', props.partie.id)); };
const confirmSkip = () => { showSkipConfirm.value = false; router.post(route('progression.next', props.partie.id)); };
const togglePause = () => { router.post(route('parties.web.pause', props.partie.id)); };
const confirmAbandon = () => { showAbandonConfirm.value = false; router.post(route('parties.web.abandon', props.partie.id)); };

const timeLeft = ref(props.progression?.temps_restant || 3600);
let timerInterval = null;

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const startTimer = () => {
    if (timerInterval) return;
    timerInterval = setInterval(() => {
        if (props.progression?.statut === 'en_cours' && timeLeft.value > 0) {
            timeLeft.value--;
            if (timeLeft.value % 30 === 0) {
                window.axios.post(route('progression.store', props.partie.id), {
                    temps_restant: timeLeft.value,
                }).catch(() => {});
            }
        }
    }, 1000);
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    startTimer();
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

        <!-- Pause -->
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

        <div class="transition-opacity duration-300" :class="{ 'opacity-50 pointer-events-none': isOffline || progression?.statut === 'pause' }">
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
                        <p class="cave-hud__title truncate max-w-[140px] lg:max-w-xs">{{ partie?.environnement?.nom }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="cave-hud__timer" :class="{ 'cave-hud__timer--urgent': timeLeft < 300 }">
                        <span>{{ formatTime(timeLeft) }}</span>
                        <i class="pi pi-clock" />
                    </div>
                    <button type="button" class="cave-hud__btn" @click="togglePause"><i class="pi pi-pause" /></button>
                </div>
            </header>

            <main class="cave-game-content cave-game-content--split">
                <!-- Énigme -->
                <article class="cave-panel">
                    <div v-if="enigme?.lieu?.photos?.length || enigme?.image_url" class="relative">
                        <img :src="enigme?.lieu?.photos?.length ? enigme.lieu.photos[0].url : enigme.image_url" class="cave-panel__img" :alt="enigme?.titre">
                        <div class="cave-level-card__overlay absolute inset-0" />
                    </div>
                    <div class="cave-panel__body">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="cave-badge cave-badge--gold">{{ enigme?.type }}</span>
                            <span class="cave-badge cave-badge--pts">+{{ enigme?.points || 0 }} pts</span>
                        </div>
                        <h2 class="cave-panel__title">{{ enigme?.titre || 'Défi secret' }}</h2>
                        <p class="cave-panel__text">
                            <i class="pi pi-info-circle mr-2 opacity-50" />
                            "{{ enigme?.texte || 'Pas de description pour cette énigme.' }}"
                        </p>
                    </div>
                </article>

                <!-- Actions latérales -->
                <div class="cave-game-sidebar-col">
                    <div class="cave-panel cave-radar-box">
                        <div class="cave-radar-pulse absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 rounded-full" :class="{ 'cave-radar-pulse--active': isLocating }" />
                        <div class="relative z-10 flex flex-col items-center text-center gap-3 py-4">
                            <div class="cave-tool-btn__icon" style="width:56px;height:56px;font-size:1.5rem"><i class="pi pi-map-marker" :class="{ 'animate-ping': isLocating }" /></div>
                            <h3 class="cave-section-title" style="font-size:0.75rem;margin:0">Scanner GPS</h3>
                            <p class="cave-hint">Portée : 10 mètres</p>
                        </div>
                    </div>

                    <button type="button" class="cave-btn w-full" :disabled="isLocating || isOffline" @click="checkLocation">
                        <i class="cave-btn__icon" :class="isLocating ? 'pi pi-spin pi-spinner' : 'pi pi-compass'" />
                        <span class="cave-btn__label">{{ isLocating ? 'Scan...' : 'Vérifier position' }}</span>
                    </button>

                    <div class="cave-panel cave-panel__body space-y-3">
                        <h3 class="cave-section-title" style="font-size:0.75rem;margin:0">Réponse</h3>
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
                <p class="cave-flash cave-flash--error mb-3">Utiliser la solution annule vos points.</p>
                <p class="cave-panel__text text-center">
                    {{ enigme?.solution || enigme?.reponse || 'Non définie' }}
                </p>
                <template #footer>
                    <div class="flex gap-2">
                        <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="showSolution = false">Annuler</button>
                        <button type="button" class="cave-btn cave-btn--danger flex-1" @click="confirmSkip">Confirmer</button>
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
