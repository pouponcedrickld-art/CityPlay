<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CaveGameLayout from '@/Layouts/CaveGameLayout.vue';
import GameMap from '@/Components/Game/GameMap.vue';
import { onMounted, ref } from 'vue';

const props = defineProps({
    partie: Object,
    lieu: Object,
    progression: Object,
});

const playerPos = ref({ lat: null, lng: null });

onMounted(() => {
    if ("geolocation" in navigator) {
        navigator.geolocation.watchPosition((position) => {
            playerPos.value = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
        });
    }
});
</script>

<template>
    <CaveGameLayout>
        <Head title="Carte de mission" />

        <div class="flex flex-col min-h-screen">
            <header class="cave-hud">
                <div class="flex items-center gap-3">
                    <Link :href="route('progression.enigme', partie.id)" class="cave-hud__btn">
                        <i class="pi pi-arrow-left" />
                    </Link>
                    <div>
                        <p class="cave-hud__mission">Carte</p>
                        <p class="cave-hud__title truncate max-w-[150px]">{{ lieu?.nom || 'Objectif' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="cave-hud__score">
                        <i class="pi pi-star-fill" />
                        <span>{{ progression?.score || 0 }}</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 relative">
                <GameMap
                    v-if="lieu"
                    :player-lat="playerPos.lat"
                    :player-lng="playerPos.lng"
                    :target-lat="lieu.latitude"
                    :target-lng="lieu.longitude"
                    :target-radius="lieu.rayon_validation || 30"
                    class="absolute inset-0"
                />
                
                <div class="absolute bottom-8 left-0 right-0 px-6 z-10">
                    <div class="cave-tablet p-4 bg-white/90 backdrop-blur-sm">
                        <h3 class="cave-panel__title mb-1" style="font-size: 1rem">{{ lieu?.nom }}</h3>
                        <p class="text-xs text-[#5C3317]/70 italic mb-4">{{ lieu?.adresse }}</p>
                        <Link :href="route('progression.enigme', partie.id)" class="cave-btn w-full">
                            <i class="cave-btn__icon pi pi-question-circle" />
                            <span class="cave-btn__label">Voir l'énigme</span>
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </CaveGameLayout>
</template>

<style scoped>
:deep(.cave-game-hub) {
    padding-bottom: 0 !important;
}
</style>
