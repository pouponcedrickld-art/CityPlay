<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
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
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
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
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <!-- Section des actions (Mot de passe oublié, Inscription, Connexion) -->
            <div class="mt-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <!-- Lien vers la réinitialisation du mot de passe -->
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Mot de passe oublié ?
                    </Link>

                    <!-- Bouton de soumission du formulaire -->
                    <PrimaryButton
                        class="ms-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Se connecter
                    </PrimaryButton>
                </div>
            </div>
        </form>

        <!-- LIEN VERS L'INSCRIPTION (OUTSIDE FORM) -->
        <div class="mt-10 pt-8 border-t border-orange-100 text-center space-y-4">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">Nouveau sur CityPlay ?</p>
            
            <Link
                :href="route('register')"
                class="inline-flex items-center justify-center w-full p-4 bg-white border-2 border-orange-100 rounded-2xl text-orange-600 font-black uppercase tracking-widest text-xs hover:bg-orange-50 hover:border-orange-200 transition-all shadow-sm active:scale-95"
            >
                Créer mon compte joueur
            </Link>
            
            <p class="text-[9px] text-gray-400 font-medium italic">
                Rejoignez l'aventure en quelques secondes !
            </p>
        </div>
    </GuestLayout>
</template>
