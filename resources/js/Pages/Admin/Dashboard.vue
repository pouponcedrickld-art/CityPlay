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
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'

defineProps({
  stats: {
    type: Object,
    default: () => ({ villes: 0, environnements: 0, lieux: 0, enigmes: 0 }),
  },
})
</script>

<style scoped>
.dashboard { max-width: 1100px; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1a202c; margin-bottom: 1.5rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #4a5568; margin-bottom: 1rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
