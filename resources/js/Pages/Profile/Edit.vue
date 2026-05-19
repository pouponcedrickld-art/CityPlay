<script setup>
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

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

            <section class="cave-profile-section cave-profile-section--logout">
                <h3 class="cave-profile-section__title">Session</h3>
                <p class="cave-profile-section__desc">
                    Quitter votre compte sur cet appareil.
                </p>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="cave-btn cave-btn--ghost cave-btn--sm"
                >
                    <i class="cave-btn__icon pi pi-sign-out" />
                    <span class="cave-btn__label">Déconnexion</span>
                </Link>
            </section>
        </div>
    </CavePlayLayout>
</template>
