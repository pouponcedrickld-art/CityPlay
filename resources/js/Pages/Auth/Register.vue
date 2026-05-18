<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, inject } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import { gsap } from 'gsap';
import connexionFile from '@/Layouts/connexion_file.png';

const showCGU = ref(false);
const showPrivacy = ref(false);
const toast = useToast();
const isDark = inject('isDark'); // Injecter le thème dynamique

const props = defineProps({
    flash: Object
});

const registerCard = ref(null);
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

    // 1. Initial GSAP set states
    gsap.set('.register-card', { y: 60, opacity: 0, scale: 0.95 });
    gsap.set('.register-art-panel', { x: -100, opacity: 0 });
    gsap.set('.register-form-panel', { x: 100, opacity: 0 });
    gsap.set('.register-stagger', { y: 15, opacity: 0 });
    gsap.set('.art-overlay-stagger', { y: 30, opacity: 0 });
    gsap.set('.neon-border-draw', { scaleX: 0 });

    // 2. High-fidelity reveal timeline
    const tl = gsap.timeline({ defaults: { ease: 'power4.out', duration: 1.4 } });
    
    tl.to('.register-card', { y: 0, opacity: 1, scale: 1, duration: 1.8 })
      .to('.register-art-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.4')
      .to('.register-form-panel', { x: 0, opacity: 1, duration: 1.6 }, '-=1.6')
      .to('.neon-border-draw', { scaleX: 1, duration: 2.0, ease: 'expo.out' }, '-=1.2')
      .to('.register-stagger', { y: 0, opacity: 1, stagger: 0.06, duration: 0.8 }, '-=1.0')
      .to('.art-overlay-stagger', { y: 0, opacity: 1, stagger: 0.1, duration: 0.8 }, '-=0.8');

    glitchTextEffect();
    window.addEventListener('mousemove', onGlobalMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onGlobalMouseMove);
});

