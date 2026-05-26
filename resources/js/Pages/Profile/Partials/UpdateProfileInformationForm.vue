<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
// Utilisation d'un computed pour garder la réactivité sur l'utilisateur
const user = computed(() => page.props.auth.user);

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    pseudo: user.value.pseudo || '',
    email: user.value.email,
    avatar: null,
});

const avatarPreview = ref(null);

// Mettre à jour le formulaire quand l'utilisateur change (ex: après un succès)
watch(user, (newUser) => {
    if (newUser) {
        form.name = newUser.name;
        form.pseudo = newUser.pseudo || '';
        form.email = newUser.email;
    }
}, { deep: true });

const onFileChange = (e) => {
    const file = e.target.files[0];
    form.avatar = file;
    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.avatar = null;
            avatarPreview.value = null;
            // Pas besoin de rafraîchir manuellement, Inertia s'en occupe
        },
    });
};
</script>

<template>
    <section class="cave-profile-section">
        <h3 class="cave-profile-section__title">Informations personnelles</h3>
        <p class="cave-profile-section__desc">
            Mettez à jour vos informations de profil.
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="flex flex-col items-center mb-4">
                <div class="relative">
                    <!-- Priorité à la preview locale, puis à l'avatar stocké, puis aux initiales -->
                    <div v-if="avatarPreview || user.avatar_path" 
                         class="w-24 h-24 rounded-full border-4 border-[#4A3525] overflow-hidden shadow-lg bg-white">
                        <img :key="user.avatar_path" :src="avatarPreview || user.avatar_path" class="w-full h-full object-cover" />
                    </div>
                    <div v-else 
                         class="w-24 h-24 rounded-full border-4 border-[#4A3525] bg-[#D4C5B3] flex items-center justify-center text-4xl font-bold text-[#4A3525] shadow-lg">
                        {{ user.name.charAt(0) }}
                    </div>
                    <label for="avatar" 
                           class="absolute bottom-0 right-0 w-8 h-8 bg-[#FF9500] border-2 border-[#4A3525] rounded-full flex items-center justify-center text-white shadow-md cursor-pointer hover:scale-110 transition-transform">
                        <i class="pi pi-camera text-xs" />
                        <input id="avatar" type="file" class="hidden" @change="onFileChange" accept="image/*" />
                    </label>
                </div>
                <p class="text-[10px] text-[#4A3525]/60 mt-2 uppercase font-bold">Changer d'avatar</p>
                <span v-if="form.errors.avatar" class="cave-field-error">{{ form.errors.avatar }}</span>
            </div>

            <div class="cave-field">
                <label for="name" class="cave-field-label">Nom complet</label>
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
                <label for="pseudo" class="cave-field-label">Pseudo (Gaming)</label>
                <input
                    id="pseudo"
                    v-model="form.pseudo"
                    type="text"
                    class="cave-field-input"
                    placeholder="Votre pseudo"
                    autocomplete="nickname"
                />
                <span v-if="form.errors.pseudo" class="cave-field-error">{{ form.errors.pseudo }}</span>
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
