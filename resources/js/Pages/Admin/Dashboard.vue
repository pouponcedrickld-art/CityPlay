<<<<<<< HEAD
<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-gray-500">Dashboard</span>
    </template>

    <div class="dashboard">
      <h1 class="page-title">Tableau de bord</h1>

      <!-- Stats cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:#ebf8ff;color:#2b6cb0">
            <i class="pi pi-map" />
          </div>
          <div>
            <div class="stat-value">{{ stats.villes }}</div>
            <div class="stat-label">Villes</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#f0fff4;color:#276749">
            <i class="pi pi-globe" />
          </div>
          <div>
            <div class="stat-value">{{ stats.environnements }}</div>
            <div class="stat-label">Environnements</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#fffaf0;color:#c05621">
            <i class="pi pi-map-marker" />
          </div>
          <div>
            <div class="stat-value">{{ stats.lieux }}</div>
            <div class="stat-label">Lieux</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#faf5ff;color:#553c9a">
            <i class="pi pi-lightbulb" />
          </div>
          <div>
            <div class="stat-value">{{ stats.enigmes }}</div>
            <div class="stat-label">Énigmes</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#fff5f5;color:#c53030">
            <i class="pi pi-users" />
          </div>
          <div>
            <div class="stat-value">{{ stats.users }}</div>
            <div class="stat-label">Utilisateurs</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#e6fffa;color:#2c7a7b">
            <i class="pi pi-play" />
          </div>
          <div>
            <div class="stat-value">{{ stats.parties }}</div>
            <div class="stat-label">Parties</div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div class="chart-container">
          <h2 class="section-title">Inscriptions (7 derniers jours)</h2>
          <div class="chart-wrapper">
            <Line :data="inscriptionsData" :options="lineOptions" />
          </div>
        </div>
        <div class="chart-container">
          <h2 class="section-title">Parties par ville</h2>
          <div class="chart-wrapper">
            <Doughnut :data="partiesData" :options="doughnutOptions" />
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="quick-actions">
        <h2 class="section-title">Actions rapides</h2>
        <div class="actions-grid">
          <Link :href="route('admin.villes.index')" class="action-card">
            <i class="pi pi-plus-circle" />
            <span>Gérer les villes</span>
          </Link>
          <Link :href="route('admin.environnements.create')" class="action-card">
            <i class="pi pi-plus-circle" />
            <span>Nouvel environnement</span>
          </Link>
          <Link :href="route('admin.environnements.index')" class="action-card">
            <i class="pi pi-list" />
            <span>Voir tous les environnements</span>
          </Link>
          <Link :href="route('admin.users.index')" class="action-card">
            <i class="pi pi-users" />
            <span>Gérer les utilisateurs</span>
          </Link>
          <Link :href="route('admin.parties.index')" class="action-card">
            <i class="pi pi-play" />
            <span>Surveiller les parties</span>
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  ArcElement
} from 'chart.js'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  ArcElement
)

const props = defineProps({
  stats: Object,
  charts: Object
})

// Configuration du graphique linéaire (inscriptions)
const inscriptionsData = computed(() => ({
  labels: props.charts.inscriptions_recentes.map(d => new Date(d.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })),
  datasets: [{
    label: 'Inscriptions',
    data: props.charts.inscriptions_recentes.map(d => d.total),
    borderColor: '#4299e1',
    backgroundColor: '#ebf8ff',
    tension: 0.3,
    fill: true
  }]
}))

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1 } }
  }
}

// Configuration du graphique en anneau (parties par ville)
const partiesData = computed(() => ({
  labels: props.charts.parties_par_ville.map(d => d.nom),
  datasets: [{
    data: props.charts.parties_par_ville.map(d => d.total),
    backgroundColor: ['#4299e1', '#48bb78', '#ecc94b', '#ed64a6', '#9f7aea'],
    borderWidth: 0
  }]
}))

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } }
  }
}
</script>

<style scoped>
.dashboard { max-width: 1100px; padding-bottom: 2rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1a202c; margin-bottom: 1.5rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #4a5568; margin-bottom: 1.25rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a202c; }
.stat-label { font-size: 0.8rem; color: #718096; }

.charts-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .charts-section { grid-template-columns: 1fr; }
}

.chart-container {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 12px;
  padding: 1.5rem;
}

.chart-wrapper {
  height: 250px;
  position: relative;
}

.actions-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.1rem;
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 8px;
  color: #2d3748;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.15s;
}

.action-card:hover {
  border-color: #63b3ed;
  color: #2b6cb0;
  background: #ebf8ff;
}
</style>
=======
<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';

</script>


<template>



</template>


<style scoped>


</style>
>>>>>>> 3cf28890aa88709638185e3d8842f9dcc479aed2
