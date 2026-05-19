<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-white font-gaming uppercase tracking-widest">Gestion / Joueurs</span>
    </template>

    <div class="users-page">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">Base de données <span class="text-primary-500">Joueurs</span></h1>
      </div>

      <div class="search-and-filters mb-10 flex gap-4">
        <span class="p-input-icon-left flex-1 max-w-md">
          <i class="pi pi-search text-primary-500/50" />
          <InputText v-model="filters['global'].value" placeholder="Rechercher un pseudo, email ou nom..." class="w-full gaming-input" />
        </span>
        <Dropdown
          v-model="filters['is_admin'].value"
          :options="roleOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="Tous les rôles"
          class="gaming-dropdown w-48"
          showClear
        />
      </div>

<<<<<<< HEAD
          <template #empty>
            <div class="py-8 text-center text-gray-500">
              <i class="pi pi-users text-4xl mb-3 block" />
              <p>Aucun utilisateur trouvé.</p>
            </div>
          </template>

          <Column field="name" header="Utilisateur" sortable>
            <template #body="{ data }">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 font-bold text-xs shadow-inner">
                  {{ data.name.charAt(0) }}
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ data.name }}</div>
                  <div class="text-xs text-gray-500">@{{ data.pseudo }}</div>
                </div>
=======
      <!-- Joueurs Grid au lieu de DataTable -->
      <div class="players-grid">
        <div v-for="(user, index) in filteredUsers" :key="user.id" class="player-card" ref="playerRefs">
          <div class="player-card-inner">
            <div class="player-header">
              <div class="player-avatar">
                <span class="avatar-text">{{ user.name.charAt(0) }}</span>
                <div class="online-indicator" :class="{ 'admin': user.is_admin }"></div>
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
              </div>
              <div class="player-role-badge" :class="{ 'is-admin': user.is_admin }">
                {{ user.is_admin ? 'ADMIN' : 'PLAYER' }}
              </div>
            </div>

            <div class="player-info">
              <h3 class="player-name">{{ user.name }}</h3>
              <p class="player-pseudo">@{{ user.pseudo }}</p>
              <p class="player-email">{{ user.email }}</p>
            </div>

<<<<<<< HEAD
          <Column field="is_admin" header="Rôle" sortable :showFilterMatchModes="false">
            <template #body="{ data }">
              <span :class="data.is_admin ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-blue-50 text-blue-700 border border-blue-100'"
                    class="px-2.5 py-1 rounded-full text-xs font-bold shadow-sm">
                {{ data.is_admin ? 'Admin' : 'Joueur' }}
              </span>
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <Dropdown
                v-model="filterModel.value"
                @change="filterCallback()"
                :options="roleOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Tous"
                class="p-column-filter"
                :showClear="true"
=======
            <div class="player-meta">
              <div class="meta-item">
                <span class="label">INSCRIPTION</span>
                <span class="value">{{ new Date(user.created_at).toLocaleDateString() }}</span>
              </div>
            </div>

            <div class="player-actions">
              <Button
                icon="pi pi-shield"
                text
                rounded
                @click="confirmToggleAdmin(user)"
                v-tooltip.top="user.is_admin ? 'Retirer admin' : 'Promouvoir admin'"
                class="action-btn"
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
              />
              <Button
                icon="pi pi-trash"
                text
                rounded
                severity="danger"
                @click="confirmDelete(user)"
                v-tooltip.top="'Bannir'"
                class="action-btn"
              />
            </div>
          </div>
          <div class="card-scan-line"></div>
          <div class="card-glow"></div>
        </div>

        <div v-if="filteredUsers.length === 0" class="empty-state py-20 text-center w-full col-span-full">
          <i class="pi pi-users text-6xl mb-4 text-white/5" />
          <p class="text-gray-500 font-gaming uppercase tracking-[0.2em]">Aucun joueur détecté dans le système</p>
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
  users: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  is_admin: { value: null, matchMode: FilterMatchMode.EQUALS }
})

const roleOptions = ref([
  { label: 'Admin', value: true },
  { label: 'Joueur', value: false }
])

