<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="cave-profile-section">
        <h3 class="cave-profile-section__title">Mot de passe</h3>
        <p class="cave-profile-section__desc">
            Utilisez un mot de passe long et unique pour sécuriser votre compte.
        </p>

        <form class="space-y-1" @submit.prevent="updatePassword">
            <div class="cave-field">
                <label for="current_password" class="cave-field-label">Mot de passe actuel</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="cave-field-input"
                    autocomplete="current-password"
                />
                <span v-if="form.errors.current_password" class="cave-field-error">{{ form.errors.current_password }}</span>
            </div>

            <div class="cave-field">
                <label for="password" class="cave-field-label">Nouveau mot de passe</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="cave-field-input"
                    autocomplete="new-password"
                />
                <span v-if="form.errors.password" class="cave-field-error">{{ form.errors.password }}</span>
            </div>

            <div class="cave-field">
                <label for="password_confirmation" class="cave-field-label">Confirmer le mot de passe</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="cave-field-input"
                    autocomplete="new-password"
                />
                <span v-if="form.errors.password_confirmation" class="cave-field-error">{{ form.errors.password_confirmation }}</span>
            </div>

            <div class="cave-profile-actions">
                <button type="submit" class="cave-btn cave-btn--sm" :disabled="form.processing">
                    <i class="cave-btn__icon pi pi-lock" />
                    <span class="cave-btn__label">Mettre à jour</span>
                </button>
                <p v-if="form.recentlySuccessful" class="cave-profile-success">Mot de passe mis à jour !</p>
            </div>
        </form>
    </section>
</template>
