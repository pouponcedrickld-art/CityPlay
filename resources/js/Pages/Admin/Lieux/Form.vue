<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="form-page">
      <h1 class="page-title">{{ lieu ? 'Modifier le lieu' : 'Nouveau lieu' }}</h1>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="form-card">

        <!-- Infos principales -->
        <section class="form-section">
          <h2 class="section-title">Informations</h2>

          <div class="field-row">
            <div class="field">
              <label>Nom du lieu <span class="required">*</span></label>
              <InputText v-model="form.nom" maxlength="150" :class="{ 'p-invalid': form.errors.nom }" />
              <small v-if="form.errors.nom" class="p-error">{{ form.errors.nom }}</small>
            </div>
            <div class="field">
              <label>Ordre d'affichage <span class="required">*</span></label>
              <InputNumber v-model="form.ordre" :min="0" :max="9999" showButtons />
              <small class="hint">Classement défini par la mairie (popularité, logique de parcours)</small>
            </div>
          </div>

          <div class="field">
            <label>Description <span class="required">*</span></label>
            <Textarea v-model="form.description" rows="3" maxlength="500" autoResize
              placeholder="Présentation du lieu affichée après validation GPS..." />
            <small class="char-count">{{ (form.description || '').length }}/500</small>
          </div>
        </section>

        <!-- GPS -->
        <section class="form-section">
          <h2 class="section-title">Géolocalisation</h2>

          <!-- Carte Interactive -->
          <div class="map-wrapper mb-4">
            <div class="map-toolbar">
              <Button type="button" icon="pi pi-map-marker" label="Me localiser"
                size="small" outlined @click="locateUser" />
            </div>
            <div ref="mapContainer" class="map-container"></div>
            <small class="hint mt-2 block">
              <i class="pi pi-info-circle" /> Cliquez sur la carte pour définir les coordonnées du lieu.
            </small>
          </div>

          <div class="field-row">
            <div class="field">
              <label>Latitude <span class="required">*</span></label>
              <InputNumber v-model="form.latitude" :minFractionDigits="6" :maxFractionDigits="7"
                placeholder="ex: 44.841389" :class="{ 'p-invalid': form.errors.latitude }" />
              <small v-if="form.errors.latitude" class="p-error">{{ form.errors.latitude }}</small>
            </div>
            <div class="field">
              <label>Longitude <span class="required">*</span></label>
              <InputNumber v-model="form.longitude" :minFractionDigits="6" :maxFractionDigits="7"
                placeholder="ex: -0.569722" :class="{ 'p-invalid': form.errors.longitude }" />
              <small v-if="form.errors.longitude" class="p-error">{{ form.errors.longitude }}</small>
            </div>
            <div class="field">
              <label>Rayon de validation (mètres) <span class="required">*</span></label>
              <InputNumber v-model="form.rayon_metres" :min="5" :max="500" showButtons suffix=" m" />
              <small class="hint">Distance max depuis le point GPS pour valider la présence</small>
            </div>
          </div>

          <!-- Lien Google Maps pour vérification -->
          <a v-if="form.latitude && form.longitude"
            :href="`https://maps.google.com/?q=${form.latitude},${form.longitude}`"
            target="_blank" class="map-link">
            <i class="pi pi-external-link" /> Vérifier sur Google Maps
          </a>
        </section>

        <!-- Photos -->
        <section class="form-section">
          <h2 class="section-title">Photos du lieu <span class="section-badge">max 4</span></h2>

          <!-- Photos existantes -->
          <div v-if="existingPhotos.length" class="photos-grid">
            <div v-for="(photo, i) in existingPhotos" :key="i" class="photo-thumb">
              <img :src="`/storage/${photo}`" :alt="`Photo ${i+1}`" />
              <button type="button" class="photo-remove" @click="removeExistingPhoto(i)">
                <i class="pi pi-times" />
              </button>
            </div>
          </div>

          <!-- Upload nouvelles photos -->
          <div v-if="canAddMorePhotos" class="upload-zone" @click="$refs.photoInput.click()"
            @dragover.prevent @drop.prevent="handleDrop">
            <i class="pi pi-upload" />
            <p>Cliquez ou glissez des photos ici</p>
            <small>JPG, PNG · max 2 Mo chacune · {{ remainingSlots }} emplacement(s) restant(s)</small>
            <input ref="photoInput" type="file" accept="image/*" :multiple="remainingSlots > 1"
              class="hidden-input" @change="handleFileSelect" />
          </div>

          <!-- Aperçu nouvelles photos -->
          <div v-if="newPhotosPreviews.length" class="photos-grid">
            <div v-for="(preview, i) in newPhotosPreviews" :key="i" class="photo-thumb new-photo">
              <img :src="preview" :alt="`Nouvelle photo ${i+1}`" />
              <button type="button" class="photo-remove" @click="removeNewPhoto(i)">
                <i class="pi pi-times" />
              </button>
              <span class="new-badge">Nouveau</span>
            </div>
          </div>
        </section>

        <!-- Actions -->
        <div class="form-actions">
          <Link :href="route('admin.lieux.index', environnement.id)">
            <Button type="button" label="Annuler" outlined severity="secondary" />
          </Link>
          <Button type="submit" :label="lieu ? 'Mettre à jour' : 'Créer le lieu'" :loading="form.processing" />
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  environnement: Object,
  lieu: Object, // null = création
})

