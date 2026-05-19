<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ lieu: Object, environnement: Object });

const form = useForm({
    nom: props.lieu.nom,
    ordre: props.lieu.ordre,
    latitude: props.lieu.latitude,
    longitude: props.lieu.longitude,
    rayon_metres: props.lieu.rayon_metres,
    description: props.lieu.description,
});

const submit = () => {
    form.put(route('admin.lieux.update', [props.environnement.id, props.lieu.id]));
};
</script>

<template>
<<<<<<< HEAD
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Modifier le lieu</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div><label>Nom</label><input v-model="form.nom" class="w-full border rounded p-2"/></div>
                <div><label>Ordre</label><input v-model="form.ordre" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Latitude</label><input v-model="form.latitude" type="number" step="any" class="w-full border rounded p-2"/></div>
                <div><label>Longitude</label><input v-model="form.longitude" type="number" step="any" class="w-full border rounded p-2"/></div>
                <div><label>Rayon (mètres)</label><input v-model="form.rayon_metres" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Description</label><textarea v-model="form.description" class="w-full border rounded p-2"></textarea></div>
                <button type="submit" class="bg-[#FF9500] hover:bg-[#E08400] text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-md active:scale-95 duration-200">Modifier</button>
=======
    <Head title="Modifier le lieu" />
    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-white font-gaming uppercase tracking-widest">Lieux / Modifier</span>
        </template>

        <div class="max-w-4xl mx-auto p-8">
            <h1 class="text-3xl font-bold mb-8 text-white font-gaming uppercase tracking-wider">Modifier l' <span class="text-primary-500">Étape</span></h1>
            
            <form @submit.prevent="submit" class="bg-dark-surface p-10 rounded-2xl border border-dark-border shadow-2xl space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Nom du lieu</label>
                        <input v-model="form.nom" type="text" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Ordre de passage</label>
                        <input v-model="form.ordre" type="number" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Latitude</label>
                        <input v-model="form.latitude" type="number" step="any" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Longitude</label>
                        <input v-model="form.longitude" type="number" step="any" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Rayon (mètres)</label>
                        <input v-model="form.rayon_metres" type="number" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Description de l'étape</label>
                    <textarea v-model="form.description" rows="4" class="bg-white/5 border border-dark-border text-white rounded-lg px-4 py-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/50 outline-none transition-all resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-6 border-t border-white/5">
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-black font-bold font-gaming uppercase tracking-widest px-8 py-3 rounded-lg shadow-[0_0_15px_rgba(255,149,0,0.3)] transition-all">
                        Mettre à jour l'étape
                    </button>
                </div>
>>>>>>> 5fbddc62cb3d8326a80c2472f9df79b62f006164
            </form>
        </div>
    </AdminLayout>
</template>
