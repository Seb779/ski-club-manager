<template>
  <Link :href="href"
    :class="[
      'px-3 py-1.5 rounded-lg text-sm transition-colors',
      active
        ? 'bg-ski-50 text-ski-700 font-medium'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
    ]">
    <slot />
  </Link>
</template>

<script setup>
defineProps({ href: String, active: Boolean })
</script>
