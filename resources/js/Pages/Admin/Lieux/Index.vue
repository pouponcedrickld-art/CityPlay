<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="page-header">
      <div>
        <h1 class="page-title">Lieux — {{ environnement.nom }}</h1>
        <p class="page-subtitle">{{ environnement.ville?.nom }} · Glissez-déposez pour réordonner</p>
      </div>
      <Link :href="route('admin.lieux.create', environnement.id)">
        <Button icon="pi pi-plus" label="Nouveau lieu" />
      </Link>
    </div>

    <!-- Liste ordonnée -->
    <div class="lieux-list">
      <div v-if="lieux.length === 0" class="empty-state">
        <i class="pi pi-map-marker" />
        <p>Aucun lieu pour l'instant. Commencez par en créer un.</p>
      </div>

      <div v-for="lieu in orderedLieux" :key="lieu.id" class="lieu-card">
        <div class="lieu-order">
          <span class="order-badge">{{ lieu.ordre }}</span>
          <i class="pi pi-bars drag-handle" title="Déplacer" />
        </div>

        <div class="lieu-info">
          <div class="lieu-name">{{ lieu.nom }}</div>
          <div class="lieu-meta">
            <span><i class="pi pi-map-marker" /> {{ lieu.latitude }}, {{ lieu.longitude }}</span>
            <span><i class="pi pi-circle" /> Rayon : {{ lieu.rayon_metres }} m</span>
            <Tag :value="`${lieu.enigmes_count}/4 énigmes`"
              :severity="lieu.enigmes_count === 4 ? 'success' : 'warn'" />
          </div>
          <p v-if="lieu.description" class="lieu-description">{{ lieu.description }}</p>
        </div>

        <div class="lieu-actions">
          <Link :href="route('admin.enigmes.index', lieu.id)">
            <Button icon="pi pi-lightbulb" v-tooltip="'Gérer les énigmes'" text rounded severity="secondary" />
          </Link>
          <Link :href="route('admin.lieux.edit', [environnement.id, lieu.id])">
            <Button icon="pi pi-pencil" text rounded severity="secondary" />
          </Link>
          <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(lieu)" />
        </div>
      </div>
    </div>

    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'

const props = defineProps({
  environnement: Object,
  lieux: Array,
})

const confirm = useConfirm()

const orderedLieux = computed(() =>
  [...props.lieux].sort((a, b) => a.ordre - b.ordre)
)

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.environnement.nom, url: route('admin.environnements.index') },
  { label: 'Lieux' },
])

const confirmDelete = (lieu) => {
  confirm.require({
    message: `Supprimer le lieu "${lieu.nom}" ? Les énigmes associées seront aussi supprimées.`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => useForm({}).delete(route('admin.lieux.destroy', [props.environnement.id, lieu.id])),
  })
}
</script>

<style scoped>
.page-header    { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; }
.page-title     { font-size: 1.4rem; font-weight: 700; color: #1a202c; }
.page-subtitle  { font-size: 0.85rem; color: #718096; margin-top: 0.2rem; }
.lieux-list     { display: flex; flex-direction: column; gap: 0.75rem; }

.empty-state {
  text-align: center;
  padding: 3rem;
  background: #fff;
  border: 2px dashed #e5e9f0;
  border-radius: 12px;
  color: #a0aec0;
}
.empty-state i { font-size: 2rem; display: block; margin-bottom: 0.75rem; }

.lieu-card {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: box-shadow 0.15s;
}
.lieu-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }

.lieu-order {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding-top: 0.2rem;
}
.order-badge {
  width: 28px; height: 28px;
  background: #ebf8ff;
  color: #2b6cb0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}
.drag-handle { color: #cbd5e0; cursor: grab; font-size: 1rem; }

.lieu-info   { flex: 1; }
.lieu-name   { font-weight: 600; color: #2d3748; font-size: 0.95rem; margin-bottom: 0.4rem; }
.lieu-meta   { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; font-size: 0.8rem; color: #718096; margin-bottom: 0.4rem; }
.lieu-meta i { margin-right: 0.2rem; }
.lieu-description { font-size: 0.82rem; color: #718096; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.lieu-actions { display: flex; gap: 0.2rem; flex-shrink: 0; }
</style>
