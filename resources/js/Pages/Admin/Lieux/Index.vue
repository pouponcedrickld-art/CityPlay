<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="[{ label: 'Parcours', url: route('admin.environnements.index') }, { label: 'Lieux' }]" />
    </template>

    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Points de <span class="text-primary-500">Passage</span></h1>
        <p class="page-subtitle">Configuration des étapes du parcours : <span class="text-white">{{ environnement.nom }}</span></p>
      </div>
      <Button icon="pi pi-plus" label="Ajouter une Étape" @click="openCreate" class="p-button-primary" />
    </div>

    <div v-if="lieux.length === 0" class="empty-state">
      <div class="empty-icon-wrapper">
        <i class="pi pi-map-marker" />
      </div>
      <p class="uppercase tracking-widest font-gaming">Aucun point de passage détecté sur cette carte</p>
      <Button label="Initialiser la première étape" icon="pi pi-plus" @click="openCreate" text class="mt-4" />
    </div>

    <div v-else class="lieux-timeline">
      <div v-for="(lieu, index) in lieux" :key="lieu.id" class="lieu-node" ref="lieuRefs">
        <div class="node-line" v-if="index !== lieux.length - 1"></div>

        <div class="node-marker">
          <div class="marker-circle">
            <span class="marker-number">{{ lieu.ordre }}</span>
          </div>
        </div>

        <div class="node-card">
          <div class="node-card-inner">
            <div class="node-info">
              <h3 class="node-name">{{ lieu.nom }}</h3>
              <div class="node-meta">
                <span class="meta-item"><i class="pi pi-map" /> {{ lieu.ville_quartier || 'Zone Inconnue' }}</span>
                <span class="meta-item"><i class="pi pi-directions" /> {{ lieu.latitude }}, {{ lieu.longitude }}</span>
              </div>
              <p class="node-description">{{ lieu.description }}</p>
            </div>

            <div class="node-actions">
              <Button icon="pi pi-pencil" text rounded @click="openEdit(lieu)" v-tooltip.top="'Modifier l\'étape'" class="action-btn" />
              <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(lieu)" v-tooltip.top="'Supprimer l\'étape'" class="action-btn-danger" />
            </div>
          </div>
          <div class="node-card-glow"></div>
        </div>
      </div>
    </div>

    <!-- Dialogs -->
    <Dialog v-model:visible="dialogVisible" :header="editTarget ? 'MODIFIER L\'ÉTAPE' : 'NOUVELLE ÉTAPE'"
      :style="{ width: '600px' }" modal class="gaming-dialog">
      <!-- Contenu du formulaire (simplifié pour le style) -->
      <form @submit.prevent="submitLieu" class="gaming-form-grid">
        <div class="field">
          <label>NOM DE L'ÉTAPE</label>
          <InputText v-model="form.nom" class="gaming-input" />
        </div>
        <!-- ... autres champs ... -->
        <div class="dialog-footer">
          <Button type="button" label="ANNULER" text @click="dialogVisible = false" />
          <Button type="submit" label="ENREGISTRER" class="p-button-primary" />
        </div>
      </form>
    </Dialog>

    <ConfirmDialog class="gaming-confirm" />
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import ConfirmDialog from 'primevue/confirmdialog'
import gsap from 'gsap'

const props = defineProps({
  environnement: Object,
  lieux: Array
})

const lieuRefs = ref([])

onMounted(() => {
  if (lieuRefs.value.length > 0) {
    gsap.from(lieuRefs.value, {
      opacity: 0,
      x: -50,
      duration: 0.6,
      stagger: 0.15,
      ease: 'power3.out'
    })

    gsap.from('.node-line', {
      scaleY: 0,
      transformOrigin: 'top',
      duration: 1.5,
      delay: 0.5,
      ease: 'expo.inOut'
    })
  }
})

const confirm = useConfirm()
const dialogVisible = ref(false)
const editTarget = ref(null)

