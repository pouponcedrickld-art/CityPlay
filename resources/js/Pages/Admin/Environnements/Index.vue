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

    <DataTable :value="environnements" stripedRows class="crud-table">
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
import { useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'

defineProps({ environnements: Array, villes: Array })

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
