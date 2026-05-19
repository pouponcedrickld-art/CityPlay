<template>
    <CavePlayLayout wide>
        <Head title="Débriefing de mission" />

        <h2 class="cave-section-title">Mission accomplie</h2>
        <p class="cave-hint text-center mb-6">Débriefing d'exploration</p>

        <div class="cave-summary-layout">
            <div class="cave-summary-layout__hero cave-panel cave-panel__body text-center">
                <span class="cave-config-label">Score de performance</span>
                <div class="cave-score-hero">{{ progression?.score || 0 }}</div>
                <p class="cave-hint"><i class="pi pi-bolt" /> Points CityPlay</p>
            </div>

            <div v-if="partie.environnement?.message_fin" class="cave-panel cave-panel__body text-center">
                <span class="cave-badge cave-badge--gold mb-3">Message de fin</span>
                <p class="cave-panel__text" style="margin:0">"{{ partie.environnement.message_fin }}"</p>
            </div>

            <div class="cave-stats-grid">
                <div class="cave-stat-box">
                    <span class="cave-stat-box__label">Énigmes résolues</span>
                    <div class="cave-stat-box__value">{{ progression?.lieux_decouverts?.length || 0 }}</div>
                </div>
                <div class="cave-stat-box">
                    <span class="cave-stat-box__label">Distance</span>
                    <div class="cave-stat-box__value">
                        {{ ((distanceTotale || 3200) / 1000).toFixed(1) }}<span style="font-size:0.5em">km</span>
                    </div>
                </div>
            </div>

            <div v-if="enigmesNonResolues?.length" class="cave-panel cave-panel__body cave-summary-layout__hero">
                <h3 class="cave-section-title" style="font-size:0.8rem">Énigmes non résolues</h3>
                <div class="space-y-3 mt-4">
                    <div
                        v-for="enigme in enigmesNonResolues"
                        :key="enigme.id"
                        class="flex items-center gap-3 p-3 rounded-xl border-2 border-[var(--cave-border-dark)] bg-[#FFF8EE]"
                    >
                        <span class="w-2 h-2 rounded-full bg-red-600" />
                        <span class="text-sm font-bold uppercase">{{ enigme.titre }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="cave-btn-stack mt-8">
            <Link :href="route('dashboard')" class="cave-btn" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-home" />
                <span class="cave-btn__label">Retour au camp</span>
            </Link>
            <button type="button" class="cave-btn cave-btn--survival" @click="showLogoutDialog = true">
                <i class="cave-btn__icon pi pi-power-off" />
                <span class="cave-btn__label">Terminer la session</span>
            </button>
        </div>

        <Dialog
            v-model:visible="showLogoutDialog"
            modal
            header="Déconnexion"
            :style="{ width: '90vw', maxWidth: '420px' }"
            class="cave-game-dialog"
        >
            <p class="mb-4">Archiver votre profil ou supprimer vos données ?</p>
            <div class="cave-btn-stack">
                <button type="button" class="cave-btn" @click="handleLogout">Archiver le profil</button>
                <button type="button" class="cave-btn cave-btn--danger" @click="showDeleteConfirm = true; showLogoutDialog = false">
                    Supprimer le profil
                </button>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="showDeleteConfirm"
            modal
            header="Confirmation"
            :style="{ width: '90vw', maxWidth: '420px' }"
            class="cave-game-dialog"
        >
            <p class="cave-panel__text" style="margin-bottom:16px">Action irréversible. Continuer ?</p>
            <template #footer>
                <div class="flex gap-3">
                    <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="showDeleteConfirm = false">Annuler</button>
                    <button type="button" class="cave-btn cave-btn--danger flex-1" @click="handleDeleteProfile">Confirmer</button>
                </div>
            </template>
        </Dialog>
    </CavePlayLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

defineProps({
    partie: Object,
    progression: Object,
    enigmesNonResolues: Array,
    distanceTotale: Number,
});

const showLogoutDialog = ref(false);
const showDeleteConfirm = ref(false);
const logoutForm = useForm({});
const deleteProfileForm = useForm({});

const handleLogout = () => logoutForm.post(route('logout'));
const handleDeleteProfile = () => deleteProfileForm.delete(route('profile.destroy'));
</script>
