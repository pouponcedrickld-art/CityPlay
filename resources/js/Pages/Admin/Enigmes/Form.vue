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
  props.enigme?.image_path ? `/storage/${props.enigme.image_path}` : null
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
.form-page     { max-width: 700px; }
.page-header   { margin-bottom: 1.5rem; }
.page-title    { font-size: 1.4rem; font-weight: 700; color: #1a202c; margin: 0.35rem 0 0.2rem; }
.page-subtitle { font-size: 0.85rem; color: #718096; margin: 0; }

.type-pill {
  display: inline-block;
  font-size: 0.75rem; font-weight: 700;
  padding: 0.2rem 0.65rem; border-radius: 6px;
  margin-bottom: 0.35rem;
}
.type-force3 { background: #fff5f5; color: #c53030; }
.type-force2 { background: #fffaf0; color: #c05621; }
.type-force1 { background: #f0fff4; color: #276749; }
.type-enfant { background: #ebf8ff; color: #2b6cb0; }

.form-card    { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; padding: 1.75rem; display: flex; flex-direction: column; gap: 2rem; }
.form-section { display: flex; flex-direction: column; gap: 0.75rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #2d3748; }
.section-hint  { font-size: 0.82rem; color: #718096; background: #f7fafc; padding: 0.5rem 0.75rem; border-radius: 6px; border-left: 3px solid #bee3f8; margin: 0; }
.required      { color: #e53e3e; }

.textarea-footer { display: flex; justify-content: space-between; align-items: center; }
.char-count      { color: #a0aec0; font-size: 0.75rem; }

.current-image { display: flex; flex-direction: column; gap: 0.5rem; }
.current-image img { max-width: 300px; border-radius: 8px; border: 1px solid #e5e9f0; }

.upload-zone {
  border: 2px dashed #cbd5e0; border-radius: 10px; padding: 2rem;
  text-align: center; cursor: pointer; color: #718096; transition: all 0.15s;
}
.upload-zone:hover { border-color: #63b3ed; color: #2b6cb0; background: #ebf8ff20; }
.upload-zone i  { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
.upload-zone p  { margin: 0 0 0.25rem; font-size: 0.875rem; }
.hidden-input   { display: none; }

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #e5e9f0; }
</style>
