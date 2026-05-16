<script setup>
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Carousel from 'primevue/carousel';

const props = defineProps({
    partie: Object,
    lieu: Object
});

const nextStep = () => {
    router.post(route('progression.next', props.partie.id));
};
</script>

<template>
    <Head title="Bonne réponse !" />

    <div class="min-h-screen bg-green-50 flex flex-col">
        <main class="flex-1 p-6 max-w-2xl mx-auto w-full space-y-8 py-12">
            <!-- BRAVO ANIMATION / ICON -->
            <div class="text-center space-y-4">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <i class="pi pi-check-circle text-5xl text-green-500 animate-bounce"></i>
                </div>
                <h1 class="text-3xl font-black text-green-950 uppercase tracking-tight">
                    Excellent !
                </h1>
                <p class="text-green-800 font-medium">
                    Vous avez résolu l'énigme avec brio.
                </p>
            </div>

            <!-- LIEU DECOUVERT -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-xl border border-green-100">
                <!-- PHOTOS DU LIEU -->
                <div v-if="lieu.photos && lieu.photos.length > 0">
                    <Carousel :value="lieu.photos" :numVisible="1" :numScroll="1" circular :autoplayInterval="3000">
                        <template #item="slotProps">
                            <img :src="slotProps.data.url" :alt="slotProps.data.alt_text" class="w-full h-64 object-cover" />
                        </template>
                    </Carousel>
                </div>
                <div v-else class="h-64 bg-gray-200 flex items-center justify-center">
                    <i class="pi pi-image text-gray-400 text-4xl"></i>
                </div>

                <div class="p-8 space-y-4">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-map-marker text-[#FF9500]"></i>
                        <h2 class="text-xl font-black text-orange-950 uppercase tracking-tight">
                            {{ lieu.nom }}
                        </h2>
                    </div>
                    
                    <p class="text-gray-600 leading-relaxed">
                        {{ lieu.description }}
                    </p>
                </div>
            </div>

            <!-- BOUTON SUIVANT -->
            <Button
                @click="nextStep"
                label="Énigme suivante"
                icon="pi pi-arrow-right"
                iconPos="right"
                class="w-full p-5 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-600 border-none font-bold uppercase tracking-widest shadow-lg shadow-green-200"
            />
        </main>
    </div>
</template>

<style scoped>
:deep(.p-carousel-indicators) {
    padding: 1rem;
}
</style>