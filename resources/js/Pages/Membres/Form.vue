<template>
  <AppLayout>
    <Head :title="mode === 'create' ? 'Nouveau membre' : 'Modifier membre'" />

    <div class="max-w-2xl">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/membres" class="text-gray-400 hover:text-gray-600 text-sm">← Membres</Link>
        <h1 class="text-xl font-semibold text-gray-900">
          {{ mode === 'create' ? 'Nouveau membre' : `Modifier ${membre?.prenom} ${membre?.nom}` }}
        </h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="card space-y-4">
          <h2 class="font-medium text-gray-700 text-sm">Type de membre</h2>
          <div class="flex gap-3">
            <label v-for="t in types" :key="t.value"
              class="flex-1 border rounded-lg p-3 cursor-pointer transition-colors"
              :class="form.type === t.value ? 'border-ski-500 bg-ski-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" v-model="form.type" :value="t.value" class="sr-only" />
              <p class="font-medium text-sm">{{ t.label }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t.desc }}</p>
            </label>
          </div>

          <!-- Parent (si enfant) -->
          <div v-if="form.type === 'enfant'">
            <label class="label">Parent / Chef de famille</label>
            <select v-model="form.parent_id" class="input">
              <option value="">— Aucun —</option>
              <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.prenom }} {{ p.nom }}</option>
            </select>
          </div>
        </div>

        <div class="card space-y-4">
          <h2 class="font-medium text-gray-700 text-sm">Identité</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Prénom *</label>
              <input v-model="form.prenom" type="text" class="input" required />
              <p v-if="errors.prenom" class="text-xs text-red-600 mt-1">{{ errors.prenom }}</p>
            </div>
            <div>
              <label class="label">Nom *</label>
              <input v-model="form.nom" type="text" class="input" required />
            </div>
          </div>
          <div>
            <label class="label">Date de naissance</label>
            <input v-model="form.date_naissance" type="date" class="input" />
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.moniteur" type="checkbox" class="rounded border-gray-300 text-ski-600" />
            Ce membre est moniteur
          </label>
        </div>

        <div class="card space-y-4">
          <h2 class="font-medium text-gray-700 text-sm">Coordonnées</h2>
          <div>
            <label class="label">Email</label>
            <input v-model="form.email" type="email" class="input" />
          </div>
          <div>
            <label class="label">Téléphone</label>
            <input v-model="form.telephone" type="tel" class="input" />
          </div>
          <div>
            <label class="label">Adresse</label>
            <input v-model="form.adresse" type="text" class="input" />
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label">NPA</label>
              <input v-model="form.npa" type="text" class="input" maxlength="10" />
            </div>
            <div class="col-span-2">
              <label class="label">Localité</label>
              <input v-model="form.localite" type="text" class="input" />
            </div>
          </div>
        </div>

        <div class="card space-y-4">
          <h2 class="font-medium text-gray-700 text-sm">Préférences</h2>
          <div>
            <label class="label">Mode d'envoi des courriers</label>
            <div class="flex gap-4 mt-1">
              <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input v-model="form.preference_envoi" type="radio" value="email" class="text-ski-600" />
                ✉ Email
              </label>
              <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input v-model="form.preference_envoi" type="radio" value="postal" class="text-ski-600" />
                📬 Courrier postal
              </label>
            </div>
          </div>
          <div>
            <label class="label">Notes internes</label>
            <textarea v-model="form.notes" rows="3" class="input" />
          </div>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn btn-primary" :disabled="processing">
            {{ processing ? 'Enregistrement…' : (mode === 'create' ? 'Créer le membre' : 'Enregistrer') }}
          </button>
          <Link href="/membres" class="btn btn-secondary">Annuler</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ membre: Object, parents: Array, mode: String })

const types = [
  { value: 'individuel',   label: 'Individuel',    desc: 'Membre seul, sans famille' },
  { value: 'chef_famille', label: 'Chef de famille', desc: 'Parent avec enfants' },
  { value: 'enfant',       label: 'Enfant',         desc: 'Rattaché à un parent' },
]

const form = useForm({
  type:             props.membre?.type             ?? 'individuel',
  parent_id:        props.membre?.parent_id        ?? '',
  prenom:           props.membre?.prenom           ?? '',
  nom:              props.membre?.nom              ?? '',
  date_naissance:   props.membre?.date_naissance   ?? '',
  email:            props.membre?.email            ?? '',
  telephone:        props.membre?.telephone        ?? '',
  adresse:          props.membre?.adresse          ?? '',
  npa:              props.membre?.npa              ?? '',
  localite:         props.membre?.localite         ?? '',
  preference_envoi: props.membre?.preference_envoi ?? 'email',
  moniteur:         props.membre?.moniteur         ?? false,
  actif:            props.membre?.actif            ?? true,
  notes:            props.membre?.notes            ?? '',
})

const errors     = ref({})
const processing = ref(false)

const submit = () => {
  if (props.mode === 'create') {
    form.post('/membres')
  } else {
    form.put(`/membres/${props.membre.id}`)
  }
}
</script>
