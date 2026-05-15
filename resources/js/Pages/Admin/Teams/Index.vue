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
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-bottom border-gray-100">
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Équipe</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Créateur</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Membres</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Créée le</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="team in teams" :key="team.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-gray-900">{{ team.nom }}</div>
                  <div class="text-xs text-gray-500">{{ team.description || 'Pas de description' }}</div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ team.createur?.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ team.users_count }} / {{ team.max_joueurs }}</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ team.code_liaison }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(team.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right">
                <button 
                  @click="deleteTeam(team)"
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
  teams: Array
})

const deleteTeam = (team) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer l'équipe ${team.nom} ?`)) {
    router.delete(route('admin.teams.destroy', team.id))
  }
}
</script>
