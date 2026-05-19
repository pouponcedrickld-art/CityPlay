<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
const showPauseConfirm = ref(false);
const showAbandonConfirm = ref(false);
const isLocating = ref(false);
const isOffline = ref(!navigator.onLine);

// Gestion de l'état offline
const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
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

            // Vérification directe sans carte (anti-triche)
            if (props.enigme?.lieu?.latitude && props.enigme?.lieu?.longitude) {
                // Utilisation d'un helper pour la distance ou envoi direct au serveur
                submitLocationAnswer();
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

const togglePause = () => {
    showPauseConfirm.value = false;
    router.post(route('parties.web.pause', props.partie.id));
};

const confirmAbandon = () => {
    showAbandonConfirm.value = false;
    router.post(route('parties.web.abandon', props.partie.id));
};

// LIVE TIMER LOGIC
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
            
            // Sync silencieuse (axios, pas Inertia) pour ne pas interrompre la page
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
    <AuthenticatedLayout>
        <Head :title="'Énigme - ' + (partie?.environnement?.nom || 'En cours')" />

        <!-- PAUSE OVERLAY (Original Disposition with Game Colors) -->
        <div v-if="progression?.statut === 'pause'" class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-xl flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-[#1a1a1a] rounded-[2.5rem] overflow-hidden shadow-2xl border-2 border-[#FF9500]/20 animate-fade-in-up">
                <div class="bg-[#1a1a1a] p-12 text-center relative border-b border-white/5">
                    <div class="absolute inset-0 opacity-10" :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, #FF9500 1px, transparent 0)', backgroundSize: '24px 24px' }"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-[#FF9500]/10 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-[#FF9500]/30 shadow-[0_0_20px_rgba(255,149,0,0.2)]">
                            <i class="pi pi-pause text-[#FF9500] text-3xl"></i>
                        </div>
                        <h2 class="text-3xl font-black text-white uppercase tracking-tighter leading-none">Partie en pause</h2>
                    </div>
                </div>
                
                <div class="p-10 space-y-4">
                    <p class="text-center font-bold text-white/60 mb-8">
                        {{ partie.environnement?.message_pause || 'Que souhaitez-vous faire ?' }}
                    </p>
                    
                    <button @click="togglePause" class="w-full p-5 bg-[#FF9500] text-black rounded-2xl font-black uppercase tracking-widest flex items-center justify-center gap-4 shadow-[0_8px_0_#cc7a00] active:shadow-none active:translate-y-[8px] transition-all">
                        <div class="w-10 h-10 bg-black/10 rounded-xl flex items-center justify-center">
                            <i class="pi pi-play-fill"></i>
                        </div>
                        Reprendre la partie
                    </button>

                    <button @click="router.get(route('dashboard'))" class="w-full p-5 bg-white/5 text-white/70 rounded-2xl font-bold border border-white/10 flex items-center gap-4 hover:bg-white/10 transition-all active:scale-95">
                        <div class="w-10 h-10 bg-[#FF9500]/10 rounded-xl flex items-center justify-center">
                            <i class="pi pi-clock text-[#FF9500]"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-black uppercase tracking-tight leading-none">Mettre en pause</p>
                            <p class="text-[10px] text-white/40">Sauvegarder et revenir plus tard</p>
                        </div>
                    </button>

                    <button @click="showAbandonConfirm = true" class="w-full p-5 bg-red-600/10 text-red-500 rounded-2xl font-bold border border-red-500/20 flex items-center gap-4 hover:bg-red-600/20 transition-all active:scale-95">
                        <div class="w-10 h-10 bg-red-600/10 rounded-xl flex items-center justify-center">
                            <i class="pi pi-flag-fill text-red-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-black uppercase tracking-tight leading-none">Terminer la Quest</p>
                            <p class="text-[10px] text-red-400">Voir le résumé final</p>
                        </div>
                    </button>

                    <div class="mt-8 p-4 bg-black/20 rounded-2xl border border-white/5 flex items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-[#FF9500]/50"></i>
                        <p class="text-[10px] text-white/30 font-black uppercase tracking-widest leading-tight">Votre profil sera conservé pendant 30 jours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="game-container min-h-screen pb-24 transition-opacity duration-300" :class="{ 'opacity-50 pointer-events-none': isOffline || progression?.statut === 'pause' }">
            <!-- OFFLINE BANNER -->
            <div v-if="isOffline" class="bg-red-600 text-white text-[10px] font-black uppercase tracking-[0.2em] p-2 text-center sticky top-0 z-50 shadow-lg">
                <i class="pi pi-wifi mr-2"></i> Connexion perdue • Mode Hors-Ligne
            </div>

            <!-- GAME HUD (Top Bar) -->
            <div class="hud-top bg-[#1a1a1a] text-white p-4 flex justify-between items-center sticky top-0 z-40 border-b-2 border-[#FF9500]/30 backdrop-blur-md bg-opacity-90">
                <div class="flex items-center gap-3">
                    <div class="step-indicator relative">
                        <svg class="w-12 h-12 -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="text-white/10" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" :stroke-dasharray="125.6" :stroke-dashoffset="125.6 * (1 - (progression?.lieux_decouverts?.length + 1) / ((progression?.lieux_restants?.length || 0) + (progression?.lieux_decouverts?.length || 0) + 1))" class="text-[#FF9500] transition-all duration-1000" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-[10px] font-black">
                            {{ (progression?.lieux_decouverts?.length || 0) + 1 }}
                        </div>
                    </div>
                    <div>
                        <h1 class="text-[10px] font-black text-[#FF9500] uppercase tracking-[0.2em]">Mission en cours</h1>
                        <p class="text-sm font-black text-white uppercase tracking-tighter truncate max-w-[120px]">
                            {{ partie?.environnement?.nom }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="timer-badge px-4 py-2 bg-white/5 rounded-2xl border border-white/10 flex items-center gap-3">
                        <div class="flex flex-col items-end">
                            <span class="text-[8px] font-black text-white/40 uppercase tracking-widest">Temps</span>
                            <span class="text-sm font-black font-mono text-white leading-none" :class="{ 'text-red-500 animate-pulse': timeLeft < 300 }">{{ formatTime(timeLeft) }}</span>
                        </div>
                        <i class="pi pi-clock text-[#FF9500]"></i>
                    </div>
                    <button @click="togglePause" class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all border border-white/10">
                        <i class="pi pi-pause text-white"></i>
                    </button>
                </div>
            </div>

            <main class="p-6 max-w-2xl mx-auto space-y-8">
                <!-- MISSION CARD (Image & Title) -->
                <div class="mission-card relative rounded-[2rem] overflow-hidden shadow-2xl border-2 border-white/10 bg-[#1a1a1a]">
                    <div v-if="enigme?.lieu?.photos?.length || enigme?.image_url" class="relative h-64">
                        <img :src="enigme?.lieu?.photos?.length ? enigme.lieu.photos[0].url : enigme.image_url" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/20 to-transparent"></div>
                    </div>
                    
                    <div class="p-8 relative">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-[#FF9500] text-black text-[9px] font-black rounded-full uppercase tracking-widest">
                                {{ enigme?.type }}
                            </span>
                            <span class="px-3 py-1 bg-white/10 text-white/60 text-[9px] font-black rounded-full uppercase tracking-widest">
                                +{{ enigme?.points || 0 }} PTS
                            </span>
                        </div>
                        <h2 class="text-3xl font-black text-white uppercase tracking-tighter mb-4 leading-none">
                            {{ enigme?.titre || 'Défi Secret' }}
                        </h2>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/5 italic text-lg text-white/90 leading-relaxed shadow-inner">
                            <i class="pi pi-info-circle text-[#FF9500] mr-2 opacity-50"></i>
                            "{{ enigme?.texte || 'Pas de description pour cette énigme.' }}"
                        </div>
                    </div>
                </div>

                <!-- RADAR / GEOLOCALISATION -->
                <div class="radar-section space-y-4">
                    <div class="relative bg-[#1a1a1a] p-8 rounded-[2rem] border-2 border-[#FF9500]/20 overflow-hidden group">
                        <div class="radar-pulse absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-[#FF9500]/5 rounded-full" :class="{ 'animating': isLocating }"></div>
                        
                        <div class="relative z-10 flex flex-col items-center text-center gap-4">
                            <div class="radar-icon-container w-20 h-20 rounded-full bg-[#FF9500]/10 border-2 border-[#FF9500]/30 flex items-center justify-center">
                                <i class="pi pi-map-marker text-3xl text-[#FF9500]" :class="{ 'animate-ping': isLocating }"></i>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm font-black text-white uppercase tracking-[0.2em]">Scanner de proximité</h3>
                                <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Portée requise : <span class="text-[#FF9500]">10 mètres</span></p>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="checkLocation"
                        class="game-btn-primary w-full p-6 rounded-[1.5rem] bg-[#FF9500] text-black font-black uppercase tracking-[0.2em] shadow-[0_8px_0_#cc7a00] active:shadow-none active:translate-y-[8px] transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isLocating || isOffline"
                    >
                        <i :class="isLocating ? 'pi pi-spin pi-spinner' : 'pi pi-compass'" class="text-xl"></i>
                        {{ isLocating ? 'Scan en cours...' : 'Vérifier ma position' }}
                    </button>
                </div>

                <!-- ANSWER FORM -->
                <div class="answer-section bg-[#1a1a1a] p-8 rounded-[2rem] border-2 border-white/5 space-y-6">
                    <h3 class="text-[10px] font-black text-[#FF9500] uppercase tracking-[0.2em] text-center">Terminal de réponse</h3>
                    <form @submit.prevent="submitTextAnswer" class="relative">
                        <InputText
                            v-model="form.reponse"
                            placeholder="Saisissez le code ou la réponse..."
                            class="game-input w-full p-6 bg-black/40 border-2 border-white/10 rounded-2xl text-white font-black placeholder:text-white/20 focus:border-[#FF9500] transition-all"
                            :disabled="form.processing || isOffline"
                        />
                        <button 
                            v-if="form.reponse"
                            type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 bg-[#FF9500] text-black rounded-xl flex items-center justify-center shadow-lg active:scale-90 transition-all"
                            :disabled="form.processing"
                        >
                            <i class="pi text-xl" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                        </button>
                    </form>
                </div>

                <!-- GAME TOOLS (Secondary Actions) -->
                <div class="grid grid-cols-4 gap-4">
                    <button @click="showHint = true" class="tool-btn group">
                        <div class="icon-box"><i class="pi pi-question text-xl"></i></div>
                        <span class="label">Indice</span>
                    </button>
                    <button @click="showSolution = true" class="tool-btn group">
                        <div class="icon-box"><i class="pi pi-eye text-xl"></i></div>
                        <span class="label">Solution</span>
                    </button>
                    <button @click="showSkipConfirm = true" class="tool-btn group danger">
                        <div class="icon-box"><i class="pi pi-fast-forward text-xl"></i></div>
                        <span class="label">Passer</span>
                    </button>
                    <button @click="showAbandonConfirm = true" class="tool-btn group danger">
                        <div class="icon-box"><i class="pi pi-power-off text-xl"></i></div>
                        <span class="label">Quitter</span>
                    </button>
                </div>
            </main>

            <!-- MODALS (Styled for Game) -->
            <Dialog v-model:visible="showHint" modal header="Transmission d'indice" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 text-[#FF9500]">
                        <i class="pi pi-bolt text-2xl animate-pulse"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Aide de mission</span>
                    </div>
                    <p class="text-white/80 font-medium leading-relaxed italic">
                        "L'indice sera implémenté prochainement pour vous aider à résoudre ce mystère !"
                    </p>
                </div>
                <template #footer>
                    <button @click="showHint = false" class="game-btn-secondary w-full p-4 rounded-xl bg-white/10 text-white font-black uppercase tracking-widest border border-white/20">
                        Fermer le canal
                    </button>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSolution" modal header="Décryptage de solution" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-6">
                    <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-xl flex items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-red-500 text-xl"></i>
                        <p class="text-[10px] font-black text-red-400 uppercase leading-tight">
                            ALERTE : L'utilisation de la solution annulera vos points.
                        </p>
                    </div>
                    <div v-if="enigme?.solution" class="bg-white/5 p-6 rounded-2xl border border-white/10 italic text-white shadow-inner text-center">
                        {{ enigme.solution }}
                    </div>
                    <div v-else class="bg-white/5 p-6 rounded-2xl border border-white/10 italic text-white shadow-inner text-center">
                        La réponse est : <span class="text-[#FF9500] font-black uppercase text-xl block mt-2">{{ enigme?.reponse || 'Non définie' }}</span>
                    </div>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full p-4 pt-0">
                        <button @click="showSolution = false" class="flex-1 p-4 rounded-xl bg-white/5 text-white/60 font-black uppercase tracking-widest text-[10px]">Annuler</button>
                        <button @click="confirmSkip" class="flex-1 p-4 rounded-xl bg-red-600 text-white font-black uppercase tracking-widest text-[10px] shadow-lg shadow-red-600/20">Confirmer</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSkipConfirm" modal header="Passer cette énigme ?" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4">
                    <p class="text-white/70 font-medium leading-relaxed italic">
                        "Voulez-vous vraiment passer ce défi ? Vous ne gagnerez aucun point pour cette étape."
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full p-4 pt-0">
                        <button @click="showSkipConfirm = false" class="flex-1 p-4 rounded-xl bg-white/5 text-white/60 font-black uppercase tracking-widest text-[10px]">Annuler</button>
                        <button @click="confirmSkip" class="flex-1 p-4 rounded-xl bg-orange-600 text-white font-black uppercase tracking-widest text-[10px]">Confirmer</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showAbandonConfirm" modal header="Abandonner la mission ?" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4">
                    <p class="text-white/70 font-medium leading-relaxed italic">
                        "Êtes-vous certain de vouloir abandonner ? Votre progression actuelle sera sauvegardée mais la mission s'arrêtera ici."
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full p-4 pt-0">
                        <button @click="showAbandonConfirm = false" class="flex-1 p-4 rounded-xl bg-white/5 text-white/60 font-black uppercase tracking-widest text-[10px]">Non, continuer</button>
                        <button @click="confirmAbandon" class="flex-1 p-4 rounded-xl bg-red-600 text-white font-black uppercase tracking-widest text-[10px] shadow-lg shadow-red-600/20">Oui, abandonner</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Reset & Overrides */
.game-container {
    background-color: #0f0f0f;
    font-family: 'Inter', sans-serif;
    color: white;
}

/* Radar Animation */
.radar-pulse.animating {
    animation: pulse-radar 2s infinite ease-out;
}

@keyframes pulse-radar {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
}

/* Tool Buttons */
.tool-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 0.5rem;
    background: #1a1a1a;
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.2s;
}
.tool-btn .icon-box {
    width: 3rem;
    height: 3rem;
    background: rgba(255, 149, 0, 0.1);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FF9500;
    transition: all 0.2s;
}
.tool-btn .label {
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255,255,255,0.4);
}
.tool-btn:active { transform: scale(0.95); }
.tool-btn:hover .icon-box { background: rgba(255, 149, 0, 0.2); }
.tool-btn.danger .icon-box { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
.tool-btn.danger .label { color: rgba(239, 68, 68, 0.4); }

/* Dialog Styling */
.game-dialog.p-dialog {
    background: #1a1a1a !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 2rem !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
}
.game-dialog .p-dialog-header {
    background: transparent !important;
    padding: 1.5rem 1.5rem 0 1.5rem !important;
    color: white !important;
}
.game-dialog .p-dialog-title {
    font-size: 1rem !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: -0.025em !important;
}
.game-dialog .p-dialog-content {
    background: transparent !important;
    color: white !important;
}
.game-dialog .p-dialog-footer {
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,0.05) !important;
    padding: 0 !important;
}

/* Form Styling */
.game-input {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.3) !important;
}
</style>

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
