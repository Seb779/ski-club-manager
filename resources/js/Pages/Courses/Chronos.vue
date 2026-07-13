<template>
  <AppLayout>
    <Head :title="`Chronos — ${course.nom}`" />

    <div class="flex items-center gap-3 mb-6">
      <Link :href="`/courses/${course.id}`" class="text-gray-400 hover:text-gray-600 text-sm">← {{ course.nom }}</Link>
      <h1 class="text-xl font-semibold text-gray-900">Saisie des chronos</h1>
      <Badge :color="statutColor(course.statut)">{{ course.statut }}</Badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Saisie -->
      <div class="card">
        <h2 class="font-medium text-gray-900 mb-4">Enregistrer un chrono</h2>
        <form @submit.prevent="saisir" class="space-y-4">
          <div>
            <label class="label">Dossard ou nom du participant</label>
            <input v-model="form.identifiant" type="text" class="input"
              placeholder="Ex: 3 ou Favre" required autofocus />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Manche</label>
              <select v-model="form.manche" class="input">
                <option v-for="n in course.nb_manches" :key="n" :value="n">Manche {{ n }}</option>
              </select>
            </div>
            <div>
              <label class="label">Temps (m:ss.cc)</label>
              <input v-model="form.temps" type="text" class="input font-mono"
                placeholder="1:23.45" :disabled="form.disqualifie" />
            </div>
          </div>

          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.disqualifie" type="checkbox" class="rounded border-gray-300 text-red-600" />
            Disqualifié (DQ)
          </label>
          <input v-if="form.disqualifie" v-model="form.raison_dq"
            type="text" class="input" placeholder="Raison de la DQ" />

          <div v-if="erreur" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ erreur }}</div>
          <div v-if="confirmation" class="text-sm text-green-700 bg-green-50 px-3 py-2 rounded-lg">{{ confirmation }}</div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            {{ loading ? 'Enregistrement…' : 'Valider le chrono' }}
          </button>
        </form>
      </div>

      <!-- Liste des chronos saisis -->
      <div>
        <h2 class="font-medium text-gray-900 mb-4">
          Participants ({{ course.participants.length }})
          <span class="text-gray-400 font-normal text-sm ml-1">
            — {{ saisis }} chrono(s) saisi(s)
          </span>
        </h2>

        <div class="space-y-1 max-h-[520px] overflow-y-auto">
          <div v-for="p in course.participants" :key="p.id"
            class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-100 bg-white hover:border-gray-200">

            <!-- Dossard -->
            <div class="w-8 h-8 rounded bg-ski-600 text-white text-sm font-bold flex items-center justify-center shrink-0">
              {{ p.dossard }}
            </div>

            <!-- Infos -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ p.membre.prenom }} {{ p.membre.nom }}
              </p>
              <p v-if="p.categorie" class="text-xs text-gray-400">{{ p.categorie.nom }}</p>
            </div>

            <!-- Chronos par manche -->
            <div class="flex gap-2">
              <div v-for="manche in course.nb_manches" :key="manche" class="text-center">
                <p class="text-xs text-gray-400">M{{ manche }}</p>
                <template v-if="getChrono(p, manche)">
                  <span v-if="getChrono(p, manche).disqualifie" class="text-xs font-mono text-red-600">DQ</span>
                  <span v-else class="text-sm font-mono font-semibold text-gray-900">
                    {{ getChrono(p, manche).temps_formate ?? '—' }}
                  </span>
                </template>
                <span v-else class="text-xs text-gray-300">—</span>
              </div>
            </div>

            <!-- Éditer -->
            <button @click="prefill(p)" class="text-xs text-gray-400 hover:text-ski-600 px-2 py-1">
              ✏
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({ course: Object })

const form = ref({ identifiant: '', manche: 1, temps: '', disqualifie: false, raison_dq: '' })
const loading     = ref(false)
const erreur      = ref('')
const confirmation = ref('')

const saisis = computed(() =>
  props.course.participants.filter(p => p.chronos?.length > 0).length
)

const getChrono = (p, manche) => p.chronos?.find(c => c.manche === manche)

const statutColor = (s) => ({ preparation: 'gray', actif: 'green', termine: 'blue', archive: 'gray' }[s] ?? 'gray')

const prefill = (p) => {
  form.value.identifiant = String(p.dossard)
}

const saisir = () => {
  loading.value = true
  erreur.value  = ''
  confirmation.value = ''

  router.post(`/courses/${props.course.id}/chronos`, form.value, {
    preserveScroll: true,
    onSuccess: () => {
      confirmation.value = `Chrono enregistré pour dossard ${form.value.identifiant}`
      form.value = { identifiant: '', manche: form.value.manche, temps: '', disqualifie: false, raison_dq: '' }
      loading.value = false
    },
    onError: (errors) => {
      erreur.value  = errors.identifiant ?? errors.temps ?? 'Erreur de saisie'
      loading.value = false
    },
  })
}
</script>
