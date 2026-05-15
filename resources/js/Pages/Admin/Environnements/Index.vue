<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="[{ label: 'Environnements' }]" />
    </template>

    <div class="page-header">
      <h1 class="page-title">Environnements</h1>
      <Link :href="route('admin.environnements.create')">
        <Button icon="pi pi-plus" label="Nouvel environnement" />
      </Link>
    </div>

    <DataTable 
      v-model:filters="filters"
      :value="environnements" 
      stripedRows 
      class="crud-table"
      :paginator="true"
      :rows="10"
      dataKey="id"
      filterDisplay="menu"
      :globalFilterFields="['nom', 'ville.nom']"
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
          Aucun environnement trouvé.
        </div>
      </template>

      <Column field="ville.nom" header="Ville" sortable />
      <Column field="nom" header="Nom" sortable />
      <Column field="lieux_count" header="Lieux" style="width:80px" />
      <Column field="retention_profils_jours" header="Rétention (j)" style="width:120px" />
      <Column header="Actions" style="width:160px">
        <template #body="{ data }">
          <div class="actions">
            <Link :href="route('admin.lieux.index', data.id)">
              <Button icon="pi pi-map-marker" v-tooltip="'Gérer les lieux'" text rounded />
            </Link>
            <Link :href="route('admin.environnements.edit', data.id)">
              <Button icon="pi pi-pencil" text rounded severity="secondary" />
            </Link>
            <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'
import { FilterMatchMode } from '@primevue/core/api'

defineProps({ environnements: Array, villes: Array })

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  nom: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'ville.nom': { value: null, matchMode: FilterMatchMode.CONTAINS }
})

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    nom: { value: null, matchMode: FilterMatchMode.CONTAINS },
    'ville.nom': { value: null, matchMode: FilterMatchMode.CONTAINS }
  }
}

const confirm = useConfirm()

const confirmDelete = (env) => {
  confirm.require({
    message: `Supprimer "${env.nom}" ? Tous les lieux et énigmes associés seront supprimés.`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => useForm({}).delete(route('admin.environnements.destroy', env.id)),
  })
}
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.page-title  { font-size: 1.4rem; font-weight: 700; color: #1a202c; }
.crud-table  { background: #fff; border-radius: 10px; border: 1px solid #e5e9f0; }
.actions     { display: flex; gap: 0.25rem; }
</style>
