<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-white font-gaming uppercase tracking-widest">Gestion / Accès Système</span>
    </template>

    <div class="invitations-page">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">Générateur de <span class="text-primary-500">Clés d'Accès</span></h1>
        <Button icon="pi pi-plus" label="Générer une Clé" @click="openCreateDialog" class="p-button-primary" />
      </div>

      <div class="search-bar mb-10">
        <span class="p-input-icon-left w-full max-w-xl">
          <i class="pi pi-search text-primary-500/50" />
          <InputText v-model="filters['global'].value" placeholder="Rechercher une clé ou un email..." class="w-full gaming-input" />
        </span>
      </div>

      <!-- Access Keys Grid -->
      <div class="keys-grid">
        <div v-for="(invitation, index) in filteredInvitations" :key="invitation.id" class="key-card" ref="keyRefs">
          <div class="key-card-inner">
            <div class="key-header">
              <div class="key-status-icon" :class="getInvitationStatus(invitation)">
                <i class="pi" :class="getStatusIcon(invitation)" />
              </div>
              <div class="key-status-text" :class="getInvitationStatus(invitation)">
                {{ getStatusLabel(invitation) }}
              </div>
            </div>

            <div class="key-content">
              <div class="key-link-wrapper">
                <label class="key-label">LIEN D'ACCÈS GÉNÉRÉ</label>
                <div class="key-display">
                  <span class="key-token">{{ invitation.token.substring(0, 12) }}...</span>
                  <div class="key-copy-actions">
                    <Button icon="pi pi-copy" text @click="copyLink(invitation.token)" v-tooltip.bottom="'Copier'" />
                    <Button icon="pi pi-whatsapp" text severity="success" @click="shareWhatsApp(invitation.token)" v-tooltip.bottom="'WhatsApp'" />
                  </div>
                </div>
              </div>

              <div class="key-target">
                <label class="key-label">DESTINATAIRE</label>
                <div class="target-value">
                  {{ invitation.email || 'LIEN PUBLIC (TOUS)' }}
                </div>
              </div>
            </div>

            <div class="key-footer">
              <div class="key-expiry">
                <i class="pi pi-clock" />
                {{ invitation.expires_at ? 'Expire le ' + new Date(invitation.expires_at).toLocaleDateString() : 'Pas d\'expiration' }}
              </div>
              <Button
                icon="pi pi-trash"
                text
                rounded
                severity="danger"
                @click="confirmDelete(invitation)"
                v-tooltip.top="'Désactiver la clé'"
                class="action-btn-danger"
              />
            </div>
          </div>
          <div class="key-hologram"></div>
        </div>

        <div v-if="filteredInvitations.length === 0" class="empty-state py-20 text-center w-full col-span-full">
          <i class="pi pi-key text-6xl mb-4 text-white/5" />
          <p class="text-gray-500 font-gaming uppercase tracking-[0.2em]">Aucune clé d'accès n'est active</p>
        </div>
      </div>
    </div>

    <!-- Dialog Création (Garder PrimeVue pour la logique) -->
    <Dialog v-model:visible="createDialogVisible" header="GÉNÉRER UNE NOUVELLE CLÉ" :style="{ width: '450px' }" modal class="gaming-dialog">
      <form @submit.prevent="submitCreate" class="flex flex-col gap-6 py-4">
        <div class="field flex flex-col gap-2">
          <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Email du destinataire (Optionnel)</label>
          <InputText v-model="createForm.email" placeholder="joueur@exemple.com" class="gaming-input" />
          <small class="text-[10px] text-gray-500 font-gaming uppercase tracking-widest">Si vide, le lien pourra être utilisé par n'importe qui.</small>
          <small v-if="createForm.errors.email" class="p-error">{{ createForm.errors.email }}</small>
        </div>

        <div class="field flex flex-col gap-2">
          <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Protocole d'expiration</label>
          <DatePicker v-model="createForm.expires_at" dateFormat="dd/mm/yy" :minDate="new Date()" showIcon class="gaming-datepicker" />
          <small class="text-[10px] text-gray-500 font-gaming uppercase tracking-widest">Délai standard : 7 jours.</small>
        </div>

        <div class="flex justify-end gap-3 mt-4">
          <Button type="button" label="ANNULER" text @click="createDialogVisible = false" />
          <Button type="submit" label="GÉNÉRER CLÉ" :loading="createForm.processing" class="p-button-primary" />
        </div>
      </form>
    </Dialog>

    <ConfirmDialog class="gaming-confirm" />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dialog from 'primevue/dialog'
import DatePicker from 'primevue/datepicker'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'
import gsap from 'gsap'

