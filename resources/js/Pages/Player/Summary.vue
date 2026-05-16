<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

const props = defineProps({
    partie: Object,
    progression: Object
});
</script>

<template>
    <Head title="Résumé de la partie" />

    <div class="min-h-screen bg-gray-50 pb-12">
        <!-- HEADER -->
        <div class="bg-white border-b border-orange-100 p-8 text-center shadow-sm">
            <div class="w-20 h-20 bg-orange-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-flag-fill text-3xl text-[#FF9500]"></i>
            </div>
            <h1 class="text-2xl font-black text-orange-950 uppercase tracking-tight">Partie Terminée</h1>
            <p class="text-orange-900/40 font-bold uppercase tracking-widest text-xs mt-1">Félicitations pour votre parcours !</p>
        </div>

        <main class="p-6 max-w-2xl mx-auto space-y-6 -mt-6">
            <!-- SCORE CARD -->
            <div class="bg-gradient-to-br from-[#FF9500] to-[#FF7B00] p-8 rounded-3xl shadow-xl text-white text-center space-y-2">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] opacity-70">Score Total</span>
                <div class="text-6xl font-black tabular-nums">
                    {{ progression.nb_enigmes_resolues * 100 }}
                </div>
                <p class="text-sm font-bold uppercase tracking-widest opacity-90">Points CityPlay</p>
            </div>

            <!-- STATS GRID -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/40">Résolues</span>
                    <div class="text-3xl font-black text-green-500">{{ progression.nb_enigmes_resolues }}</div>
                    <i class="pi pi-check-circle text-green-100 text-4xl absolute -right-2 -bottom-2"></i>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-900/40">Passées</span>
                    <div class="text-3xl font-black text-red-400">0</div>
                    <i class="pi pi-times-circle text-red-50 text-4xl absolute -right-2 -bottom-2"></i>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="space-y-4 pt-6">
                <Link :href="route('player.dashboard')" class="block">
                    <Button
                        label="Retour au tableau de bord"
                        icon="pi pi-home"
                        class="w-full p-5 rounded-2xl bg-orange-950 border-none font-bold uppercase tracking-widest shadow-lg"
                    />
                </Link>

                <Button
                    label="Partager mon score"
                    icon="pi pi-share-alt"
                    class="w-full p-5 rounded-2xl bg-white text-orange-950 border-2 border-orange-950 font-bold uppercase tracking-widest hover:bg-gray-50 transition-colors"
                />
            </div>
        </main>
    </div>
</template>