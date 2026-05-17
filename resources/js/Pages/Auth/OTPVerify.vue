<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputOtp from 'primevue/inputotp';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const page = usePage();

const form = useForm({
    code: '',
});

onMounted(() => {
    if (page.props.flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: page.props.flash.success, life: 3000 });
    }
    if (page.props.flash?.info) {
        toast.add({ severity: 'info', summary: 'Information', detail: page.props.flash.info, life: 3000 });
    }
});

const submit = () => {
    form.post(route('otp.verify'), {
        onFinish: () => form.reset('code'),
    });
};

const resendCode = () => {
    form.post(route('otp.resend'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'info', summary: 'Code envoyé', detail: 'Un nouveau code OTP vous a été envoyé.', life: 3000 });
        }
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Vérification OTP" />

        <div class="mb-8 text-center">
            <div class="w-20 h-20 bg-orange-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                <i class="pi pi-shield text-3xl text-[#FF9500]"></i>
            </div>
            <h1 class="text-2xl font-black text-orange-950 uppercase tracking-tight">Vérification <span class="text-[#FF9500]">OTP</span></h1>
            <p class="mt-2 text-sm text-gray-600 font-medium">
                Un code de vérification à 6 chiffres a été envoyé à votre adresse email.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <!-- Zone de saisie du code OTP -->
            <div class="flex flex-col items-center">
                <InputOtp 
                    v-model="form.code" 
                    :length="6" 
                    integerOnly
                    class="custom-otp-input"
                />
                <!-- Affichage des erreurs de validation du code -->
                <InputError class="mt-4" :message="form.errors.code" />
            </div>

            <!-- Boutons d'action -->
            <div class="flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center py-4 text-sm font-bold uppercase tracking-widest bg-gradient-to-r from-[#FF9500] to-[#FF7B00] border-none shadow-lg shadow-orange-200"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing || form.code.length !== 6"
                >
                    Vérifier le code
                </PrimaryButton>

                <button
                    type="button"
                    @click="resendCode"
                    class="text-xs font-bold text-orange-600 hover:text-orange-700 uppercase tracking-widest transition-colors flex items-center justify-center gap-2"
                    :disabled="form.processing"
                >
                    <i class="pi pi-refresh" :class="{ 'animate-spin': form.processing }"></i>
                    Renvoyer un code
                </button>
            </div>
        </form>

        <!-- LIEN DE REDIRECTION (OUTSIDE FORM) -->
        <div class="mt-10 pt-6 border-t border-orange-100 flex flex-col items-center gap-4">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">Vous ne recevez rien ?</p>
            
            <Link
                :href="route('logout.register')"
                method="post"
                as="button"
                class="group flex items-center gap-2 px-6 py-3 bg-orange-50 rounded-2xl text-orange-600 hover:bg-orange-100 transition-all border border-orange-100"
            >
                <i class="pi pi-user-plus text-xs group-hover:-translate-x-1 transition-transform"></i>
                <span class="text-xs font-black uppercase tracking-widest">Retourner à l'inscription</span>
            </Link>
            
            <p class="text-[9px] text-gray-400 font-bold uppercase italic text-center px-4">
                Utile si vous avez fait une faute dans votre adresse email
            </p>
        </div>

        <div class="mt-8 text-center">
            <p class="text-[9px] uppercase font-black tracking-[0.4em] text-orange-900/10">
                CityPlay Secure
            </p>
        </div>
    </GuestLayout>
</template>

<style scoped>
.custom-otp-input :deep(.p-inputotp-input) {
    width: 3rem;
    height: 4rem;
    font-size: 1.5rem;
    font-weight: 900;
    border-radius: 16px;
    border: 2px solid rgba(255, 149, 0, 0.1);
    background: white;
    color: #FF9500;
    transition: all 0.3s ease;
    text-align: center;
}

.custom-otp-input :deep(.p-inputotp-input:focus) {
    border-color: #FF9500;
    box-shadow: 0 0 0 4px rgba(255, 149, 0, 0.1);
    transform: translateY(-2px);
}

@media (max-width: 640px) {
    .custom-otp-input :deep(.p-inputotp-input) {
        width: 2.5rem;
        height: 3.5rem;
        font-size: 1.25rem;
    }
}
</style>