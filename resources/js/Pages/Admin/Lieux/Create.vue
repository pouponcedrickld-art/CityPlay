<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ environnement: Object });

const form = useForm({
    nom: '',
    ordre: 1,
    latitude: '',
    longitude: '',
    rayon_metres: 50,
    description: '',
    environnement_id: props.environnement.id,
});

const submit = () => {
    form.post(route('admin.lieux.store', props.environnement.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Créer un lieu</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div><label>Nom</label><input v-model="form.nom" class="w-full border rounded p-2"/></div>
                <div><label>Ordre</label><input v-model="form.ordre" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Latitude</label><input v-model="form.latitude" type="number" step="any" class="w-full border rounded p-2"/></div>
                <div><label>Longitude</label><input v-model="form.longitude" type="number" step="any" class="w-full border rounded p-2"/></div>
                <div><label>Rayon (mètres)</label><input v-model="form.rayon_metres" type="number" class="w-full border rounded p-2"/></div>
                <div><label>Description</label><textarea v-model="form.description" class="w-full border rounded p-2"></textarea></div>
                <button type="submit" class="bg-[#FF9500] hover:bg-[#E08400] text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-md active:scale-95 duration-200">Créer</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
