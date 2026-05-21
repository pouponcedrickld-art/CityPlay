<template>
    <!-- Overlay/Backdrop -->
    <div 
        v-if="show" 
        class="fixed inset-0 z-[200] bg-black/80 backdrop-blur-md flex items-center justify-center p-4 md:p-8"
    >
        <!-- The Book Container -->
        <div class="tutorial-book w-full max-w-2xl rounded-[2.5rem] border-[4px] shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]"
             style="background: var(--tutorial-bg, #2a1c14); border-color: var(--tutorial-border, #5c4033);">
            
            <!-- Book Decorative Header -->
            <div class="p-6 md:p-8 text-center relative border-b-2 shrink-0"
                 style="border-color: color-mix(in srgb, var(--accent-primary) 30%, transparent);">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, var(--accent-primary) 1.5px, transparent 0); background-size: 32px 32px;"></div>
                <button @click="closeTutorial" 
                        class="absolute top-4 right-4 w-10 h-10 rounded-full border-2 flex items-center justify-center transition-all shadow-inner z-20"
                        style="background: rgba(0,0,0,0.2); border-color: var(--accent-primary); color: var(--accent-primary);"
                        @mouseover="e => e.currentTarget.style.background = 'var(--accent-primary)'"
                        @mouseleave="e => e.currentTarget.style.background = 'rgba(0,0,0,0.2)'">
                    <i class="pi pi-times text-lg"></i>
                </button>

                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center mx-auto mb-4 shadow-inner"
                         style="background: rgba(0,0,0,0.2); border-color: var(--accent-primary); color: var(--accent-primary);">
                        <i class="pi pi-book text-2xl"></i>
                    </div>
                    <h2 class="card-title text-2xl md:text-3xl uppercase tracking-tighter player-title">Livre des Règles</h2>
                    <div class="flex justify-center gap-2 mt-4">
                        <div 
                            v-for="(_, index) in steps" 
                            :key="index"
                            class="h-2 rounded-full transition-all duration-500"
                            :style="currentStep === index 
                              ? 'width: 2rem; background: var(--accent-primary);' 
                              : 'width: 0.5rem; background: rgba(0,0,0,0.2); border: 1px solid color-mix(in srgb, var(--accent-primary) 50%, transparent);'"
                        ></div>
                    </div>
                </div>
            </div>
            
            <!-- Book Pages (Carousel) -->
            <div class="flex-1 overflow-hidden relative" style="background: rgba(0,0,0,0.05);">
                <div class="absolute inset-0 p-6 md:p-10 overflow-y-auto">
                    <transition name="page-turn" mode="out-in">
                        <div :key="currentStep" class="tutorial-page h-full flex flex-col justify-center">
                            
                            <!-- Step Content -->
                            <div class="text-center mb-8">
                                <i :class="steps[currentStep].icon" class="text-5xl mb-6" style="color: var(--accent-primary);"></i>
                                <h3 class="card-title text-2xl mb-4 player-title">{{ steps[currentStep].title }}</h3>
                                <p class="font-serif italic text-lg leading-relaxed max-w-lg mx-auto player-muted-text">
                                    {{ steps[currentStep].description }}
                                </p>
                            </div>

                            <!-- Visual Example (Optional mockups could go here) -->
                            <div v-if="steps[currentStep].details" 
                                 class="rounded-2xl p-6 shadow-inner text-left max-w-lg mx-auto w-full"
                                 style="background: rgba(0,0,0,0.2); border: 1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent);">
                                <ul class="space-y-4">
                                    <li v-for="(detail, i) in steps[currentStep].details" :key="i" class="flex items-start gap-3">
                                        <i class="pi pi-check-circle mt-1" style="color: var(--accent-primary);"></i>
                                        <span class="text-sm player-muted-text">{{ detail }}</span>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </transition>
                </div>
            </div>

            <!-- Book Footer (Navigation) -->
            <div class="p-6 md:p-8 border-t shrink-0 flex justify-between items-center relative z-10"
                 style="background: rgba(0,0,0,0.1); border-color: color-mix(in srgb, var(--accent-primary) 30%, transparent);">
                <button 
                    @click="prevStep" 
                    :class="{'invisible': currentStep === 0}"
                    class="p-4 px-6 rounded-xl font-black uppercase tracking-widest flex items-center gap-3 transition-all text-[10px] player-nav-btn"
                >
                    <i class="pi pi-arrow-left"></i> Précédent
                </button>

                <button 
                    v-if="currentStep < steps.length - 1"
                    @click="nextStep" 
                    class="p-4 px-6 rounded-xl font-black uppercase tracking-widest flex items-center gap-3 shadow-[0_4px_0_rgba(0,0,0,0.3)] hover:opacity-80 active:translate-y-[4px] active:shadow-none transition-all text-[10px]"
                    style="background: var(--accent-primary); color: white;"
                >
                    Suivant <i class="pi pi-arrow-right"></i>
                </button>

                <button 
                    v-else
                    @click="closeTutorial" 
                    class="p-4 px-6 rounded-xl bg-green-600 text-white font-black uppercase tracking-widest flex items-center gap-3 shadow-[0_4px_0_rgba(0,0,0,0.3)] hover:bg-green-500 active:translate-y-[4px] active:shadow-none transition-all text-[10px]"
                >
                    Commencer l'aventure <i class="pi pi-check"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:show']);

