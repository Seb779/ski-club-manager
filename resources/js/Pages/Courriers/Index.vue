<template>
  <AppLayout>
    <Head title="Courriers" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold text-gray-900">Courriers du ski-club</h1>
      <Link href="/courriers/create" class="btn btn-primary">+ Nouveau courrier</Link>
    </div>

    <div class="space-y-2">
      <div v-if="courriers.data.length === 0" class="card text-center text-gray-400 py-10">
        Aucun courrier. Créez votre premier courrier.
      </div>

      <div v-for="c in courriers.data" :key="c.id"
        class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-4 hover:border-gray-300 transition-colors">

        <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 shrink-0">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>

        <div class="flex-1 min-w-0">
          <p class="font-medium text-gray-900 truncate">{{ c.titre }}</p>
          <p class="text-xs text-gray-400">
            {{ c.membres_count }} destinataire(s)
            <span v-if="c.saison"> · Saison {{ c.saison.libelle }}</span>
            <span v-if="c.envoye_le"> · {{ formatDate(c.envoye_le) }}</span>
          </p>
        </div>

        <!-- Modes d'envoi -->
        <div class="flex gap-1.5">
          <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">✉ Email</span>
          <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">📬 Postal</span>
        </div>

        <Badge :color="c.statut === 'envoye' ? 'green' : 'gray'">
          {{ c.statut === 'envoye' ? 'Envoyé' : 'Brouillon' }}
        </Badge>

        <div class="flex gap-2 ml-2">
          <Link :href="`/courriers/${c.id}`" class="text-xs text-gray-400 hover:text-ski-600">Voir</Link>
          <Link v-if="c.statut === 'brouillon'" :href="`/courriers/${c.id}/edit`"
            class="text-xs text-gray-400 hover:text-ski-600">Modifier</Link>
          <a :href="`/courriers/${c.id}/pdf`" target="_blank"
            class="text-xs text-gray-400 hover:text-gray-600">PDF</a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'

defineProps({ courriers: Object })

const formatDate = (d) => new Date(d).toLocaleDateString('fr-CH', { day: '2-digit', month: '2-digit', year: 'numeric' })
</script>
