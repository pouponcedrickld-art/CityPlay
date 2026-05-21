<template>
    <CavePlayLayout wide>
        <Head title="Tableau de Bord" />

        <!-- Vies / score joueur -->
        <div class="cave-lives-bar">
            <div class="flex flex-col items-center">
                <span class="cave-lives-label">Missions</span>
                <span class="cave-score-value">
                    {{ parties.length }} <span class="star">★</span>
                </span>
            </div>
            <div class="w-px h-8 bg-white/20 mx-2"></div>
            <div class="flex flex-col items-center">
                <span class="cave-lives-label">Score Global</span>
                <span class="cave-score-value">
                    {{ $page.props.auth.user.total_score || 0 }} <span class="star">pts</span>
                </span>
            </div>
        </div>

        <h2 class="cave-section-title">Camp de base</h2>
        <p class="text-center text-sm font-bold mb-4" style="color: var(--cave-border-dark); opacity: 0.75">
            Bonjour, <strong>{{ $page.props.auth.user.name }}</strong> !
        </p>

        <!-- Boutons principaux (stack pierre) -->
        <div class="cave-btn-stack mb-6">
            <Link :href="route('parties.web.create')" class="cave-btn" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-play" />
                <span class="cave-btn__label">Nouvelle aventure</span>
            </Link>
            <Link
                :href="route('parties.web.create') + '?tab=join'"
                class="cave-btn"
                style="text-decoration:none"
            >
                <i class="cave-btn__icon pi pi-key" />
                <div class="cave-btn__content">
                    <span class="cave-btn__label">Rejoindre une équipe</span>
                    <span class="cave-btn__sub">Entrez un code d'invitation</span>
                </div>
            </Link>
        </div>

        <!-- Équipes créées -->
        <section v-if="equipesCreees.length" class="mb-6">
            <h3 class="cave-section-title" style="font-size:0.85rem">Mes équipes</h3>
            <div class="cave-levels-grid cave-levels-grid--multi">
                <div
                    v-for="partie in equipesCreees"
                    :key="'team-' + partie.id"
                    class="cave-mission-card"
                    style="animation-delay: 0.1s"
                >
                    <span
                        class="cave-mission-card__status"
                        :class="partie.statut === 'en_attente' ? 'cave-mission-card__status--waiting' : 'cave-mission-card__status--active'"
                    >
                        {{ getStatutLabel(partie.statut) }}
                    </span>
                    <h4 class="cave-mission-card__title">{{ partie.environnement?.nom }}</h4>
                    <p class="text-[10px] font-bold uppercase mb-3" style="color: var(--cave-border-dark); opacity: 0.6">
                        {{ partie.nb_membres || 1 }} / {{ partie.parametres?.nb_joueurs || 10 }} joueurs
                    </p>

                    <div class="cave-invite-block">
                        <p class="text-[9px] font-bold uppercase" style="color: var(--cave-border-dark)">
                            <i class="pi pi-link" /> Lien
                        </p>
                        <div class="cave-invite-row">
                            <span class="text-[9px] truncate flex-1 opacity-70">{{ partie.lien_invitation }}</span>
                            <button
                                type="button"
                                class="cave-copy-btn"
                                @click="copyText(partie.lien_invitation, partie.id + '-link')"
                            >
                                <i :class="copiedId === partie.id + '-link' ? 'pi pi-check' : 'pi pi-copy'" />
                            </button>
                        </div>
                        <p class="text-[9px] font-bold uppercase mt-2" style="color: var(--cave-border-dark)">
                            <i class="pi pi-key" /> Code
                        </p>
                        <div class="cave-invite-row">
                            <span class="cave-invite-code">{{ partie.code_liaison }}</span>
                            <button
                                type="button"
                                class="cave-copy-btn"
                                @click="copyText(partie.code_liaison, partie.id + '-code')"
                            >
                                <i :class="copiedId === partie.id + '-code' ? 'pi pi-check' : 'pi pi-copy'" />
                            </button>
                        </div>
                    </div>

                    <Link
                        v-if="partie.statut === 'en_attente'"
                        :href="route('parties.team-setup', partie.id)"
                        class="cave-btn mt-3"
                        style="text-decoration:none; min-height:48px; padding:10px"
                    >
                        <i class="cave-btn__icon pi pi-cog" />
                        <span class="cave-btn__label">Gérer l'équipe</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Missions en cours -->
        <section class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="cave-section-title" style="font-size:0.85rem; margin-bottom: 0">Missions actives</h3>
                <Link :href="route('player.classement')" class="text-[10px] font-black uppercase text-orange-600 hover:underline">Voir tout le classement</Link>
            </div>

            <div v-if="parties.length" class="cave-levels-grid cave-levels-grid--multi">
                <Link
                    v-for="partie in parties"
                    :key="partie.id"
                    :href="route('progression.enigme', partie.id)"
                    class="cave-mission-card"
                >
                    <span class="cave-mission-card__status cave-mission-card__status--active">
                        {{ getStatutLabel(partie.statut) }}
                    </span>
                    <h4 class="cave-mission-card__title">{{ partie.environnement?.nom }}</h4>
                    <div class="flex justify-between text-[9px] font-bold uppercase mb-2" style="color: var(--cave-border-dark); opacity: 0.7">
                        <span>{{ partie.progression?.score || 0 }} pts</span>
                        <span>{{ progressionPct(partie) }}%</span>
                    </div>
                    <div class="cave-progress-track">
                        <div
                            class="cave-progress-fill"
                            :style="{ width: progressionPct(partie) + '%' }"
                        ></div>
                    </div>
                </Link>
            </div>

            <div v-else class="cave-empty">
                <i class="pi pi-map-marker" />
                <p class="font-bold uppercase text-sm mb-1">Aucune mission</p>
                <p class="cave-hint">Lancez une aventure pour commencer</p>
            </div>
        </section>

        <!-- Classement Rapide -->
        <section v-if="topPlayers && topPlayers.length" class="mb-6">
            <h3 class="cave-section-title" style="font-size:0.85rem">Classement Mondial</h3>
            <div class="cave-panel p-4 flex flex-col gap-3">
                <div v-for="(player, idx) in topPlayers" :key="player.id" class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black"
                            :class="idx === 0 ? 'bg-yellow-400' : (idx === 1 ? 'bg-slate-300' : 'bg-orange-400')">
                            {{ idx + 1 }}
                        </span>
                        <span class="text-xs font-bold uppercase">{{ player.name }}</span>
                    </div>
                    <span class="text-xs font-black">{{ player.total_score }} <span class="opacity-50 text-[9px]">pts</span></span>
                </div>
                <Link :href="route('player.classement')" class="cave-btn mt-2" style="min-height:36px; font-size: 0.7rem; text-decoration: none">
                    <i class="cave-btn__icon pi pi-trophy" style="font-size: 0.8rem" />
                    <span class="cave-btn__label">Classement complet</span>
                </Link>
            </div>
        </section>

        <!-- Parcours à proximité -->
        <section v-if="environnements.length">
            <h3 class="cave-section-title" style="font-size:0.85rem">
                {{ geolocalise ? 'Près de vous' : 'Explorer' }}
            </h3>
            <p v-if="isLocating" class="cave-hint mb-3">
                <i class="pi pi-spin pi-spinner" /> Localisation...
            </p>
            <div class="cave-levels-grid cave-levels-grid--multi">
                <article
                    v-for="env in environnements"
                    :key="env.id"
                    class="cave-level-card"
                    @click="router.get(route('parties.web.create'))"
                >
                    <div class="cave-level-card__img-wrap" style="height:90px">
                        <img
                            :src="env.image_url || 'https://images.unsplash.com/photo-1519307212971-dd9561667ffb?auto=format&fit=crop&q=80&w=400'"
                            :alt="env.nom"
                            class="cave-level-card__img"
                        />
                        <div class="cave-level-card__overlay" />
                        <span v-if="env.distance_km != null" class="cave-level-card__badge">
                            {{ env.distance_km }} km
                        </span>
                        <h3 class="cave-level-card__title" style="font-size:0.85rem">{{ env.nom }}</h3>
                    </div>
                </article>
            </div>
        </section>
    </CavePlayLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