const currentStep = ref(0);

const steps = [
    {
        title: "Le Plateau de Jeu",
        icon: "pi pi-map",
        description: "Bienvenue dans CityPlay. Votre point de départ est la Zone de Commandement. Ici, vous choisissez vos missions (Decks) et suivez votre progression.",
        details: [
            "Sélectionnez un deck parmi les environnements disponibles.",
            "Visualisez vos missions en cours.",
            "Une fois prêt, piochez une carte pour lancer une étape."
        ]
    },
    {
        title: "L'Épreuve",
        icon: "pi pi-compass",
        description: "Chaque étape de la mission vous présente une énigme ou un défi. Lisez attentivement le parchemin.",
        details: [
            "Saisissez votre réponse dans le champ prévu.",
            "Une bonne réponse débloque l'étape suivante.",
            "Des points d'aventure (PTS) vous sont accordés pour chaque succès."
        ]
    },
    {
        title: "La Boussole",
        icon: "pi pi-map-marker",
        description: "CityPlay est une aventure dans le monde réel. Prouvez votre présence sur le lieu de l'énigme pour obtenir un bonus de points !",
        details: [
            "Rendez-vous physiquement à l'emplacement indiqué.",
            "Cliquez sur la Boussole de Validation pour vous géolocaliser.",
            "Gagnez des points bonus (+20 pts) si vous êtes assez proche !"
        ]
    },
    {
        title: "L'Arsenal",
        icon: "pi pi-bolt",
        description: "Si vous êtes bloqué, utilisez les jetons à votre disposition, mais attention, ils ont un coût.",
        details: [
            "Jeton Indice : Révèle un indice contre des points.",
            "Jeton Solution : Révèle la réponse, mais annule les points de l'étape.",
            "Jeton Passer : Ignore l'énigme moyennant une lourde pénalité."
        ]
    }
];

const closeTutorial = () => {
    emit('update:show', false);
    setTimeout(() => {
        currentStep.value = 0;
    }, 500); // Reset after animation
};

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

// Animation d'entrée
watch(() => props.show, (newVal) => {
    if (newVal) {
        nextTick(() => {
            gsap.fromTo('.tutorial-book', 
                { y: 100, opacity: 0, rotationX: 10, scale: 0.9 },
                { y: 0, opacity: 1, rotationX: 0, scale: 1, duration: 0.8, ease: 'back.out(1.2)' }
            );
        });
    }
});
</script>

<style scoped>
/* Page turn animation */
.page-turn-enter-active,
.page-turn-leave-active {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-turn-enter-from {
    opacity: 0;
    transform: translateX(50px) rotateY(-10deg);
}

.page-turn-leave-to {
    opacity: 0;
    transform: translateX(-50px) rotateY(10deg);
}

/* Dark mode defaults */
.player-title { color: #f5e8c7; }
.player-muted-text { color: rgba(245, 232, 199, 0.7); }
.player-nav-btn {
    background: rgba(0,0,0,0.2);
    color: #f5e8c7;
    border: 1px solid rgba(255, 149, 0, 0.3);
}
.player-nav-btn:hover {
    background: rgba(0,0,0,0.4);
    border-color: #FF9500;
}

/* Light mode overrides */
:global(:root.light-theme) .tutorial-book {
    --tutorial-bg: #faf6ee;
    --tutorial-border: #c8a96e;
}
:global(:root.light-theme) .player-title { color: #1a1208 !important; }
:global(:root.light-theme) .player-muted-text { color: #6b4c1e !important; }
:global(:root.light-theme) .player-nav-btn {
    background: rgba(200, 169, 110, 0.15) !important;
    color: #1a1208 !important;
    border-color: #c8a96e !important;
}
:global(:root.light-theme) .player-nav-btn:hover {
    background: rgba(200, 169, 110, 0.3) !important;
    border-color: #059669 !important;
}
</style>
