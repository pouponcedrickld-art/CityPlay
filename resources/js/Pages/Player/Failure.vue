<template>
    <Head title="Échec de mission" />

    <div class="cave-result-screen">
        <div class="cave-leaves" aria-hidden="true">
            <span v-for="n in 6" :key="n" class="cave-leaf" />
        </div>

        <div class="cave-result-card cave-result-card--shake relative z-10">
            <div class="cave-result-icon cave-result-icon--fail">
                <i class="pi pi-times" />
            </div>
            <h1 class="cave-result-title">Mission compromise</h1>
            <p class="cave-result-sub" style="color:var(--cave-btn-survival)">Code erroné</p>
            <p class="cave-result-message">
                "{{ partie.environnement?.message_mauvaise_reponse || 'Ce n\'est pas la bonne réponse. Reprenez vos esprits, l\'indice est peut-être juste sous vos yeux.' }}"
            </p>
            <div class="cave-btn-stack">
                <button type="button" class="cave-btn cave-btn--danger" @click="tryAgain">
                    <i class="cave-btn__icon pi pi-refresh" />
                    <span class="cave-btn__label">Réessayer</span>
                </button>
                <button type="button" class="cave-btn cave-btn--ghost" @click="skip">
                    <span class="cave-btn__label">Passer l'énigme</span>
                </button>
            </div>
            <p class="cave-hint mt-4">Chaque erreur vous rapproche du but</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';

const props = defineProps({
    partie: Object,
});

const tryAgain = () => {
    router.get(route('progression.enigme', props.partie.id));
};

const skip = () => {
    router.post(route('progression.next', props.partie.id));
};

onMounted(() => {
    gsap.set('.gsap-fail-card', { y: -50, opacity: 0, rotationZ: 5 });

    const tl = gsap.timeline();
    tl.to('.gsap-fail-card', { y: 0, opacity: 1, rotationZ: 0, duration: 0.6, ease: 'bounce.out' })
      .to('.gsap-fail-card', { 
          x: [-5, 5, -5, 5, 0], 
          duration: 0.4, 
          ease: 'power1.inOut' 
      }, '+=0.2');
});
</script>