// Cyber Glitch text effect
const headingText = ref("GUILD JOIN");
const glitchTextEffect = () => {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#@$%&*0123456789";
    const originalText = "GUILD JOIN";
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
    const card = registerCard.value;
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
    name: '',
    pseudo: '',
    email: '',
    password: '',
    password_confirmation: '',
    consent_cgu: false,
    consent_donnees: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Rejoindre la Guilde - Bénin Quest" />

        <!-- 3D PERSPECTIVE CARD WRAPPER -->
        <div 
            ref="registerCard"
            class="register-card max-w-5xl w-full mx-auto flex flex-col md:flex-row overflow-hidden rounded-[2.5rem] border transition-all duration-500 shadow-2xl relative"
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
            <div class="register-art-panel relative w-full md:w-1/2 min-h-[350px] md:min-h-[600px] overflow-hidden select-none">
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
                        Nouvelle Amazone / Guerrier
                    </span>
                    <h4 class="art-overlay-stagger text-xl font-black text-white uppercase tracking-tight drop-shadow-lg">
                        Prenez les Armes
                    </h4>
                    <p class="art-overlay-stagger text-[10px] text-white/60 font-semibold tracking-wider flex items-center gap-1.5 drop-shadow-md">
                        <i class="pi pi-compass animate-spin-slow"></i> Créez votre profil et gagnez de l'XP !
                    </p>
                </div>
            </div>

            <!-- RIGHT FORM SIDE -->
            <div 
                @mouseenter="isInteracting = true" 
                @mouseleave="isInteracting = false"
                @focusin="isInteracting = true"
                @focusout="isInteracting = false"
                class="register-form-panel w-full md:w-1/2 p-8 md:p-10 flex flex-col justify-center relative overflow-hidden max-h-[650px] overflow-y-auto"
            >
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/5 via-transparent to-transparent pointer-events-none"></div>

                <!-- Header details -->
                <div class="register-stagger mb-5 space-y-1">
                    <span 
                        :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" 
                        class="text-[9px] font-black uppercase tracking-[0.3em] transition-colors duration-500"
                    >
                        Nouveau Chasseur de Trésor • Bénin Quest 🇧🇯
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
                        Créez votre profil de joueur pour participer aux plus grandes quêtes d'exploration et d'énigmes du Bénin.
                    </p>
                </div>

                <!-- Form block -->
                <form @submit.prevent="submit" class="space-y-3.5">
                    <!-- Grid for Name & Pseudo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Name Field -->
                        <div class="register-stagger space-y-1">
                            <InputLabel for="name" value="Nom Réel" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
                            <div class="relative">
                                <span :class="isDark ? 'text-white/30' : 'text-[#1c1917]/30'" class="absolute inset-y-0 left-0 pl-4 flex items-center transition-colors duration-500">
                                    <i class="pi pi-user text-sm"></i>
                                </span>
                                <input
                                    id="name"
                                    type="text"
                                    :class="[
                                        isDark 
                                            ? 'bg-black/50 border-white/5 text-white placeholder:text-white/20 focus:border-[#FF9500]' 
                                            : 'bg-[#faf9f6] border-[#e2dfd5] text-[#1c1917] placeholder:text-[#1c1917]/30 focus:border-[#008751]',
                                        'w-full pl-11 pr-4 py-2.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                    ]"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Ex: Koffi Mensah"
                                />
                            </div>
                            <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.name" />
                        </div>

                        <!-- Pseudo Field -->
                        <div class="register-stagger space-y-1">
                            <InputLabel for="pseudo" value="Nom de Code (Pseudo)" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
                            <div class="relative">
                                <span :class="isDark ? 'text-white/30' : 'text-[#1c1917]/30'" class="absolute inset-y-0 left-0 pl-4 flex items-center transition-colors duration-500">
                                    <i class="pi pi-compass text-sm"></i>
                                </span>
                                <input
                                    id="pseudo"
                                    type="text"
                                    :class="[
                                        isDark 
                                            ? 'bg-black/50 border-white/5 text-white placeholder:text-white/20 focus:border-[#FF9500]' 
                                            : 'bg-[#faf9f6] border-[#e2dfd5] text-[#1c1917] placeholder:text-[#1c1917]/30 focus:border-[#008751]',
                                        'w-full pl-11 pr-4 py-2.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                    ]"
                                    v-model="form.pseudo"
                                    required
                                    autocomplete="nickname"
                                    placeholder="Ex: Amazone"
                                />
                            </div>
                            <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.pseudo" />
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="register-stagger space-y-1">
                        <InputLabel for="email" value="Adresse Email" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
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
                                    'w-full pl-11 pr-4 py-2.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                ]"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder="koffi@exemple.com"
                            />
                        </div>
                        <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.email" />
                    </div>

                    <!-- Grid for Password & Confirmation -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Password Field -->
                        <div class="register-stagger space-y-1">
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
                                        'w-full pl-11 pr-4 py-2.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                    ]"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="register-stagger space-y-1">
                            <InputLabel for="password_confirmation" value="Confirmation de la Clé" :class="isDark ? 'text-white/50' : 'text-[#1c1917]/50'" class="text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500" />
                            <div class="relative">
                                <span :class="isDark ? 'text-white/30' : 'text-[#1c1917]/30'" class="absolute inset-y-0 left-0 pl-4 flex items-center transition-colors duration-500">
                                    <i class="pi pi-lock text-sm"></i>
                                </span>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    :class="[
                                        isDark 
                                            ? 'bg-black/50 border-white/5 text-white placeholder:text-white/20 focus:border-[#FF9500]' 
                                            : 'bg-[#faf9f6] border-[#e2dfd5] text-[#1c1917] placeholder:text-[#1c1917]/30 focus:border-[#008751]',
                                        'w-full pl-11 pr-4 py-2.5 border-2 rounded-xl font-bold focus:ring-0 focus:outline-none transition-all shadow-inner text-sm'
                                    ]"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <!-- Checkbox : CGU -->
                    <div class="register-stagger pt-1">
                        <label class="flex items-start cursor-pointer group select-none">
                            <Checkbox name="consent_cgu" v-model:checked="form.consent_cgu" required :class="[
                                isDark ? 'border-white/10 bg-black/40 text-[#FF9500]' : 'border-[#e2dfd5] bg-[#faf9f6] text-[#008751]',
                                'rounded focus:ring-0 mt-0.5 transition-colors duration-500'
                            ]" />
                            <span :class="isDark ? 'text-white/40 group-hover:text-white/70' : 'text-[#1c1917]/40 group-hover:text-[#1c1917]/70'" class="ms-3 text-[10px] font-bold leading-relaxed transition-colors duration-300">
                                J'accepte les <button type="button" @click="showCGU = true" :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black hover:underline focus:outline-none transition-colors duration-500">Conditions Générales de la Guilde</button>
                            </span>
                        </label>
                        <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.consent_cgu" />
                    </div>

                    <!-- Checkbox : Privacy -->
                    <div class="register-stagger">
                        <label class="flex items-start cursor-pointer group select-none">
                            <Checkbox name="consent_donnees" v-model:checked="form.consent_donnees" required :class="[
                                isDark ? 'border-white/10 bg-black/40 text-[#FF9500]' : 'border-[#e2dfd5] bg-[#faf9f6] text-[#008751]',
                                'rounded focus:ring-0 mt-0.5 transition-colors duration-500'
                            ]" />
                            <span :class="isDark ? 'text-white/40 group-hover:text-white/70' : 'text-[#1c1917]/40 group-hover:text-[#1c1917]/70'" class="ms-3 text-[10px] font-bold leading-relaxed transition-colors duration-300">
                                J'accepte la <button type="button" @click="showPrivacy = true" :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black hover:underline focus:outline-none transition-colors duration-500">Charte de Confidentialité</button> et la géolocalisation
                            </span>
                        </label>
                        <InputError class="mt-1 text-xs font-bold text-red-500" :message="form.errors.consent_donnees" />
                    </div>

                    <!-- Actions link + button -->
                    <div class="register-stagger pt-2 flex items-center justify-between gap-4">
                        <Link
                            :href="route('login')"
                            :class="[
                                isDark ? 'text-white/40 hover:text-white' : 'text-[#1c1917]/50 hover:text-[#1c1917]/80',
                                'text-[10px] font-black uppercase tracking-widest transition-colors duration-500'
                            ]"
                        >
                            Déjà Aventurier ?
                        </Link>

                        <button
                            ref="submitButton"
                            type="submit"
                            :class="[
                                isDark 
                                    ? 'bg-[#FF9500] text-black shadow-[0_5px_0_#cc7a00] hover:bg-[#FF9500]/95 hover:shadow-[0_0_20px_rgba(255,149,0,0.4)]' 
                                    : 'bg-[#008751] text-white shadow-[0_5px_0_#005c37] hover:bg-[#008751]/95 hover:shadow-[0_0_20px_rgba(0,135,81,0.3)]',
                                'px-6 py-3.5 rounded-xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 active:shadow-none active:translate-y-[5px] transition-all cursor-pointer disabled:opacity-50 disabled:pointer-events-none select-none relative z-10'
                            ]"
                            :disabled="form.processing"
                        >
                            <span>Créer ma Légende 🏆</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal CGU -->
        <Dialog v-model:visible="showCGU" modal header="Charte d'Utilisation" :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :class="isDark ? 'game-dialog-dark' : 'game-dialog-light'">
            <div :class="[
                isDark ? 'bg-black/40 border-white/5 text-white/80' : 'bg-[#faf9f6] border-black/5 text-[#1c1917]/80',
                'prose prose-sm max-w-none p-6 rounded-2xl space-y-4 max-h-[60vh] overflow-y-auto border'
            ]">
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">1. Présentation du service</h4>
                    <p class="text-xs leading-relaxed opacity-70">CityPlay Bénin est une plateforme de gaming et de chasses au trésor touristiques permettant de découvrir le patrimoine béninois par la résolution d'énigmes.</p>
                </div>
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">2. Utilisation et Fair-play</h4>
                    <p class="text-xs leading-relaxed opacity-70">L'utilisateur s'engage à respecter les monuments publics (Porte du Non-Retour, Palais d'Abomey) lors de sa quête. Toute tentative de triche GPS entraînera le bannissement de la guilde.</p>
                </div>
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">3. Responsabilité</h4>
                    <p class="text-xs leading-relaxed opacity-70">Le jeu se déroule en extérieur. Restez vigilant à votre environnement physique lors de vos sessions d'exploration de la ville.</p>
                </div>
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">4. Propriété intellectuelle</h4>
                    <p class="text-xs leading-relaxed opacity-70">Le contenu du jeu (énigmes historiques, indices visuels, logos) est protégé par le droit d'auteur.</p>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end p-4 pt-0">
                    <button 
                        type="button" 
                        @click="showCGU = false" 
                        :class="[
                            isDark ? 'bg-[#FF9500] text-black shadow-[#FF9500]/25' : 'bg-[#008751] text-white shadow-[#008751]/25',
                            'px-5 py-2.5 font-black uppercase tracking-widest text-[10px] rounded-xl hover:opacity-90 transition-all active:scale-95 shadow-md'
                        ]"
                    >
                        J'ai compris
                    </button>
                </div>
            </template>
        </Dialog>

        <!-- Modal Privacy -->
        <Dialog v-model:visible="showPrivacy" modal header="Protection des Explorateurs" :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :class="isDark ? 'game-dialog-dark' : 'game-dialog-light'">
            <div :class="[
                isDark ? 'bg-black/40 border-white/5 text-white/80' : 'bg-[#faf9f6] border-black/5 text-[#1c1917]/80',
                'prose prose-sm max-w-none p-6 rounded-2xl space-y-4 max-h-[60vh] overflow-y-auto border'
            ]">
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">1. Collecte des données de guilde</h4>
                    <p class="text-xs leading-relaxed opacity-70">Nous collectons les données d'inscription ainsi que vos scores de quêtes et temps de parcours.</p>
                </div>
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">2. Utilisation du GPS</h4>
                    <p class="text-xs leading-relaxed opacity-70">La géolocalisation en temps réel est requise uniquement pour valider votre présence physique devant le monument ou le lieu touristique de l'énigme.</p>
                </div>
                <div>
                    <h4 :class="isDark ? 'text-[#FF9500]' : 'text-[#008751]'" class="font-black uppercase text-xs tracking-wider transition-colors duration-500">3. Vos droits d'Aventurier</h4>
                    <p class="text-xs leading-relaxed opacity-70">Conformément aux lois de protection des données, vous pouvez supprimer définitivement votre profil et vos points d'XP de nos serveurs à tout moment.</p>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end p-4 pt-0">
                    <button 
                        type="button" 
                        @click="showPrivacy = false" 
                        :class="[
                            isDark ? 'bg-[#FF9500] text-black shadow-[#FF9500]/25' : 'bg-[#008751] text-white shadow-[#008751]/25',
                            'px-5 py-2.5 font-black uppercase tracking-widest text-[10px] rounded-xl hover:opacity-90 transition-all active:scale-95 shadow-md'
                        ]"
                    >
                        J'ai compris
                    </button>
                </div>
            </template>
        </Dialog>
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

