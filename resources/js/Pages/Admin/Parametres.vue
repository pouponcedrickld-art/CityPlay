<script setup>
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ parametres: Object });
</script>

<template>
    <Head title="Paramètres" />
    
    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-white font-gaming uppercase tracking-widest">Gestion / Paramètres</span>
        </template>

        <div class="p-8">
            <h1 class="text-3xl font-bold mb-8 text-white font-gaming uppercase tracking-wider">Paramètres</h1>
            <div class="bg-dark-surface rounded-2xl border border-dark-border p-10 shadow-2xl">
                <p class="text-gray-400 font-gaming uppercase tracking-widest text-sm mb-4">Configuration globale de l'application</p>
                <div class="h-px bg-white/5 w-full mb-6"></div>
                <p class="text-gray-500 italic">Les options de configuration avancées seront bientôt disponibles.</p>
            </div>
        </div>
    </AdminLayout>
</template>
