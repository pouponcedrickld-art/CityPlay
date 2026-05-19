<script setup>
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ parametres: Object });
</script>

<template>
    <Head title="Paramètres" />
    
    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-gray-500">Gestion / Paramètres</span>
        </template>

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4 text-gray-800">Paramètres</h1>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p class="text-gray-600">Configuration globale de l'application.</p>
                <!-- Ajoutez ici les formulaires de paramètres si nécessaire -->
            </div>
        </div>
    </AdminLayout>
</template>