const form = useForm({
  nom: '',
  description: '',
  latitude: '',
  longitude: '',
  ville_quartier: '',
  ordre: 1
})

const openCreate = () => {
  editTarget.value = null
  form.reset()
  form.ordre = props.lieux.length + 1
  dialogVisible.value = true
}

const openEdit = (lieu) => {
  editTarget.value = lieu
  form.nom = lieu.nom
  form.description = lieu.description
  form.latitude = lieu.latitude
  form.longitude = lieu.longitude
  form.ville_quartier = lieu.ville_quartier
  form.ordre = lieu.ordre
  dialogVisible.value = true
}

const submitLieu = () => {
  if (editTarget.value) {
    form.put(route('admin.lieux.update', [props.environnement.id, editTarget.value.id]), {
      onSuccess: () => dialogVisible.value = false
    })
  } else {
    form.post(route('admin.lieux.store', props.environnement.id), {
      onSuccess: () => dialogVisible.value = false
    })
  }
}

const confirmDelete = (lieu) => {
  confirm.require({
    message: `Voulez-vous supprimer l'étape "${lieu.nom}" ?`,
    header: 'SUPPRESSION ÉTAPE',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'SUPPRIMER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.lieux.destroy', [props.environnement.id, lieu.id]))
    }
  })
}
</script>

<style scoped>
.lieux-timeline {
  position: relative;
  padding-left: 3rem;
  margin-top: 2rem;
}

.lieu-node {
  position: relative;
  margin-bottom: 3rem;
  display: flex;
  align-items: flex-start;
  gap: 2rem;
}

.node-line {
  position: absolute;
  left: -2.05rem;
  top: 3rem;
  width: 2px;
  height: calc(100% + 1rem);
  background: linear-gradient(180deg, #FF9500, transparent);
  z-index: 1;
}

.node-marker {
  position: absolute;
  left: -3.5rem;
  top: 0.5rem;
  z-index: 2;
}

.marker-circle {
  width: 3rem;
  height: 3rem;
  background: #0b0e14;
  border: 2px solid #FF9500;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.3);
  transition: all 0.3s;
}

.lieu-node:hover .marker-circle {
  background: #FF9500;
  transform: scale(1.1);
  box-shadow: 0 0 25px rgba(255, 149, 0, 0.5);
}

.marker-number {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 800;
  color: #FF9500;
  font-size: 1.25rem;
}

.lieu-node:hover .marker-number {
  color: #000;
}

.node-card {
  flex: 1;
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
}

.node-card:hover {
  border-color: #FF9500;
  transform: translateX(10px);
}

.node-card-inner {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 2;
}

.node-name {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.node-meta {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.meta-item {
  font-size: 0.75rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.meta-item i {
  color: #FF9500;
}

.node-description {
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.6;
}

.node-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: rgba(255, 255, 255, 0.02) !important;
  border: 1px solid #1f2937 !important;
}

.action-btn:hover {
  background: rgba(255, 149, 0, 0.1) !important;
  border-color: #FF9500 !important;
  color: #FF9500 !important;
}

.action-btn-danger:hover {
  background: #ef4444 !important;
  color: #fff !important;
}

.node-card-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, rgba(255, 149, 0, 0.03), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.node-card:hover .node-card-glow {
  opacity: 1;
}

.empty-state {
  text-align: center;
  padding: 5rem;
  background: rgba(17, 24, 39, 0.5);
  border: 2px dashed #1f2937;
  border-radius: 20px;
  margin-top: 2rem;
}

.empty-icon-wrapper {
  width: 80px;
  height: 80px;
  background: rgba(255, 149, 0, 0.05);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 2rem;
  font-size: 2.5rem;
  color: rgba(255, 149, 0, 0.2);
}

.page-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.page-subtitle {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.9rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.gaming-input :deep(.p-inputtext) {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
  color: #fff !important;
}
</style>
