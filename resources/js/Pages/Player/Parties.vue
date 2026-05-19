<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

defineProps({ parties: Array });

const statutLabel = (s) => ({
    en_attente: 'En attente',
    en_cours: 'En mission',
    terminee: 'Terminée',
    pause: 'En pause',
}[s] || s);
</script>

<template>
    <CavePlayLayout wide>
        <Head title="Mes parties" />

        <h2 class="cave-section-title">Mes parties</h2>

        <div class="cave-btn-stack mb-6">
            <Link :href="route('parties.web.create')" class="cave-btn" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-plus" />
                <span class="cave-btn__label">Créer une partie</span>
            </Link>
        </div>

        <div v-if="!parties?.length" class="cave-empty">
            <i class="pi pi-inbox" />
            <p class="font-bold uppercase text-sm">Aucune partie</p>
            <p class="cave-hint">Lancez votre première aventure !</p>
        </div>

        <div v-else class="cave-levels-grid cave-levels-grid--multi">
            <div v-for="partie in parties" :key="partie.id" class="cave-mission-card">
                <span class="cave-mission-card__status cave-mission-card__status--active">
                    {{ statutLabel(partie.statut) }}
                </span>
                <h3 class="cave-mission-card__title">Partie #{{ partie.id }}</h3>
                <p class="text-xs font-bold uppercase opacity-60 mb-3">
                    {{ partie.mode }} · {{ partie.environnement?.nom || 'Parcours' }}
                </p>
                <Link :href="route('parties.web.show', partie.id)" class="cave-btn cave-btn--sm" style="text-decoration:none">
                    <i class="cave-btn__icon pi pi-arrow-right" />
                    <span class="cave-btn__label">Continuer</span>
                </Link>
            </div>
        </div>
    </CavePlayLayout>
</template>
