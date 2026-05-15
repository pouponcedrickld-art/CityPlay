<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
                        J'accepte les <Link :href="route('legal.cgu')" class="underline hover:text-gray-900" target="_blank">Conditions Générales d'Utilisation</Link>
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.consent_cgu" />
            </div>

            <!-- Accept Privacy Policy -->
            <div class="mt-4">
                <label class="flex items-center">
                    <Checkbox name="consent_donnees" v-model:checked="form.consent_donnees" required />
                    <span class="ms-2 text-sm text-gray-600">
                        J'accepte la <Link :href="route('legal.privacy')" class="underline hover:text-gray-900" target="_blank">Politique de Confidentialité</Link> et le traitement de mes données
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.consent_donnees" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
