<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="page-header">
      <div>
        <h1 class="page-title">Énigmes — {{ lieu.nom }}</h1>
        <p class="page-subtitle">{{ lieu.environnement?.ville?.nom }} · {{ lieu.environnement?.nom }}</p>
      </div>
      <div class="completion-badge" :class="completionClass">
        <i :class="completionIcon" />
        {{ completedCount }}/4 énigmes renseignées
      </div>
    </div>

    <!-- Convention difficulté -->
    <Message severity="info" :closable="false" class="convention-msg">
      <strong>Convention difficulté :</strong> Force 3 = plus difficile (énigme cryptique) ·
      Force 2 = intermédiaire · Force 1 = facile (avec indices directs) · Enfant = adapté aux enfants
    </Message>

    <!-- Cartes des 4 énigmes -->
    <div class="enigmes-grid">
      <div v-for="(typeLabel, typeKey) in types" :key="typeKey" class="enigme-card"
        :class="{ 'has-content': enigmes[typeKey], 'missing': !enigmes[typeKey] }">

        <!-- Header carte -->
        <div class="card-header">
          <div class="type-badge" :class="`type-${typeKey}`">
            {{ typeLabel }}
          </div>
          <Tag v-if="enigmes[typeKey]" value="Renseignée" severity="success" />
          <Tag v-else value="Manquante" severity="warn" />
        </div>

        <!-- Contenu si existant -->
        <div v-if="enigmes[typeKey]" class="card-content">
          <p class="enigme-texte">{{ enigmes[typeKey].texte }}</p>
          <div v-if="enigmes[typeKey].image_url" class="enigme-image">
            <img :src="`/storage/${enigmes[typeKey].image_url}`" alt="Image énigme" />
          </div>
          <div v-else class="no-image">
            <i class="pi pi-image" /> Pas d'image
          </div>
        </div>

        <div v-else class="card-empty">
          <i class="pi pi-lightbulb" />
          <p>Cette énigme n'est pas encore renseignée.</p>
        </div>

        <!-- Actions -->
        <div class="card-footer">
          <Link :href="route('admin.enigmes.edit', [lieu.id, typeKey])">
            <Button :icon="enigmes[typeKey] ? 'pi pi-pencil' : 'pi pi-plus'"
              :label="enigmes[typeKey] ? 'Modifier' : 'Ajouter'"
              :severity="enigmes[typeKey] ? 'secondary' : 'primary'"
              size="small" outlined />
          </Link>
          <Button v-if="enigmes[typeKey]" icon="pi pi-trash" text rounded severity="danger"
            size="small" @click="confirmDelete(typeKey, typeLabel)" />
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
import Message from 'primevue/message'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'

const props = defineProps({
  lieu: Object,
  enigmes: Object, // { force1: {...}, force2: {...}, ... } (peut être null par type)
  types: Object,   // { force3: 'Difficile (Force 3)', ... }
})

const confirm = useConfirm()

const completedCount = computed(() =>
  Object.keys(props.types).filter(t => props.enigmes[t]).length
)

const completionClass = computed(() => ({
  'badge-complete': completedCount.value === 4,
  'badge-partial': completedCount.value > 0 && completedCount.value < 4,
  'badge-empty': completedCount.value === 0,
}))

const completionIcon = computed(() =>
  completedCount.value === 4 ? 'pi pi-check-circle' : 'pi pi-exclamation-circle'
)

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.lieu.environnement?.nom, url: route('admin.lieux.index', props.lieu.environnement_id) },
  { label: 'Lieux', url: route('admin.lieux.index', props.lieu.environnement_id) },
  { label: props.lieu.nom },
  { label: 'Énigmes' },
])

const confirmDelete = (typeKey, typeLabel) => {
  confirm.require({
    message: `Supprimer l'énigme "${typeLabel}" du lieu "${props.lieu.nom}" ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => useForm({}).delete(route('admin.enigmes.destroy', [props.lieu.id, typeKey])),
  })
}
</script>

<style scoped>
.page-header  { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
.page-title   { font-size: 1.4rem; font-weight: 700; color: #1a202c; }
.page-subtitle { font-size: 0.85rem; color: #718096; margin-top: 0.2rem; }

.completion-badge {
  display: flex; align-items: center; gap: 0.4rem;
  font-size: 0.82rem; font-weight: 600;
  padding: 0.4rem 0.85rem;
  border-radius: 99px;
}
.badge-complete { background: #f0fff4; color: #276749; }
.badge-partial  { background: #fffaf0; color: #c05621; }
.badge-empty    { background: #fff5f5; color: #c53030; }

.convention-msg { margin-bottom: 1.25rem; font-size: 0.875rem; }

.enigmes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.enigme-card {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: box-shadow 0.15s;
}
.enigme-card:hover  { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.enigme-card.missing { border-style: dashed; opacity: 0.85; }

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f0f4f8;
}

.type-badge {
  font-size: 0.8rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}
.type-force3 { background: #fff5f5; color: #c53030; }
.type-force2 { background: #fffaf0; color: #c05621; }
.type-force1 { background: #f0fff4; color: #276749; }
.type-enfant { background: #ebf8ff; color: #2b6cb0; }

.card-content { padding: 1rem; flex: 1; }
.enigme-texte {
  font-size: 0.82rem;
  color: #4a5568;
  line-height: 1.5;
  margin: 0 0 0.75rem;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  white-space: pre-line;
}
.enigme-image img { width: 100%; border-radius: 6px; object-fit: cover; max-height: 100px; }
.no-image { display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; color: #cbd5e0; }

.card-empty {
  padding: 1.5rem 1rem;
  text-align: center;
  color: #a0aec0;
  flex: 1;
}
.card-empty i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
.card-empty p { font-size: 0.82rem; margin: 0; }

.card-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid #f0f4f8;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
