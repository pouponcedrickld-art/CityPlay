<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="form-page">
      <div class="page-header">
        <div>
          <div class="type-pill" :class="`type-${type}`">{{ typeLabel }}</div>
          <h1 class="page-title">
            {{ enigme ? 'Modifier l\'énigme' : 'Créer l\'énigme' }}
          </h1>
          <p class="page-subtitle">Lieu : {{ lieu.nom }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="form-card">

        <!-- Texte de l'énigme -->
        <section class="form-section">
          <h2 class="section-title">Texte de l'énigme <span class="required">*</span></h2>
          <p class="section-hint" v-if="type === 'force3'">
            💡 Force 3 = la plus difficile. Rédigez une énigme cryptique ou poétique sans donner le nom du lieu.
          </p>
          <p class="section-hint" v-else-if="type === 'force2'">
            💡 Force 2 = intermédiaire. Donnez des indices sur le lieu sans le nommer directement.
          </p>
          <p class="section-hint" v-else-if="type === 'force1'">
            💡 Force 1 = facile. Vous pouvez mentionner le nom ou donner des indications très directes.
          </p>
          <p class="section-hint" v-else-if="type === 'enfant'">
            💡 Enfant = adapté aux plus jeunes. Utilisez un langage simple et un ton ludique.
          </p>

          <Textarea v-model="form.texte" rows="6" maxlength="2000" autoResize
            :placeholder="textePlaceholder"
            :class="{ 'p-invalid': form.errors.texte }" />
          <div class="textarea-footer">
            <small v-if="form.errors.texte" class="p-error">{{ form.errors.texte }}</small>
            <small class="char-count">{{ form.texte.length }}/2000</small>
          </div>
        </section>

        <!-- Image -->
        <section class="form-section">
          <h2 class="section-title">Image (optionnelle)</h2>

          <!-- Image existante -->
          <div v-if="currentImageUrl && !removeImage" class="current-image">
            <img :src="currentImageUrl" alt="Image actuelle" />
            <Button type="button" icon="pi pi-trash" label="Supprimer l'image"
              severity="danger" text size="small" @click="removeImage = true" />
          </div>

          <!-- Upload nouvelle image -->
          <div v-if="!currentImageUrl || removeImage">
            <div v-if="!newImagePreview" class="upload-zone" @click="$refs.imageInput.click()"
              @dragover.prevent @drop.prevent="handleDrop">
              <i class="pi pi-image" />
              <p>Cliquez ou glissez une image ici</p>
              <small>JPG, PNG · max 2 Mo</small>
              <input ref="imageInput" type="file" accept="image/*" class="hidden-input" @change="handleFileSelect" />
            </div>

            <div v-else class="current-image">
              <img :src="newImagePreview" alt="Aperçu" />
              <Button type="button" icon="pi pi-times" label="Retirer"
                severity="secondary" text size="small" @click="clearNewImage" />
            </div>
          </div>
        </section>

        <!-- Actions -->
        <div class="form-actions">
          <Link :href="route('admin.enigmes.index', lieu.id)">
            <Button type="button" label="Annuler" outlined severity="secondary" />
          </Link>
          <Button type="submit"
            :label="enigme ? 'Mettre à jour' : 'Enregistrer'"
            :loading="form.processing" />
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'

const props = defineProps({
  lieu: Object,
  enigme: Object,    // null = création
  type: String,      // 'force1' | 'force2' | 'force3' | 'enfant'
  typeLabel: String,
})

const removeImage = ref(false)
const newImageFile = ref(null)
const newImagePreview = ref(null)

const currentImageUrl = computed(() =>
  props.enigme?.image_url ? `/storage/${props.enigme.image_url}` : null
)

const form = useForm({
  texte: props.enigme?.texte ?? '',
  image: null,
  remove_image: false,
})

const textePlaceholder = computed(() => {
  const placeholders = {
    force3: 'Ex: "Je garde les secrets de la cité entre deux rives...\nMon nom résonne à chaque heure depuis des siècles.\nQui suis-je ?"',
    force2: 'Ex: "Ce monument datant du XVIIIe siècle se trouve à l\'angle de la place principale...\nSes colonnes vous accueilleront.",',
    force1: 'Ex: "Rendez-vous à la Place de la Bourse. Cherchez le monument face au fleuve.",',
    enfant: 'Ex: "🗺️ Tu cherches un très vieux bâtiment avec de grandes portes. Il est près de l\'eau ! 💧"',
  }
  return placeholders[props.type] ?? ''
})

const handleFileSelect = (e) => setImage(e.target.files[0])
const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  if (file?.type.startsWith('image/')) setImage(file)
}