// Fix pour les icônes Leaflet par défaut
onMounted(() => {
  delete L.Icon.Default.prototype._getIconUrl;
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  });
})

const mapContainer = ref(null)
const map = ref(null)
const marker = ref(null)

onMounted(() => {
  const initialLat = props.lieu?.latitude ? parseFloat(props.lieu.latitude) : 44.841389
  const initialLng = props.lieu?.longitude ? parseFloat(props.lieu.longitude) : -0.569722

  map.value = L.map(mapContainer.value).setView([initialLat, initialLng], 13)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map.value)

  // Si on a déjà un lieu, on place le marqueur
  if (props.lieu) {
    marker.value = L.marker([initialLat, initialLng], { draggable: true }).addTo(map.value)

    marker.value.on('dragend', (e) => {
      const { lat, lng } = e.target.getLatLng()
      form.latitude = parseFloat(lat.toFixed(7))
      form.longitude = parseFloat(lng.toFixed(7))
    })
  }

  // Clic sur la carte pour définir la position
  map.value.on('click', (e) => {
    const { lat, lng } = e.latlng

    form.latitude = parseFloat(lat.toFixed(7))
    form.longitude = parseFloat(lng.toFixed(7))

    if (marker.value) {
      marker.value.setLatLng(e.latlng)
    } else {
      marker.value = L.marker(e.latlng, { draggable: true }).addTo(map.value)

      marker.value.on('dragend', (ev) => {
        const pos = ev.target.getLatLng()
        form.latitude = parseFloat(pos.lat.toFixed(7))
        form.longitude = parseFloat(pos.lng.toFixed(7))
      })
    }
  })
})

const locateUser = () => {
  if (!navigator.geolocation) {
    alert("La géolocalisation n'est pas supportée par votre navigateur.")
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords
      const latlng = [latitude, longitude]

      // Centrer la carte
      map.value.setView(latlng, 16)

      // Mettre à jour le formulaire
      form.latitude = parseFloat(latitude.toFixed(7))
      form.longitude = parseFloat(longitude.toFixed(7))

      // Mettre à jour le marqueur
      if (marker.value) {
        marker.value.setLatLng(latlng)
      } else {
        marker.value = L.marker(latlng, { draggable: true }).addTo(map.value)

        marker.value.on('dragend', (ev) => {
          const pos = ev.target.getLatLng()
          form.latitude = parseFloat(pos.lat.toFixed(7))
          form.longitude = parseFloat(pos.lng.toFixed(7))
        })
      }
    },
    (error) => {
      alert("Impossible de récupérer votre position. Vérifiez vos autorisations.")
    }
  )
}

// Photos existantes (édition)
const existingPhotos = ref(props.lieu?.photos ?? [])
const newPhotosFiles  = ref([])
const newPhotosPreviews = ref([])

const MAX_PHOTOS = 4
const canAddMorePhotos = computed(() => existingPhotos.value.length + newPhotosFiles.value.length < MAX_PHOTOS)
const remainingSlots   = computed(() => MAX_PHOTOS - existingPhotos.value.length - newPhotosFiles.value.length)

const form = useForm({
  nom:            props.lieu?.nom ?? '',
  ordre:          props.lieu?.ordre ?? 1,
  latitude:       props.lieu?.latitude ? parseFloat(props.lieu.latitude) : null,
  longitude:      props.lieu?.longitude ? parseFloat(props.lieu.longitude) : null,
  rayon_metres:   props.lieu?.rayon_metres ?? 50,
  description:    props.lieu?.description ?? '',
  photos:         [],
  existing_photos: existingPhotos.value,
})

const removeExistingPhoto = (i) => {
  existingPhotos.value.splice(i, 1)
  form.existing_photos = existingPhotos.value
}

const handleFileSelect = (e) => addFiles(Array.from(e.target.files))
const handleDrop = (e) => addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')))

const addFiles = (files) => {
  const toAdd = files.slice(0, remainingSlots.value)
  toAdd.forEach(file => {
    newPhotosFiles.value.push(file)
    const reader = new FileReader()
    reader.onload = (e) => newPhotosPreviews.value.push(e.target.result)
    reader.readAsDataURL(file)
  })
}

