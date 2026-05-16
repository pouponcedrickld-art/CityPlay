<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Paramètres" />

    <SidebarLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-orange-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Paramètres du système</h3>
                    <p class="text-gray-600">
                        Cette page permettra de configurer les options globales de CityPlay (durée de conservation des données, rayons de validation GPS, etc.).
                    </p>
                    
                    <div class="mt-6 p-4 bg-orange-50 rounded-xl border border-orange-100">
                        <p class="text-sm text-orange-800 font-medium">
                            <i class="pi pi-info-circle mr-2"></i>
                            Fonctionnalité en cours de développement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
