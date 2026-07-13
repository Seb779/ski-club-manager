<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-screen-xl mx-auto px-4 flex items-center gap-4 h-14">
        <Link href="/" class="flex items-center gap-2 font-semibold text-ski-700 shrink-0">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Ski-Club Manager
        </Link>

        <div class="flex items-center gap-1 ml-4">
          <NavLink href="/membres" :active="route().current('membres.*')">Membres</NavLink>
          <NavLink href="/cotisations" :active="route().current('cotisations.*')">Cotisations</NavLink>
          <NavLink href="/groupes" :active="route().current('groupes.*')">Groupes</NavLink>
          <NavLink href="/courses" :active="route().current('courses.*')">Courses</NavLink>
          <NavLink href="/courriers" :active="route().current('courriers.*')">Courriers</NavLink>
          <NavLink href="/saisons" :active="route().current('saisons.*') || route().current('categories.*')">Paramètres</NavLink>
        </div>

        <div class="ml-auto flex items-center gap-3">
          <span v-if="saisonActive" class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
            Saison {{ saisonActive.libelle }}
          </span>
        </div>
      </div>
    </nav>

    <!-- Flash messages -->
    <div v-if="$page.props.flash?.success || $page.props.errors"
      class="max-w-screen-xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash?.success"
        class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="Object.keys($page.props.errors || {}).length"
        class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        <p v-for="(err, key) in $page.props.errors" :key="key">{{ err }}</p>
      </div>
    </div>

    <!-- Page content -->
    <main class="max-w-screen-xl mx-auto px-4 py-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import NavLink from '@/Components/UI/NavLink.vue'

const page   = usePage()
const saisonActive = computed(() => page.props.saisonActive)
</script>
