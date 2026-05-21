<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Slider from 'primevue/slider';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

const props = defineProps({
    environnements: Array,
    tab: { type: String, default: 'create' },
});

const page = usePage();
const activeTab = ref(props.tab === 'join' ? 'join' : 'create');

watch(() => props.tab, (tab) => {
    activeTab.value = tab === 'join' ? 'join' : 'create';
});

const joinForm = useForm({ code: '' });

const showConfig = ref(false);
const selectedEnv = ref(null);

const form = useForm({
    environnement_id: null,
    mode: 'solo',
    duree: 60,
    locomotion: 'pied',
    difficulte: 2,
    nb_joueurs: 1,
});

const modes = [
    { label: 'Solo', value: 'solo', icon: 'pi pi-user' },
    { label: 'Équipe', value: 'team', icon: 'pi pi-users' },
];

const difficulties = [
    { label: 'Facile', value: 1 },
    { label: 'Normal', value: 2 },
    { label: 'Expert', value: 3 },
];

const locomotions = [
    { label: 'À pied', value: 'pied', icon: 'pi pi-directions', factor: 1 },
    { label: 'Vélo', value: 'velo', icon: 'pi pi-circle', factor: 0.4 },
    { label: 'Voiture', value: 'voiture', icon: 'pi pi-car', factor: 0.2 },
];

const openConfig = (env) => {
    selectedEnv.value = env;
    form.environnement_id = env.id;
    form.difficulte = env.difficulte || 2;
    showConfig.value = true;
};

const estimatedTime = computed(() => {
    if (!selectedEnv.value) return 60;
    const baseTime = selectedEnv.value.duree_estimee || 60;
    const locomotion = locomotions.find((l) => l.value === form.locomotion);
    return Math.round(baseTime * (locomotion?.factor || 1));
});

watch(() => form.locomotion, () => {
    form.duree = estimatedTime.value;
});

const submit = () => {
    form.post(route('parties.web.store'), {
        onSuccess: () => { showConfig.value = false; },
    });
};

const submitJoin = () => {
    joinForm.post(route('parties.rejoindre.form'));
};

const setTab = (tab) => {
    activeTab.value = tab;
};
</script>

