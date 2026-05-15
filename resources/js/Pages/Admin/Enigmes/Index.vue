<<<<<<< HEAD
<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="page-header">
      <div>
        <h1 class="page-title">Énigmes — {{ lieu.nom }}</h1>
        <p class="page-subtitle">{{ lieu.environnement?.ville?.nom }} · {{ lieu.environnement?.nom }}</p>
      </div>
      <div class="completion-badge" :class="completionClass">
        <i :class="completionIcon" />
        {{ completedCount }}/4 énigmes renseignées
      </div>
    </div>

    <!-- Convention difficulté -->
    <Message severity="info" :closable="false" class="convention-msg">
      <strong>Convention difficulté :</strong> Force 3 = plus difficile (énigme cryptique) ·
      Force 2 = intermédiaire · Force 1 = facile (avec indices directs) · Enfant = adapté aux enfants
    </Message>

    <!-- Cartes des 4 énigmes -->
    <div class="enigmes-grid">
      <div v-for="(typeLabel, typeKey) in types" :key="typeKey" class="enigme-card"
        :class="{ 'has-content': enigmes[typeKey], 'missing': !enigmes[typeKey] }">

        <!-- Header carte -->
        <div class="card-header">
          <div class="type-badge" :class="`type-${typeKey}`">
            {{ typeLabel }}
          </div>
          <Tag v-if="enigmes[typeKey]" value="Renseignée" severity="success" />
          <Tag v-else value="Manquante" severity="warn" />
        </div>

        <!-- Contenu si existant -->
        <div v-if="enigmes[typeKey]" class="card-content">
          <p class="enigme-texte">{{ enigmes[typeKey].texte }}</p>
          <div v-if="enigmes[typeKey].image_url" class="enigme-image">
            <img :src="`/storage/${enigmes[typeKey].image_url}`" alt="Image énigme" />
          </div>
          <div v-else class="no-image">
            <i class="pi pi-image" /> Pas d'image
          </div>
        </div>

        <div v-else class="card-empty">
          <i class="pi pi-lightbulb" />
          <p>Cette énigme n'est pas encore renseignée.</p>
        </div>

        <!-- Actions -->
        <div class="card-footer">
          <Link :href="route('admin.enigmes.edit', [lieu.id, typeKey])">
            <Button :icon="enigmes[typeKey] ? 'pi pi-pencil' : 'pi pi-plus'"
              :label="enigmes[typeKey] ? 'Modifier' : 'Ajouter'"
              :severity="enigmes[typeKey] ? 'secondary' : 'primary'"
              size="small" outlined />
          </Link>
          <Button v-if="enigmes[typeKey]" icon="pi pi-trash" text rounded severity="danger"
            size="small" @click="confirmDelete(typeKey, typeLabel)" />
        </div>
      </div>
    </div>

    <ConfirmDialog />
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import Breadcrumb from 'primevue/breadcrumb'
import ConfirmDialog from 'primevue/confirmdialog'

const props = defineProps({
  lieu: Object,
  enigmes: Object, // { force1: {...}, force2: {...}, ... } (peut être null par type)
  types: Object,   // { force3: 'Difficile (Force 3)', ... }
})

const confirm = useConfirm()

const completedCount = computed(() =>
  Object.keys(props.types).filter(t => props.enigmes[t]).length
)

const completionClass = computed(() => ({
  'badge-complete': completedCount.value === 4,
  'badge-partial': completedCount.value > 0 && completedCount.value < 4,
  'badge-empty': completedCount.value === 0,
}))

const completionIcon = computed(() =>
  completedCount.value === 4 ? 'pi pi-check-circle' : 'pi pi-exclamation-circle'
)

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.lieu.environnement?.nom, url: route('admin.lieux.index', props.lieu.environnement_id) },
  { label: 'Lieux', url: route('admin.lieux.index', props.lieu.environnement_id) },
  { label: props.lieu.nom },
  { label: 'Énigmes' },
])

