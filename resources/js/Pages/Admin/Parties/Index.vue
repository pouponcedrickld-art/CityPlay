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
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-bottom border-gray-100">
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Environnement</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Créateur</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mode</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="partie in parties" :key="partie.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ partie.id }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ partie.environnement?.nom }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ partie.createur?.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600 uppercase">{{ partie.mode }}</td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(partie.statut)" class="px-2 py-1 rounded-full text-xs font-semibold">
                  {{ partie.statut }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(partie.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right">
                <button 
                  @click="deletePartie(partie)"
                  class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                  title="Supprimer"
                >
                  <i class="pi pi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'

defineProps({
  parties: Array
})

const getStatusClass = (statut) => {
  switch (statut) {
    case 'en_cours': return 'bg-green-100 text-green-700'
    case 'terminee': return 'bg-gray-100 text-gray-700'
    case 'pause': return 'bg-yellow-100 text-yellow-700'
    default: return 'bg-blue-100 text-blue-700'
  }
}

const deletePartie = (partie) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer la partie #${partie.id} ?`)) {
    router.delete(route('admin.parties.destroy', partie.id))
  }
}
</script>