const removeNewPhoto = (i) => {
  newPhotosFiles.value.splice(i, 1)
  newPhotosPreviews.value.splice(i, 1)
}

const submit = () => {
  form.photos = newPhotosFiles.value
  form.existing_photos = existingPhotos.value

  const url = props.lieu
    ? route('admin.lieux.update', [props.environnement.id, props.lieu.id])
    : route('admin.lieux.store', props.environnement.id)

  if (props.lieu) {
    // Mode édition : on ajoute le spoofing de méthode PUT pour Laravel
    form.transform((data) => ({
      ...data,
      _method: 'PUT'
    })).post(url, { forceFormData: true })
  } else {
    // Mode création
    form.post(url, { forceFormData: true })
  }
}

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.environnement.nom, url: route('admin.lieux.index', props.environnement.id) },
  { label: 'Lieux', url: route('admin.lieux.index', props.environnement.id) },
  { label: props.lieu ? 'Modifier' : 'Nouveau' },
])
</script>

<style scoped>
.form-page { max-width: 900px; }
.page-title { 
  font-size: 1.875rem; 
  font-weight: 700; 
  color: #fff;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 2rem;
}

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

.form-section { display: flex; flex-direction: column; gap: 1.5rem; }

.section-title { 
  font-size: 1.1rem; 
  font-weight: 700; 
  color: #FF9500; 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 0.5rem; 
  padding-bottom: 0.75rem; 
  border-bottom: 1px solid rgba(255, 149, 0, 0.2); 
  display: flex; 
  align-items: center; 
  gap: 0.75rem; 
}

.section-badge { 
  font-size: 0.65rem; 
  font-weight: 800; 
  background: rgba(255, 149, 0, 0.1); 
  color: #FF9500; 
  padding: 0.2rem 0.6rem; 
  border-radius: 4px; 
  border: 1px solid rgba(255, 149, 0, 0.2);
}

.field { display: flex; flex-direction: column; gap: 0.5rem; }

.field label { 
  font-size: 0.75rem; 
  font-weight: 700; 
  color: rgba(255, 255, 255, 0.7); 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.field-row { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
  gap: 1.5rem; 
}

.char-count { 
  color: rgba(255, 255, 255, 0.2); 
  font-size: 0.7rem; 
  text-align: right; 
  font-family: 'Rajdhani', sans-serif;
}

.hint { 
  color: #4b5563; 
  font-size: 0.75rem; 
  font-style: italic;
}

.required { color: #ef4444; }

.map-link { 
  display: inline-flex; 
  align-items: center; 
  gap: 0.5rem; 
  font-size: 0.8rem; 
  color: #FF9500; 
  text-decoration: none; 
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}
.map-link:hover { opacity: 0.8; }

.photos-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.photo-thumb {
  position: relative;
  aspect-ratio: 1;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #1f2937;
  transition: border-color 0.2s;
}
.photo-thumb:hover { border-color: #FF9500; }
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; }

.photo-remove {
  position: absolute; top: 8px; right: 8px;
  background: rgba(0,0,0,0.7);
  border: none; border-radius: 8px;
  width: 28px; height: 28px;
  color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem;
  transition: background 0.2s;
}
.photo-remove:hover { background: #ef4444; }

.new-photo { border-color: rgba(255, 149, 0, 0.3); }
.new-badge {
  position: absolute; bottom: 8px; left: 8px;
  background: #FF9500; color: #000;
  font-size: 0.6rem; font-weight: 800;
  padding: 0.2rem 0.5rem; border-radius: 4px;
  font-family: 'Rajdhani', sans-serif;
  text-transform: uppercase;
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
.upload-zone small { font-size: 0.75rem; color: #374151; }
.hidden-input { display: none; }

.map-wrapper {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #1f2937;
  position: relative;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.map-toolbar {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 1000;
}
.map-container {
  height: 350px;
  width: 100%;
  z-index: 1;
}

.form-actions { 
  display: flex; 
  justify-content: flex-end; 
  gap: 1rem; 
  padding-top: 1.5rem; 
  border-top: 1px solid #1f2937; 
}

:deep(.p-inputtext), :deep(.p-inputnumber-input), :deep(.p-textarea) {
  background: rgba(255, 255, 255, 0.03) !important;
  border-color: #1f2937 !important;
  color: #fff !important;
}

:deep(.p-inputtext:hover), :deep(.p-textarea:hover) {
  border-color: rgba(255, 149, 0, 0.5) !important;
}

:deep(.p-inputtext:focus), :deep(.p-textarea:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 0 1px rgba(255, 149, 0, 0.2) !important;
}
</style>
