<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-gray-500">Gestion / Équipes</span>
    </template>

    <div class="teams-page">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Équipes</h1>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <DataTable
          v-model:filters="filters"
          :value="teams"
          :paginator="true"
          :rows="10"
          dataKey="id"
          responsiveLayout="scroll"
          stripedRows
          class="p-datatable-sm"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} équipes"
          :globalFilterFields="['nom', 'description', 'createur.name', 'code_liaison']"
          filterDisplay="menu"
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
              <i class="pi pi-users text-4xl mb-3 block" />
              <p>Aucune équipe trouvée.</p>
            </div>
          </template>

          <Column header="Équipe" sortable field="nom">
            <template #body="{ data }">
              <div>
                <div class="font-medium text-gray-900">{{ data.nom }}</div>
                <div class="text-xs text-gray-500">{{ data.description || 'Pas de description' }}</div>
              </div>
            </template>
          </Column>

          <Column header="Créateur" sortable field="createur.name">
            <template #body="{ data }">
              {{ data.createur?.name }}
            </template>
          </Column>

          <Column header="Membres" sortable field="users_count">
            <template #body="{ data }">
              <span class="text-sm">{{ data.users_count }} / {{ data.max_joueurs }}</span>
            </template>
          </Column>

          <Column field="code_liaison" header="Code" sortable>
            <template #body="{ data }">
              <span class="font-mono text-xs text-gray-500">{{ data.code_liaison }}</span>
            </template>
          </Column>

          <Column header="Créée le" sortable field="created_at">
            <template #body="{ data }">
              {{ new Date(data.created_at).toLocaleDateString() }}
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

    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'

defineProps({
  teams: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  nom: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'createur.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
  code_liaison: { value: null, matchMode: FilterMatchMode.EQUALS }
})

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    nom: { value: null, matchMode: FilterMatchMode.CONTAINS },
    'createur.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
    code_liaison: { value: null, matchMode: FilterMatchMode.EQUALS }
  }
}

const confirm = useConfirm()

const confirmDelete = (team) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer l'équipe ${team.nom} ?`,
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.teams.destroy', team.id))
    }
  })
}
</script>