<template>
    <CavePlayLayout wide>

        <Head title="Jouer" />

        <h2 class="cave-section-title">Jouer</h2>

        <!-- Tabs pierre -->
        <div class="cave-tabs">
            <button type="button" class="cave-tab" :class="{ 'cave-tab--active': activeTab === 'create' }"
                @click="setTab('create')">
                <i class="pi pi-plus-circle" /> Créer
            </button>
            <button type="button" class="cave-tab" :class="{ 'cave-tab--active': activeTab === 'join' }"
                @click="setTab('join')">
                <i class="pi pi-key" /> Rejoindre
            </button>
        </div>

        <!-- Flash -->
        <div v-if="page.props.flash?.error" class="cave-flash cave-flash--error">
            <i class="pi pi-exclamation-circle mr-2" />{{ page.props.flash.error }}
        </div>
        <div v-if="page.props.flash?.success" class="cave-flash cave-flash--success">
            <i class="pi pi-check-circle mr-2" />{{ page.props.flash.success }}
        </div>
        <div v-if="form.errors.environnement_id" class="cave-flash cave-flash--error">
            Veuillez sélectionner un parcours valide.
        </div>

        <!-- REJOINDRE : code -->
        <section v-if="activeTab === 'join'">
            <div class="cave-tablet">
                <div class="text-center mb-5">
                    <div class="cave-logo-icon" style="width:56px;height:56px;font-size:1.5rem;margin:0 auto 10px">🔑
                    </div>
                    <p class="cave-hint" style="margin-top:0;font-size:0.8rem;opacity:0.85">
                        Entrez le code reçu de votre organisateur
                    </p>
                </div>
                <label class="cave-input-label">Code d'invitation</label>
                <input v-model="joinForm.code" type="text" class="cave-input" placeholder="ABC123" maxlength="12"
                    autocomplete="off" />
                <p v-if="joinForm.errors.code" class="cave-flash cave-flash--error mt-3" style="margin-bottom:0">
                    {{ joinForm.errors.code }}
                </p>
                <p class="cave-hint">Compte existant ? Utilisez le code, pas le lien.</p>
                <button type="button" class="cave-btn mt-5" :disabled="joinForm.processing" @click="submitJoin">
                    <i class="cave-btn__icon pi pi-sign-in" />
                    <span class="cave-btn__label">Rejoindre l'équipe</span>
                </button>
            </div>

            <Link :href="route('dashboard')" class="cave-btn cave-btn--survival mt-4" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-arrow-left" />
                <span class="cave-btn__label">Retour au camp</span>
            </Link>
        </section>

        <!-- CRÉER : grille de parcours -->
        <section v-else>
            <div class="cave-lives-bar">
                <span class="cave-lives-label">Parcours</span>
                <span class="cave-score-value">{{ environnements.length }} <span class="star">★</span></span>
            </div>

            <div v-if="environnements.length" class="cave-levels-grid cave-levels-grid--multi">
                <article v-for="env in environnements" :key="env.id" class="cave-level-card" @click="openConfig(env)">
                    <div class="cave-level-card__img-wrap">
                        <img :src="env.image_url || 'https://images.unsplash.com/photo-1519307212971-dd9561667ffb?auto=format&fit=crop&q=80&w=800'"
                            :alt="env.nom" class="cave-level-card__img" />
                        <div class="cave-level-card__overlay" />
                        <span class="cave-level-card__badge">{{ env.lieux_count || 0 }} étapes</span>
                        <h3 class="cave-level-card__title">{{ env.nom }}</h3>
                    </div>
                    <div class="cave-level-card__body">
                        <div class="cave-level-card__meta">
                            <span>{{ env.duree_estimee || 60 }} min</span>
                            <div class="cave-level-card__stars">
                                <span v-for="i in 3" :key="i" :class="{ filled: i <= (env.difficulte || 2) }" />
                            </div>
                        </div>
                        <button type="button" class="cave-btn" style="animation-delay:0s">
                            <i class="cave-btn__icon pi pi-play" />
                            <span class="cave-btn__label">Configurer</span>
                        </button>
                    </div>
                </article>
            </div>

            <div v-else class="cave-empty">
                <i class="pi pi-map" />
                <p class="font-bold uppercase text-sm">Aucun parcours disponible</p>
            </div>
        </section>

        <!-- Modal configuration -->
        <Dialog v-model:visible="showConfig" modal :unstyled="true" :header="'Niveau : ' + (selectedEnv?.nom || '')"
            :style="{ width: '90vw', maxWidth: '420px' }" :pt="{
                root: { class: 'cave-dialog-root cave-play-hub' }, /* Injection des variables css si nécessaire */
                mask: { class: 'cave-dialog-mask' },
                header: { class: 'cave-dialog-header' },
                title: { class: 'cave-dialog-title' },
                content: { class: 'cave-dialog-content' },
                footer: { class: 'cave-dialog-footer' },
                closeButton: { class: 'cave-dialog-close' },
                closeButtonIcon: { class: 'cave-dialog-close-icon' },
            }">
            <div class="space-y-6">
                <div>
                    <span class="cave-config-label">Mode de jeu</span>
                    <div class="cave-tabs">
                        <button v-for="m in modes" :key="m.value" type="button" class="cave-tab"
                            :class="{ 'cave-tab--active': form.mode === m.value }" @click="form.mode = m.value">
                            <i :class="m.icon" class="cave-tab-icon" /> {{ m.label }}
                        </button>
                    </div>
                </div>

                <div v-if="form.mode === 'team'">
                    <div class="flex justify-between items-center mb-2">
                        <span class="cave-config-label" style="margin:0">Joueurs (max 10)</span>
                        <span class="cave-score-value" style="font-size:0.9rem">{{ form.nb_joueurs }}</span>
                    </div>
                    <Slider v-model="form.nb_joueurs" :min="2" :max="10" class="w-full" />
                </div>

                <div>
                    <span class="cave-config-label">Transport</span>
                    <div class="cave-tabs">
                        <button v-for="loc in locomotions" :key="loc.value" type="button" class="cave-tab"
                            :class="{ 'cave-tab--active': form.locomotion === loc.value }"
                            @click="form.locomotion = loc.value">
                            <i :class="loc.icon" class="cave-tab-icon" />
                            {{ loc.label }}
                        </button>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="cave-config-label" style="margin:0">Durée</span>
                        <span class="cave-score-value" style="font-size:1rem">{{ form.duree }} min</span>
                    </div>
                    <Slider v-model="form.duree" :min="15" :max="180" :step="5" class="w-full" />
                </div>

                <div>
                    <span class="cave-config-label">Difficulté</span>
                    <div class="cave-tabs">
                        <button v-for="d in difficulties" :key="d.value" type="button" class="cave-tab"
                            :class="{ 'cave-tab--active': form.difficulte === d.value }"
                            @click="form.difficulte = d.value">
                            {{ d.label }}
                        </button>
                    </div>
                </div>
            </div>
            <template #footer>
                <button type="button" class="cave-btn w-full" :disabled="form.processing" @click="submit">
                    <i class="cave-btn__icon" :class="form.mode === 'solo' ? 'pi pi-play' : 'pi pi-users'" />
                    <span class="cave-btn__label">
                        {{ form.mode === 'solo' ? 'Lancer la mission' : 'Créer l\'équipe' }}
                    </span>
                </button>
            </template>
        </Dialog>
    </CavePlayLayout>
