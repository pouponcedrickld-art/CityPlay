<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="cave-profile-section">
        <h3 class="cave-profile-section__title">Supprimer le compte</h3>
        <p class="cave-profile-section__desc">
            Cette action est définitive. Toutes vos données seront supprimées.
        </p>

        <button type="button" class="cave-btn cave-btn--danger cave-btn--sm" @click="confirmUserDeletion">
            <i class="cave-btn__icon pi pi-trash" />
            <span class="cave-btn__label">Supprimer mon compte</span>
        </button>

        <Modal variant="cave" :show="confirmingUserDeletion" max-width="md" @close="closeModal">
            <div class="cave-profile-modal p-6">
                <h3 class="cave-profile-section__title">Confirmer la suppression</h3>
                <p class="cave-profile-section__desc" style="margin-bottom:16px">
                    Entrez votre mot de passe pour confirmer la suppression définitive de votre compte.
                </p>

                <div class="cave-field">
                    <label for="delete-password" class="cave-field-label">Mot de passe</label>
                    <input
                        id="delete-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="cave-field-input"
                        placeholder="••••••••"
                        @keyup.enter="deleteUser"
                    />
                    <span v-if="form.errors.password" class="cave-field-error">{{ form.errors.password }}</span>
                </div>

                <div class="cave-profile-actions" style="margin-top:20px">
                    <button type="button" class="cave-btn cave-btn--ghost flex-1" @click="closeModal">
                        Annuler
                    </button>
                    <button
                        type="button"
                        class="cave-btn cave-btn--danger flex-1"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
