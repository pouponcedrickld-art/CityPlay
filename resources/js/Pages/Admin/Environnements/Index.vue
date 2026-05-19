<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-white font-gaming uppercase tracking-widest">Contenu / Parcours</span>
    </template>

    <div class="quests-page">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">Catalogue des <span class="text-primary-500">Parcours</span></h1>
        <Link :href="route('admin.environnements.create')">
          <Button icon="pi pi-plus" label="Nouvelle Quête" class="p-button-primary" />
        </Link>
      </div>

      <div class="search-bar mb-10">
        <span class="p-input-icon-left w-full max-w-xl">
          <i class="pi pi-search text-primary-500/50" />
          <InputText v-model="filters['global'].value" placeholder="Rechercher un parcours (nom, ville)..." class="w-full gaming-input" />
        </span>
      </div>

      <!-- Quests Grid -->
      <div class="quests-grid">
        <div v-for="(env, index) in filteredEnvs" :key="env.id" class="quest-card" ref="questRefs">
          <div class="quest-card-inner">
            <div class="quest-header">
              <div class="quest-city-badge">
                <i class="pi pi-map" />
                {{ env.ville?.nom }}
              </div>
              <div class="quest-id">LVL {{ env.id }}</div>
            </div>

            <h3 class="quest-title">{{ env.nom }}</h3>

            <div class="quest-content">
              <div class="quest-stats">
                <div class="quest-stat">
                  <span class="label">ÉNIGMES</span>
                  <span class="value">{{ env.enigmes_count || 0 }}</span>
                </div>
                <div class="quest-stat">
                  <span class="label">RÉTENTION</span>
                  <span class="value">{{ env.retention_profils_jours }}j</span>
                </div>
              </div>
              <p class="quest-description">{{ env.regles || 'AUCUNE RÈGLE DÉFINIE' }}</p>
            </div>

            <div class="quest-footer">
              <div class="quest-actions">
                <Link :href="route('admin.environnements.edit', env.id)">
                  <Button icon="pi pi-pencil" text rounded v-tooltip.top="'Modifier'" class="action-btn" />
                </Link>
                <Link :href="route('admin.lieux.index', env.id)">
                  <Button icon="pi pi-map-marker" text rounded v-tooltip.top="'Gérer les lieux'" class="action-btn" />
                </Link>
                <Link :href="route('admin.enigmes.index', env.id)">
                  <Button icon="pi pi-question-circle" text rounded v-tooltip.top="'Gérer les énigmes'" class="action-btn" />
                </Link>
                <Button
                  icon="pi pi-trash"
                  text
                  rounded
                  severity="danger"
                  @click="confirmDelete(env)"
                  v-tooltip.top="'Supprimer'"
                  class="action-btn-danger"
                />
              </div>
            </div>
          </div>
          <div class="quest-border-glow"></div>
        </div>

        <div v-if="filteredEnvs.length === 0" class="empty-state py-20 text-center w-full col-span-full">
          <i class="pi pi-globe text-6xl mb-4 text-white/5" />
          <p class="text-gray-500 font-gaming uppercase tracking-[0.2em]">Aucun parcours n'a été répertorié</p>
        </div>
      </div>
    </div>

    <ConfirmDialog class="gaming-confirm" />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'
import gsap from 'gsap'

const props = defineProps({
  environnements: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const filteredEnvs = computed(() => {
  if (!filters.value.global.value) return props.environnements
  const search = filters.value.global.value.toLowerCase()
  return props.environnements.filter(e =>
    e.nom.toLowerCase().includes(search) ||
    e.ville?.nom.toLowerCase().includes(search)
  )
})

const questRefs = ref([])

const animateQuests = () => {
  if (questRefs.value.length > 0) {
    gsap.fromTo(questRefs.value,
      {
        opacity: 0,
        rotateX: -45,
        y: 50
      },
      {
        opacity: 1,
        rotateX: 0,
        y: 0,
        duration: 0.6,
        stagger: 0.1,
        ease: 'power2.out',
        clearProps: 'all'
      }
    )
  }
}

onMounted(() => {
  animateQuests()
})

watch(filteredEnvs, () => {
  setTimeout(animateQuests, 0)
})

const confirm = useConfirm()

const confirmDelete = (env) => {
  confirm.require({
    message: `Voulez-vous vraiment supprimer le parcours "${env.nom}" ? Cette action supprimera également toutes les énigmes et lieux associés.`,
    header: 'ALERTE DE SUPPRESSION',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'SUPPRIMER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.environnements.destroy', env.id))
    }
  })
}
</script>

<style scoped>
.quests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  perspective: 1000px;
}

.quest-card {
  position: relative;
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  transform-style: preserve-3d;
}

.quest-card:hover {
  border-color: #FF9500;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
  transform: translateY(-10px) rotateX(5deg);
}

.quest-card-inner {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.quest-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.quest-city-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  color: #FF9500;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  background: rgba(255, 149, 0, 0.1);
  padding: 0.25rem 0.75rem;
  border-radius: 99px;
}

.quest-id {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.7rem;
  font-weight: 800;
  color: #4b5563;
}

.quest-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
}

.quest-content {
  flex: 1;
}

.quest-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.quest-stat {
  display: flex;
  flex-direction: column;
}

.quest-stat .label {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.6rem;
  font-weight: 800;
  color: #374151;
  letter-spacing: 0.1em;
}

.quest-stat .value {
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #e2e8f0;
}

.quest-description {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.quest-footer {
  padding-top: 1.25rem;
  border-top: 1px solid #1f2937;
}

.quest-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-btn {
  background: rgba(255, 255, 255, 0.02) !important;
  border: 1px solid #1f2937 !important;
  transition: all 0.3s !important;
}

.action-btn:hover {
  background: rgba(255, 149, 0, 0.1) !important;
  border-color: #FF9500 !important;
  color: #FF9500 !important;
}

.action-btn-danger {
  background: rgba(239, 68, 68, 0.05) !important;
  border: 1px solid rgba(239, 68, 68, 0.1) !important;
  color: #ef4444 !important;
}

.action-btn-danger:hover {
  background: #ef4444 !important;
  color: #fff !important;
  box-shadow: 0 0 15px rgba(239, 68, 68, 0.4) !important;
}

.quest-border-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  box-shadow: inset 0 0 20px rgba(255, 149, 0, 0);
  transition: all 0.4s;
  pointer-events: none;
}

.quest-card:hover .quest-border-glow {
  box-shadow: inset 0 0 30px rgba(255, 149, 0, 0.05);
}

.gaming-input :deep(.p-inputtext) {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
  color: #fff !important;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding-left: 3rem;
}

.gaming-input :deep(.p-inputtext:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.2) !important;
}
</style>
