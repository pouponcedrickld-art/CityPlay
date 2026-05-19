<script setup>
import { Link } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { ref, onMounted, onUnmounted, provide } from 'vue';
import { gsap } from 'gsap';
import logoDark from './logo_dark.jpg';
import logoWhite from './logo_whrite.jpg';

const isDark = ref(true); // Par défaut sombre pour l'immersion Gaming
const embersCanvas = ref(null);

let animationFrameId = null;
let particles = [];
const mouse = { x: -1000, y: -1000, active: false };

onMounted(() => {
    // 1. Charger le thème sauvegardé
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        isDark.value = false;
        document.documentElement.classList.add('light-theme');
    } else {
        isDark.value = true;
        document.documentElement.classList.remove('light-theme');
    }

    // 2. Initialiser le Canvas de Braises Interactif (GSAP Powered)
    initCanvas();

    // 3. Animation d'entrée globale du Layout
    gsap.set('.vignette-overlay', { opacity: 0 });
    gsap.set('.global-layout-container', { scale: 0.96, opacity: 0 });

    const tl = gsap.timeline({ defaults: { ease: 'power4.out', duration: 1.5 } });
    tl.to('.vignette-overlay', { opacity: 1, duration: 2.0 })
      .to('.global-layout-container', { scale: 1, opacity: 1, duration: 1.8 }, '-=1.5');
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    window.removeEventListener('resize', resizeCanvas);
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseleave', onMouseLeave);
});

// Initialiser le système de particules/braises
const initCanvas = () => {
    const canvas = embersCanvas.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseleave', onMouseLeave);

    // Créer les particules (braises du Bénin Quest)
    const particleCount = Math.min(60, Math.floor((canvas.width * canvas.height) / 25000));
    particles = [];
    for (let i = 0; i < particleCount; i++) {
        particles.push(createParticle(canvas));
    }

    // Boucle d'animation
    const tick = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(p => {
            // Mouvement vertical ascendant (braises)
            p.y -= p.speedY;
            p.x += Math.sin(p.angle) * 0.4;
            p.angle += p.angleSpeed;

            // Réaction à la souris (Répulsion fluide)
            if (mouse.active) {
                const dx = p.x - mouse.x;
                const dy = p.y - mouse.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 150) {
                    const force = (150 - dist) / 150;
                    const angle = Math.atan2(dy, dx);
                    p.x += Math.cos(angle) * force * 4;
                    p.y += Math.sin(angle) * force * 4;
                }
            }

            // Réinitialisation si hors écran (haut ou côtés)
            if (p.y < -10 || p.x < -10 || p.x > canvas.width + 10) {
                Object.assign(p, createParticle(canvas, true));
            }

            // Dessiner la particule (braise dorée/verte selon le thème)
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            
            // Couleur de braise dynamique selon le thème
            if (isDark.value) {
                // Braises dorées et orangées flamboyantes
                ctx.fillStyle = `rgba(${p.colorR}, ${p.colorG}, 0, ${p.alpha})`;
                ctx.shadowColor = `rgba(${p.colorR}, ${p.colorG}, 0, 0.6)`;
            } else {
                // Braises énergétiques vert forêt et émeraude
                ctx.fillStyle = `rgba(0, ${p.colorG}, ${p.colorB}, ${p.alpha})`;
                ctx.shadowColor = `rgba(0, ${p.colorG}, ${p.colorB}, 0.5)`;
            }
            
            ctx.shadowBlur = p.size * 2.5;
            ctx.fill();
        });

        animationFrameId = requestAnimationFrame(tick);
    };

    tick();
};

const createParticle = (canvas, fromBottom = false) => {
    return {
        x: Math.random() * canvas.width,
        y: fromBottom ? canvas.height + 20 : Math.random() * canvas.height,
        size: Math.random() * 2.5 + 1.2,
        speedY: Math.random() * 0.7 + 0.3,
        angle: Math.random() * Math.PI * 2,
        angleSpeed: Math.random() * 0.02 - 0.01,
        // Teintes dorées/orangées pour le mode sombre
        colorR: Math.floor(Math.random() * 55) + 200, // 200 - 255
        colorG: Math.floor(Math.random() * 100) + 100, // 100 - 200
        // Teintes vertes pour le mode clair
        colorB: Math.floor(Math.random() * 80) + 80,
        alpha: Math.random() * 0.5 + 0.3
    };
};

const resizeCanvas = () => {
    const canvas = embersCanvas.value;
    if (!canvas) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
};

const onMouseMove = (e) => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.active = true;
};

const onMouseLeave = () => {
    mouse.active = false;
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    if (isDark.value) {
        document.documentElement.classList.remove('light-theme');
    } else {
        document.documentElement.classList.add('light-theme');
    }
    
    // Smooth transition flash effect on change
    gsap.fromTo('.vignette-overlay', 
        { opacity: 0.3 }, 
        { opacity: 1, duration: 1.2, ease: 'power2.out' }
    );
};

// Injection du thème pour que les formulaires s'adaptent instantanément
provide('isDark', isDark);
</script>

<template>
    <div 
        :class="[
            isDark ? 'dark bg-[#0a0907] text-white' : 'light bg-[#faf9f5] text-[#1c1917]',
            'relative min-h-screen w-full font-sans flex items-center justify-center p-4 sm:p-6 md:p-8 overflow-hidden transition-colors duration-700 select-none'
        ]"
    >
        <Toast />

        <!-- CANVAS DE PARTICULES INTERACTIF -->
        <canvas ref="embersCanvas" class="absolute inset-0 pointer-events-none z-10"></canvas>

        <!-- CINEMATIC VIGNETTE & BACKDROP OVERLAY -->
        <div 
            :class="[
                isDark 
                    ? 'bg-gradient-to-tr from-black via-[#0d0c0a]/95 to-black/60 backdrop-blur-[1px]' 
                    : 'bg-gradient-to-tr from-[#f3f0e8]/90 via-[#f9f8f5]/85 to-transparent backdrop-blur-[1.5px]',
                'vignette-overlay absolute inset-0 z-0 transition-all duration-700'
            ]"
        ></div>

        <!-- FLOATING THEME SWITCHER -->
        <button 
            @click="toggleTheme" 
            type="button"
            :class="[
                isDark 
                    ? 'bg-white/5 border-white/10 hover:bg-[#FF9500] hover:text-black text-[#FF9500] shadow-[0_0_20px_rgba(255,149,0,0.12)]' 
                    : 'bg-black/5 border-black/10 hover:bg-[#059669] hover:text-white text-[#059669] shadow-[0_0_20px_rgba(0,135,81,0.08)]',
                'absolute top-6 right-6 z-50 p-3.5 rounded-2xl border backdrop-blur-md transition-all duration-500 active:scale-90 flex items-center justify-center cursor-pointer'
            ]"
            title="Changer de thème"
        >
            <i :class="isDark ? 'pi pi-sun text-base animate-spin-slow' : 'pi pi-moon text-base'" />
        </button>

        <!-- CONTENEUR GLOBAL CENTRÉ -->
        <div class="global-layout-container relative z-20 w-full max-w-6xl mx-auto my-auto flex items-center justify-center">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 12s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
