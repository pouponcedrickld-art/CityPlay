<script setup>
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';

const props = defineProps({
    enigme: Object,
    lieu: Object,
    type: String,
    types: Array
});

const form = useForm({
    type: props.enigme.type,
    texte: props.enigme.texte,
    reponse: props.enigme.reponse,
    image: null,
});

const onFileChange = (e) => {
    form.image = e.target.files[0];
};

const submit = () => {
    form.post(route('admin.enigmes.update', [props.lieu.id, props.enigme.id]));
};
</script>

<template>
    <Head title="Modifier l'Énigme" />

    <AdminLayout>
        <template #breadcrumb>
            <span class="text-sm text-white font-gaming uppercase tracking-widest">Énigmes / Modifier</span>
        </template>

        <div class="max-w-4xl mx-auto p-8">
            <div class="mb-8">
                <button 
                    @click="$window.history.back()" 
                    class="flex items-center gap-2 text-gray-500 hover:text-primary-500 font-bold text-[10px] uppercase tracking-widest transition-colors mb-4"
                >
                    <i class="pi pi-arrow-left" />
                    RETOUR AU CODEX
                </button>
                <h1 class="text-3xl font-bold text-white font-gaming uppercase tracking-wider">
                    Modifier l' <span class="text-primary-500">Énigme</span>
                </h1>
                <p class="text-gray-500 font-medium text-sm mt-1 uppercase tracking-widest font-gaming">
                    DÉFI #{{ enigme.id }} POUR : <span class="text-white">{{ lieu.nom }}</span>
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-dark-surface p-10 rounded-2xl border border-dark-border shadow-2xl space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Type de défi</label>
                        <Select
                            v-model="form.type"
                            :options="types"
                            placeholder="Sélectionner la difficulté"
                            class="gaming-dropdown w-full"
                        />
                        <small class="text-red-500" v-if="form.errors.type">{{ form.errors.type }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Réponse attendue</label>
                        <InputText
                            v-model="form.reponse"
                            placeholder="Ex: LA TOUR EIFFEL"
                            class="gaming-input w-full"
                        />
                        <small class="text-red-500" v-if="form.errors.reponse">{{ form.errors.reponse }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Texte de l'énigme</label>
                    <Textarea
                        v-model="form.texte"
                        rows="5"
                        placeholder="Écrivez ici le défi que les joueurs devront résoudre..."
                        class="gaming-input w-full p-4"
                    />
                    <small class="text-red-500" v-if="form.errors.texte">{{ form.errors.texte }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold font-gaming uppercase tracking-widest text-primary-500/70">Visuel d'illustration (Optionnel)</label>
                    <div v-if="enigme.image_url" class="mb-4">
                        <p class="text-[10px] text-gray-500 uppercase mb-2">Visuel actuel :</p>
                        <img :src="`/storage/${enigme.image_url}`" class="w-48 rounded-lg border border-dark-border">
                    </div>
                    <div class="upload-zone" @click="$refs.fileInput.click()">
                        <i class="pi pi-cloud-upload text-3xl mb-2" />
                        <p class="font-gaming">REMPLACER L'IMAGE</p>
                        <span v-if="form.image" class="text-primary-500 text-xs mt-2">{{ form.image.name }}</span>
                        <input type="file" ref="fileInput" class="hidden" @change="onFileChange" accept="image/*">
                    </div>
                    <small class="text-red-500" v-if="form.errors.image">{{ form.errors.image }}</small>
                </div>

                <div class="flex justify-end pt-6 border-t border-white/5">
                    <Button type="submit" label="METTRE À JOUR LE DÉFI" class="p-button-primary shadow-glow" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.gaming-input :deep(.p-inputtext), .gaming-input :deep(.p-textarea) {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
  color: #fff !important;
}

.gaming-dropdown {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid #1f2937 !important;
}

.upload-zone {
  border: 2px dashed #1f2937;
  border-radius: 12px;
  padding: 3rem;
  text-align: center;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.01);
  transition: all 0.3s;
}

.upload-zone:hover {
  border-color: #FF9500;
  background: rgba(255, 149, 0, 0.03);
}

.shadow-glow {
  box-shadow: 0 0 20px rgba(255, 149, 0, 0.3);
}
</style>