/* Scrollbar styling for form panel */
.register-form-panel::-webkit-scrollbar {
    width: 6px;
}
.register-form-panel::-webkit-scrollbar-track {
    background: transparent;
}
.register-form-panel::-webkit-scrollbar-thumb {
    background: rgba(255, 149, 0, 0.2);
    border-radius: 3px;
}
:deep(.light) .register-form-panel::-webkit-scrollbar-thumb {
    background: rgba(0, 135, 81, 0.15);
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

/* Dialog overrides for adventure cards - Dark Theme */
:deep(.game-dialog-dark.p-dialog-mask) {
    background-color: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(8px) !important;
}
:deep(.game-dialog-dark.p-dialog) {
    background: #161616 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 2.5rem !important;
    box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.9) !important;
    overflow: hidden;
}
:deep(.game-dialog-dark .p-dialog-header) {
    background: transparent !important;
    padding: 2rem 2rem 1rem 2rem !important;
    color: white !important;
    border: none !important;
}
:deep(.game-dialog-dark .p-dialog-header .p-dialog-title) {
    font-size: 1.25rem !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: -0.025em !important;
}
:deep(.game-dialog-dark .p-dialog-header-icons .p-dialog-header-icon) {
    color: rgba(255, 255, 255, 0.4) !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border-radius: 50% !important;
    width: 2rem !important;
    height: 2rem !important;
    transition: all 0.2s !important;
}
:deep(.game-dialog-dark .p-dialog-content) {
    background: transparent !important;
    padding: 0 2rem 1.5rem 2rem !important;
    color: white !important;
}
:deep(.game-dialog-dark .p-dialog-footer) {
    background: transparent !important;
    padding: 0 2rem 2rem 2rem !important;
    border: none !important;
}

