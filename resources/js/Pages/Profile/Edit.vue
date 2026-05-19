<script setup>
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;
</script>

<template>
    <CavePlayLayout wide>
        <Head title="Mon profil" />

        <h2 class="cave-section-title">Mon profil</h2>
        <p class="cave-hint text-center mb-6">
            {{ user.name }} · {{ user.email }}
        </p>

        <div class="cave-profile-stack">
            <UpdateProfileInformationForm
                :must-verify-email="mustVerifyEmail"
                :status="status"
            />
            <UpdatePasswordForm />
            <DeleteUserForm />
        </div>
    </CavePlayLayout>
</template>
