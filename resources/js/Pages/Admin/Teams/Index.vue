<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-white font-gaming uppercase tracking-widest">Gestion / Équipes</span>
    </template>

    <div class="teams-page">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">Gestion des <span class="text-primary-500">Squads</span></h1>
      </div>

      <div class="search-bar mb-10">
        <span class="p-input-icon-left w-full max-w-xl">
          <i class="pi pi-search text-primary-500/50" />
          <InputText v-model="filters['global'].value" placeholder="Rechercher une squad (nom, code, créateur)..." class="w-full gaming-input" />
        </span>
      </div>

      <!-- Teams Grid -->
      <div class="squads-grid">
        <div v-for="(team, index) in filteredTeams" :key="team.id" class="squad-card" ref="teamRefs">
          <div class="squad-card-inner">
            <div class="squad-header">
              <div class="squad-info-top">
                <h3 class="squad-name">{{ team.nom }}</h3>
                <span class="squad-code">{{ team.code_liaison }}</span>
              </div>
              <div class="squad-members-badge">
                <i class="pi pi-users" />
                {{ team.users_count }} / {{ team.max_joueurs }}
              </div>
            </div>

            <p class="squad-description">{{ team.description || 'AUCUNE MISSION DÉFINIE' }}</p>

            <div class="squad-meta">
              <div class="meta-item">
                <span class="meta-label">COMMANDANT</span>
                <span class="meta-value">{{ team.createur?.name }}</span>
              </div>
              <div class="meta-item text-right">
                <span class="meta-label">CRÉATION</span>
                <span class="meta-value">{{ new Date(team.created_at).toLocaleDateString() }}</span>
              </div>
            </div>

            <div class="squad-actions">
              <Button
                icon="pi pi-trash"
                text
                rounded
                severity="danger"
                @click="confirmDelete(team)"
                v-tooltip.top="'Dissoudre la squad'"
                class="action-btn-danger"
              />
            </div>
          </div>
          <div class="squad-corner-top"></div>
          <div class="squad-corner-bottom"></div>
        </div>

        <div v-if="filteredTeams.length === 0" class="empty-state py-20 text-center w-full col-span-full">
          <i class="pi pi-users text-6xl mb-4 text-white/5" />
          <p class="text-gray-500 font-gaming uppercase tracking-[0.2em]">Aucune squad active sur le terrain</p>
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
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'
import gsap from 'gsap'

const props = defineProps({
  teams: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const filteredTeams = computed(() => {
  if (!filters.value.global.value) return props.teams
  const search = filters.value.global.value.toLowerCase()
  return props.teams.filter(t =>
    t.nom.toLowerCase().includes(search) ||
    t.code_liaison.toLowerCase().includes(search) ||
    t.createur?.name.toLowerCase().includes(search)
  )
})

const teamRefs = ref([])

const animateTeams = () => {
  if (teamRefs.value.length > 0) {
    gsap.fromTo(teamRefs.value,
      {
        opacity: 0,
        y: 50,
        skewX: -5
      },
      {
        opacity: 1,
        y: 0,
        skewX: 0,
        duration: 0.5,
        stagger: 0.1,
        ease: 'power3.out',
        clearProps: 'all'
      }
    )
  }
}

onMounted(() => {
  animateTeams()
})

watch(filteredTeams, () => {
  setTimeout(animateTeams, 0)
})

const confirm = useConfirm()

const confirmDelete = (team) => {
  confirm.require({
    message: `Voulez-vous vraiment dissoudre la squad "${team.nom}" ?`,
    header: 'DISSOLUTION ÉQUIPE',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'DISSOUDRE',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.teams.destroy', team.id))
    }
  })
}
</script>

<style scoped>
.squads-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 2rem;
}

.squad-card {
  position: relative;
  background: rgba(17, 24, 39, 0.8);
  border: 1px solid #1f2937;
  padding: 1.75rem;
  border-radius: 4px;
  clip-path: polygon(
    0 15px, 15px 0,
    calc(100% - 15px) 0, 100% 15px,
    100% calc(100% - 15px), calc(100% - 15px) 100%,
    15px 100%, 0 calc(100% - 15px)
  );
  transition: all 0.3s ease;
}

.squad-card:hover {
  background: #111827;
  border-color: #FF9500;
  transform: scale(1.02);
  box-shadow: 0 0 30px rgba(255, 149, 0, 0.1);
}

.squad-card-inner {
  position: relative;
  z-index: 2;
}

.squad-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.squad-info-top {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.squad-name {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  line-height: 1;
}

.squad-code {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  color: #FF9500;
  letter-spacing: 0.2em;
  opacity: 0.8;
}

.squad-members-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 149, 0, 0.1);
  border: 1px solid rgba(255, 149, 0, 0.2);
  padding: 0.35rem 0.75rem;
  border-radius: 4px;
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  color: #FF9500;
}

.squad-description {
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 2rem;
  min-height: 3rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.squad-meta {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-top: 1px dashed #1f2937;
  border-bottom: 1px dashed #1f2937;
  margin-bottom: 1.5rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-label {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.6rem;
  font-weight: 800;
  color: #4b5563;
  letter-spacing: 0.1em;
}

.meta-value {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  color: #e2e8f0;
  text-transform: uppercase;
}

.squad-actions {
  display: flex;
  justify-content: flex-end;
}

.action-btn-danger {
  background: rgba(239, 68, 68, 0.05) !important;
  border: 1px solid rgba(239, 68, 68, 0.1) !important;
  color: #ef4444 !important;
  font-family: 'Rajdhani', sans-serif !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
}

.action-btn-danger:hover {
  background: #ef4444 !important;
  color: #fff !important;
  box-shadow: 0 0 20px rgba(239, 68, 68, 0.4) !important;
}

.squad-corner-top, .squad-corner-bottom {
  position: absolute;
  width: 10px;
  height: 10px;
  border-color: #FF9500;
  border-style: solid;
  opacity: 0;
  transition: all 0.3s;
}

.squad-corner-top {
  top: 10px;
  left: 10px;
  border-width: 2px 0 0 2px;
}

.squad-corner-bottom {
  bottom: 10px;
  right: 10px;
  border-width: 0 2px 2px 0;
}

.squad-card:hover .squad-corner-top,
.squad-card:hover .squad-corner-bottom {
  opacity: 1;
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
