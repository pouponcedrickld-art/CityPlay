<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-white font-gaming uppercase tracking-widest">Gestion / Parties</span>
    </template>

    <div class="parties-page">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">Sessions de <span class="text-primary-500">Jeu</span></h1>
      </div>

      <div class="filters-bar mb-10 flex flex-wrap gap-4">
        <span class="p-input-icon-left flex-1 min-w-[300px]">
          <i class="pi pi-search text-primary-500/50" />
          <InputText v-model="filters['global'].value" placeholder="Rechercher une session (ID, joueur, parcours)..." class="w-full gaming-input" />
        </span>
        <Dropdown v-model="filters['mode'].value" :options="modes" placeholder="Tous les modes" class="gaming-dropdown w-48" showClear />
        <Dropdown v-model="filters['statut'].value" :options="statuts" placeholder="Tous les statuts" class="gaming-dropdown w-48" showClear />
      </div>

      <!-- Parties Grid -->
      <div class="sessions-grid">
        <div v-for="(partie, index) in filteredParties" :key="partie.id" class="session-card" ref="sessionRefs">
          <div class="session-card-inner">
            <div class="session-header">
              <span class="session-id">#{{ partie.id }}</span>
              <div class="session-status" :class="getStatusClass(partie.statut)">
                {{ partie.statut.replace('_', ' ') }}
              </div>
            </div>

            <div class="session-body">
              <div class="session-path">
                <i class="pi pi-map-marker text-primary-500" />
                <span class="path-name">{{ partie.environnement?.nom }}</span>
              </div>

              <div class="session-player">
                <div class="player-avatar-small">
                  {{ partie.createur?.name.charAt(0) }}
                </div>
                <div class="player-details">
                  <span class="player-name">{{ partie.createur?.name }}</span>
                  <span class="player-email">{{ partie.createur?.email }}</span>
                </div>
              </div>
            </div>

            <div class="session-footer">
              <div class="session-meta">
                <div class="meta-tag">
                  <i class="pi pi-bolt" />
                  {{ partie.mode }}
                </div>
                <div class="meta-tag">
                  <i class="pi pi-calendar" />
                  {{ new Date(partie.created_at).toLocaleDateString() }}
                </div>
              </div>

              <div class="session-actions">
                <Button
                  icon="pi pi-trash"
                  text
                  rounded
                  severity="danger"
                  @click="confirmDelete(partie)"
                  v-tooltip.top="'Terminer la session'"
                  class="action-btn-danger"
                />
              </div>
            </div>
          </div>
          <div class="session-glow"></div>
        </div>

        <div v-if="filteredParties.length === 0" class="empty-state py-20 text-center w-full col-span-full">
          <i class="pi pi-play text-6xl mb-4 text-white/5" />
          <p class="text-gray-500 font-gaming uppercase tracking-[0.2em]">Aucune session active détectée</p>
        </div>
      </div>
    </div>

    <ConfirmDialog class="gaming-confirm" />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'
import gsap from 'gsap'

const props = defineProps({
  parties: Array,
  modes: Array,
  statuts: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  mode: { value: null, matchMode: FilterMatchMode.EQUALS },
  statut: { value: null, matchMode: FilterMatchMode.EQUALS }
})

const filteredParties = computed(() => {
  let results = props.parties

  if (filters.value.global.value) {
    const search = filters.value.global.value.toLowerCase()
    results = results.filter(p =>
      p.id.toString().includes(search) ||
      p.environnement?.nom.toLowerCase().includes(search) ||
      p.createur?.name.toLowerCase().includes(search) ||
      p.createur?.email.toLowerCase().includes(search)
    )
  }

  if (filters.value.mode.value) {
    results = results.filter(p => p.mode === filters.value.mode.value)
  }

  if (filters.value.statut.value) {
    results = results.filter(p => p.statut === filters.value.statut.value)
  }

  return results
})

const sessionRefs = ref([])

const animateSessions = () => {
  if (sessionRefs.value.length > 0) {
    gsap.fromTo(sessionRefs.value,
      {
        opacity: 0,
        scale: 0.8,
        y: 20
      },
      {
        opacity: 1,
        scale: 1,
        y: 0,
        duration: 0.4,
        stagger: 0.08,
        ease: 'back.out(1.4)',
        clearProps: 'all'
      }
    )
  }
}

onMounted(() => {
  animateSessions()
})

watch(filteredParties, () => {
  setTimeout(animateSessions, 0)
})

const getStatusClass = (statut) => {
  switch (statut) {
    case 'en_cours': return 'status-active'
    case 'terminee': return 'status-ended'
    case 'pause': return 'status-paused'
    case 'en_attente': return 'status-waiting'
    default: return 'status-default'
  }
}

const confirm = useConfirm()

const confirmDelete = (partie) => {
  confirm.require({
    message: `Voulez-vous forcer l'arrêt de la session #${partie.id} ?`,
    header: 'TERMINER LA SESSION',
    icon: 'pi pi-power-off',
    acceptLabel: 'ARRÊTER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.parties.destroy', partie.id))
    }
  })
}
</script>

<style scoped>
.sessions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.session-card {
  position: relative;
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.session-card:hover {
  border-color: #FF9500;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  transform: translateY(-5px);
}

.session-card-inner {
  position: relative;
  z-index: 2;
  padding: 1.5rem;
}

.session-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.session-id {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 700;
  color: #FF9500;
  font-size: 1.1rem;
  letter-spacing: 0.05em;
}

.session-status {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  border: 1px solid transparent;
}

.status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
.status-ended { background: rgba(156, 163, 175, 0.1); color: #9ca3af; border-color: rgba(156, 163, 175, 0.2); }
.status-paused { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
.status-waiting { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2); }

.session-body {
  margin-bottom: 1.5rem;
}

.session-path {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.path-name {
  font-family: 'Rajdhani', sans-serif;
  font-weight: 700;
  color: #fff;
  font-size: 1rem;
  text-transform: uppercase;
}

.session-player {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.02);
  padding: 0.75rem;
  border-radius: 12px;
}

.player-avatar-small {
  width: 32px;
  height: 32px;
  background: #1f2937;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Rajdhani', sans-serif;
  font-weight: 700;
  color: #FF9500;
  font-size: 0.9rem;
}

.player-details {
  display: flex;
  flex-direction: column;
}

.player-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #e2e8f0;
}

.player-email {
  font-size: 0.7rem;
  color: #4b5563;
}

.session-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #1f2937;
}

.session-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meta-tag {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.7rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.meta-tag i {
  font-size: 0.7rem;
  color: #FF9500;
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

.session-glow {
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100px;
  background: radial-gradient(circle at top right, rgba(255, 149, 0, 0.05) 0%, transparent 70%);
  pointer-events: none;
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

.gaming-dropdown {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
}

.gaming-dropdown :deep(.p-dropdown-label) {
  color: #94a3b8 !important;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-size: 0.85rem;
}
</style>
