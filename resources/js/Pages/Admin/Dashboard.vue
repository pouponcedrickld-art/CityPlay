<script setup>
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import { computed } from 'vue';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
} from 'chart.js';
import { Bar, Line, Doughnut } from 'vue-chartjs';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement
);

const props = defineProps({
    stats: Object,
    charts: Object
});

// Chart Data: Inscriptions Récentes
const inscriptionsChartData = computed(() => ({
    labels: props.charts.inscriptions_recentes.map(item => item.date),
    datasets: [
        {
            label: 'Nouvelles inscriptions',
            backgroundColor: '#8B5CF6',
            borderColor: '#8B5CF6',
            data: props.charts.inscriptions_recentes.map(item => item.total),
            tension: 0.4,
            fill: false,
        }
    ]
}));

// Chart Data: Parties par Ville
const villesChartData = computed(() => ({
    labels: props.charts.parties_par_ville.map(item => item.nom),
    datasets: [
        {
            label: 'Nombre de parties',
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#6366F1'],
            data: props.charts.parties_par_ville.map(item => item.total),
        }
    ]
}));

// Chart Data: Statut des Parties
const statutChartData = computed(() => {
    const labels = {
        'en_cours': 'En cours',
        'terminee': 'Terminée',
        'suspendue': 'Suspendue',
        'abandonnee': 'Abandonnée'
    };

    return {
        labels: props.charts.parties_par_statut.map(item => labels[item.statut] || item.statut),
        datasets: [
            {
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                data: props.charts.parties_par_statut.map(item => item.total),
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        }
    }
};

const barOptions = {
    ...chartOptions,
    plugins: {
        legend: {
            display: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Inscriptions Récentes -->
                <Card class="chart-card">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-user-plus text-purple-500"></i>
                            <span>Évolution des inscriptions</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="h-[300px]">
                            <Line
                                v-if="charts.inscriptions_recentes.length > 0"
                                :data="inscriptionsChartData"
                                :options="chartOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-400 italic">
                                Aucune inscription ces 7 derniers jours
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Parties par Ville -->
                <Card class="chart-card">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-map-marker text-blue-500"></i>
                            <span>Répartition par ville</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="h-[300px]">
                            <Bar
                                v-if="charts.parties_par_ville.length > 0"
                                :data="villesChartData"
                                :options="barOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-400 italic">
                                Aucune donnée disponible
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Statut des Parties -->
                <Card class="chart-card">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-chart-pie text-green-500"></i>
                            <span>Statut des parties</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="h-[300px]">
                            <Doughnut
                                v-if="charts.parties_par_statut.length > 0"
                                :data="statutChartData"
                                :options="chartOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-400 italic">
                                Aucune donnée disponible
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Stats / Table -->
                <Card class="chart-card">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-list text-orange-500"></i>
                            <span>Résumé de l'activité</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Total des équipes</span>
                                <span class="font-bold text-xl">{{ stats.teams }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Total des lieux</span>
                                <span class="font-bold text-xl">{{ stats.lieux }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Total des énigmes</span>
                                <span class="font-bold text-xl">{{ stats.enigmes }}</span>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500 text-center">
                                    Dernière mise à jour : {{ new Date().toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.chart-card {
    border: none;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border-radius: 1rem;
    overflow: hidden;
}

:deep(.p-card-title) {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    padding: 1.25rem 1.25rem 0;
}

:deep(.p-card-body) {
    padding: 0;
}

:deep(.p-card-content) {
    padding: 1.25rem;
}

.dashboard-content {
    background-color: #f9fafb;
    min-height: calc(100vh - 64px);
}
</style>
