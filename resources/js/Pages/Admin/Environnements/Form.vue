<template>
  <AdminLayout>
    <template #breadcrumb>
      <Breadcrumb :model="breadcrumbs" />
    </template>

    <div class="form-page">
      <h1 class="page-title">{{ environnement ? 'Modifier l\'environnement' : 'Nouvel environnement' }}</h1>

      <form @submit.prevent="submit" class="form-card">
        <!-- Informations générales -->
        <section class="form-section">
          <h2 class="section-title">Informations générales</h2>

          <div class="field-row">
            <div class="field">
              <label>Ville <span class="required">*</span></label>
              <Dropdown v-model="form.ville_id" :options="villes" optionLabel="nom" optionValue="id"
                placeholder="Sélectionner une ville" :class="{ 'p-invalid': form.errors.ville_id }" />
              <small v-if="form.errors.ville_id" class="p-error">{{ form.errors.ville_id }}</small>
            </div>

            <div class="field">
              <label>Nom de l'environnement <span class="required">*</span></label>
              <InputText v-model="form.nom" maxlength="150" :class="{ 'p-invalid': form.errors.nom }" />
              <small class="char-count">{{ form.nom.length }}/150</small>
              <small v-if="form.errors.nom" class="p-error">{{ form.errors.nom }}</small>
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label>Rétention des profils <span class="required">*</span></label>
              <Dropdown v-model="form.retention_profils_jours" :options="retentionOptions"
                optionLabel="label" optionValue="value" />
            </div>

            <div class="field">
              <label>Durée de vie du lien d'invitation (heures) <span class="required">*</span></label>
              <InputNumber v-model="form.duree_vie_lien_heures" :min="1" :max="168" />
            </div>
          </div>

          <div class="field">
            <label>Règles du jeu</label>
            <Textarea v-model="form.regles" rows="3" maxlength="2000" autoResize />
          </div>
        </section>

        <!-- Messages paramétrables -->
        <section class="form-section">
          <h2 class="section-title">Messages affichés aux joueurs</h2>
          <p class="section-hint">Ces messages sont affichés pendant le jeu. Laissez vide pour utiliser le message par défaut.</p>

          <div class="field">
            <label>✅ Message bonne réponse</label>
            <Textarea v-model="form.message_bonne_reponse" rows="2" maxlength="500" autoResize
              placeholder="Ex: Bravo ! Vous avez trouvé ce lieu." />
            <small class="char-count">{{ (form.message_bonne_reponse || '').length }}/500</small>
          </div>

          <div class="field">
            <label>❌ Message mauvaise réponse</label>
            <Textarea v-model="form.message_mauvaise_reponse" rows="2" maxlength="500" autoResize
              placeholder="Ex: Pas encore, essayez un indice !" />
            <small class="char-count">{{ (form.message_mauvaise_reponse || '').length }}/500</small>
          </div>

          <div class="field">
            <label>⏸ Message pause</label>
            <Textarea v-model="form.message_pause" rows="2" maxlength="500" autoResize />
            <small class="char-count">{{ (form.message_pause || '').length }}/500</small>
          </div>

          <div class="field">
            <label>🏁 Message fin de partie</label>
            <Textarea v-model="form.message_fin" rows="2" maxlength="500" autoResize
              placeholder="Ex: Bravo ! Découvrez notre restaurant partenaire..." />
            <small class="char-count">{{ (form.message_fin || '').length }}/500</small>
          </div>
        </section>

        <!-- Liens externes (Conclusion) -->
        <section class="form-section">
          <h2 class="section-title">Liens de conclusion (A4)</h2>
          <p class="section-hint">Liens affichés à la fin de la partie pour orienter les joueurs.</p>

          <div class="field">
            <label>🍴 Lien restauration</label>
            <InputText v-model="form.lien_restauration" placeholder="https://..." />
            <small v-if="form.errors.lien_restauration" class="p-error">{{ form.errors.lien_restauration }}</small>
          </div>

          <div class="field">
            <label>🎁 Lien boutique souvenirs</label>
            <InputText v-model="form.lien_boutique" placeholder="https://..." />
            <small v-if="form.errors.lien_boutique" class="p-error">{{ form.errors.lien_boutique }}</small>
          </div>

          <div class="field">
            <label>⭐ Lien notation / améliorations</label>
            <InputText v-model="form.lien_notation" placeholder="https://..." />
            <small v-if="form.errors.lien_notation" class="p-error">{{ form.errors.lien_notation }}</small>
          </div>
        </section>

        <!-- Actions -->
        <div class="form-actions">
          <Link :href="route('admin.environnements.index')">
            <Button type="button" label="Annuler" outlined severity="secondary" />
          </Link>
          <Button type="submit" :label="environnement ? 'Mettre à jour' : 'Créer'" :loading="form.processing" />
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Pages/Admin/Layouts/AdminLayout.vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'

