<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Codex des <span class="text-primary-500">Énigmes</span></h1>
        <p class="page-subtitle">{{ lieu.environnement?.ville?.nom }} · <span class="text-white">{{ lieu.nom }}</span></p>
      </div>

      <div class="header-actions">
        <div class="completion-status">
          <div class="status-indicator" :class="completionClass">
            <i class="pi" :class="completionIcon" />
            <span>{{ completedCount }} / 4 ÉNIGMES</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Message -->
    <Message severity="info" class="convention-msg" :closable="false">
      <i class="pi pi-info-circle mr-2" />
      <span>PROTOCOLE : Un lieu complet doit contenir 4 énigmes (Force 1, 2, 3 et Enfant).</span>
    </Message>

    <div class="enigmes-grid">
      <div v-for="(typeLabel, typeKey) in types" :key="typeKey" class="enigme-card"
        :class="{ 'has-content': enigmes[typeKey], 'missing': !enigmes[typeKey] }" ref="enigmeRefs">

        <div class="card-inner">
          <div class="card-header">
            <div class="type-badge" :class="`type-${typeKey}`">
              {{ typeLabel }}
            </div>
            <div class="card-status-tag">
               <Tag v-if="enigmes[typeKey]" value="ACTIF" severity="success" />
               <Tag v-else value="INACTIF" severity="danger" />
            </div>
          </div>

          <div class="card-body">
            <div v-if="enigmes[typeKey]">
              <div class="enigme-image" v-if="enigmes[typeKey].image_url">
                <img :src="`/storage/${enigmes[typeKey].image_url}`" alt="Visuel">
                <div class="image-overlay"></div>
              </div>
              <div class="no-image" v-else>
                <i class="pi pi-image" />
                <span>AUCUN VISUEL</span>
              </div>
              <p class="enigme-texte">{{ enigmes[typeKey].texte }}</p>
            </div>

            <div v-else class="card-empty">
              <div class="empty-icon">
                <i class="pi pi-lock" />
              </div>
              <p>EMPLACEMENT DISPONIBLE</p>
            </div>
          </div>

          <div class="card-footer">
            <Link :href="route('admin.enigmes.edit', [lieu.id, typeKey])">
              <Button :icon="enigmes[typeKey] ? 'pi pi-pencil' : 'pi pi-plus'"
                :label="enigmes[typeKey] ? 'MODIFIER' : 'AJOUTER'"
                :class="enigmes[typeKey] ? 'p-button-outlined' : 'p-button-primary'"
                size="small" />
            </Link>
            <Button v-if="enigmes[typeKey]" icon="pi pi-trash" text rounded severity="danger"
              @click="confirmDelete(typeKey, typeLabel)" class="action-btn-danger" />
          </div>
        </div>
        <div class="card-glow"></div>
      </div>
    </div>

    <ConfirmDialog class="gaming-confirm" />
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'
import gsap from 'gsap'

const props = defineProps({
  lieu: Object,
  enigmes: Object,
  types: Object,
})

const enigmeRefs = ref([])

onMounted(() => {
  if (enigmeRefs.value.length > 0) {
    gsap.from(enigmeRefs.value, {
      opacity: 0,
      y: 30,
      scale: 0.9,
      duration: 0.5,
      stagger: 0.1,
      ease: 'back.out(1.7)'
    })
  }
})

const confirm = useConfirm()

const completedCount = computed(() =>
  Object.keys(props.types).filter(t => props.enigmes[t]).length
)

const completionClass = computed(() => ({
  'status-complete': completedCount.value === 4,
  'status-partial': completedCount.value > 0 && completedCount.value < 4,
  'status-empty': completedCount.value === 0,
}))

const completionIcon = computed(() =>
  completedCount.value === 4 ? 'pi-check-circle' : 'pi-exclamation-circle'
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
    message: `Voulez-vous supprimer l'énigme "${typeLabel}" ?`,
    header: 'PROTOCOLE DE SUPPRESSION',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'SUPPRIMER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => useForm({}).delete(route('admin.enigmes.destroy', [props.lieu.id, typeKey])),
  })
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0;
}

.page-subtitle {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.9rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-top: 0.5rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-family: 'Rajdhani', sans-serif;
  font-weight: 800;
  font-size: 0.8rem;
  border: 1px solid transparent;
}

.status-complete { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
.status-partial { background: rgba(255, 149, 0, 0.1); color: #FF9500; border-color: rgba(255, 149, 0, 0.2); }
.status-empty { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

.convention-msg {
  background: rgba(17, 24, 39, 0.8) !important;
  border: 1px solid #1f2937 !important;
  color: #94a3b8 !important;
  margin-bottom: 2rem !important;
}

.enigmes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.enigme-card {
  position: relative;
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 400px;
  display: flex;
  flex-direction: column;
}

.enigme-card:hover {
  border-color: #FF9500;
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
}

.card-inner {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
  z-index: 2;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.type-badge {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 800;
  font-size: 0.7rem;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.type-force_3 { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.type-force_2 { background: rgba(255, 149, 0, 0.2); color: #FF9500; border: 1px solid rgba(255, 149, 0, 0.3); }
.type-force_1 { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.type-enfant { background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }

.card-body {
  flex: 1;
}

.enigme-image {
  position: relative;
  width: 100%;
  height: 150px;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 1rem;
  border: 1px solid #1f2937;
}

.enigme-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(0deg, rgba(11, 14, 20, 0.6) 0%, transparent 100%);
}

.no-image {
  width: 100%;
  height: 150px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px dashed #1f2937;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  color: #374151;
  font-family: 'Rajdhani', sans-serif;
  font-weight: 700;
  font-size: 0.75rem;
}

.enigme-texte {
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-empty {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #374151;
}

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  opacity: 0.2;
}

.card-empty p {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 700;
  font-size: 0.8rem;
  letter-spacing: 0.1em;
}

.card-footer {
  padding-top: 1.5rem;
  border-top: 1px solid #1f2937;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.action-btn-danger:hover {
  background: rgba(239, 68, 68, 0.1) !important;
}

.card-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at top right, rgba(255, 149, 0, 0.05) 0%, transparent 70%);
  pointer-events: none;
}

:deep(.p-tag) {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 800;
  font-size: 0.6rem;
  letter-spacing: 0.05em;
}
</style>
