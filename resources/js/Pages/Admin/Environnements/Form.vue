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
              <small class="char-count">{{ (form.nom || '').length }}/150</small>
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
import { computed, watch, onMounted } from 'vue'
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

// Sécurité supplémentaire : si l'objet environnement change, on met à jour le formulaire
watch(() => props.environnement, (newEnv) => {
  if (newEnv) {
    form.ville_id = newEnv.ville_id;
    form.nom = newEnv.nom;
    form.retention_profils_jours = newEnv.retention_profils_jours;
    form.duree_vie_lien_heures = newEnv.duree_vie_lien_heures;
    form.regles = newEnv.regles;
    form.message_bonne_reponse = newEnv.message_bonne_reponse;
    form.message_mauvaise_reponse = newEnv.message_mauvaise_reponse;
    form.message_fin = newEnv.message_fin;
    form.message_pause = newEnv.message_pause;
    form.lien_restauration = newEnv.lien_restauration;
    form.lien_boutique = newEnv.lien_boutique;
    form.lien_notation = newEnv.lien_notation;
  }
}, { immediate: true });

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
.form-page  { max-width: 760px; }
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a202c; margin-bottom: 1.5rem; }
.form-card  { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; padding: 1.75rem; display: flex; flex-direction: column; gap: 2rem; }
.form-section { display: flex; flex-direction: column; gap: 1rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #2d3748; margin-bottom: 0.25rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e9f0; }
.section-hint  { font-size: 0.8rem; color: #718096; margin-top: -0.5rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: #4a5568; }
.field input,
.field textarea,
.field .p-dropdown,
.field .p-inputnumber {
  width: 100% !important;
}
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.char-count { color: #a0aec0; font-size: 0.75rem; text-align: right; }
.required { color: #e53e3e; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #e5e9f0; }
</style>
