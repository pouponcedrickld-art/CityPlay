<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, inject, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { gsap } from 'gsap';
import connexionFile from '@/Layouts/connexion_file.png';

const toast = useToast();
const isDark = inject('isDark');

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    flash: Object
});

const loginCard = ref(null);
const artImage = ref(null);
const submitButton = ref(null);
const isInteracting = ref(false);

onMounted(() => {
    if (props.flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: props.flash.success, life: 3000 });
    }
    if (props.flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: props.flash.error, life: 3000 });
    }

    // 1. Initial GSAP set states (Hidden/offscreen for massive entry animation)
    gsap.set('.login-card', { y: 60, opacity: 0, scale: 0.95 });
    gsap.set('.login-art-panel', { x: -100, opacity: 0 });
    gsap.set('.login-form-panel', { x: 100, opacity: 0 });
    gsap.set('.login-stagger', { y: 25, opacity: 0 });
    gsap.set('.art-overlay-stagger', { y: 30, opacity: 0 });
    gsap.set('.neon-border-draw', { scaleX: 0 });

    // 2. High-fidelity timeline reveal sequence
    const tl = gsap.timeline({ defaults: { ease: 'power4.out', duration: 1.4 } });
    
    tl.to('.login-card', { y: 0, opacity: 1, scale: 1, duration: 1.8 })
      .to('.login-art-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.4')
      .to('.login-form-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.6')
      .to('.neon-border-draw', { scaleX: 1, duration: 2.0, ease: 'expo.out' }, '-=1.2')
      .to('.login-stagger', { y: 0, opacity: 1, stagger: 0.08, duration: 0.8 }, '-=1.0')
      .to('.art-overlay-stagger', { y: 0, opacity: 1, stagger: 0.1, duration: 0.8 }, '-=0.8');

    // 3. Cyber Hologram glitch title load-in on "WELCOME"
    glitchTextEffect();

    // 4. Mouse Interactive listeners for Parallax and 3D Tilt
    window.addEventListener('mousemove', onGlobalMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onGlobalMouseMove);
});

