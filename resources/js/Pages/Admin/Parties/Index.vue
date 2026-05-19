<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-gray-500">Gestion / Parties</span>
    </template>

    <div class="parties-page">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Parties</h1>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <DataTable
          v-model:filters="filters"
          :value="parties"
          :paginator="true"
          :rows="10"
          dataKey="id"
          responsiveLayout="scroll"
          stripedRows
          class="p-datatable-sm"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} parties"
          :globalFilterFields="['id', 'environnement.nom', 'createur.name', 'mode', 'statut']"
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
              <i class="pi pi-play text-4xl mb-3 block" />
              <p>Aucune partie trouvée.</p>
            </div>
          </template>

          <Column field="id" header="ID" sortable>
            <template #body="{ data }">
              <span class="font-medium text-gray-900">#{{ data.id }}</span>
            </template>
          </Column>

          <Column header="Environnement" sortable field="environnement.nom">
            <template #body="{ data }">
              {{ data.environnement?.nom }}
            </template>
          </Column>

          <Column header="Joueur" sortable field="createur.name">
            <template #body="{ data }">
              <div class="flex flex-col">
                <span class="font-medium">{{ data.createur?.name }}</span>
                <span class="text-xs text-gray-400">{{ data.createur?.email }}</span>
              </div>
            </template>
          </Column>


          <Column field="mode" header="Mode" sortable :showFilterMatchModes="false">
            <template #body="{ data }">
              <span class="uppercase text-xs font-medium">{{ data.mode }}</span>
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="modes" placeholder="Sélectionner" class="p-column-filter" :showClear="true" />
            </template>
          </Column>

          <Column field="statut" header="Statut" sortable :showFilterMatchModes="false">
            <template #body="{ data }">
              <span :class="getStatusClass(data.statut)" class="px-2 py-1 rounded-full text-xs font-semibold">
                {{ data.statut }}
              </span>
            </template>
            <template #filter="{ filterModel, filterCallback }">
              <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="statuts" placeholder="Sélectionner" class="p-column-filter" :showClear="true" />
            </template>
          </Column>

          <Column header="Date" sortable field="created_at">
            <template #body="{ data }">
              {{ new Date(data.created_at).toLocaleDateString() }}
            </template>
          </Column>

          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-end gap-2">
                <Button
                  icon="pi pi-trash"
                  text
                  rounded
                  severity="danger"
                  @click="confirmDelete(data)"
                  v-tooltip.top="'Supprimer'"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>


    <ConfirmDialog />
    <Toast />
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
import Dropdown from 'primevue/dropdown'
import ConfirmDialog from 'primevue/confirmdialog'
import Toast from 'primevue/toast'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { FilterMatchMode } from '@primevue/core/api'

const props = defineProps({
  parties: Array
})

const toast = useToast()
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'environnement.nom': { value: null, matchMode: FilterMatchMode.CONTAINS },
  'createur.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
  mode: { value: null, matchMode: FilterMatchMode.EQUALS },
  statut: { value: null, matchMode: FilterMatchMode.EQUALS }
})

const modes = ref(['solo', 'team', 'famille'])
const statuts = ref(['en_cours', 'terminee', 'pause', 'en_attente'])

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    'environnement.nom': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'createur.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
    mode: { value: null, matchMode: FilterMatchMode.EQUALS },
    statut: { value: null, matchMode: FilterMatchMode.EQUALS }
  }
}

const confirm = useConfirm()

const getStatusClass = (statut) => {
  switch (statut) {
    case 'en_cours': return 'bg-blue-50 text-blue-700 border border-blue-100' // Info (Bleu vif)
    case 'terminee': return 'bg-emerald-50 text-emerald-700 border border-emerald-100' // Succès (Vert émeraude)
    case 'pause': return 'bg-orange-50 text-orange-700 border border-orange-100' // Avertissement (Orange #FF9500)
    case 'en_attente': return 'bg-orange-50 text-orange-700 border border-orange-100' // Avertissement (Orange #FF9500)
    default: return 'bg-gray-50 text-gray-600 border border-gray-200'
  }
}

const confirmDelete = (partie) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer la partie #${partie.id} ?`,
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.parties.destroy', partie.id))
    }
  })
}
</script>
