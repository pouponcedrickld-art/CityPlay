<template>
  <AdminLayout>
    <template #breadcrumb>
      <span class="text-sm text-gray-500">Gestion / Utilisateurs</span>
    </template>

    <div class="users-page">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Utilisateurs</h1>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <DataTable
          v-model:filters="filters"
          :value="users"
          :paginator="true"
          :rows="10"
          dataKey="id"
          responsiveLayout="scroll"
          stripedRows
          class="p-datatable-sm"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} utilisateurs"
          :globalFilterFields="['name', 'pseudo', 'email']"
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
              </div>
            </template>
          </Column>

          <Column field="email" header="Email" sortable></Column>

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
              />
            </template>
          </Column>

          <Column header="Inscrit le" sortable field="created_at">
            <template #body="{ data }">
              {{ new Date(data.created_at).toLocaleDateString() }}
            </template>
          </Column>

          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-end gap-2">
                <Button
                  icon="pi pi-shield"
                  text
                  rounded
                  severity="secondary"
                  @click="confirmToggleAdmin(data)"
                  v-tooltip.top="'Changer statut Admin'"
                />
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
import { useConfirm } from 'primevue/useconfirm'
import { FilterMatchMode } from '@primevue/core/api'

defineProps({
  users: Array
})

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  pseudo: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  email: { value: null, matchMode: FilterMatchMode.CONTAINS },
  is_admin: { value: null, matchMode: FilterMatchMode.EQUALS }
})

const roleOptions = ref([
  { label: 'Admin', value: true },
  { label: 'Joueur', value: false }
])

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    pseudo: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    is_admin: { value: null, matchMode: FilterMatchMode.EQUALS }
  }
}

const confirm = useConfirm()

const confirmToggleAdmin = (user) => {
  confirm.require({
    message: `Voulez-vous vraiment changer le statut administrateur de ${user.name} ?`,
    header: 'Confirmation de rôle',
    icon: 'pi pi-shield',
    acceptLabel: 'Confirmer',
    rejectLabel: 'Annuler',
    accept: () => {
      router.post(route('admin.users.toggle-admin', user.id))
    }
  })
}

const confirmDelete = (user) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ? Cette action est irréversible.`,
    header: 'Suppression définitive',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(route('admin.users.destroy', user.id))
    }
  })
}
</script>