/* Dialog overrides for adventure cards - Light Theme */
:deep(.game-dialog-light.p-dialog-mask) {
    background-color: rgba(28, 25, 23, 0.45) !important;
    backdrop-filter: blur(8px) !important;
}
:deep(.game-dialog-light.p-dialog) {
    background: #ffffff !important;
    border: 1px solid #e2dfd5 !important;
    border-radius: 2.5rem !important;
    box-shadow: 0 30px 60px -15px rgba(28, 25, 23, 0.15) !important;
    overflow: hidden;
}
:deep(.game-dialog-light .p-dialog-header) {
    background: transparent !important;
    padding: 2rem 2rem 1rem 2rem !important;
    color: #1c1917 !important;
    border: none !important;
}
:deep(.game-dialog-light .p-dialog-header .p-dialog-title) {
    font-size: 1.25rem !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: -0.025em !important;
}
:deep(.game-dialog-light .p-dialog-header-icons .p-dialog-header-icon) {
    color: rgba(28, 25, 23, 0.4) !important;
    background: rgba(28, 25, 23, 0.05) !important;
    border-radius: 50% !important;
    width: 2rem !important;
    height: 2rem !important;
    transition: all 0.2s !important;
}
:deep(.game-dialog-light .p-dialog-content) {
    background: transparent !important;
    padding: 0 2rem 1.5rem 2rem !important;
    color: #1c1917 !important;
}
:deep(.game-dialog-light .p-dialog-footer) {
    background: transparent !important;
    padding: 0 2rem 2rem 2rem !important;
    border: none !important;
}

/* 3D Depth shadows */
.register-card {
    transform-style: preserve-3d;
}
</style>
