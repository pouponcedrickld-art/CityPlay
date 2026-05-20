<script setup>
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import InputOtp from 'primevue/inputotp';
import InputError from '@/Components/InputError.vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

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
    if (page.props.flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: page.props.flash.error, life: 4000 });
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
    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 font-sans">
        <Head title="Vérification de sécurité" />
        <Toast />

        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <!-- Header épuré -->
            <div class="bg-slate-900 p-8 text-center">
                <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/20">
                    <i class="pi pi-shield text-2xl text-white"></i>
                </div>
                <h1 class="text-xl font-bold text-white tracking-tight">Vérification de sécurité</h1>
                <p class="text-slate-400 text-sm mt-2">Code OTP requis pour continuer</p>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Pour sécuriser votre accès, un code de validation à 6 chiffres a été envoyé à votre adresse email.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Saisie OTP standard -->
                    <div class="flex flex-col items-center">
                        <InputOtp
                            v-model="form.code"
                            :length="6"
                            integerOnly
                            class="otp-standard-input"
                        />
                        <InputError class="mt-4" :message="form.errors.code" />
                    </div>

                    <!-- Actions -->
                    <div class="space-y-4">
                        <button
                            type="submit"
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 disabled:opacity-50"
                            :disabled="form.processing || form.code.length !== 6"
                        >
                            <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                            <span>Valider mon accès</span>
                        </button>

                        <button
                            type="button"
                            @click="resendCode"
                            class="w-full py-3 text-slate-500 hover:text-blue-600 text-xs font-bold uppercase tracking-widest transition-colors flex items-center justify-center gap-2"
                            :disabled="form.processing"
                        >
                            <i class="pi pi-refresh" :class="{ 'animate-spin': form.processing }"></i>
                            Renvoyer le code
                        </button>
                    </div>
                </form>

                <div class="mt-10 pt-8 border-t border-slate-100">
                    <div class="flex flex-col items-center gap-4 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Un problème ?</p>
                        <Link
                            :href="route('logout.register')"
                            method="post"
                            as="button"
                            class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors underline decoration-2 underline-offset-4"
                        >
                            Retourner à l'accueil
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-8 text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em]">
            CityPlay Protection System
        </p>
    </div>
</template>

<style scoped>
.otp-standard-input :deep(.p-inputotp-input) {
    width: 3rem;
    height: 4rem;
    font-size: 1.5rem;
    font-weight: 700;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    background: white;
    color: #1e293b;
    transition: all 0.2s ease;
    text-align: center;
}

.otp-standard-input :deep(.p-inputotp-input:focus) {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    outline: none;
}

@media (max-width: 640px) {
    .otp-standard-input :deep(.p-inputotp-input) {
        width: 2.5rem;
        height: 3.5rem;
    }
}
</style>
