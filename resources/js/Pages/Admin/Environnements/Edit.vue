<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ environnement: Object });

const form = useForm({
    nom: props.environnement.nom,
    ville_id: props.environnement.ville_id,
    retention_profils_jours: props.environnement.retention_profils_jours,
    duree_vie_lien_heures: props.environnement.duree_vie_lien_heures,
    regles: props.environnement.regles,
    message_bonne_reponse: props.environnement.message_bonne_reponse,
    message_mauvaise_reponse: props.environnement.message_mauvaise_reponse,
    message_fin: props.environnement.message_fin,
    message_pause: props.environnement.message_pause,
});

const submit = () => {
    form.put(route('admin.environnements.update', props.environnement.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Modifier l'environnement</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div><label>Nom</label><input v-model="form.nom" class="w-full border rounded p-2"/></div>
                <div><label>Ville ID</label><input v-model="form.ville_id" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Rétention (jours)</label><input v-model="form.retention_profils_jours" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Durée vie lien (h)</label><input v-model="form.duree_vie_lien_heures" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Règles</label><textarea v-model="form.regles" class="w-full border rounded p-2"></textarea></div>
                <div><label>Message bonne réponse</label><input v-model="form.message_bonne_reponse" class="w-full border rounded p-2"/></div>
                <div><label>Message mauvaise réponse</label><input v-model="form.message_mauvaise_reponse" class="w-full border rounded p-2"/></div>
                <div><label>Message fin</label><input v-model="form.message_fin" class="w-full border rounded p-2"/></div>
                <div><label>Message pause</label><input v-model="form.message_pause" class="w-full border rounded p-2"/></div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Modifier</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
