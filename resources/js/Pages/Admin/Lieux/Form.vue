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
              <div class="search-container">
                <IconField iconPosition="left">
                  <InputIcon class="pi pi-search" />
                  <InputText v-model="searchQuery" placeholder="Rechercher une adresse..." @keyup.enter="searchLocation" />
                </IconField>
                <div v-if="isSearching" class="search-loader">
                  <i class="pi pi-spin pi-spinner" />
                </div>
                <ul v-if="searchResults.length" class="search-results">
                  <li v-for="res in searchResults" :key="res.place_id" @click="selectResult(res)">
                    <i class="pi pi-map-marker" />
                    <span>{{ res.display_name }}</span>
                  </li>
                </ul>
              </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import axios from 'axios'

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

// Recherche d'adresse
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)

const searchLocation = async () => {
  if (!searchQuery.value || searchQuery.value.length < 3) return

  isSearching.value = true
  searchResults.value = []

  try {
    const response = await axios.get('https://nominatim.openstreetmap.org/search', {
      params: {
        q: searchQuery.value,
        format: 'json',
        addressdetails: 1,
        limit: 5
      }
    })
    searchResults.value = response.data
  } catch (error) {
    console.error("Erreur lors de la recherche :", error)
  } finally {
    isSearching.value = false
  }
}

const selectResult = (res) => {
  const lat = parseFloat(res.lat)
  const lon = parseFloat(res.lon)
  const latlng = [lat, lon]

  // Mettre à jour la carte et le formulaire
  map.value.setView(latlng, 16)
  form.latitude = parseFloat(lat.toFixed(7))
  form.longitude = parseFloat(lon.toFixed(7))

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

  // Nettoyer la recherche
  searchQuery.value = res.display_name
  searchResults.value = []
}

const handleClickOutside = (event) => {
  const searchContainer = document.querySelector('.search-container')
  if (searchContainer && !searchContainer.contains(event.target)) {
    searchResults.value = []
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

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
.form-page     { max-width: 800px; }
.page-title    { font-size: 1.4rem; font-weight: 700; color: #1a202c; margin-bottom: 1.5rem; }
.form-card     { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; padding: 1.75rem; display: flex; flex-direction: column; gap: 2rem; }
.form-section  { display: flex; flex-direction: column; gap: 1rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #2d3748; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e9f0; display: flex; align-items: center; gap: 0.5rem; }
.section-badge { font-size: 0.7rem; font-weight: 600; background: #ebf8ff; color: #2b6cb0; padding: 0.1rem 0.5rem; border-radius: 99px; }
.field       { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: #4a5568; }
.field-row   { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.char-count  { color: #a0aec0; font-size: 0.75rem; text-align: right; }
.hint        { color: #a0aec0; font-size: 0.75rem; }
.required    { color: #e53e3e; }
.map-link    { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; color: #3182ce; text-decoration: none; }
.map-link:hover { text-decoration: underline; }

.photos-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}
.photo-thumb {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e5e9f0;
}
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
.photo-remove {
  position: absolute; top: 4px; right: 4px;
  background: rgba(0,0,0,0.55);
  border: none; border-radius: 50%;
  width: 22px; height: 22px;
  color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.65rem;
}
.new-photo { border-color: #90cdf4; }
.new-badge {
  position: absolute; bottom: 4px; left: 4px;
  background: #2b6cb0; color: #fff;
  font-size: 0.6rem; font-weight: 600;
  padding: 0.1rem 0.4rem; border-radius: 4px;
}

.upload-zone {
  border: 2px dashed #cbd5e0;
  border-radius: 10px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  color: #718096;
  transition: all 0.15s;
}
.upload-zone:hover { border-color: #63b3ed; color: #2b6cb0; background: #ebf8ff20; }
.upload-zone i    { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
.upload-zone p    { margin: 0 0 0.25rem; font-size: 0.875rem; }
.upload-zone small { font-size: 0.75rem; }
.hidden-input { display: none; }

.map-wrapper {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e9f0;
  position: relative;
}
.map-toolbar {
  position: absolute;
  top: 10px;
  right: 10px;
  left: 10px;
  z-index: 1000;
  display: flex;
  gap: 10px;
  pointer-events: none;
}
.map-toolbar > * {
  pointer-events: auto;
}
.search-container {
  flex: 1;
  position: relative;
  max-width: 400px;
}
.search-container input {
  width: 100% !important;
  background: white !important;
  color: #1a1b1e !important; /* Force dark text since background is white */
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
.search-loader {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #FF9500;
}
.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  margin-top: 5px;
  padding: 0;
  list-style: none;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  max-height: 250px;
  overflow-y: auto;
  z-index: 1001;
}
.search-results li {
  padding: 10px 15px;
  cursor: pointer;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.85rem;
  color: #334155;
  transition: background 0.2s;
}
.search-results li:hover {
  background: #f8fafc;
}
.search-results li i {
  color: #FF9500;
  margin-top: 3px;
}
.search-results li:last-child {
  border-bottom: none;
}
.map-toolbar .p-button {
  background: white !important;
  color: #FF9500 !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
  white-space: nowrap;
}
.map-container {
  height: 300px;
  width: 100%;
  z-index: 1;
}

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #e5e9f0; }
</style>
