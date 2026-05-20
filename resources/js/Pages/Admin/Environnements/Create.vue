<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    nom: '',
    ville_id: '',
    retention_profils_jours: 90,
    duree_vie_lien_heures: 24,
    regles: '',
    message_bonne_reponse: '',
    message_mauvaise_reponse: '',
    message_fin: '',
    message_pause: '',
});

const submit = () => {
    form.post(route('admin.environnements.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Créer un environnement</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Nom</label>
                    <input v-model="form.nom" type="text" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Ville ID</label>
                    <input v-model="form.ville_id" type="number" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Rétention profils (jours)</label>
                    <input v-model="form.retention_profils_jours" type="number" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Durée vie lien (heures)</label>
                    <input v-model="form.duree_vie_lien_heures" type="number" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Règles</label>
                    <textarea v-model="form.regles" class="w-full border rounded p-2"></textarea>
                </div>
                <div>
                    <label>Message bonne réponse</label>
                    <input v-model="form.message_bonne_reponse" type="text" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Message mauvaise réponse</label>
                    <input v-model="form.message_mauvaise_reponse" type="text" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Message fin</label>
                    <input v-model="form.message_fin" type="text" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label>Message pause</label>
                    <input v-model="form.message_pause" type="text" class="w-full border rounded p-2" />
                </div>
                <button type="submit" class="bg-[#FF9500] hover:bg-[#E08400] text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-md active:scale-95 duration-200">
                    Créer
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