const props = defineProps({
  invitations: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const filteredInvitations = computed(() => {
  if (!filters.value.global.value) return props.invitations
  const search = filters.value.global.value.toLowerCase()
  return props.invitations.filter(i =>
    i.token.toLowerCase().includes(search) ||
    (i.email && i.email.toLowerCase().includes(search))
  )
})

const keyRefs = ref([])

const animateKeys = () => {
  if (keyRefs.value.length > 0) {
    gsap.fromTo(keyRefs.value,
      {
        opacity: 0,
        scale: 0.5,
        y: 100
      },
      {
        opacity: 1,
        scale: 1,
        y: 0,
        duration: 0.5,
        stagger: 0.1,
        ease: 'elastic.out(1, 0.75)',
        clearProps: 'all'
      }
    )
  }
}

onMounted(() => {
  animateKeys()
})

watch(filteredInvitations, () => {
  setTimeout(animateKeys, 0)
})

const getInvitationStatus = (inv) => {
  if (inv.used_at) return 'status-used'
  if (inv.expires_at && new Date(inv.expires_at) < new Date()) return 'status-expired'
  return 'status-valid'
}

const getStatusLabel = (inv) => {
  if (inv.used_at) return 'UTILISÉE'
  if (inv.expires_at && new Date(inv.expires_at) < new Date()) return 'EXPIRÉE'
  return 'VALIDE'
}

const getStatusIcon = (inv) => {
  if (inv.used_at) return 'pi-check-circle'
  if (inv.expires_at && new Date(inv.expires_at) < new Date()) return 'pi-times-circle'
  return 'pi-shield'
}

const confirm = useConfirm()
const createDialogVisible = ref(false)

const createForm = useForm({
  email: '',
  expires_at: null
})

const openCreateDialog = () => {
  createForm.reset()
  createDialogVisible.value = true
}

const submitCreate = () => {
  createForm.post(route('admin.invitations.store'), {
    onSuccess: () => {
      createDialogVisible.value = false
      createForm.reset()
    }
  })
}

const confirmDelete = (invitation) => {
  confirm.require({
    message: 'Voulez-vous vraiment désactiver cette clé d\'accès ?',
    header: 'SÉCURITÉ SYSTÈME',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'DÉSACTIVER',
    rejectLabel: 'ANNULER',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.invitations.destroy', invitation.id))
    }
  })
}

const getInvitationLink = (token) => {
  // URL courte et plus robuste (évite les problèmes de querystring copiée/trimée)
  return `${window.location.origin}/invite/${token}`
}

const copyLink = (token) => {
  navigator.clipboard.writeText(getInvitationLink(token))
}

const shareWhatsApp = (token) => {
  const link = getInvitationLink(token)
  const text = encodeURIComponent(`Rejoins-moi sur CityPlay ! Utilise ce lien pour t'inscrire : ${link}`)
  window.open(`https://wa.me/?text=${text}`, '_blank')
}
</script>

<style scoped>
.keys-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
}

.key-card {
  position: relative;
  background: linear-gradient(135deg, #111827, #0b0e14);
  border: 1px solid #1f2937;
  border-radius: 4px;
  padding: 1.5rem;
  transition: all 0.3s;
  overflow: hidden;
}

.key-card:hover {
  border-color: #FF9500;
  box-shadow: 0 0 30px rgba(255, 149, 0, 0.15);
  transform: scale(1.02);
}

.key-card-inner {
  position: relative;
  z-index: 2;
}

.key-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.key-status-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.status-valid { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.status-used { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.status-expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.key-status-text {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.1em;
}

.key-content {
  margin-bottom: 2rem;
}

.key-label {
  display: block;
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.6rem;
  font-weight: 800;
  color: #374151;
  letter-spacing: 0.15em;
  margin-bottom: 0.5rem;
}

.key-display {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid #1f2937;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.key-token {
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  color: #e2e8f0;
  font-weight: 600;
}

.key-copy-actions {
  display: flex;
  gap: 0.25rem;
}

.target-value {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}

.key-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #1f2937;
}

.key-expiry {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.7rem;
  font-weight: 700;
  color: #4b5563;
}

.key-expiry i {
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

.key-hologram {
  position: absolute;
  bottom: -20%;
  right: -10%;
  width: 150px;
  height: 150px;
  background: radial-gradient(circle at center, rgba(255, 149, 0, 0.03) 0%, transparent 70%);
  pointer-events: none;
}

.gaming-input :deep(.p-inputtext) {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
  color: #fff !important;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.gaming-input :deep(.p-inputtext:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 15px rgba(255, 149, 0, 0.2) !important;
}

.gaming-datepicker :deep(.p-inputtext) {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
  color: #fff !important;
}
</style>
