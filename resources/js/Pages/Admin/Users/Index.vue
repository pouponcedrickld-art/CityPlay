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
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-bottom border-gray-100">
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Inscrit le</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                    {{ user.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-xs text-gray-500">@{{ user.pseudo }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
              <td class="px-6 py-4">
                <span :class="user.is_admin ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'" 
                      class="px-2 py-1 rounded-full text-xs font-semibold">
                  {{ user.is_admin ? 'Admin' : 'Joueur' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(user.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button 
                    @click="toggleAdmin(user)"
                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                    title="Changer statut Admin"
                  >
                    <i class="pi pi-shield"></i>
                  </button>
                  <button 
                    @click="deleteUser(user)"
                    class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                    title="Supprimer"
                  >
                    <i class="pi pi-trash"></i>
                  </button>
                </div>
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
  users: Array
})

const toggleAdmin = (user) => {
  if (confirm(`Voulez-vous vraiment changer le statut administrateur de ${user.name} ?`)) {
    router.post(route('admin.users.toggle-admin', user.id))
  }
}

const deleteUser = (user) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ? Cette action est irréversible.`)) {
    router.delete(route('admin.users.destroy', user.id))
  }
}
</script>
