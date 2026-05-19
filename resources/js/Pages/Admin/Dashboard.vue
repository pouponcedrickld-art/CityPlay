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
            backgroundColor: '#FF9500',
            borderColor: '#FF9500',
            data: props.charts.inscriptions_recentes.map(item => item.total),
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(255, 149, 0, 0.1)',
        }
    ]
}));

// Chart Data: Parties par Ville
const villesChartData = computed(() => ({
    labels: props.charts.parties_par_ville.map(item => item.nom),
    datasets: [
        {
            label: 'Nombre de parties',
            backgroundColor: ['#FF9500', '#FFB347', '#FFD28E', '#FFF1DC', '#6366F1'],
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

    const colors = {
        'en_cours': '#3B82F6',   // Info (Bleu vif)
        'terminee': '#10B981',   // Succès (Vert émeraude)
        'suspendue': '#FF9500',  // Avertissement (Orange #FF9500)
        'abandonnee': '#EF4444'  // Danger (Rouge pur)
    };

    return {
        labels: props.charts.parties_par_statut.map(item => labels[item.statut] || item.statut),
        datasets: [
            {
<<<<<<< HEAD
                backgroundColor: props.charts.parties_par_statut.map(item => colors[item.statut] || '#9CA3AF'),
=======
                backgroundColor: ['#FF9500', '#10B981', '#F59E0B', '#EF4444'],
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
                data: props.charts.parties_par_statut.map(item => item.total),
                borderColor: '#111827',
                borderWidth: 2,
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
            labels: {
                color: '#94a3b8',
                font: {
                    family: 'Rajdhani',
                    size: 12
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                color: 'rgba(255, 255, 255, 0.05)'
            },
            ticks: {
                color: '#94a3b8'
            }
        },
        y: {
            grid: {
                color: 'rgba(255, 255, 255, 0.05)'
            },
            ticks: {
                color: '#94a3b8'
            }
        }
    }
};

const barOptions = {
    ...chartOptions,
    plugins: {
        ...chartOptions.plugins,
        legend: {
            display: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                color: '#94a3b8'
            },
            grid: {
                color: 'rgba(255, 255, 255, 0.05)'
            }
        },
        x: {
            ticks: {
                color: '#94a3b8'
            },
            grid: {
                color: 'rgba(255, 255, 255, 0.05)'
            }
        }
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-white font-gaming uppercase tracking-widest">Dashboard</span>
        </template>

        <div class="dashboard-content">
            <h1 class="text-3xl font-bold mb-8 text-white">Tableau de bord <span class="text-primary-500">Global</span></h1>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="stat-card">
                    <div class="stat-card-inner">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-xs uppercase font-gaming tracking-widest mb-1">Villes</p>
                                <p class="text-4xl font-bold text-white">{{ stats.villes }}</p>
                            </div>
                            <div class="stat-icon-wrapper bg-primary-500/10 text-primary-500">
                                <i class="pi pi-map text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-inner">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-xs uppercase font-gaming tracking-widest mb-1">Parcours</p>
                                <p class="text-4xl font-bold text-white">{{ stats.environnements }}</p>
                            </div>
                            <div class="stat-icon-wrapper bg-green-500/10 text-green-500">
                                <i class="pi pi-globe text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-inner">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-xs uppercase font-gaming tracking-widest mb-1">Énigmes</p>
                                <p class="text-4xl font-bold text-white">{{ stats.enigmes }}</p>
                            </div>
                            <div class="stat-icon-wrapper bg-orange-500/10 text-orange-500">
                                <i class="pi pi-question-circle text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-inner">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-xs uppercase font-gaming tracking-widest mb-1">Joueurs</p>
                                <p class="text-4xl font-bold text-white">{{ stats.users }}</p>
                            </div>
                            <div class="stat-icon-wrapper bg-purple-500/10 text-purple-500">
                                <i class="pi pi-users text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Inscriptions Récentes -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                            <h2 class="text-lg font-gaming text-white">Évolution des inscriptions</h2>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="h-[300px]">
                            <Line
                                v-if="charts.inscriptions_recentes.length > 0"
                                :data="inscriptionsChartData"
                                :options="chartOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-500 italic font-gaming">
                                AUCUNE INSCRIPTION CES 7 DERNIERS JOURS
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parties par Ville -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                            <h2 class="text-lg font-gaming text-white">Répartition par ville</h2>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="h-[300px]">
                            <Bar
                                v-if="charts.parties_par_ville.length > 0"
                                :data="villesChartData"
                                :options="barOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-500 italic font-gaming">
                                AUCUNE DONNÉE DISPONIBLE
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statut des Parties -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                            <h2 class="text-lg font-gaming text-white">Statut des parties</h2>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="h-[300px]">
                            <Doughnut
                                v-if="charts.parties_par_statut.length > 0"
                                :data="statutChartData"
                                :options="chartOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-gray-500 italic font-gaming">
                                AUCUNE DONNÉE DISPONIBLE
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats / Table -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                            <h2 class="text-lg font-gaming text-white">Résumé de l'activité</h2>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-white/5 rounded-lg border border-white/10 hover:border-primary-500/30 transition-colors">
                                <span class="text-gray-400 font-gaming">TOTAL DES ÉQUIPES</span>
                                <span class="font-bold text-2xl text-primary-500">{{ stats.teams }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-white/5 rounded-lg border border-white/10 hover:border-primary-500/30 transition-colors">
                                <span class="text-gray-400 font-gaming">TOTAL DES LIEUX</span>
                                <span class="font-bold text-2xl text-primary-500">{{ stats.lieux }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-white/5 rounded-lg border border-white/10 hover:border-primary-500/30 transition-colors">
                                <span class="text-gray-400 font-gaming">TOTAL DES ÉNIGMES</span>
                                <span class="font-bold text-2xl text-primary-500">{{ stats.enigmes }}</span>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/5">
                                <p class="text-xs text-gray-500 text-center font-gaming tracking-widest uppercase">
                                    Dernière mise à jour : {{ new Date().toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.dashboard-content {
    min-height: 100%;
}

.stat-card {
    position: relative;
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-radius: 12px;
    padding: 1px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(255, 149, 0, 0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 0 15px rgba(255, 149, 0, 0.1);
}

.stat-card:hover::before {
    transform: translateX(100%);
}

.stat-card-inner {
    background: #111827;
    padding: 1.5rem;
    border-radius: 11px;
    height: 100%;
}

.stat-icon-wrapper {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
}

.chart-container {
    background: #111827;
    border: 1px solid #1f2937;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.chart-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #1f2937;
    background: rgba(255, 255, 255, 0.02);
}

.chart-body {
    padding: 1.5rem;
}

:deep(.p-card) {
    background: transparent;
    border: none;
    color: inherit;
}
</style>
