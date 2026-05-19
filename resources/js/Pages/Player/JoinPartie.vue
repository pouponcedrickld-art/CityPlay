<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CavePlayLayout from '@/Layouts/CavePlayLayout.vue';

const props = defineProps({
    partie: Object,
});

const copiedLink = ref(false);
const copiedCode = ref(false);

const invitationLink = props.partie.lien_invitation || props.partie.lien_partage;
const invitationCode = props.partie.code_invitation || props.partie.code_liaison;

const copyToClipboard = async (text, type) => {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }
    if (type === 'link') {
        copiedLink.value = true;
        setTimeout(() => { copiedLink.value = false; }, 2500);
    } else {
        copiedCode.value = true;
        setTimeout(() => { copiedCode.value = false; }, 2500);
    }
};

const selectRole = (role) => {
    router.post(route('parties.update-role', props.partie.id), { role }, {
        onSuccess: () => router.get(route('progression.enigme', props.partie.id)),
    });
};
</script>

<template>
    <CavePlayLayout wide hide-logo>
        <Head title="Configuration Équipe" />

        <h2 class="cave-section-title">Choisissez votre rôle</h2>
        <p class="cave-hint text-center mb-5">
            {{ partie.environnement?.nom }} · {{ partie.nb_membres || 1 }}/{{ partie.parametres?.nb_joueurs || 10 }} joueurs
        </p>

        <div class="cave-team-setup">
            <button type="button" class="cave-role-card cave-role-card--challenger" @click="selectRole('challenger')">
                <div class="cave-role-card__icon"><i class="pi pi-bolt" /></div>
                <h3 class="cave-role-card__title">Challenger</h3>
                <p class="text-xs opacity-80 italic max-w-xs">Dominez le classement et visez la victoire.</p>
            </button>

            <button type="button" class="cave-role-card cave-role-card--participant" @click="selectRole('participant')">
                <div class="cave-role-card__icon"><i class="pi pi-users" /></div>
                <h3 class="cave-role-card__title">Participant</h3>
                <p class="text-xs opacity-80 italic max-w-xs">Jouez en équipe et partagez l'aventure.</p>
            </button>

            <div class="cave-team-invite-bar cave-tablet">
                <p class="cave-section-title" style="font-size:0.75rem;margin-bottom:12px">Invitez vos coéquipiers</p>

                <div class="cave-invite-block" style="margin-top:0">
                    <p class="text-[9px] font-bold uppercase"><i class="pi pi-link" /> Lien — sans compte</p>
                    <div class="cave-invite-row">
                        <span class="text-[10px] truncate flex-1 opacity-80">{{ invitationLink }}</span>
                        <button type="button" class="cave-copy-btn" @click.stop="copyToClipboard(invitationLink, 'link')">
                            <i :class="copiedLink ? 'pi pi-check' : 'pi pi-copy'" />
                        </button>
                    </div>
                </div>

                <div class="cave-invite-block">
                    <p class="text-[9px] font-bold uppercase"><i class="pi pi-key" /> Code — compte existant</p>
                    <div class="cave-invite-row">
                        <span class="cave-invite-code">{{ invitationCode }}</span>
                        <button type="button" class="cave-copy-btn" @click.stop="copyToClipboard(invitationCode, 'code')">
                            <i :class="copiedCode ? 'pi pi-check' : 'pi pi-copy'" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CavePlayLayout>
</template>
