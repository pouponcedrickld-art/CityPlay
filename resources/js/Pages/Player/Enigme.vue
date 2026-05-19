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
    router.post(route('parties.web.abandon', props.partie.i// LIVE TIMER LOGIC
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
                router.post(route('progression.store', props.partie.id), {
                    temps_restant: timeLeft.value
                }, { preserveScroll: true, preserveState: true });
            }
        }
    }, 1000);
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    startTimer();

    // GSAP Animations
    gsap.set('.gsap-hud', { y: -50, opacity: 0 });
    gsap.set('.gsap-main-card', { y: 100, opacity: 0, rotationZ: -2 });
    gsap.set('.gsap-action-card', { y: 50, opacity: 0, scale: 0.95 });
    gsap.set('.gsap-token', { scale: 0, opacity: 0, rotation: -90 });

    const tl = gsap.timeline();
    tl.to('.gsap-hud', { y: 0, opacity: 1, duration: 0.6, ease: 'power2.out' })
      .to('.gsap-main-card', { y: 0, opacity: 1, rotationZ: 0, duration: 0.8, ease: 'back.out(1.2)' }, '-=0.2')
      .to('.gsap-action-card', { y: 0, opacity: 1, scale: 1, duration: 0.6, stagger: 0.15, ease: 'power2.out' }, '-=0.4')
      .to('.gsap-token', { scale: 1, opacity: 1, rotation: 0, duration: 0.5, stagger: 0.1, ease: 'back.out(2)' }, '-=0.4');
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="'Quête - ' + (partie?.environnement?.nom || 'En cours')" />

        <!-- PAUSE OVERLAY -->
        <div v-if="progression?.statut === 'pause'" class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex items-center justify-center p-6">
            <div class="w-full max-w-md game-board rounded-[2.5rem] overflow-hidden shadow-2xl border-4 border-[#5c4033] animate-fade-in-up">
                <div class="bg-[#1f140e] p-12 text-center relative border-b-2 border-[#5c4033]">
                    <div class="absolute inset-0 opacity-20" :style="{ backgroundImage: 'radial-gradient(circle at 2px 2px, #FF9500 1.5px, transparent 0)', backgroundSize: '32px 32px' }"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-[#2a1c14] rounded-full border-4 border-[#FF9500] flex items-center justify-center mx-auto mb-6 shadow-[0_0_20px_rgba(255,149,0,0.5)]">
                            <i class="pi pi-pause text-[#FF9500] text-3xl"></i>
                        </div>
                        <h2 class="card-title text-3xl text-[#f5e8c7]">Partie en Pause</h2>
                    </div>
                </div>
                
                <div class="bg-[#2a1c14] p-10 space-y-4">
                    <p class="text-center font-bold text-[#f5e8c7]/70 mb-8 italic font-serif">
                        {{ partie.environnement?.message_pause || 'L\'aventure est suspendue. Que souhaitez-vous faire ?' }}
                    </p>
                    
                    <button @click="togglePause" class="w-full p-4 bg-[#FF9500] text-[#1f140e] rounded-xl font-black uppercase tracking-widest flex items-center justify-center gap-4 shadow-[0_5px_0_#cc7a00] active:translate-y-[5px] active:shadow-none transition-all">
                        <i class="pi pi-play-fill text-xl"></i>
                        Reprendre la partie
                    </button>

                    <button @click="router.get(route('dashboard'))" class="w-full p-4 bg-[#1f140e] text-[#f5e8c7] rounded-xl font-black uppercase tracking-widest flex items-center justify-center gap-4 border border-[#5c4033] hover:border-[#FF9500]/50 transition-all">
                        <i class="pi pi-home text-xl"></i>
                        Quitter la table
                    </button>
                </div>
            </div>
        </div>

        <div class="play-mat min-h-screen pb-24 transition-opacity duration-300 relative" :class="{ 'opacity-50 pointer-events-none': isOffline || progression?.statut === 'pause' }">
            <!-- OFFLINE BANNER -->
            <div v-if="isOffline" class="bg-red-900 border-b-2 border-red-600 text-red-200 card-badge text-[10px] p-2 text-center sticky top-0 z-50 shadow-lg">
                <i class="pi pi-wifi mr-2"></i> Connexion perdue • Mode Hors-Ligne
            </div>

            <!-- GAME HUD (Top Bar as a wooden plaque) -->
            <div class="gsap-hud bg-[#1f140e] text-[#f5e8c7] p-3 flex justify-between items-center sticky top-0 z-40 border-b-[4px] border-[#5c4033] shadow-[0_5px_15px_rgba(0,0,0,0.5)]">
                <div class="flex items-center gap-4">
                    <!-- Progress Token -->
                    <div class="relative w-12 h-12 bg-[#2a1c14] border-2 border-[#5c4033] rounded-full shadow-inner flex items-center justify-center">
                        <svg class="absolute inset-0 w-full h-full -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="rgba(255,149,0,0.1)" stroke-width="3" fill="transparent" />
                            <circle cx="24" cy="24" r="20" stroke="#FF9500" stroke-width="3" fill="transparent" :stroke-dasharray="125.6" :stroke-dashoffset="125.6 * (1 - (progression?.lieux_decouverts?.length + 1) / ((progression?.lieux_restants?.length || 0) + (progression?.lieux_decouverts?.length || 0) + 1))" class="transition-all duration-1000" />
                        </svg>
                        <div class="text-[12px] card-title text-[#FF9500] drop-shadow-md">
                            {{ (progression?.lieux_decouverts?.length || 0) + 1 }}
                        </div>
                    </div>
                    <div>
                        <h1 class="card-badge text-[9px] text-[#FF9500]/70">Quête Actuelle</h1>
                        <p class="card-title text-sm truncate max-w-[150px] md:max-w-[300px]">
                            {{ partie?.environnement?.nom }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 bg-[#2a1c14] rounded-lg border border-[#5c4033] flex items-center gap-2 shadow-inner">
                        <i class="pi pi-clock text-[#FF9500] text-sm"></i>
                        <span class="card-badge text-sm" :class="{ 'text-red-500 animate-pulse': timeLeft < 300 }">{{ formatTime(timeLeft) }}</span>
                    </div>
                    <button @click="togglePause" class="w-10 h-10 bg-[#2a1c14] rounded-lg flex items-center justify-center hover:bg-[#3f2a1e] active:scale-95 transition-all border border-[#5c4033] shadow-[0_2px_0_#1f140e]">
                        <i class="pi pi-pause text-[#FF9500]"></i>
                    </button>
                </div>
            </div>

            <main class="p-4 md:p-8 max-w-3xl mx-auto space-y-8">
                <!-- MISSION CARD (The active play card) -->
                <div class="board-card gsap-main-card">
                    <div v-if="enigme?.lieu?.photos?.length || enigme?.image_url" class="relative h-64 border-b-2 border-[#5c4033]">
                        <img :src="enigme?.lieu?.photos?.length ? enigme.lieu.photos[0].url : enigme.image_url" class="w-full h-full object-cover opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2a1c14] to-transparent"></div>
                    </div>
                    <div v-else class="h-32 bg-[url('/img/pattern-wood.png')] bg-cover border-b-2 border-[#5c4033] flex items-center justify-center">
                        <i class="pi pi-map text-5xl text-black/20"></i>
                    </div>
                    
                    <div class="p-8 relative bg-[#2a1c14]">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-[#FF9500] text-[#1f140e] text-[9px] card-badge rounded-md">
                                Épreuve: {{ enigme?.type }}
                            </span>
                            <span class="px-3 py-1 bg-[#1f140e] border border-[#5c4033] text-[#f5e8c7] text-[9px] card-badge rounded-md">
                                Récompense: +{{ enigme?.points || 0 }} PTS
                            </span>
                        </div>
                        <h2 class="card-title text-3xl md:text-4xl text-[#f5e8c7] mb-6 drop-shadow-md">
                            {{ enigme?.titre || 'Défi Secret' }}
                        </h2>
                        <div class="bg-[#1f140e] p-6 rounded-xl border border-[#3f2a1e] text-lg text-[#f5e8c7]/90 leading-relaxed font-serif shadow-inner">
                            <p class="first-letter:text-5xl first-letter:font-black first-letter:text-[#FF9500] first-letter:mr-3 first-letter:float-left">
                                {{ enigme?.texte || 'Pas de description pour cette énigme.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RADAR / GEOLOCALISATION CARD -->
                <div class="board-card gsap-action-card p-6 bg-[#2a1c14] border-2 border-[#5c4033]">
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            <div class="radar-pulse absolute inset-0 bg-[#FF9500]/20 rounded-full" :class="{ 'animating': isLocating }"></div>
                            <div class="w-16 h-16 rounded-full bg-[#1f140e] border-2 border-[#FF9500] flex items-center justify-center relative z-10 shadow-[0_0_15px_rgba(255,149,0,0.3)]">
                                <i class="pi pi-compass text-2xl text-[#FF9500]" :class="{ 'animate-spin': isLocating }"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="card-title text-lg text-[#f5e8c7] mb-1">Boussole de Validation</h3>
                            <p class="card-badge text-[9px] text-[#FF9500]/70 mb-3">Rendez-vous sur place (10m max)</p>
                            
                            <button
                                @click="checkLocation"
                                class="w-full p-3 rounded-lg bg-[#5c4033] text-[#f5e8c7] card-badge text-[10px] hover:bg-[#FF9500] hover:text-[#1f140e] active:scale-95 transition-all flex items-center justify-center gap-2 border border-[#3f2a1e] shadow-[0_4px_0_#1f140e] active:translate-y-[4px] active:shadow-none disabled:opacity-50"
                                :disabled="isLocating || isOffline"
                            >
                                <i :class="isLocating ? 'pi pi-spin pi-spinner' : 'pi pi-map-marker'" class="text-sm"></i>
                                {{ isLocating ? 'Analyse...' : 'Prouver ma présence' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ANSWER FORM CARD -->
                <div class="board-card gsap-action-card p-6 bg-[#2a1c14] border-2 border-[#5c4033]">
                    <h3 class="card-title text-lg text-[#f5e8c7] mb-4 flex items-center gap-2">
                        <i class="pi pi-key text-[#FF9500]"></i> Formule de Réponse
                    </h3>
                    <form @submit.prevent="submitTextAnswer" class="relative">
                        <InputText
                            v-model="form.reponse"
                            placeholder="Saisissez la réponse trouvée..."
                            class="w-full p-4 bg-[#1f140e] border-2 border-[#3f2a1e] rounded-xl text-[#f5e8c7] font-black placeholder:text-[#f5e8c7]/30 focus:border-[#FF9500] transition-all shadow-inner"
                            :disabled="form.processing || isOffline"
                        />
                        <button 
                            v-if="form.reponse"
                            type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-[#FF9500] text-[#1f140e] rounded-lg flex items-center justify-center shadow-lg active:scale-90 transition-all hover:bg-[#ffaa33]"
                            :disabled="form.processing"
                        >
                            <i class="pi text-lg" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                        </button>
                    </form>
                </div>

                <!-- GAME TOOLS (Tokens) -->
                <div class="grid grid-cols-4 gap-4 mt-8">
                    <button @click="showHint = true" class="gsap-token tool-token group">
                        <div class="token-inner bg-[#1f140e] border-2 border-[#5c4033] group-hover:border-[#FF9500]">
                            <i class="pi pi-question text-xl text-[#FF9500] group-hover:drop-shadow-[0_0_5px_#FF9500]"></i>
                        </div>
                        <span class="card-badge text-[9px] text-[#f5e8c7]/50 mt-2 block">Indice</span>
                    </button>
                    <button @click="showSolution = true" class="gsap-token tool-token group">
                        <div class="token-inner bg-[#1f140e] border-2 border-[#5c4033] group-hover:border-[#FF9500]">
                            <i class="pi pi-eye text-xl text-[#FF9500] group-hover:drop-shadow-[0_0_5px_#FF9500]"></i>
                        </div>
                        <span class="card-badge text-[9px] text-[#f5e8c7]/50 mt-2 block">Solution</span>
                    </button>
                    <button @click="showSkipConfirm = true" class="gsap-token tool-token group">
                        <div class="token-inner bg-[#1f140e] border-2 border-[#5c4033] group-hover:border-red-500">
                            <i class="pi pi-fast-forward text-xl text-red-500 group-hover:drop-shadow-[0_0_5px_red]"></i>
                        </div>
                        <span class="card-badge text-[9px] text-red-400/50 mt-2 block">Passer</span>
                    </button>
                    <button @click="showAbandonConfirm = true" class="gsap-token tool-token group">
                        <div class="token-inner bg-[#1f140e] border-2 border-[#5c4033] group-hover:border-red-500">
                            <i class="pi pi-power-off text-xl text-red-500 group-hover:drop-shadow-[0_0_5px_red]"></i>
                        </div>
                        <span class="card-badge text-[9px] text-red-400/50 mt-2 block">Quitter</span>
                    </button>
                </div>
            </main>

            <!-- MODALS (Styled for Game) -->
            <Dialog v-model:visible="showHint" modal header="Carte Indice" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4 text-center">
                    <i class="pi pi-question-circle text-4xl text-[#FF9500] mb-2 drop-shadow-[0_0_10px_#FF9500]"></i>
                    <p class="text-[#f5e8c7]/80 font-serif italic text-lg">
                        "L'indice sera implémenté prochainement pour vous aider à résoudre ce mystère !"
                    </p>
                </div>
                <template #footer>
                    <button @click="showHint = false" class="w-full p-4 rounded-xl bg-[#1f140e] text-[#f5e8c7] card-badge border border-[#5c4033] hover:border-[#FF9500] transition-colors">
                        Remettre la carte
                    </button>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSolution" modal header="Carte Solution" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-6">
                    <div class="bg-[#1f140e] border border-red-900/50 p-4 rounded-xl text-center">
                        <i class="pi pi-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="card-badge text-[9px] text-red-400">
                            ALERTE : Lire cette carte annulera vos points.
                        </p>
                    </div>
                    <div v-if="enigme?.solution" class="bg-[#1f140e] p-6 rounded-xl border border-[#5c4033] font-serif italic text-[#f5e8c7] text-center">
                        {{ enigme.solution }}
                    </div>
                    <div v-else class="bg-[#1f140e] p-6 rounded-xl border border-[#5c4033] font-serif italic text-[#f5e8c7] text-center">
                        La réponse est : <span class="text-[#FF9500] card-title text-xl block mt-3">{{ enigme?.reponse || 'Non définie' }}</span>
                    </div>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full px-6 pb-6">
                        <button @click="showSolution = false" class="flex-1 p-3 rounded-xl bg-[#1f140e] border border-[#5c4033] text-[#f5e8c7]/50 card-badge hover:text-[#f5e8c7]">Garder secrète</button>
                        <button @click="confirmSkip" class="flex-1 p-3 rounded-xl bg-red-900/80 text-red-100 card-badge shadow-inner">Révéler & Passer</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showSkipConfirm" modal header="Défausser la Quête ?" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4 text-center">
                    <i class="pi pi-forward text-4xl text-red-500/80 mb-2"></i>
                    <p class="text-[#f5e8c7]/80 font-serif italic">
                        "Voulez-vous vraiment passer ce défi ? Vous ne gagnerez aucun point pour cette étape."
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full px-6 pb-6">
                        <button @click="showSkipConfirm = false" class="flex-1 p-3 rounded-xl bg-[#1f140e] border border-[#5c4033] text-[#f5e8c7]/50 card-badge hover:text-[#f5e8c7]">Annuler</button>
                        <button @click="confirmSkip" class="flex-1 p-3 rounded-xl bg-[#5c4033] text-[#FF9500] border border-[#FF9500]/30 card-badge hover:bg-[#FF9500] hover:text-[#1f140e]">Confirmer</button>
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showAbandonConfirm" modal header="Ranger le Plateau ?" :style="{ width: '90vw', maxWidth: '400px' }" class="game-dialog">
                <div class="p-6 space-y-4 text-center">
                    <i class="pi pi-ban text-4xl text-red-500/80 mb-2"></i>
                    <p class="text-[#f5e8c7]/80 font-serif italic">
                        "Êtes-vous certain de vouloir abandonner ? Votre progression actuelle sera sauvegardée mais la mission s'arrêtera ici."
                    </p>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full px-6 pb-6">
                        <button @click="showAbandonConfirm = false" class="flex-1 p-3 rounded-xl bg-[#1f140e] border border-[#5c4033] text-[#f5e8c7]/50 card-badge hover:text-[#f5e8c7]">Non, continuer</button>
                        <button @click="confirmAbandon" class="flex-1 p-3 rounded-xl bg-red-900/80 text-red-100 card-badge shadow-inner">Oui, abandonner</button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Reset & Overrides handled by app.css mostly */

/* Radar Animation */
.radar-pulse.animating {
    animation: pulse-radar 1.5s infinite ease-out;
}

@keyframes pulse-radar {
    0% { transform: scale(0.8); opacity: 0.8; }
    100% { transform: scale(2); opacity: 0; }
}

/* Tool Tokens */
.tool-token {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: transparent;
    border: none;
    cursor: pointer;
}
.tool-token .token-inner {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 5px 15px rgba(0,0,0,0.5), inset 0 2px 5px rgba(255,255,255,0.05);
}
.tool-token:hover .token-inner {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 20px rgba(0,0,0,0.6), inset 0 2px 5px rgba(255,255,255,0.1);
}
.tool-token:active .token-inner {
    transform: translateY(2px) scale(0.95);
    box-shadow: 0 2px 5px rgba(0,0,0,0.5);
}
</style>
