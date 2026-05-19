<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { inject, onMounted, onUnmounted, ref } from 'vue';
import { gsap } from 'gsap';
import connexionFile from '@/Layouts/connexion_file.png';

const isDark = inject('isDark');

defineProps({
    status: {
        type: String,
    },
});

const forgotCard = ref(null);
const artImage = ref(null);
const submitButton = ref(null);
const isInteracting = ref(false);

onMounted(() => {
    // 1. Initial GSAP set states
    gsap.set('.forgot-card', { y: 60, opacity: 0, scale: 0.95 });
    gsap.set('.forgot-art-panel', { x: -100, opacity: 0 });
    gsap.set('.forgot-form-panel', { x: 100, opacity: 0 });
    gsap.set('.forgot-stagger', { y: 25, opacity: 0 });
    gsap.set('.art-overlay-stagger', { y: 30, opacity: 0 });
    gsap.set('.neon-border-draw', { scaleX: 0 });

    // 2. High-fidelity reveal timeline
    const tl = gsap.timeline({ defaults: { ease: 'power4.out', duration: 1.4 } });
    
    tl.to('.forgot-card', { y: 0, opacity: 1, scale: 1, duration: 1.8 })
      .to('.forgot-art-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.4')
      .to('.forgot-form-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.6')
      .to('.neon-border-draw', { scaleX: 1, duration: 2.0, ease: 'expo.out' }, '-=1.2')
      .to('.forgot-stagger', { y: 0, opacity: 1, stagger: 0.08, duration: 0.8 }, '-=1.0')
      .to('.art-overlay-stagger', { y: 0, opacity: 1, stagger: 0.1, duration: 0.8 }, '-=0.8');

    glitchTextEffect();
    window.addEventListener('mousemove', onGlobalMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onGlobalMouseMove);
});

// Cyber Glitch text effect
const headingText = ref("LOST WAY");
const glitchTextEffect = () => {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#@$%&*0123456789";
    const originalText = "LOST WAY";
    let iterations = 0;
    
    const interval = setInterval(() => {
        headingText.value = originalText
            .split("")
            .map((char, index) => {
                if (index < iterations) {
                    return originalText[index];
                }
                return chars[Math.floor(Math.random() * chars.length)];
            })
            .join("");
            
        if (iterations >= originalText.length) {
            clearInterval(interval);
            headingText.value = originalText;
        }
        iterations += 1/3;
    }, 45);
};

