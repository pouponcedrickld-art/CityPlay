<script setup>
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';

const props = defineProps({
    partie: Object
});

const tryAgain = () => {
    router.get(route('progression.enigme', props.partie.id));
};

const skip = () => {
    if (confirm('Passer cette énigme ? Vous ne marquerez pas de points.')) {
        router.post(route('progression.next', props.partie.id));
    }
};
</script>

<template>
    <Head title="Mauvaise réponse" />

    <div class="min-h-screen bg-red-50 flex flex-col items-center justify-center p-6">
        <div class="max-w-md w-full bg-white p-10 rounded-3xl shadow-xl border border-red-100 text-center space-y-8">
            <!-- ERROR ICON -->
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                <i class="pi pi-times text-5xl text-red-500"></i>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-black text-red-950 uppercase tracking-tight">
                    Dommage !
                </h1>
                <p class="text-gray-600 font-medium">
                    Ce n'est pas la bonne réponse. Ne vous découragez pas !
                </p>
            </div>

            <div class="space-y-4">
                <Button
                    @click="tryAgain"
                    label="Réessayer"
                    icon="pi pi-refresh"
                    class="w-full p-4 rounded-2xl bg-red-500 hover:bg-red-600 border-none font-bold uppercase tracking-widest shadow-lg shadow-red-200"
                />

                <Button
                    @click="skip"
                    label="Passer l'énigme"
                    icon="pi pi-fast-forward"
                    class="w-full p-4 rounded-2xl bg-white text-red-500 border-2 border-red-500 hover:bg-red-50 font-bold uppercase tracking-widest transition-colors"
                />
            </div>

            <p class="text-[10px] uppercase font-black tracking-widest text-red-900/40">
                L'erreur fait partie de l'aventure !
            </p>
        </div>
    </div>
</template>