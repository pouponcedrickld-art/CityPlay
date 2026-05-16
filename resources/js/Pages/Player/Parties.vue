<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ parties: Array });
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Mes parties</h1>
            <Link :href="route('parties.create')" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
                + Créer une partie
            </Link>
            <div v-if="parties.length === 0" class="text-gray-500">
                Aucune partie. Créez votre première partie !
            </div>
            <div v-else class="space-y-4">
                <div v-for="partie in parties" :key="partie.id" class="border rounded p-4">
                    <h3 class="font-bold">Partie #{{ partie.id }}</h3>
                    <p>Mode: {{ partie.mode }} | Statut: {{ partie.statut }}</p>
                    <Link :href="route('parties.show', partie.id)" class="text-blue-500">
                        Voir →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
