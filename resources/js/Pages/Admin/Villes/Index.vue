<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="[{ label: 'Villes' }]" />
    </template>

    <div class="page-header">
      <h1 class="page-title">Mondes / Villes</h1>
      <Button icon="pi pi-plus" label="Ajouter un monde" @click="openCreate" class="p-button-primary" />
    </div>

    <div class="search-bar mb-8">
      <span class="p-input-icon-left w-full max-w-md">
        <i class="pi pi-search" />
        <InputText v-model="filters['global'].value" placeholder="Rechercher un monde..." class="w-full" />
      </span>
    </div>

    <!-- Cards Grid au lieu de DataTable -->
    <div class="worlds-grid">
      <div v-for="(ville, index) in filteredVilles" :key="ville.id" class="world-card" ref="cardRefs">
        <div class="world-card-inner">
          <div class="world-icon">
            <i class="pi pi-map" />
          </div>
          <div class="world-content">
            <h3 class="world-name">{{ ville.nom }}</h3>
            <div class="world-stats">
              <span class="stat-item">
                <i class="pi pi-globe" />
                {{ ville.environnements_count }} Parcours
              </span>
            </div>
          </div>
          <div class="world-actions">
            <Button icon="pi pi-pencil" text rounded @click="openEdit(ville)" v-tooltip.top="'Modifier'" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(ville)" v-tooltip.top="'Supprimer'" />
          </div>
        </div>
        <div class="world-card-glow"></div>
      </div>

      <div v-if="filteredVilles.length === 0" class="empty-worlds py-12 text-center w-full col-span-full">
        <i class="pi pi-search text-5xl mb-4 text-white/10" />
        <p class="text-gray-500 font-gaming uppercase tracking-widest">Aucun monde trouvé</p>
      </div>
    </div>

    <!-- Dialog Créer / Éditer (Garder PrimeVue pour la logique simple) -->
    <Dialog v-model:visible="dialogVisible" :header="editTarget ? 'Modifier le monde' : 'Nouveau monde'"
      :style="{ width: '450px' }" modal class="gaming-dialog">
      <form @submit.prevent="submitVille">
        <div class="field">
          <label for="ville_nom">Nom du monde <span class="required">*</span></label>
          <InputText id="ville_nom" v-model="form.nom" maxlength="150" :class="{ 'p-invalid': errors.nom }" autofocus />
          <div class="flex justify-between mt-1">
            <small v-if="errors.nom" class="p-error">{{ errors.nom }}</small>
            <small class="char-count">{{ form.nom.length }}/150</small>
          </div>
        </div>
        <div class="dialog-footer">
          <Button type="button" label="Annuler" text @click="dialogVisible = false" />
          <Button type="submit" :label="editTarget ? 'Mettre à jour' : 'Initialiser le monde'" :loading="form.processing" class="p-button-primary" />
        </div>
      </form>
    </Dialog>

    <!-- Confirm Delete -->
    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'
import { FilterMatchMode } from '@primevue/core/api'
import gsap from 'gsap'

const props = defineProps({
  villes: Array,
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const filteredVilles = computed(() => {
  if (!filters.value.global.value) return props.villes
  const search = filters.value.global.value.toLowerCase()
  return props.villes.filter(v => v.nom.toLowerCase().includes(search))
})

const cardRefs = ref([])

const animateCards = () => {
  if (cardRefs.value.length > 0) {
    gsap.fromTo(cardRefs.value,
      {
        opacity: 0,
        y: 30,
        scale: 0.9
      },
      {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.5,
        stagger: 0.1,
        ease: 'back.out(1.7)',
        clearProps: 'all'
      }
    )
  }
}

onMounted(() => {
  animateCards()
})

// Réanimer quand le filtrage change
watch(filteredVilles, () => {
  setTimeout(animateCards, 0)
})

const confirm = useConfirm()
const dialogVisible = ref(false)
const editTarget = ref(null)

const form = useForm({ nom: '' })
const errors = ref({})

const openCreate = () => {
  editTarget.value = null
  form.reset()
  errors.value = {}
  dialogVisible.value = true
}

const openEdit = (ville) => {
  editTarget.value = ville
  form.nom = ville.nom
  errors.value = {}
  dialogVisible.value = true
}

const submitVille = () => {
  errors.value = {}
  if (!form.nom.trim()) {
    errors.value.nom = 'Le nom est requis.'
    return
  }

  if (editTarget.value) {
    form.put(route('admin.villes.update', editTarget.value.id), {
      onSuccess: () => {
        dialogVisible.value = false
        form.reset()
      },
      onError: (e) => { errors.value = e },
    })
  } else {
    form.post(route('admin.villes.store'), {
      onSuccess: () => {
        dialogVisible.value = false
        form.reset()
      },
      onError: (e) => { errors.value = e },
    })
  }
}

const confirmDelete = (ville) => {
  confirm.require({
    message: `Voulez-vous vraiment détruire le monde "${ville.nom}" ? Cette action est irréversible.`,
    header: 'ALERTE DE SUPPRESSION',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Détruire',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger p-button-outlined',
    accept: () => {
      useForm({}).delete(route('admin.villes.destroy', ville.id))
    },
  })
}
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}
.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #fff;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.worlds-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.world-card {
  position: relative;
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 16px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.world-card:hover {
  transform: translateY(-5px) scale(1.02);
  border-color: #FF9500;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.world-card-inner {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.world-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 149, 0, 0.1);
  border: 1px solid rgba(255, 149, 0, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FF9500;
  font-size: 1.5rem;
  transition: all 0.3s;
}

.world-card:hover .world-icon {
  background: #FF9500;
  color: #000;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.5);
}

.world-content {
  flex: 1;
}

.world-name {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.world-stats {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;
  color: #94a3b8;
  display: flex;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.stat-item i {
  color: #FF9500;
  font-size: 0.7rem;
}

.world-actions {
  display: flex;
  gap: 0.25rem;
  opacity: 0;
  transform: translateX(10px);
  transition: all 0.3s;
}

.world-card:hover .world-actions {
  opacity: 1;
  transform: translateX(0);
}

.world-card-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at center, rgba(255, 149, 0, 0.1) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.3s;
  z-index: 1;
}

.world-card:hover .world-card-glow {
  opacity: 1;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.field label {
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(255, 149, 0, 0.7);
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.char-count {
  color: rgba(255, 255, 255, 0.3);
  font-size: 0.7rem;
  font-family: 'Rajdhani', sans-serif;
}

.required { color: #ef4444; }

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1rem;
}

:deep(.p-inputtext) {
  background: rgba(255, 255, 255, 0.03) !important;
  border-color: #1f2937 !important;
  color: #fff !important;
}

:deep(.p-inputtext:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 0 1px rgba(255, 149, 0, 0.2) !important;
}
</style>