const props = defineProps({
  villes: Array,
  environnement: Object, // null = création
})

const form = useForm({
  ville_id:                props.environnement?.ville_id ?? null,
  nom:                     props.environnement?.nom ?? '',
  retention_profils_jours: props.environnement?.retention_profils_jours ?? 90,
  duree_vie_lien_heures:   props.environnement?.duree_vie_lien_heures ?? 24,
  regles:                  props.environnement?.regles ?? '',
  message_bonne_reponse:   props.environnement?.message_bonne_reponse ?? '',
  message_mauvaise_reponse: props.environnement?.message_mauvaise_reponse ?? '',
  message_fin:             props.environnement?.message_fin ?? '',
  message_pause:           props.environnement?.message_pause ?? '',
  lien_restauration:       props.environnement?.lien_restauration ?? '',
  lien_boutique:           props.environnement?.lien_boutique ?? '',
  lien_notation:           props.environnement?.lien_notation ?? '',
})

const retentionOptions = [
  { label: '30 jours', value: 30 },
  { label: '90 jours', value: 90 },
  { label: '180 jours', value: 180 },
  { label: '1 an', value: 365 },
]

const breadcrumbs = computed(() => [
  { label: 'Environnements', url: route('admin.environnements.index') },
  { label: props.environnement ? 'Modifier' : 'Nouveau' },
])

const submit = () => {
  if (props.environnement) {
    form.put(route('admin.environnements.update', props.environnement.id))
  } else {
    form.post(route('admin.environnements.store'))
  }
}
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
}

.section-hint { 
  font-size: 0.85rem; 
  color: rgba(226, 232, 240, 0.5); 
  margin-top: -1rem; 
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

.field input,
.field textarea,
.field :deep(.p-dropdown),
.field :deep(.p-inputnumber),
.field :deep(.p-inputtext) {
  width: 100% !important;
}

.field-row { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 1.5rem; 
}

.char-count { 
  color: rgba(255, 255, 255, 0.2); 
  font-size: 0.7rem; 
  text-align: right; 
  font-family: 'Rajdhani', sans-serif;
}

.required { color: #ef4444; }

.form-actions { 
  display: flex; 
  justify-content: flex-end; 
  gap: 1rem; 
  padding-top: 1.5rem; 
  border-top: 1px solid #1f2937; 
}

:deep(.p-dropdown), :deep(.p-inputnumber-input), :deep(.p-inputtext), :deep(.p-textarea) {
  background: rgba(255, 255, 255, 0.03) !important;
  border-color: #1f2937 !important;
  color: #fff !important;
}

:deep(.p-dropdown:hover), :deep(.p-inputtext:hover), :deep(.p-textarea:hover) {
  border-color: rgba(255, 149, 0, 0.5) !important;
}

:deep(.p-dropdown:focus), :deep(.p-inputtext:focus), :deep(.p-textarea:focus) {
  border-color: #FF9500 !important;
  box-shadow: 0 0 0 1px rgba(255, 149, 0, 0.2) !important;
}
</style>