const confirmDelete = (typeKey, typeLabel) => {
  confirm.require({
    message: `Supprimer l'énigme "${typeLabel}" du lieu "${props.lieu.nom}" ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Supprimer',
    rejectLabel: 'Annuler',
    acceptClass: 'p-button-danger',
    accept: () => useForm({}).delete(route('admin.enigmes.destroy', [props.lieu.id, typeKey])),
  })
}
</script>

<style scoped>
.page-header  { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
.page-title   { font-size: 1.4rem; font-weight: 700; color: #1a202c; }
.page-subtitle { font-size: 0.85rem; color: #718096; margin-top: 0.2rem; }

.completion-badge {
  display: flex; align-items: center; gap: 0.4rem;
  font-size: 0.82rem; font-weight: 600;
  padding: 0.4rem 0.85rem;
  border-radius: 99px;
}
.badge-complete { background: #f0fff4; color: #276749; }
.badge-partial  { background: #fffaf0; color: #c05621; }
.badge-empty    { background: #fff5f5; color: #c53030; }

.convention-msg { margin-bottom: 1.25rem; font-size: 0.875rem; }

.enigmes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.enigme-card {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: box-shadow 0.15s;
}
.enigme-card:hover  { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.enigme-card.missing { border-style: dashed; opacity: 0.85; }

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f0f4f8;
}

.type-badge {
  font-size: 0.8rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}
.type-force3 { background: #fff5f5; color: #c53030; }
.type-force2 { background: #fffaf0; color: #c05621; }
.type-force1 { background: #f0fff4; color: #276749; }
.type-enfant { background: #ebf8ff; color: #2b6cb0; }

.card-content { padding: 1rem; flex: 1; }
.enigme-texte {
  font-size: 0.82rem;
  color: #4a5568;
  line-height: 1.5;
  margin: 0 0 0.75rem;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  white-space: pre-line;
}
.enigme-image img { width: 100%; border-radius: 6px; object-fit: cover; max-height: 100px; }
.no-image { display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; color: #cbd5e0; }

.card-empty {
  padding: 1.5rem 1rem;
  text-align: center;
  color: #a0aec0;
  flex: 1;
}
.card-empty i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
.card-empty p { font-size: 0.82rem; margin: 0; }

.card-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid #f0f4f8;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
=======
<script setup>
/*
|--------------------------------------------------------------------------
| IMPORTS VUE + INERTIA
|--------------------------------------------------------------------------
*/
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { router, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { useToast } from 'primevue/usetoast';

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/
const props = defineProps({
    enigmes: {
        type: Array,
        default: () => []
    },
    flash: Object
});

const toast = useToast();

onMounted(() => {
    if (props.flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: props.flash.success, life: 3000 });
    }
    if (props.flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: props.flash.error, life: 3000 });
    }
});

/*
|--------------------------------------------------------------------------
| FILTRES UI
|--------------------------------------------------------------------------
*/
const search = ref('');
const filterType = ref(null);
const filterLieu = ref(null);

/*
|--------------------------------------------------------------------------
| TYPES FIXES
|--------------------------------------------------------------------------
*/
const types = [
    { label: 'Force 1', value: 'force1' },
    { label: 'Force 2', value: 'force2' },
    { label: 'Force 3', value: 'force3' },
    { label: 'Enfant', value: 'enfant' }
];

/*
|--------------------------------------------------------------------------
| FILTRAGE DYNAMIQUE
|--------------------------------------------------------------------------
*/
const filteredEnigmes = computed(() => {
    return props.enigmes.filter(e => {
        const matchSearch = !search.value || 
            (e.texte && e.texte.toLowerCase().includes(search.value.toLowerCase()));

        const matchType = !filterType.value || e.type === filterType.value;

        const matchLieu = !filterLieu.value || 
            (e.lieu?.nom && e.lieu.nom.toLowerCase().includes(filterLieu.value.toLowerCase()));

        return matchSearch && matchType && matchLieu;
    });
});

/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/
const deleteEnigme = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette énigme ?')) {
        router.delete(route('enigmes.destroy', id));
    }
};

const getTypeColor = (type) => {
    switch (type) {
        case 'force1': return 'bg-green-100 text-green-700 border-green-200';
        case 'force2': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'force3': return 'bg-red-100 text-red-700 border-red-200';
        case 'enfant': return 'bg-purple-100 text-purple-700 border-purple-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};
</script>

<template>
    <Head title="Gestion des Énigmes" />

    <SidebarLayout>
        <div class="p-4 sm:p-8 min-h-screen bg-gray-50/50">
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-orange-950 uppercase tracking-tight">
                        Énigmes <span class="text-[#FF9500]">Admin</span>
                    </h1>
                    <p class="text-orange-900/60 font-medium text-sm mt-1 uppercase tracking-widest">
                        Gestion du catalogue des défis ({{ filteredEnigmes.length }})
                    </p>
                </div>

                <Button
                    label="Nouvelle énigme"
                    icon="pi pi-plus"
                    class="p-button-orange shadow-lg shadow-orange-200"
                    @click="router.visit(route('enigmes.create'))"
                />
            </div>

            <!-- FILTRES SECTION -->
            <div class="bg-white p-6 rounded-3xl border border-orange-100 shadow-sm mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Recherche</label>
                        <IconField>
                            <InputIcon class="pi pi-search text-orange-300" />
                            <InputText
                                v-model="search"
                                placeholder="Rechercher un texte..."
                                class="w-full border-orange-50 focus:border-orange-300 focus:ring-orange-100 rounded-xl"
                            />
                        </IconField>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Difficulté</label>
                        <Select
                            v-model="filterType"
                            :options="types"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Toutes les difficultés"
                            showClear
                            class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-orange-900/40 ml-1">Lieu</label>
                        <IconField>
                            <InputIcon class="pi pi-map-marker text-orange-300" />
                            <InputText
                                v-model="filterLieu"
                                placeholder="Filtrer par lieu..."
                                class="w-full border-orange-50 focus:border-orange-300 rounded-xl"
                            />
                        </IconField>
                    </div>
                </div>
            </div>

            <!-- LISTE SECTION -->
            <div class="grid grid-cols-1 gap-6">
                <div
                    v-for="enigme in filteredEnigmes"
                    :key="enigme.id"
                    class="group bg-white p-6 rounded-3xl border border-orange-100 shadow-sm hover:shadow-xl hover:shadow-orange-100/50 transition-all duration-300"
                >
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <!-- INFOS PRINCIPALES -->
                        <div class="flex-1 space-y-4">
                            <div class="flex items-center gap-3">
                                <span 
                                    :class="[getTypeColor(enigme.type), 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border']"
                                >
                                    {{ enigme.type }}
                                </span>
                                <h2 class="text-xl font-black text-orange-950 uppercase tracking-tight group-hover:text-[#FF9500] transition-colors">
                                    {{ enigme.titre || 'Sans titre' }}
                                </h2>
                            </div>

                            <div class="bg-orange-50/50 p-4 rounded-2xl border border-orange-100/50">
                                <p class="text-orange-900/80 font-medium leading-relaxed">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-orange-400 block mb-1">Question :</span>
                                    {{ enigme.texte }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-4 pt-2">
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-orange-50 shadow-sm">
                                    <i class="pi pi-map-marker text-[#FF9500]" />
                                    <span class="text-xs font-bold text-orange-900/60 uppercase tracking-wide">
                                        {{ enigme.lieu?.nom || 'Lieu non défini' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-orange-50 shadow-sm">
                                    <i class="pi pi-star-fill text-[#FF9500]" />
                                    <span class="text-xs font-bold text-orange-900/60 uppercase tracking-wide">
                                        {{ enigme.points }} Points
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="flex md:flex-col gap-2 shrink-0 justify-end md:justify-start">
                            <Button
                                icon="pi pi-pencil"
                                label="Modifier"
                                class="p-button-outlined p-button-warning border-orange-200 text-orange-600 hover:bg-orange-50 rounded-xl font-bold uppercase text-[10px] tracking-widest"
                                @click="router.visit(route('enigmes.edit', enigme.id))"
                            />
                            <Button
                                icon="pi pi-trash"
                                label="Supprimer"
                                class="p-button-outlined p-button-danger border-red-100 text-red-500 hover:bg-red-50 rounded-xl font-bold uppercase text-[10px] tracking-widest"
                                @click="deleteEnigme(enigme.id)"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-if="filteredEnigmes.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-orange-100 mt-8">
                <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
                    <i class="pi pi-search text-2xl text-orange-300" />
                </div>
                <h3 class="text-orange-950 font-bold">Aucune énigme trouvée</h3>
                <p class="text-orange-900/40 text-sm mt-1">Essayez d'ajuster vos filtres de recherche</p>
            </div>

        </div>
    </SidebarLayout>
</template>

<style>
/* Styles globaux pour les boutons orange conformes à la sidebar */
.p-button-orange {
    background: linear-gradient(135deg, #FF9500 0%, #FF7B00 100%) !important;
    border: none !important;
    border-radius: 16px !important;
    padding: 0.75rem 1.5rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-size: 0.8rem !important;
}

.p-button-orange:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 149, 0, 0.3) !important;
}

/* Custom primevue styles to match the vibe */
.p-inputtext:focus {
    box-shadow: 0 0 0 2px rgba(255, 149, 0, 0.1) !important;
}

.p-select:not(.p-disabled).p-focus {
    box-shadow: 0 0 0 2px rgba(255, 149, 0, 0.1) !important;
    border-color: #FF9500 !important;
}
</style>
>>>>>>> 3cf28890aa88709638185e3d8842f9dcc479aed2