</template>
<style scoped>
/* ── Labels & Icônes internes ────────────────────────────── */
.cave-config-label {
    display: block;
    font-family: var(--cave-font-title);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--cave-border-dark);
    margin-bottom: 8px;
    opacity: 0.9;
}

.cave-tab-icon {
    color: inherit; /* Utilise la couleur dynamique du bouton parent (.cave-tab) */
    font-size: 1rem;
}

/* ── Structure globale du Dialog PrimeVue ────────────────── */
:deep(.cave-dialog-root) {
    display: flex;
    align-items: center;
    justify-content: center;
}

:deep(.cave-dialog-root .p-dialog) {
    border: 4px solid var(--cave-border-dark) !important;
    border-radius: 24px !important;
    overflow: hidden;
    background: #FFF8EE !important;
    box-shadow: 0 12px 0 var(--cave-border-darker), 0 20px 40px var(--cave-shadow-lg) !important;
    
    /* Force la modale à s'adapter uniquement à la taille de son contenu */
    display: flex;
    flex-direction: column;
    height: auto !important; 
    max-height: 90vh !important; 
}

/* ── Header (Haut de la modale) ──────────────────────────── */
:deep(.cave-dialog-root .p-dialog-header) {
    background: linear-gradient(180deg, var(--cave-rock-light), var(--cave-rock)) !important;
    border-bottom: 4px solid var(--cave-border-dark) !important;
    padding: 1.25rem 1.5rem !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

:deep(.cave-dialog-root .p-dialog-title) {
    font-family: var(--cave-font-title) !important;
    font-size: 1.1rem !important;
    color: var(--cave-border-dark) !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Bouton Fermer (X) */
:deep(.cave-dialog-root .p-dialog-header-icon) {
    color: var(--cave-btn-text) !important;
    border: 3px solid var(--cave-border-dark) !important;
    border-radius: 12px !important;
    background: linear-gradient(180deg, var(--cave-btn-survival-top), var(--cave-btn-survival-bottom)) !important;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 0 var(--cave-border-darker) !important;
    cursor: pointer;
}

/* ── Content (Zone centrale blanche) ─────────────────────── */
:deep(.cave-dialog-root .p-dialog-content) {
    background: #FFF8EE !important;
    padding: 1.5rem 1.5rem 0.5rem 1.5rem !important; /* Resserre l'espace avant le footer */
    flex: none !important; /* Empêche le stretch vertical */
}

/* ── Footer (Zone basse marron / Bouton Lancer) ──────────── */
:deep(.cave-dialog-root .p-dialog-footer) {
    background: var(--cave-rock-light) !important; /* Devient marron clair texturé comme la maquette */
    border-top: 4px solid var(--cave-border-dark) !important; /* Ligne de séparation cartoon */
    padding: 1.25rem 1.5rem 1.5rem !important;
}

/* ── Slider Config (Barre verte & Bouton Or) ─────────────── */
:deep(.cave-dialog-root .p-slider) {
    background: var(--cave-border-dark) !important; /* Fond marron de la ligne */
    border: 1px solid var(--cave-border-darker) !important;
    height: 10px;
    border-radius: 99px;
}

:deep(.cave-dialog-root .p-slider-range) {
    background: linear-gradient(90deg, #2ecc71, var(--cave-btn-primary)) !important; /* Jauge verte */
    border-radius: 99px;
}

:deep(.cave-dialog-root .p-slider-handle) {
    border: 3px solid var(--cave-border-dark) !important;
    background: var(--cave-gold) !important; /* Pion jaune/or */
    width: 24px !important;
    height: 24px !important;
    margin-top: -8px !important;
    border-radius: 50% !important;
    box-shadow: 0 3px 0 var(--cave-border-darker) !important;
    cursor: grab;
}
</style>