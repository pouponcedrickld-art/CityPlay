<script setup>
import { Head, router } from '@inertiajs/vue3';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';

const props = defineProps({
    partie: Object,
    lieu: Object,
    progression: Object,
    points_gagnes: { type: Number, default: 0 },
    score_total: { type: Number, default: 0 },
});

const nextStep = () => {
    if (props.progression?.statut === 'terminee' || !props.progression?.enigme_courante_id) {
        router.get(route('progression.summary', props.partie.id));
        return;
    }
    router.get(route('progression.enigme', props.partie.id));
};
</script>

<template>
    <CaveGameLayout>
        <Head title="Victoire !" />

        <div class="cave-result-screen">
            <div class="cave-result-icon cave-result-icon--win relative z-10">
                <i class="pi pi-star-fill" />
            </div>

            <h1 class="cave-result-title relative z-10">Excellent !</h1>
            <p class="cave-result-sub relative z-10">
                +{{ points_gagnes }} pts · Total : {{ score_total }} pts
            </p>

            <article class="cave-discovery-card relative z-10">
                <div v-if="lieu?.photos?.length" class="relative h-56">
                    <img :src="lieu.photos[0].url" class="w-full h-full object-cover" :alt="lieu.nom">
                </div>
                <div v-else class="h-40 flex items-center justify-center bg-[var(--cave-rock)]">
                    <i class="pi pi-map text-4xl opacity-30" />
                </div>
                <div class="p-6">
                    <span class="cave-badge cave-badge--gold mb-2">Lieu découvert</span>
                    <h2 class="cave-panel__title">{{ lieu?.nom || 'Lieu secret' }}</h2>
                    <p class="cave-panel__text" style="margin:0">
                        "{{ partie.environnement?.message_bonne_reponse || lieu?.description || 'Vous avez percé les secrets de cet endroit. La mission continue !' }}"
                    </p>
                </div>
            </article>

            <button type="button" class="cave-btn relative z-10" style="max-width:420px;width:100%" @click="nextStep">
                <i class="cave-btn__icon pi pi-arrow-right" />
                <span class="cave-btn__label">Mission suivante</span>
            </button>
            <p class="cave-hint relative z-10 mt-3">Appuyez pour continuer votre aventure</p>
        </div>
    </CaveGameLayout>
</template>
