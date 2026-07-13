<template>
  <AppLayout>
    <Head title="Tableau de bord" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-semibold text-gray-900">Tableau de bord</h1>
      <span v-if="saison" class="text-sm text-gray-500">Saison {{ saison.libelle }}</span>
    </div>

    <!-- Stats cotisations -->
    <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <StatCard label="Membres actifs" :value="stats.total" />
      <StatCard label="Cotisations payées" :value="stats.payees"
        :sub="`${stats.total ? Math.round(stats.payees / stats.total * 100) : 0}% encaissées`" />
      <StatCard label="En attente" :value="stats.envoyes" sub="Envoyées, non payées" />
      <StatCard label="Non envoyées" :value="stats.brouillon ?? 0" sub="Action requise" sub-color="yellow" />
    </div>
    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8 text-center">
      <p class="text-yellow-800 text-sm font-medium">Aucune saison active.</p>
      <Link href="/saisons/create" class="btn btn-primary mt-3 inline-flex">Créer une saison</Link>
    </div>

    <!-- Raccourcis -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Link href="/membres" class="card hover:border-ski-300 transition-colors group">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 bg-ski-50 rounded-lg flex items-center justify-center text-ski-600 group-hover:bg-ski-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          </div>
          <span class="font-medium text-gray-900">Membres</span>
        </div>
        <p class="text-sm text-gray-500">Gérer les membres, familles et enfants</p>
      </Link>

      <Link href="/courses" class="card hover:border-ski-300 transition-colors group">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center text-yellow-600 group-hover:bg-yellow-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l9-18 9 18M6.75 13.5h10.5" /></svg>
          </div>
          <span class="font-medium text-gray-900">Courses</span>
        </div>
        <p class="text-sm text-gray-500">Concours, chronos et classements</p>
      </Link>

      <Link href="/courriers" class="card hover:border-ski-300 transition-colors group">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
          </div>
          <span class="font-medium text-gray-900">Courriers</span>
        </div>
        <p class="text-sm text-gray-500">Communications du ski-club</p>
      </Link>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard  from '@/Components/UI/StatCard.vue'

defineProps({ saison: Object, stats: Object })
</script>
