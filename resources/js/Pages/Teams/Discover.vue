<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

const props = defineProps({
    teams: Array,
});

const form = useForm({});

const joinTeam = (teamId) => {
    form.post(route('teams.join', teamId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <CavePlayLayout wide>
        <Head title="Découvrir des équipes" />

        <h2 class="cave-section-title">Découvrir des équipes</h2>

        <div v-if="teams.length" class="space-y-4 px-4">
            <div v-for="team in teams" :key="team.id" class="cave-tablet">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg text-[#4A3525]">{{ team.nom }}</h3>
                        <p class="text-sm text-[#4A3525]/70">Chef : {{ team.createur.name }}</p>
                    </div>
                    <div class="cave-score-value" style="font-size: 0.9rem;">
                        {{ team.users_count }} / {{ team.max_joueurs }} 👥
                    </div>
                </div>
                
                <p v-if="team.description" class="text-sm text-[#4A3525]/80 mb-4 italic">
                    "{{ team.description }}"
                </p>

                <button 
                    @click="joinTeam(team.id)" 
                    class="cave-btn w-full"
                    :disabled="form.processing"
                >
                    <i class="cave-btn__icon pi pi-user-plus" />
                    <span class="cave-btn__label">Demander à rejoindre</span>
                </button>
            </div>
        </div>

        <div v-else class="cave-empty">
            <i class="pi pi-search" />
            <p class="font-bold uppercase text-sm">Aucune équipe publique trouvée</p>
            <p class="text-xs opacity-60">Créez votre propre équipe pour commencer !</p>
        </div>

        <div class="px-4 mt-6">
            <Link :href="route('dashboard')" class="cave-btn cave-btn--survival w-full" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-arrow-left" />
                <span class="cave-btn__label">Retour au camp</span>
            </Link>
        </div>
    </CavePlayLayout>
</template>