const props = defineProps({
    parties: { type: Array, default: () => [] },
    environnements: { type: Array, default: () => [] },
    geolocalise: { type: Boolean, default: false },
    topPlayers: { type: Array, default: () => [] },
});

const isLocating = ref(!props.geolocalise);
const copiedId = ref(null);

const equipesCreees = computed(() =>
    props.parties.filter(
        (p) => p.mode === 'team' && p.code_liaison && p.statut !== 'terminee'
    )
);

const copyText = async (text, id) => {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }
    copiedId.value = id;
    setTimeout(() => { copiedId.value = null; }, 2500);
};

const progressionPct = (partie) => {
    const done = partie.progression?.lieux_decouverts?.length || 0;
    const rest = partie.progression?.lieux_restants?.length || 0;
    const total = done + rest;
    return total ? Math.round((done / total) * 100) : 0;
};

const requestNearbyEnvironments = () => {
    if (!navigator.geolocation) {
        isLocating.value = false;
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (position) => {
            isLocating.value = false;
            router.get(route('dashboard'), {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['environnements', 'geolocalise'],
            });
        },
        () => { isLocating.value = false; },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
    );
};

onMounted(() => {
    if (!props.geolocalise) requestNearbyEnvironments();
});

const getStatutLabel = (statut) => {
    const labels = {
        en_attente: 'En attente',
        en_cours: 'En mission',
        terminee: 'Terminée',
        suspendue: 'En pause',
        pause: 'En pause',
    };
    return labels[statut] || statut;
};
</script>
