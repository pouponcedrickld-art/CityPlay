<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ parametres: Object });
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Paramètres</h1>
            <p>Configuration globale de l'application.</p>
        </div>
    </AuthenticatedLayout>
</template>
