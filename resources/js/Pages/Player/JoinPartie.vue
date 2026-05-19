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

onMounted(() => {
    gsap.set('.gsap-header', { y: -30, opacity: 0 });
    gsap.set('.role-card', { y: 100, opacity: 0, rotationY: 45 });
    gsap.set('.code-overlay', { y: 20, opacity: 0 });

    const tl = gsap.timeline();
    tl.to('.gsap-header', { y: 0, opacity: 1, duration: 0.8, ease: 'back.out(1.5)' })
      .to('.role-card', { y: 0, opacity: 1, rotationY: 0, duration: 0.8, stagger: 0.2, ease: 'power3.out' }, '-=0.4')
      .to('.code-overlay', { y: 0, opacity: 1, duration: 0.6, ease: 'power2.out' }, '-=0.2');
});
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

                <!-- CODE OVERLAY (Like a token/plaque on the board) -->
                <div class="code-overlay bg-[#2a1c14] border-2 border-[#5c4033] p-6 rounded-2xl max-w-sm mx-auto text-center shadow-[0_10px_30px_rgba(0,0,0,0.8)] relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#1f140e] border border-[#5c4033] px-4 py-1 rounded-full card-badge text-[8px] text-[#FF9500]">
                        Sceau d'Invitation
                    </div>
                    <p class="card-badge text-[9px] text-white/40 mb-3 mt-2">Partagez ce code avec d'autres aventuriers</p>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-[#1f140e] p-3 rounded-xl border border-black text-center font-black text-2xl tracking-[0.3em] text-[#f5e8c7] shadow-inner" style="font-family: 'Share Tech Mono', monospace;">
                            {{ partie.code_liaison }}
                        </div>
                        <button @click="copyCode" class="w-14 bg-[#5c4033] text-[#f5e8c7] rounded-xl flex items-center justify-center hover:bg-[#FF9500] hover:text-[#1f140e] active:scale-95 transition-all shadow-[0_4px_0_#3f2a1e] active:translate-y-[4px] active:shadow-none">
                            <i class="pi pi-copy text-xl"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </CavePlayLayout>
</template>
