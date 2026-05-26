<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import { onMounted, onUnmounted, ref } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    partie: Object,
    lieu: Object,
    progression: Object,
    points_gagnes: { type: Number, default: 0 },
    score_total: { type: Number, default: 0 },
});

const page = usePage();
const celebrationContainer = ref(null);
const sparkles = ref([]);

const createSparkle = () => {
    const id = Math.random().toString(36).substr(2, 9);
    const style = {
        left: Math.random() * 100 + '%',
        top: Math.random() * 100 + '%',
        transform: `scale(${Math.random()})`,
        backgroundColor: ['#FFD700', '#FFA500', '#FFFFFF', '#FF6347'][Math.floor(Math.random() * 4)]
    };
    sparkles.value.push({ id, style });
    setTimeout(() => {
        sparkles.value = sparkles.value.filter(s => s.id !== id);
    }, 2000);
};

const nextStep = () => {
    if (props.progression?.statut === 'terminee' || !props.progression?.enigme_courante_id) {
        router.get(route('progression.summary', props.partie.id));
        return;
    }
    router.get(route('progression.enigme', props.partie.id));
};

onMounted(() => {
    // GSAP Animations
    const tl = gsap.timeline();
    
    tl.from('.cave-result-icon', {
        scale: 0,
        rotation: -180,
        duration: 1,
        ease: 'back.out(1.7)'
    })
    .from('.cave-result-title', {
        y: 20,
        opacity: 0,
        duration: 0.5
    }, '-=0.5')
    .from('.cave-result-sub', {
        y: 10,
        opacity: 0,
        duration: 0.5
    }, '-=0.3')
    .from('.cave-discovery-card', {
        y: 50,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    }, '-=0.2')
    .from('.cave-btn', {
        scale: 0.8,
        opacity: 0,
        duration: 0.5
    }, '-=0.3');

    // Sparkles interval
    const interval = setInterval(createSparkle, 150);

    // Écoute sur le canal privé de la partie
    window.Echo.private(`partie.${props.partie.id}`)
        .listen('.EnigmeResolue', (e) => {
            if (e.resolu_par.id !== page.props.auth.user.id) {
                router.visit(route('progression.enigme', props.partie.id), {
                    replace: true
                });
            }
        });

    onUnmounted(() => {
        clearInterval(interval);
        window.Echo.leave(`partie.${props.partie.id}`);
    });
});
</script>

<template>
    <CaveGameLayout>
        <Head title="Victoire !" />

        <div class="cave-result-screen overflow-hidden" ref="celebrationContainer">
            <!-- Sparkles Overlay -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div v-for="sparkle in sparkles" :key="sparkle.id" 
                     class="sparkle absolute w-2 h-2 rounded-full"
                     :style="sparkle.style">
                </div>
            </div>

            <div class="cave-result-icon cave-result-icon--win relative z-10 shadow-2xl">
                <i class="pi pi-star-fill animate-pulse" />
            </div>

            <h1 class="cave-result-title relative z-10 text-4xl font-black uppercase tracking-widest text-[var(--cave-gold)] drop-shadow-lg">
                Excellent !
            </h1>
            
            <div class="cave-result-sub relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md px-6 py-2 rounded-full border border-white/20">
                <span class="text-white font-bold">+{{ points_gagnes }} PTS</span>
                <div class="w-px h-4 bg-white/30"></div>
                <span class="text-[var(--cave-gold)] font-black">TOTAL : {{ score_total }} PTS</span>
            </div>

            <article class="cave-discovery-card relative z-10 transform hover:scale-[1.02] transition-transform duration-300">
                <div v-if="lieu?.photos?.length" class="relative h-64 overflow-hidden rounded-t-3xl">
                    <img :src="lieu.photos[0].url" class="w-full h-full object-cover" :alt="lieu.nom">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-4 left-6 right-6">
                        <span class="cave-badge cave-badge--gold mb-1">Lieu découvert</span>
                        <h2 class="text-2xl font-black text-white uppercase">{{ lieu?.nom || 'Lieu secret' }}</h2>
                    </div>
                </div>
                <div v-else class="h-48 flex items-center justify-center bg-[var(--cave-rock)] rounded-t-3xl">
                    <i class="pi pi-map text-5xl opacity-20 text-white" />
                </div>
                <div class="p-8 bg-white/95 backdrop-blur-sm rounded-b-3xl">
                    <p class="cave-panel__text italic text-lg text-[var(--cave-border-dark)] leading-relaxed" style="margin:0">
                        "{{ partie.environnement?.message_bonne_reponse || lieu?.description || 'Vous avez percé les secrets de cet endroit. La mission continue !' }}"
                    </p>
                </div>
            </article>

            <div class="cave-btn-stack w-full max-w-[420px] relative z-10">
                <button type="button" class="cave-btn w-full group overflow-hidden" @click="nextStep">
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                    <i class="cave-btn__icon pi pi-arrow-right group-hover:translate-x-1 transition-transform" />
                    <span class="cave-btn__label">Mission suivante</span>
                </button>
                <p class="cave-hint text-center text-white/60 animate-bounce">
                    Appuyez pour continuer votre aventure
                </p>
            </div>
        </div>
    </CaveGameLayout>
</template>

<style scoped>
.sparkle {
    animation: sparkle-animation 2s forwards ease-out;
}

@keyframes sparkle-animation {
    0% { transform: scale(0) rotate(0deg); opacity: 1; }
    100% { transform: scale(1.5) rotate(180deg); opacity: 0; y: -100px; }
}

.cave-result-icon--win {
    background: radial-gradient(circle at center, var(--cave-gold), var(--cave-gold-dark));
    box-shadow: 0 0 50px rgba(255, 215, 0, 0.4);
}

.cave-discovery-card {
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    max-width: 500px;
}
</style>
