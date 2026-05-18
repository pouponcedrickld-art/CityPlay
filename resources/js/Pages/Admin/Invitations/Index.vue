<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-gray-500">Gestion / Invitations Application</span>
    </template>

    <div class="invitations-page">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Invitations à l'application</h1>
        <Button icon="pi pi-plus" label="Générer une invitation" @click="openCreateDialog" />
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <DataTable
          v-model:filters="filters"
          :value="invitations"
          :paginator="true"
          :rows="10"
          dataKey="id"
          responsiveLayout="scroll"
          stripedRows
          class="p-datatable-sm"
          filterDisplay="menu"
          :globalFilterFields="['token', 'email']"
        >
          <template #header>
            <div class="flex justify-between items-center">
              <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters['global'].value" placeholder="Recherche globale..." class="p-inputtext-sm" />
              </span>
              <Button type="button" icon="pi pi-filter-slash" label="Effacer" outlined size="small" @click="clearFilter()" />
            </div>
          </template>

          <template #empty>
            <div class="py-8 text-center text-gray-500">
              <i class="pi pi-link text-4xl mb-3 block" />
              <p>Aucune invitation générée.</p>
            </div>
          </template>

          <Column field="token" header="Lien d'invitation">
            <template #body="{ data }">
              <div class="flex flex-col gap-1">
                <span class="text-xs font-mono text-gray-500 truncate max-w-[200px]" :title="getInvitationLink(data.token)">
                  {{ getInvitationLink(data.token) }}
                </span>
                <div class="flex gap-2">
                    <Button icon="pi pi-copy" text size="small" class="p-0" @click="copyLink(data.token)" v-tooltip.bottom="'Copier le lien'" />
                    <Button icon="pi pi-whatsapp" text size="small" severity="success" class="p-0" @click="shareWhatsApp(data.token)" v-tooltip.bottom="'Partager sur WhatsApp'" />
                </div>
              </div>
            </template>
          </Column>

          <Column field="email" header="Destinataire">
            <template #body="{ data }">
              <span v-if="data.email" class="text-sm">{{ data.email }}</span>
              <span v-else class="text-xs italic text-gray-400">Tous (Lien public)</span>
            </template>
          </Column>

          <Column field="used_at" header="Statut">
            <template #body="{ data }">
              <span v-if="data.used_at" class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                Utilisé le {{ new Date(data.used_at).toLocaleDateString() }}
              </span>
              <span v-else-if="isExpired(data.expires_at)" class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">
                Expiré
              </span>
              <span v-else class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-semibold">
                Valide
              </span>
            </template>
          </Column>

          <Column field="expires_at" header="Expire le">
            <template #body="{ data }">
              <span class="text-sm">{{ data.expires_at ? new Date(data.expires_at).toLocaleDateString() : 'Jamais' }}</span>
            </template>
          </Column>

          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <Button
                icon="pi pi-trash"
                text
                rounded
                severity="danger"
                @click="confirmDelete(data)"
                v-tooltip.top="'Supprimer'"
              />
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Dialog Création Invitation -->
    <Dialog v-model:visible="createDialogVisible" header="Générer un lien d'accès" :style="{ width: '400px' }" modal>
      <form @submit.prevent="submitCreate" class="flex flex-col gap-4 py-2">
        <div class="field flex flex-col gap-2">
          <label class="font-bold text-sm">Email du destinataire (Optionnel)</label>
          <InputText v-model="createForm.email" placeholder="email@exemple.com" :class="{ 'p-invalid': createForm.errors.email }" />
          <small class="text-xs text-gray-500">Si vide, le lien pourra être utilisé par n'importe qui.</small>
          <small v-if="createForm.errors.email" class="p-error">{{ createForm.errors.email }}</small>
        </div>

        <div class="field flex flex-col gap-2">
          <label class="font-bold text-sm">Date d'expiration</label>
          <DatePicker v-model="createForm.expires_at" dateFormat="dd/mm/yy" :minDate="new Date()" showIcon />
          <small class="text-xs text-gray-500">Par défaut : 7 jours.</small>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <Button type="button" label="Annuler" text @click="createDialogVisible = false" />
          <Button type="submit" label="Générer le lien" :loading="createForm.processing" />
        </div>
      </form>
    </Dialog>

    <ConfirmDialog />
    <Toast />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dialog from 'primevue/dialog'
import DatePicker from 'primevue/datepicker'
import ConfirmDialog from 'primevue/confirmdialog'
import Toast from 'primevue/toast'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { FilterMatchMode } from '@primevue/core/api'

const props = defineProps({
  invitations: Array
})

const toast = useToast()
const confirm = useConfirm()

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
})

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  }
}

const createDialogVisible = ref(false)
const createForm = useForm({
  email: '',
  expires_at: null,
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
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Invitation générée !', life: 3000 })
    }
  })
}

const getInvitationLink = (token) => {
    return `${window.location.origin}/register?invite=${token}`
}

const copyLink = (token) => {
    const link = getInvitationLink(token)
    navigator.clipboard.writeText(link)
    toast.add({ severity: 'success', summary: 'Copié', detail: 'Lien copié dans le presse-papier', life: 2000 })
}

const shareWhatsApp = (token) => {
    const link = getInvitationLink(token)
    const text = encodeURIComponent(`Bonjour ! Voici votre lien d'accès pour CityQuest : ${link}`)
    window.open(`https://wa.me/?text=${text}`, '_blank')
}

const isExpired = (date) => {
    if (!date) return false
    return new Date(date) < new Date()
}

const confirmDelete = (invitation) => {
  confirm.require({
    message: `Supprimer cette invitation ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.invitations.destroy', invitation.id))
    }
  })
}
</script>
