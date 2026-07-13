<template>
  <AppLayout>
    <Head title="Cotisations" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-gray-900">Cotisations</h1>
        <p class="text-sm text-gray-500 mt-0.5">Saison {{ saison?.libelle }}</p>
      </div>
      <div class="flex gap-2">
        <button @click="envoyerMasse" class="btn btn-secondary" :disabled="!stats.brouillon">
          ✉ Envoyer {{ stats.brouillon }} non-envoyée(s)
        </button>
        <Link href="/cotisations/create" class="btn btn-primary">+ Nouvelle</Link>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
      <StatCard label="Total" :value="stats.total" />
      <StatCard label="Non envoyées" :value="stats.brouillon" />
      <StatCard label="Envoyées" :value="stats.envoye" />
      <StatCard label="Payées" :value="stats.paye" />
      <StatCard label="Encaissé" :value="`CHF ${Number(stats.montant_encaisse).toFixed(0)}.—`" />
    </div>

    <!-- Filtres -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" type="search" placeholder="Rechercher un membre…"
        class="input max-w-xs" @input="doFilter" />
      <select v-model="statutFilter" class="input w-auto" @change="doFilter">
        <option value="">Tous les statuts</option>
        <option value="brouillon">Non envoyé</option>
        <option value="envoye">Envoyé</option>
        <option value="paye">Payé</option>
        <option value="annule">Annulé</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Membre</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Montant</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Envoi</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Statut</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="c in cotisations.data" :key="c.id" class="table-row-hover">
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ c.membre.prenom }} {{ c.membre.nom }}
            </td>
            <td class="px-4 py-3">
              <Badge :color="c.type === 'famille' ? 'purple' : 'blue'">
                {{ c.type === 'famille' ? 'Famille' : 'Individuel' }}
              </Badge>
            </td>
            <td class="px-4 py-3 text-right font-mono font-semibold">CHF {{ c.montant }}</td>
            <td class="px-4 py-3 text-xs">
              {{ c.mode_envoi === 'email' ? '✉ Email' : '📬 Postal' }}
            </td>
            <td class="px-4 py-3">
              <Badge :color="statutColor(c.statut)">{{ statutLabel(c.statut) }}</Badge>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button v-if="c.statut === 'brouillon'"
                  @click="envoyer(c)" class="text-xs text-sky-600 hover:underline">Envoyer</button>
                <button v-if="c.statut === 'envoye'"
                  @click="marquerPaye(c)" class="text-xs text-green-600 hover:underline">Marquer payé</button>
                <a :href="`/cotisations/${c.id}/pdf`" target="_blank"
                  class="text-xs text-gray-400 hover:text-gray-600">PDF</a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
  cotisations: Object, saison: Object, stats: Object, filters: Object
})

const search       = ref(props.filters?.search ?? '')
const statutFilter = ref(props.filters?.statut ?? '')

const statutLabel = (s) => ({ brouillon: 'Non envoyé', envoye: 'Envoyé', paye: 'Payé', annule: 'Annulé' }[s] ?? s)
const statutColor = (s) => ({ brouillon: 'gray', envoye: 'yellow', paye: 'green', annule: 'red' }[s] ?? 'gray')

let timer
const doFilter = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/cotisations', {
      search: search.value,
      statut: statutFilter.value,
      saison_id: props.saison?.id,
    }, { preserveState: true, replace: true })
  }, 300)
}

const envoyer    = (c) => router.post(`/cotisations/${c.id}/envoyer`, {}, { preserveScroll: true })
const marquerPaye = (c) => router.post(`/cotisations/${c.id}/marquer-paye`, {}, { preserveScroll: true })
const envoyerMasse = () => {
  if (confirm('Envoyer toutes les cotisations non-envoyées ?')) {
    router.post('/cotisations/envoyer-masse', { saison_id: props.saison?.id }, { preserveScroll: true })
  }
}
</script>
