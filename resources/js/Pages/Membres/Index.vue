<template>
  <AppLayout>
    <Head title="Membres" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold text-gray-900">Membres</h1>
      <Link href="/membres/create" class="btn btn-primary">+ Nouveau membre</Link>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-3 mb-5">
      <input v-model="search" type="search" placeholder="Rechercher…"
        class="input max-w-xs" @input="doSearch" />
      <select v-model="typeFilter" class="input w-auto" @change="doSearch">
        <option value="">Tous les types</option>
        <option value="individuel">Individuels</option>
        <option value="chef_famille">Chefs de famille</option>
        <option value="enfant">Enfants</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Membre</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Envoi</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Groupes</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="m in membres.data" :key="m.id" class="table-row-hover">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="w-8 h-8 rounded-full bg-ski-100 text-ski-700 text-xs font-semibold flex items-center justify-center shrink-0">
                  {{ initiales(m) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ m.prenom }} {{ m.nom }}</p>
                  <p v-if="m.parent" class="text-xs text-gray-400">↳ Famille {{ m.parent.nom }}</p>
                  <p v-if="m.enfants?.length" class="text-xs text-gray-400">{{ m.enfants.length }} enfant(s)</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <Badge :color="typeColor(m.type)">{{ typeLabel(m.type) }}</Badge>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ m.email ?? '—' }}</td>
            <td class="px-4 py-3">
              <span class="text-xs" :class="m.preference_envoi === 'email' ? 'text-blue-600' : 'text-yellow-600'">
                {{ m.preference_envoi === 'email' ? '✉ Email' : '📬 Postal' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap gap-1">
                <span v-for="g in m.groupes" :key="g.id"
                  class="text-xs px-2 py-0.5 rounded-full text-white"
                  :style="{ background: g.couleur }">
                  {{ g.nom }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="`/membres/${m.id}`" class="text-xs text-gray-400 hover:text-ski-600">Voir</Link>
                <Link :href="`/membres/${m.id}/edit`" class="text-xs text-gray-400 hover:text-ski-600">Modifier</Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="membres.last_page > 1" class="border-t border-gray-100 px-4 py-3 flex gap-2">
        <Link v-for="link in membres.links" :key="link.label"
          :href="link.url ?? '#'"
          v-html="link.label"
          :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-ski-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 pointer-events-none' : '']" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({ membres: Object, saison: Object, filters: Object })

const search     = ref(props.filters?.search ?? '')
const typeFilter = ref(props.filters?.type ?? '')

const initiales = (m) => (m.prenom[0] + m.nom[0]).toUpperCase()

const typeLabel = (t) => ({ individuel: 'Individuel', chef_famille: 'Famille', enfant: 'Enfant' }[t] ?? t)
const typeColor = (t) => ({ individuel: 'blue', chef_famille: 'purple', enfant: 'green' }[t] ?? 'gray')

let timer
const doSearch = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/membres', { search: search.value, type: typeFilter.value }, { preserveState: true, replace: true })
  }, 300)
}
</script>