const setImage = (file) => {
  newImageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => newImagePreview.value = e.target.result
  reader.readAsDataURL(file)
}

const clearNewImage = () => {
  newImageFile.value = null
  newImagePreview.value = null
}

const submit = () => {
  form.image = newImageFile.value
  form.remove_image = removeImage.value

  form.post(route('admin.enigmes.upsert', [props.lieu.id, props.type]), {
    forceFormData: true,
  })
}

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.lieu.environnement?.nom, url: route('admin.lieux.index', props.lieu.environnement_id) },
  { label: props.lieu.nom, url: route('admin.enigmes.index', props.lieu.id) },
  { label: 'Énigmes', url: route('admin.enigmes.index', props.lieu.id) },
  { label: props.typeLabel },
])
</script>

<style scoped>
.form-page { max-width: 800px; }
.page-header { margin-bottom: 2rem; }
.page-title { 
  font-size: 1.875rem; 
  font-weight: 700; 
  color: #fff;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0.5rem 0 0.25rem; 
}
.page-subtitle { 
  font-size: 0.85rem; 
  color: rgba(226, 232, 240, 0.5); 
  margin: 0; 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.type-pill {
  display: inline-block;
  font-size: 0.7rem; 
  font-weight: 800;
  padding: 0.25rem 0.75rem; 
  border-radius: 6px;
  margin-bottom: 0.5rem;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.type-force3 { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
.type-force2 { background: rgba(255, 149, 0, 0.1); color: #FF9500; border: 1px solid rgba(255, 149, 0, 0.2); }
.type-force1 { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.type-enfant { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }

.form-card { 
  background: #111827; 
  border: 1px solid #1f2937; 
  border-radius: 16px; 
  padding: 2.5rem; 
  display: flex; 
  flex-direction: column; 
  gap: 2.5rem; 
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.form-section { display: flex; flex-direction: column; gap: 1rem; }

.section-title { 
  font-size: 1.1rem; 
  font-weight: 700; 
  color: #FF9500; 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 0.5rem; 
}

.section-hint { 
  font-size: 0.85rem; 
  color: #60a5fa; 
  background: rgba(59, 130, 246, 0.05); 
  padding: 1rem; 
  border-radius: 8px; 
  border: 1px solid rgba(59, 130, 246, 0.1); 
  margin: 0; 
  line-height: 1.5;
}

.required { color: #ef4444; }

.textarea-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; }
.char-count { 
  color: rgba(255, 255, 255, 0.2); 
  font-size: 0.7rem; 
  font-family: 'Rajdhani', sans-serif;
}

.current-image { display: flex; flex-direction: column; gap: 1rem; }
.current-image img { 
  max-width: 400px; 
  border-radius: 12px; 
  border: 1px solid #1f2937; 
}

.upload-zone {
  border: 2px dashed #1f2937; 
  border-radius: 12px; 
  padding: 3rem;
  text-align: center; 
  cursor: pointer; 
  color: #4b5563; 
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  background: rgba(255, 255, 255, 0.02);
}
.upload-zone:hover { 
  border-color: #FF9500; 
  color: #FF9500; 
  background: rgba(255, 149, 0, 0.05); 
}
.upload-zone i { font-size: 2.5rem; display: block; margin-bottom: 1rem; opacity: 0.3; }
.upload-zone p { 
  margin: 0 0 0.5rem; 
  font-size: 0.9rem; 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}
.hidden-input { display: none; }

.form-actions { 
  display: flex; 
  justify-content: flex-end; 
  gap: 1rem; 
  padding-top: 1.5rem; 
  border-top: 1px solid #1f2937; 
}

:deep(.p-textarea) {
  background: rgba(255, 255, 255, 0.03) !important;
  border-color: #1f2937 !important;
  color: #fff !important;
  font-size: 0.9rem !important;
  padding: 1rem !important;
}

:deep(.p-textarea:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 0 1px rgba(255, 149, 0, 0.2) !important;
}
</style>
