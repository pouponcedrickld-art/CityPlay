<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import CaveGameLayout from "@/Layouts/CaveGameLayout.vue";
import { onMounted, onUnmounted } from "vue";

const props = defineProps({
    partie: Object,
});

const page = usePage();

const tryAgain = () => {
    router.get(route("progression.enigme", props.partie.id));
};

const skip = () => {
    router.post(route("progression.next", props.partie.id));
};

onMounted(() => {
    // Écoute sur le canal privé de la partie pour synchroniser si un coéquipier passe l'énigme
    window.Echo.private(`partie.${props.partie.id}`).listen(
        ".EnigmeResolue",
        (e) => {
            console.log("Progression mise à jour via WebSocket (Failure):", e);

            // Si l'action a été faite par quelqu'un d'autre
            if (e.resolu_par.id !== page.props.auth.user.id) {
                if (e.lieu_id) {
                    // Si résolue, on montre le succès
                    router.visit(
                        route("progression.success", {
                            partie: props.partie.id,
                            lieu: e.lieu_id,
                        }),
                    );
                } else {
                    // Sinon (passée), on va à la suivante
                    router.visit(route("progression.enigme", props.partie.id), {
                        replace: true,
                    });
                }
            }
        },
    );
});

onUnmounted(() => {
    window.Echo.leave(`partie.${props.partie.id}`);
});
</script>

<template>
    <CaveGameLayout>

        <Head title="Échec de mission" />

        <div class="cave-result-screen">
            <div class="cave-result-card cave-result-card--shake relative z-10">
                <div class="cave-result-icon cave-result-icon--fail">
                    <i class="pi pi-times" />
                </div>
                <h1 class="cave-result-title">Mission compromise</h1>
                <p class="cave-result-sub" style="color: var(--cave-btn-survival)">
                    Code erroné
                </p>
                <p class="cave-result-message">
                    "{{
                        partie.environnement?.message_mauvaise_reponse ||
                        `Ce n'est pas la bonne réponse. Reprenez vos
                    esprits, l'indice est peut-être juste sous vos yeux.`
                    }}"
                </p>

                <div class="cave-btn-stack">
                    <button type="button" class="cave-btn cave-btn--danger" @click="tryAgain">
                        <i class="cave-btn__icon pi pi-refresh" />
                        <span class="cave-btn__label">Réessayer</span>
                    </button>
                    <button type="button" class="cave-btn cave-btn--ghost" @click="skip">
                        <span class="cave-btn__label">Passer l'énigme</span>
                    </button>
                </div>
                <p class="cave-hint mt-4">
                    Chaque erreur vous rapproche du but
                </p>
            </div>
        </div>
    </CaveGameLayout>
</template>