// 3D Tilt Card and Parallax Artwork Image Effect
const onGlobalMouseMove = (e) => {
    const card = forgotCard.value;
    const artImg = artImage.value;
    if (!card) return;

    const { clientX, clientY } = e;
    const { innerWidth, innerHeight } = window;

    if (isInteracting.value) {
        // Smoothly animate back to normal stable state
        gsap.to(card, {
            rotateY: 0,
            rotateX: 0,
            transformPerspective: 1200,
            ease: 'power2.out',
            duration: 0.6
        });
        if (artImg) {
            gsap.to(artImg, {
                x: 0,
                y: 0,
                scale: 1.1,
                ease: 'power2.out',
                duration: 0.6
            });
        }
        return; // Skip tilt and parallax calculations
    }

    const xPct = (clientX / innerWidth - 0.5) * 2;
    const yPct = (clientY / innerHeight - 0.5) * 2;

    gsap.to(card, {
        rotateY: xPct * 8,
        rotateX: -yPct * 8,
        transformPerspective: 1200,
        ease: 'power2.out',
        duration: 0.6
    });

    if (artImg) {
        gsap.to(artImg, {
            x: -xPct * 20,
            y: -yPct * 20,
            scale: 1.12 + Math.abs(xPct * 0.03),
            ease: 'power1.out',
            duration: 0.8
        });
    }

    const btn = submitButton.value;
    if (btn) {
        const rect = btn.getBoundingClientRect();
        const btnX = rect.left + rect.width / 2;
        const btnY = rect.top + rect.height / 2;
        const dist = Math.hypot(clientX - btnX, clientY - btnY);

        if (dist < 100) {
            gsap.to(btn, {
                x: (clientX - btnX) * 0.25,
                y: (clientY - btnY) * 0.25,
                scale: 1.03,
                ease: 'power2.out',
                duration: 0.4
            });
        } else {
            gsap.to(btn, {
                x: 0,
                y: 0,
                scale: 1,
                ease: 'power3.out',
                duration: 0.5
            });
        }
    }
};

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Boussole Perdue - Bénin Quest" />

        <!-- 3D PERSPECTIVE CARD WRAPPER -->
        <div 
            ref="forgotCard"
            class="forgot-card max-w-5xl w-full mx-auto flex flex-col md:flex-row overflow-hidden rounded-[2.5rem] border transition-all duration-500 shadow-2xl relative"
            :class="isDark 
                ? 'bg-[#0f0e0c]/90 border-white/5 shadow-black/90 hover:border-[#FF9500]/30' 
                : 'bg-[#faf9f6]/95 border-[#e2dfd5] shadow-stone-400/25 hover:border-[#008751]/30'"
        >
            <!-- TOP NEON BORDER LIGHT DECORATION -->
            <div 
                class="neon-border-draw absolute top-0 left-0 right-0 h-[3px] z-30 origin-left"
                :class="isDark ? 'bg-gradient-to-r from-[#FF9500] via-[#ffd699] to-transparent' : 'bg-gradient-to-r from-[#008751] via-[#85e0b7] to-transparent'"
            ></div>

            <!-- LEFT ARTWORK SIDE -->
            <div class="forgot-art-panel relative w-full md:w-1/2 min-h-[350px] md:min-h-[600px] overflow-hidden select-none">
                <div 
                    ref="artImage"
                    class="absolute inset-0 bg-cover bg-center scale-[1.1]"
                    :style="{ backgroundImage: 'url(' + connexionFile + ')' }"
                ></div>

                <div 
                    class="absolute inset-0 z-10 transition-all duration-500"
                    :class="isDark 
                        ? 'bg-gradient-to-t md:bg-gradient-to-r from-[#0a0907]/90 via-black/40 to-transparent' 
                        : 'bg-gradient-to-t md:bg-gradient-to-r from-[#faf9f5]/90 via-transparent to-transparent'"
                ></div>

                <div class="absolute bottom-0 left-0 p-8 space-y-3 z-20 hidden md:block">
                    <span 
                        class="art-overlay-stagger px-3 py-1 text-[9px] font-black tracking-[0.2em] text-white rounded-full uppercase"
                        :class="isDark ? 'bg-[#FF9500]' : 'bg-[#008751]'"
                    >
                        Assistance Aventurier
                    </span>
                    <h4 class="art-overlay-stagger text-xl font-black text-white uppercase tracking-tight drop-shadow-lg">
                        Boussole de la Guilde
                    </h4>
                    <p class="art-overlay-stagger text-[10px] text-white/60 font-semibold tracking-wider flex items-center gap-1.5 drop-shadow-md">
                        <i class="pi pi-compass animate-spin-slow"></i> Retrouvez votre chemin vers la gloire !
                    </p>
                </div>
            </div>

            <!-- RIGHT FORM SIDE -->
            <div 
                @mouseenter="isInteracting = true" 
                @mouseleave="isInteracting = false"
                @focusin="isInteracting = true"
                @focusout="isInteracting = false"
                class="forgot-form-panel w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/5 via-transparent to-transparent pointer-events-none"></div>

                <div class="forgot-stagger mb-6 space-y-2">
                    <span 
                        :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" 
                        class="text-[9px] font-black uppercase tracking-[0.3em] transition-colors duration-500"
                    >
                        Explorateur Égaré • Bénin Quest 🇧🇯
                    </span>
                    
                    <h3 
                        :class="isDark ? 'text-white' : 'text-[#1c1917]'" 
                        class="text-4xl font-black uppercase tracking-tighter transition-colors duration-500 flex items-center gap-2 select-text"
                    >
                        {{ headingText }} <span class="animate-pulse" :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'">.</span>
                    </h3>

                    <p 
                        :class="isDark ? 'text-white/40' : 'text-[#1c1917]/50'" 
                        class="text-xs font-semibold transition-colors duration-500 leading-relaxed"
                    >
                        Votre boussole de connexion a perdu le nord ? Recevez un nouveau lien d'accès en renseignant votre email.
                    </p>
                </div>

                <!-- Guidance Info block -->
                <div 
                    class="forgot-stagger mb-6 p-4 border rounded-2xl text-xs leading-relaxed transition-all duration-500"
                    :class="isDark ? 'bg-white/5 border-white/5 text-white/50' : 'bg-[#f0ede6] border-black/5 text-[#1c1917]/60'"
                >
                    Pas de panique ! Indiquez-nous simplement votre adresse email et nous vous enverrons un lien magique pour réinitialiser vos accès d'explorateur.
                </div>

                <div v-if="status" class="mb-4 p-4 bg-green-500/10 border border-green-500/20 text-xs font-bold text-green-400 rounded-2xl">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email field -->
                    <div class="forgot-stagger space-y-2">
                        <InputLabel for="email" value="Votre Adresse Email de Guilde" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
                        <div class="relative">
                            <span :class="isDark ? 'text-white/30' : 'text-[#1c1917]/30'" class="absolute inset-y-0 left-0 pl-4 flex items-center transition-colors duration-500">
                                <i class="pi pi-envelope text-sm"></i>
                            </span>
                            <input
                                id="email"
                                type="email"
                                :class="[
                                    isDark 
                                        ? 'bg-black/50 border-white/5 text-white placeholder:text-white/20 focus:border-[#FF9500]' 
                                        : 'bg-[#faf9f6] border-[#e2dfd5] text-[#1c1917] placeholder:text-[#1c1917]/30 focus:border-[#008751]',
                                    'w-full pl-11 pr-4 py-3.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                ]"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="explorateur@benin-quest.bj"
                            />
                        </div>
                        <InputError class="mt-2 text-xs font-bold text-red-500" :message="form.errors.email" />
                    </div>

                    <!-- Actions link + button -->
                    <div class="forgot-stagger pt-2 flex items-center justify-between gap-4">
                        <Link
                            :href="route('login')"
                            :class="[
                                isDark ? 'text-white/40 hover:text-white' : 'text-[#1c1917]/50 hover:text-[#1c1917]/80',
                                'text-[10px] font-black uppercase tracking-widest transition-colors duration-500'
                            ]"
                        >
                            Retour au Camp
                        </Link>

                        <button
                            ref="submitButton"
                            type="submit"
                            :class="[
                                isDark 
                                    ? 'bg-[#FF9500] text-black shadow-[0_5px_0_#cc7a00] hover:bg-[#FF9500]/95 hover:shadow-[0_0_20px_rgba(255,149,0,0.4)]' 
                                    : 'bg-[#008751] text-white shadow-[0_5px_0_#005c37] hover:bg-[#008751]/95 hover:shadow-[0_0_20px_rgba(0,135,81,0.3)]',
                                'px-6 py-4 rounded-xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 active:shadow-none active:translate-y-[5px] transition-all cursor-pointer disabled:opacity-50 disabled:pointer-events-none select-none relative z-10'
                            ]"
                            :disabled="form.processing"
                        >
                            <span>Retrouver mon Chemin 🗺️</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 10s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Beautiful Focus transitions */
input {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
input:focus {
    box-shadow: 0 0 20px rgba(255, 149, 0, 0.25) !important;
    transform: translateY(-2px);
}
:deep(.light) input:focus {
    box-shadow: 0 0 20px rgba(0, 135, 81, 0.2) !important;
}

/* 3D Depth shadows */
.forgot-card {
    transform-style: preserve-3d;
}
</style>