// Character Glitch effect on Heading
const headingText = ref("WELCOME");
const glitchTextEffect = () => {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#@$%&*0123456789";
    const originalText = "WELCOME";
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
    const card = loginCard.value;
    const artImg = artImage.value;
    if (!card) return;

    // Viewport measurements
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

    // 3D TILT EFFECT ON THE WHOLE CARD
    // Calculate mouse percentage relative to screen center
    const xPct = (clientX / innerWidth - 0.5) * 2; // -1 to 1
    const yPct = (clientY / innerHeight - 0.5) * 2; // -1 to 1

    // Rotate card slightly based on mouse position
    gsap.to(card, {
        rotateY: xPct * 8, // Up to 8 degrees rotation
        rotateX: -yPct * 8, // Up to 8 degrees rotation
        transformPerspective: 1200,
        ease: 'power2.out',
        duration: 0.6
    });

    // PARALLAX EFFECT ON THE IMAGE INNER LAYER
    if (artImg) {
        gsap.to(artImg, {
            x: -xPct * 20, // Pan image horizontally up to 20px
            y: -yPct * 20, // Pan image vertically up to 20px
            scale: 1.12 + Math.abs(xPct * 0.03), // Subtle scale shift
            ease: 'power1.out',
            duration: 0.8
        });
    }

    // MAGNETIC ATTRACT ON BUTTONS AND ACCENTS
    const btn = submitButton.value;
    if (btn) {
        const rect = btn.getBoundingClientRect();
        const btnX = rect.left + rect.width / 2;
        const btnY = rect.top + rect.height / 2;
        const dist = Math.hypot(clientX - btnX, clientY - btnY);

        if (dist < 100) {
            // Pull button toward mouse
            gsap.to(btn, {
                x: (clientX - btnX) * 0.25,
                y: (clientY - btnY) * 0.25,
                scale: 1.03,
                ease: 'power2.out',
                duration: 0.4
            });
        } else {
            // Reset button position
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
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion - Bénin Quest" />

        <!-- 3D PERSPECTIVE CARD WRAPPER -->
        <div 
            ref="loginCard"
            class="login-card max-w-5xl w-full mx-auto flex flex-col md:flex-row overflow-hidden rounded-[2.5rem] border transition-all duration-500 shadow-2xl relative"
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
            <div class="login-art-panel relative w-full md:w-1/2 min-h-[350px] md:min-h-[600px] overflow-hidden select-none">
                <!-- Inner Image layer for deep parallax panning -->
                <div 
                    ref="artImage"
                    class="absolute inset-0 bg-cover bg-center scale-[1.1]"
                    :style="{ backgroundImage: 'url(' + connexionFile + ')' }"
                ></div>

                <!-- Gradient cinematic fog and vignettes -->
                <div 
                    class="absolute inset-0 z-10 transition-all duration-500"
                    :class="isDark 
                        ? 'bg-gradient-to-t md:bg-gradient-to-r from-[#0a0907]/90 via-black/40 to-transparent' 
                        : 'bg-gradient-to-t md:bg-gradient-to-r from-[#faf9f5]/90 via-transparent to-transparent'"
                ></div>

                <!-- Epic gaming badges overlay -->
                <div class="absolute bottom-0 left-0 p-8 space-y-3 z-20 hidden md:block">
                    <span 
                        class="art-overlay-stagger px-3 py-1 text-[9px] font-black tracking-[0.2em] text-white rounded-full uppercase"
                        :class="isDark ? 'bg-[#FF9500]' : 'bg-[#008751]'"
                    >
                        Saison 1 : Les Origines
                    </span>
                    <h4 class="art-overlay-stagger text-xl font-black text-white uppercase tracking-tight drop-shadow-lg">
                        Dominez le Classement
                    </h4>
                    <p class="art-overlay-stagger text-[10px] text-white/60 font-semibold tracking-wider flex items-center gap-1.5 drop-shadow-md">
                        <i class="pi pi-compass animate-spin-slow"></i> Résolvez des énigmes d'Abomey à Ganvié !
                    </p>
                </div>
            </div>

            <!-- RIGHT FORM SIDE -->
            <div 
                @mouseenter="isInteracting = true" 
                @mouseleave="isInteracting = false"
                @focusin="isInteracting = true"
                @focusout="isInteracting = false"
                class="login-form-panel w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center relative overflow-hidden"
            >
                <!-- Holographic scanning grids overlay for high-end gamer feel -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/5 via-transparent to-transparent pointer-events-none"></div>

                <!-- Heading details -->
                <div class="login-stagger mb-8 space-y-2">
                    <span 
                        :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" 
                        class="text-[9px] font-black uppercase tracking-[0.3em] transition-colors duration-500"
                    >
                        Portail des Explorateurs • Bénin Quest 🇧🇯
                    </span>
                    
                    <!-- Cyber Glitch Heading text -->
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
                        Prêt à percer les mystères de Ouidah, à voguer sur Ganvié ou à braver les énigmes de Dassa ? Entrez dans le jeu !
                    </p>
                </div>

                <div v-if="status" class="mb-4 p-4 bg-green-500/10 border border-green-500/20 text-xs font-bold text-green-400 rounded-2xl">
                    {{ status }}
                </div>

                <!-- Login form -->
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <!-- Email field -->
                    <div class="login-stagger space-y-2">
                        <InputLabel for="email" value="Identifiant Aventurier (Email)" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
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

                    <!-- Password field -->
                    <div class="login-stagger space-y-2">
                        <InputLabel for="password" value="Clé Secrète (Mot de passe)" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
                        <div class="relative">
                            <span :class="isDark ? 'text-white/30' : 'text-[#1c1917]/30'" class="absolute inset-y-0 left-0 pl-4 flex items-center transition-colors duration-500">
                                <i class="pi pi-lock text-sm"></i>
                            </span>
                            <input
                                id="password"
                                type="password"
                                :class="[
                                    isDark 
                                        ? 'bg-black/50 border-white/5 text-white placeholder:text-white/20 focus:border-[#FF9500]' 
                                        : 'bg-[#faf9f6] border-[#e2dfd5] text-[#1c1917] placeholder:text-[#1c1917]/30 focus:border-[#008751]',
                                    'w-full pl-11 pr-4 py-3.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                ]"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError class="mt-2 text-xs font-bold text-red-500" :message="form.errors.password" />
                    </div>

                    <!-- Remember & Forgot details -->
                    <div class="login-stagger flex items-center justify-between pt-1">
                        <label class="flex items-center cursor-pointer group select-none">
                            <Checkbox name="remember" v-model:checked="form.remember" :class="[
                                isDark ? 'border-white/10 bg-black/40 text-[#FF9500]' : 'border-[#e2dfd5] bg-[#faf9f6] text-[#008751]',
                                'rounded focus:ring-0 transition-colors duration-500'
                            ]" />
                            <span :class="isDark ? 'text-white/40 group-hover:text-white/70' : 'text-[#1c1917]/40 group-hover:text-[#1c1917]/70'" class="ms-2 text-[10px] font-black uppercase tracking-wider transition-all duration-300">Rester connecté</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            :class="[
                                isDark ? 'text-[#FF9500] hover:text-[#FF9500]/80' : 'text-[#008751] hover:text-[#008751]/80',
                                'text-[10px] font-black uppercase tracking-widest transition-colors duration-500'
                            ]"
                        >
                            Boussole Perdue ?
                        </Link>
                    </div>

                    <!-- MAGNETIC LOGIN BUTTON -->
                    <div class="login-stagger pt-3 relative">
                        <button
                            ref="submitButton"
                            type="submit"
                            :class="[
                                isDark 
                                    ? 'bg-[#FF9500] text-black shadow-[0_5px_0_#cc7a00] hover:bg-[#FF9500]/95 hover:shadow-[0_0_20px_rgba(255,149,0,0.4)]' 
                                    : 'bg-[#008751] text-white shadow-[0_5px_0_#005c37] hover:bg-[#008751]/95 hover:shadow-[0_0_20px_rgba(0,135,81,0.3)]',
                                'w-full p-4 rounded-xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 active:shadow-none active:translate-y-[5px] transition-all cursor-pointer disabled:opacity-50 disabled:pointer-events-none select-none relative z-10'
                            ]"
                            :disabled="form.processing"
                        >
                            <span>Lancer la Session de Jeu 🎮</span>
                            <i class="pi pi-sign-in text-xs font-black"></i>
                        </button>
                    </div>
                </form>

                <!-- REGISTER REDIRECT LINK -->
                <div :class="isDark ? 'border-white/5' : 'border-black/5'" class="login-stagger mt-6 pt-5 border-t text-center space-y-3 transition-colors duration-500">
                    <p :class="isDark ? 'text-white/30' : 'text-[#1c1917]/40'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500">Nouveau sur CityPlay ?</p>
                    
                    <Link
                        :href="route('register')"
                        :class="[
                            isDark 
                                ? 'bg-white/5 border-white/10 text-white hover:bg-white/10 hover:border-[#FF9500]/40 hover:text-[#FF9500]' 
                                : 'bg-[#008751]/5 border-[#008751]/10 text-[#008751] hover:bg-[#008751]/10 hover:border-[#008751]/40',
                            'inline-flex items-center justify-center w-full p-3.5 border rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-sm active:scale-95 select-none'
                        ]"
                    >
                        Créer mon Profil d'Explorateur
                    </Link>
                </div>
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
.login-card {
    transform-style: preserve-3d;
}
</style>
