<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <SidebarLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Stat Cards -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Parties Totales</div>
                        <div class="mt-2 text-3xl font-bold text-orange-600">{{ stats.total_parties }}</div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Joueurs Inscrits</div>
                        <div class="mt-2 text-3xl font-bold text-orange-600">{{ stats.total_users }}</div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Énigmes Créées</div>
                        <div class="mt-2 text-3xl font-bold text-orange-600">{{ stats.total_enigmes }}</div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Parties en Cours</div>
                        <div class="mt-2 text-3xl font-bold text-orange-600">{{ stats.parties_en_cours }}</div>
                    </div>
                </div>

                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Bienvenue dans l'espace Administration</h3>
                    <p class="text-gray-600">
                        Utilisez le menu latéral pour gérer les lieux, les énigmes et les parcours de CityPlay.
                    </p>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>