<template>
    <CaveGameLayout>
        <Head title="Échec de mission" />

        <div class="cave-result-screen min-h-screen">
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
    </CaveGameLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';

const props = defineProps({
    partie: Object,
});

const tryAgain = () => {
    router.get(route('progression.enigme', props.partie.id));
};

const skip = () => {
    router.post(route('progression.next', props.partie.id));
};
</script>
