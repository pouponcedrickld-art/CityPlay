<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const showCGU = ref(false);
const showPrivacy = ref(false);
const toast = useToast();

const props = defineProps({
    flash: Object
});

onMounted(() => {
    if (props.flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: props.flash.success, life: 3000 });
    }
    if (props.flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: props.flash.error, life: 3000 });
    }
});

const form = useForm({
    name: '',
    pseudo: '',
    email: '',
    password: '',
    password_confirmation: '',
    consent_cgu: false,
    consent_donnees: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="pseudo" value="Pseudo" />

                <TextInput
                    id="pseudo"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.pseudo"
                    required
                    autocomplete="nickname"
                />

                <InputError class="mt-2" :message="form.errors.pseudo" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Accept CGU -->
            <div class="mt-4">
                <label class="flex items-center">
                    <Checkbox name="consent_cgu" v-model:checked="form.consent_cgu" required />
                    <span class="ms-2 text-sm text-gray-600">
                        J'accepte les <button type="button" @click="showCGU = true" class="underline hover:text-gray-900">Conditions Générales d'Utilisation</button>
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.consent_cgu" />
            </div>

            <!-- Accept Privacy Policy -->
            <div class="mt-4">
                <label class="flex items-center">
                    <Checkbox name="consent_donnees" v-model:checked="form.consent_donnees" required />
                    <span class="ms-2 text-sm text-gray-600">
                        J'accepte la <button type="button" @click="showPrivacy = true" class="underline hover:text-gray-900">Politique de Confidentialité</button> et le traitement de mes données
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.consent_donnees" />
            </div>

            <!-- Modal CGU -->
            <Dialog v-model:visible="showCGU" modal header="Conditions Générales d'Utilisation" :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class="legal-dialog">
                <div class="legal-content prose prose-sm max-w-none p-6 rounded-2xl">
                    <h3 class="text-orange-600 font-bold uppercase">1. Présentation du service</h3>
                    <p>CityPlay est une plateforme de chasses au trésor et d'énigmes urbaines permettant aux utilisateurs de découvrir des lieux d'intérêt tout en s'amusant.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">2. Utilisation du service</h3>
                    <p>L'utilisateur s'engage à utiliser le service de manière respectueuse des lieux publics et des autres utilisateurs. Toute utilisation abusive pourra entraîner la suspension du compte.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">3. Responsabilité</h3>
                    <p>CityPlay ne pourra être tenu responsable des accidents ou dommages survenant lors de la pratique des activités proposées sur la plateforme.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">4. Propriété intellectuelle</h3>
                    <p>Le contenu de CityPlay (énigmes, textes, images, logo) est protégé par le droit d'auteur.</p>
                </div>
                <template #footer>
                    <Button label="J'ai compris" icon="pi pi-check" @click="showCGU = false" class="p-button-orange" autofocus />
                </template>
            </Dialog>

            <!-- Modal Privacy -->
            <Dialog v-model:visible="showPrivacy" modal header="Politique de Confidentialité" :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class="legal-dialog">
                <div class="legal-content prose prose-sm max-w-none p-6 rounded-2xl">
                    <h3 class="text-orange-600 font-bold uppercase">1. Collecte des données</h3>
                    <p>Nous collectons les informations nécessaires à votre inscription (nom, pseudo, email) ainsi que vos données de progression dans les jeux.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">2. Utilisation des données</h3>
                    <p>Vos données sont utilisées pour gérer votre compte, suivre vos performances et améliorer l'expérience de jeu sur CityPlay.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">3. Géolocalisation</h3>
                    <p>Le service nécessite l'accès à votre position GPS pour valider votre présence sur les lieux des énigmes.</p>
                    
                    <h3 class="text-orange-600 font-bold uppercase">4. Vos droits</h3>
                    <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles.</p>
                </div>
                <template #footer>
                    <Button label="J'ai compris" icon="pi pi-check" @click="showPrivacy = false" class="p-button-orange" autofocus />
                </template>
            </Dialog>

            <!-- Actions : Déjà inscrit ? / Créer mon compte -->
            <div class="mt-8 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <!-- Lien vers la page de connexion -->
                    <Link
                        :href="route('login')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                    >
                        Déjà inscrit ?
                    </Link>

                    <!-- Bouton de validation de l'inscription -->
                    <PrimaryButton
                        class="ms-4 px-8 py-4 bg-gradient-to-r from-[#FF9500] to-[#FF7B00] border-none font-black uppercase tracking-widest text-xs shadow-lg shadow-orange-200"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Créer mon compte
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
/* Personnalisation de l'overlay (fond derrière le modal) */
:deep(.p-dialog-mask) {
    background-color: rgba(0, 0, 0, 0.45) !important; /* Assombrit légèrement l'arrière-plan */
    backdrop-filter: blur(3px); /* Ajoute un léger flou pour faire ressortir le modal */
}

/* Personnalisation du conteneur du modal */
:deep(.p-dialog) {
    border-radius: 24px !important;
    border: none !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    overflow: hidden;
}

:deep(.p-dialog-header) {
    background: white !important;
    padding: 1.5rem 1.5rem 0.5rem 1.5rem !important;
}

:deep(.p-dialog-content) {
    background: white !important;
    padding: 0 1.5rem 1rem 1.5rem !important;
}

:deep(.p-dialog-footer) {
    background: white !important;
    padding: 1rem 1.5rem 1.5rem 1.5rem !important;
    border-top: 1px solid rgba(255, 149, 0, 0.05);
}

.legal-content {
    background: linear-gradient(135deg, rgba(255, 149, 0, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
    border: 1px solid rgba(255, 149, 0, 0.1);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
}

.p-button-orange {
    background: linear-gradient(135deg, #FF9500 0%, #FF7B00 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 0.6rem 1.2rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-size: 0.75rem !important;
    box-shadow: 0 4px 12px rgba(255, 149, 0, 0.2) !important;
}

.p-button-orange:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(255, 149, 0, 0.3) !important;
}
</style>