const filteredUsers = computed(() => {
  let results = props.users

  if (filters.value.global.value) {
    const search = filters.value.global.value.toLowerCase()
    results = results.filter(u =>
      u.name.toLowerCase().includes(search) ||
      u.pseudo.toLowerCase().includes(search) ||
      u.email.toLowerCase().includes(search)
    )
  }

  if (filters.value.is_admin.value !== null && filters.value.is_admin.value !== undefined) {
    results = results.filter(u => u.is_admin === filters.value.is_admin.value)
  }

  return results
})

const playerRefs = ref([])

const animatePlayers = () => {
  if (playerRefs.value.length > 0) {
    gsap.fromTo(playerRefs.value,
      {
        opacity: 0,
        x: -20,
        rotateY: -30,
        perspective: 1000
      },
      {
        opacity: 1,
        x: 0,
        rotateY: 0,
        duration: 0.6,
        stagger: 0.05,
        ease: 'power2.out',
        clearProps: 'transform'
      }
    )
  }
}

onMounted(() => {
  animatePlayers()
})

watch(filteredUsers, () => {
  setTimeout(animatePlayers, 0)
})

const confirm = useConfirm()

const confirmToggleAdmin = (user) => {
  confirm.require({
    message: `Voulez-vous vraiment modifier les privilèges de ${user.name} ?`,
    header: 'SÉCURITÉ SYSTÈME',
    icon: 'pi pi-shield',
    acceptLabel: 'CONFIRMER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-primary',
    accept: () => {
      router.post(route('admin.users.toggle-admin', user.id))
    }
  })
}

const confirmDelete = (user) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer l'accès de ${user.name} ? Cette action est irréversible.`,
    header: 'PROTOCOLE DE SUPPRESSION',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'SUPPRIMER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.users.destroy', user.id))
    }
  })
}
</script>

<style scoped>
.users-page {
  perspective: 1000px;
}

.players-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
}

.player-card {
  position: relative;
  background: linear-gradient(145deg, #111827, #0b0e14);
  border: 1px solid #1f2937;
  border-radius: 20px;
  padding: 1.5rem;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.player-card:hover {
  transform: translateY(-10px);
  border-color: #FF9500;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(255, 149, 0, 0.1);
}

.player-card-inner {
  position: relative;
  z-index: 2;
}

.player-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.player-avatar {
  width: 64px;
  height: 64px;
  background: #1f2937;
  border: 2px solid #374151;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: all 0.3s;
}

.player-card:hover .player-avatar {
  border-color: #FF9500;
  background: rgba(255, 149, 0, 0.1);
}

.avatar-text {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
}

.online-indicator {
  position: absolute;
  bottom: -4px;
  right: -4px;
  width: 14px;
  height: 14px;
  background: #10b981;
  border: 3px solid #111827;
  border-radius: 50%;
  box-shadow: 0 0 10px #10b981;
}

.online-indicator.admin {
  background: #FF9500;
  box-shadow: 0 0 10px #FF9500;
}

.player-role-badge {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: #94a3b8;
  letter-spacing: 0.1em;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.player-role-badge.is-admin {
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
  border-color: rgba(255, 149, 0, 0.2);
}

.player-info {
  margin-bottom: 1.5rem;
}

.player-name {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.player-pseudo {
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  color: #FF9500;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.player-email {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;
  color: #4b5563;
  overflow: hidden;
  text-overflow: ellipsis;
}

.player-meta {
  padding-top: 1rem;
  border-top: 1px solid #1f2937;
  margin-bottom: 1.5rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-item .label {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.6rem;
  font-weight: 700;
  color: #374151;
  letter-spacing: 0.15em;
}

.meta-item .value {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;
  color: #94a3b8;
}

.player-actions {
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

.card-scan-line {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(255, 149, 0, 0.2), transparent);
  opacity: 0;
  z-index: 1;
}

.player-card:hover .card-scan-line {
  animation: scan 2s linear infinite;
  opacity: 1;
}

@keyframes scan {
  0% { top: 0; }
  100% { top: 100%; }
}

.card-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 150%;
  height: 150%;
  background: radial-gradient(circle at center, rgba(255, 149, 0, 0.05) 0%, transparent 70%);
  transform: translate(-50%, -50%);
  opacity: 0;
  transition: opacity 0.4s;
  pointer-events: none;
}

.player-card:hover .card-glow {
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
