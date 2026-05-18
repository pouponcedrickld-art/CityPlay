<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

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
    <Head title="Créer un environnement" />
    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-white font-gaming uppercase tracking-widest">Environnements / Créer</span>
        </template>

        <div class="max-w-4xl mx-auto p-8">
            <h1 class="text-3xl font-bold mb-8 text-white font-gaming uppercase tracking-wider">Créer un <span class="text-primary-500">environnement</span></h1>

            <form @submit.prevent="submit" class="bg-dark-surface p-10 rounded-2xl border border-dark-border shadow-2xl space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Nom</label>
                        <input v-model="form.nom" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Ville ID</label>
                        <input v-model="form.ville_id" type="number" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Rétention profils (jours)</label>
                        <input v-model="form.retention_profils_jours" type="number" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Durée vie lien (heures)</label>
                        <input v-model="form.duree_vie_lien_heures" type="number" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Règles</label>
                    <textarea v-model="form.regles" rows="4" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Message bonne réponse</label>
                        <input v-model="form.message_bonne_reponse" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Message mauvaise réponse</label>
                        <input v-model="form.message_mauvaise_reponse" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Message fin</label>
                        <input v-model="form.message_fin" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Message pause</label>
                        <input v-model="form.message_pause" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-white/5">
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-black font-bold font-gaming uppercase tracking-widest px-8 py-3 rounded-lg shadow-[0_0_15px_rgba(255,149,0,0.3)] transition-all">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
