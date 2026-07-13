<template>
  <AppLayout>
    <Head :title="`Classement — ${course.nom}`" />

    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <Link :href="`/courses/${course.id}`" class="text-gray-400 hover:text-gray-600 text-sm">← {{ course.nom }}</Link>
        <h1 class="text-xl font-semibold text-gray-900">Classements</h1>
      </div>
      <a :href="`/courses/${course.id}/classement/pdf`" target="_blank" class="btn btn-secondary">
        📄 Exporter PDF
      </a>
    </div>

    <div v-if="categories.length === 0" class="card text-center text-gray-500 py-8">
      Aucune catégorie configurée pour cette saison.
      <Link href="/categories" class="text-ski-600 hover:underline ml-1">Configurer</Link>
    </div>

    <div v-for="cat in categories" :key="cat.id" class="mb-6">
      <div class="flex items-center gap-3 mb-3">
        <h2 class="font-semibold text-gray-900">{{ cat.nom }}</h2>
        <span class="text-xs text-gray-400">{{ cat.annee_naissance_min }}–{{ cat.annee_naissance_max }}</span>
        <span class="badge" :class="cat.genre === 'M' ? 'badge-blue' : cat.genre === 'F' ? 'badge-purple' : 'badge-gray'">
          {{ cat.genre === 'M' ? 'Garçons' : cat.genre === 'F' ? 'Filles' : 'Mixte' }}
        </span>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-center px-3 py-2.5 font-medium text-gray-600 w-12">Rang</th>
              <th class="text-center px-3 py-2.5 font-medium text-gray-600 w-12">№</th>
              <th class="text-left px-3 py-2.5 font-medium text-gray-600">Participant</th>
              <th v-for="m in course.nb_manches" :key="m"
                class="text-right px-3 py-2.5 font-medium text-gray-600">Manche {{ m }}</th>
              <th class="text-right px-3 py-2.5 font-medium text-gray-600">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="classements[cat.id]?.length === 0">
              <td colspan="99" class="px-4 py-6 text-center text-gray-400 text-xs">
                Aucun résultat pour cette catégorie
              </td>
            </tr>
            <tr v-for="(res, idx) in classements[cat.id]" :key="res.participant.id"
              :class="['table-row-hover', idx < 3 ? 'font-medium' : '']">
              <td class="px-3 py-2.5 text-center">
                <span :class="['w-7 h-7 rounded-full inline-flex items-center justify-center text-xs font-bold',
                  idx === 0 ? 'bg-yellow-100 text-yellow-800' :
                  idx === 1 ? 'bg-gray-100 text-gray-700' :
                  idx === 2 ? 'bg-orange-100 text-orange-700' : 'text-gray-500']">
                  {{ idx + 1 }}
                </span>
              </td>
              <td class="px-3 py-2.5 text-center">
                <span class="w-7 h-7 rounded bg-ski-600 text-white text-xs font-bold inline-flex items-center justify-center">
                  {{ res.participant.dossard }}
                </span>
              </td>
              <td class="px-3 py-2.5">
                {{ res.participant.membre.prenom }} {{ res.participant.membre.nom }}
              </td>
              <td v-for="m in course.nb_manches" :key="m" class="px-3 py-2.5 text-right font-mono text-gray-700">
                {{ getTemps(res.participant, m) ?? '—' }}
              </td>
              <td class="px-3 py-2.5 text-right font-mono font-bold text-gray-900">
                {{ res.temps_formate }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ course: Object, categories: Array, classements: Object })

const getTemps = (participant, manche) => {
  const c = participant.chronos?.find(ch => ch.manche === manche)
  return c?.disqualifie ? 'DQ' : c?.temps_formate
}
</script>
