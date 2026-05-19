<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="cave-profile-section">
        <h3 class="cave-profile-section__title">Informations personnelles</h3>
        <p class="cave-profile-section__desc">
            Mettez à jour votre nom et votre adresse e-mail.
        </p>

        <form class="space-y-1" @submit.prevent="form.patch(route('profile.update'))">
            <div class="cave-field">
                <label for="name" class="cave-field-label">Nom</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="cave-field-input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <span v-if="form.errors.name" class="cave-field-error">{{ form.errors.name }}</span>
            </div>

            <div class="cave-field">
                <label for="email" class="cave-field-label">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="cave-field-input"
                    required
                    autocomplete="username"
                />
                <span v-if="form.errors.email" class="cave-field-error">{{ form.errors.email }}</span>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="mb-4">
                <p class="cave-hint" style="text-align:left">
                    Votre e-mail n'est pas vérifié.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="cave-profile-link"
                    >
                        Renvoyer l'e-mail de vérification
                    </Link>
                </p>
                <p v-show="status === 'verification-link-sent'" class="cave-profile-verify-ok">
                    Un nouveau lien a été envoyé.
                </p>
            </div>

            <div class="cave-profile-actions">
                <button type="submit" class="cave-btn cave-btn--sm" :disabled="form.processing">
                    <i class="cave-btn__icon pi pi-save" />
                    <span class="cave-btn__label">Enregistrer</span>
                </button>
                <p v-if="form.recentlySuccessful" class="cave-profile-success">Enregistré !</p>
            </div>
        </form>
    </section>
</template>
