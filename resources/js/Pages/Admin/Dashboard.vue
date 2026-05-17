<script setup>
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';

const props = defineProps({
    stats: Object,
    charts: Object
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-gray-500">Dashboard</span>
        </template>

        <div class="dashboard-content p-4">
            <h1 class="text-2xl font-bold mb-6">Tableau de bord</h1>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <Card class="stat-card">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm uppercase font-bold">Villes</p>
                                <p class="text-3xl font-black">{{ stats.villes }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-map text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="stat-card">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm uppercase font-bold">Parcours</p>
                                <p class="text-3xl font-black">{{ stats.environnements }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-globe text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="stat-card">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm uppercase font-bold">Énigmes</p>
                                <p class="text-3xl font-black">{{ stats.enigmes }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-question-circle text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="stat-card">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm uppercase font-bold">Joueurs</p>
                                <p class="text-3xl font-black">{{ stats.users }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-users text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity or basic chart info -->
                <Card>
                    <template #title>Parties par ville</template>
                    <template #content>
                        <div v-if="charts.parties_par_ville.length > 0">
                            <ul class="divide-y divide-gray-100">
                                <li v-for="item in charts.parties_par_ville" :key="item.nom" class="py-3 flex justify-between items-center">
                                    <span class="font-medium">{{ item.nom }}</span>
                                    <span class="bg-gray-100 px-3 py-1 rounded-full text-sm font-bold">{{ item.total }} parties</span>
                                </li>
                            </ul>
                        </div>
                        <p v-else class="text-gray-400 italic">Aucune donnée disponible</p>
                    </template>
                </Card>

                <Card>
                    <template #title>Dernières inscriptions</template>
                    <template #content>
                        <div v-if="charts.inscriptions_recentes.length > 0">
                            <ul class="divide-y divide-gray-100">
                                <li v-for="item in charts.inscriptions_recentes" :key="item.date" class="py-3 flex justify-between items-center">
                                    <span class="font-medium">{{ item.date }}</span>
                                    <span class="text-green-600 font-bold">+{{ item.total }}</span>
                                </li>
                            </ul>
                        </div>
                        <p v-else class="text-gray-400 italic">Aucune inscription ces 7 derniers jours</p>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-4px);
}
</style>
