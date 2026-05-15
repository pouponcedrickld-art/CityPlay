<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="[{ label: 'Villes' }]" />
    </template>

    <div class="page-header">
      <h1 class="page-title">Villes</h1>
      <Button icon="pi pi-plus" label="Nouvelle ville" @click="openCreate" />
    </div>

    <!-- Table des villes -->
    <DataTable 
      v-model:filters="filters"
      :value="villes" 
      stripedRows 
      class="crud-table"
      :paginator="true"
      :rows="10"
      dataKey="id"
      filterDisplay="menu"
      :globalFilterFields="['nom']"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <span class="p-input-icon-left">
            <i class="pi pi-search" />
            <InputText v-model="filters['global'].value" placeholder="Recherche..." class="p-inputtext-sm" />
          </span>
          <Button type="button" icon="pi pi-filter-slash" label="Effacer" outlined size="small" @click="clearFilter()" />
        </div>
      </template>

      <template #empty>
        <div class="py-4 text-center text-gray-500">
          Aucune ville trouvée.
        </div>
      </template>

      <Column field="nom" header="Nom" sortable />
      <Column field="environnements_count" header="Environnements" style="width:160px" />
      <Column header="Actions" style="width:120px">
        <template #body="{ data }">
          <div class="actions">
            <Button icon="pi pi-pencil" text rounded severity="secondary" @click="openEdit(data)" />
            <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- Dialog Créer / Éditer -->
    <Dialog v-model:visible="dialogVisible" :header="editTarget ? 'Modifier la ville' : 'Nouvelle ville'"
      :style="{ width: '400px' }" modal>
      <form @submit.prevent="submitVille">
        <div class="field">
          <label for="ville_nom">Nom <span class="required">*</span></label>
          <InputText id="ville_nom" v-model="form.nom" maxlength="150" :class="{ 'p-invalid': errors.nom }" />
          <small class="char-count">{{ form.nom.length }}/150</small>
          <small v-if="errors.nom" class="p-error">{{ errors.nom }}</small>
        </div>
        <div class="dialog-footer">
          <Button type="button" label="Annuler" text @click="dialogVisible = false" />
          <Button type="submit" :label="editTarget ? 'Mettre à jour' : 'Créer'" :loading="form.processing" />
        </div>
      </form>
    </Dialog>

    <!-- Confirm Delete -->
    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'
import { FilterMatchMode } from '@primevue/core/api'

const props = defineProps({
  villes: Array,
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  nom: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    nom: { value: null, matchMode: FilterMatchMode.CONTAINS }
  }
}

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
      onSuccess: () => { dialogVisible.value = false },
      onError: (e) => { errors.value = e },
    })
  } else {
    form.post(route('admin.villes.store'), {
      onSuccess: () => { dialogVisible.value = false },
      onError: (e) => { errors.value = e },
    })
  }
}

const confirmDelete = (ville) => {
  confirm.require({
    message: `Supprimer la ville "${ville.nom}" ? Tous ses environnements seront aussi supprimés.`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
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
  margin-bottom: 1.25rem;
}
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a202c; }
.crud-table { background: #fff; border-radius: 10px; border: 1px solid #e5e9f0; }
.actions { display: flex; gap: 0.25rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 1rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: #4a5568; }
.field input { width: 100%; }
.char-count { color: #a0aec0; font-size: 0.75rem; text-align: right; }
.required { color: #e53e3e; }
.dialog-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 0.5rem; }
</style>
