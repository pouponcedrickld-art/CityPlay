<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

const props = defineProps({
    requests: Array,
});

const form = useForm({});

const acceptRequest = (requestId) => {
    form.post(route('teams.requests.accept', requestId), {
        preserveScroll: true,
    });
};

const rejectRequest = (requestId) => {
    form.post(route('teams.requests.reject', requestId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <CavePlayLayout wide>
        <Head title="Demandes d'adhésion" />

        <h2 class="cave-section-title">Demandes d'adhésion</h2>

        <div v-if="requests.length" class="space-y-4 px-4">
            <div v-for="request in requests" :key="request.id" class="cave-tablet">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-[#D4C5B3] border-2 border-[#4A3525] flex items-center justify-center text-xl">
                        👤
                    </div>
                    <div>
                        <h3 class="font-bold text-[#4A3525]">{{ request.user.name }}</h3>
                        <p class="text-xs text-[#4A3525]/70">Souhaite rejoindre : <strong>{{ request.team.nom }}</strong></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button 
                        @click="acceptRequest(request.id)" 
                        class="cave-btn"
                        style="background: linear-gradient(180deg, #2ecc71, #27ae60); border-color: #1e8449;"
                        :disabled="form.processing"
                    >
                        <i class="cave-btn__icon pi pi-check" />
                        <span class="cave-btn__label">Accepter</span>
                    </button>
                    <button 
                        @click="rejectRequest(request.id)" 
                        class="cave-btn cave-btn--survival"
                        :disabled="form.processing"
                    >
                        <i class="cave-btn__icon pi pi-times" />
                        <span class="cave-btn__label">Refuser</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="cave-empty">
            <i class="pi pi-bell-slash" />
            <p class="font-bold uppercase text-sm">Aucune demande en attente</p>
        </div>

        <div class="px-4 mt-6">
            <Link :href="route('dashboard')" class="cave-btn cave-btn--survival w-full" style="text-decoration:none">
                <i class="cave-btn__icon pi pi-arrow-left" />
                <span class="cave-btn__label">Retour au camp</span>
            </Link>
        </div>
    </CavePlayLayout>
</template>
