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

        <div class="flex flex-col items-center justify-center gap-10 w-full max-w-md mx-auto py-8 text-center relative z-20">
            <!-- PARTIE 1 : INFOS -->
            <div class="flex flex-col items-center">
                <div class="w-20 h-20 bg-[#fff5e6] rounded-3xl flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(255,149,0,0.1)]">
                    <i class="pi pi-shield text-3xl text-[#FF9500]"></i>
                </div>
                <h1 class="text-3xl font-black tracking-tighter uppercase mb-2 text-white">
                    Vérification <br> <span class="text-[#FF9500]">OTP</span>
                </h1>
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest leading-relaxed max-w-[280px]">
                    Un code de vérification à 6 chiffres a été envoyé à votre adresse email.
                </p>
            </div>

            <!-- PARTIE 2 : FORMULAIRE -->
            <div class="w-full flex flex-col items-center gap-6">
                <form @submit.prevent="submit" class="w-full flex flex-col items-center gap-6">
                    <div class="flex flex-col items-center w-full">
                        <InputOtp
                            v-model="form.code"
                            :length="6"
                            integerOnly
                            class="custom-otp-input"
                        />
                        <InputError class="mt-4" :message="form.errors.code" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 rounded-2xl text-xs font-black uppercase tracking-[0.3em] bg-[#9e5200] text-white/80 border border-white/5 shadow-[0_0_40px_rgba(0,0,0,0.3)] hover:bg-[#FF7B00] hover:text-white hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50"
                        :disabled="form.processing || form.code.length !== 6"
                    >
                        Vérifier le code
                    </button>
                </form>

                <button
                    type="button"
                    @click="resendCode"
                    class="text-[9px] font-black text-orange-500/80 hover:text-orange-400 uppercase tracking-[0.4em] transition-all flex items-center gap-2 group"
                    :disabled="form.processing"
                >
                    <i class="pi pi-refresh group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': form.processing }"></i>
                    Renvoyer un code
                </button>
            </div>

            <!-- PARTIE 3 : FALLBACK -->
            <div class="flex flex-col items-center gap-4 pt-4 border-t border-white/5 w-full">
                <p class="text-[9px] text-gray-500 font-black uppercase tracking-[0.3em]">Vous ne recevez rien ?</p>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="group flex items-center gap-3 px-8 py-3 bg-white rounded-2xl text-[#FF7B00] hover:bg-[#fff5e6] transition-all shadow-[0_10px_30px_rgba(0,0,0,0.3)]"
                >
                    <i class="pi pi-user-plus text-[10px]"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Retourner à l'inscription</span>
                </Link>

                <p class="text-[8px] text-gray-600 font-bold uppercase italic tracking-wider">
                    Utile si vous avez fait une faute dans votre adresse email
                </p>

                <div class="mt-2">
                    <p class="text-[8px] uppercase font-black tracking-[0.5em] text-white/5">
                        CityPlay Secure
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.custom-otp-input :deep(.p-inputotp-input) {
    width: 3.5rem;
    height: 4.5rem;
    font-size: 1.5rem;
    font-weight: 900;
    border-radius: 12px;
    border: 1px solid rgba(255, 149, 0, 0.2);
    background: #0d0c0a;
    color: #FF9500;
    transition: all 0.3s ease;
    text-align: center;
}

.custom-otp-input :deep(.p-inputotp-input:focus) {
    border-color: #FF9500;
    box-shadow: 0 0 20px rgba(255, 149, 0, 0.15);
    background: #1a1814;
}

@media (max-width: 1024px) {
    .custom-otp-input :deep(.p-inputotp-input) {
        width: 3rem;
        height: 4rem;
    }
}

@media (max-width: 640px) {
    .custom-otp-input :deep(.p-inputotp-input) {
        width: 2.2rem;
        height: 3.2rem;
        font-size: 1.2rem;
        border-radius: 8px;
    }
}
</style>
