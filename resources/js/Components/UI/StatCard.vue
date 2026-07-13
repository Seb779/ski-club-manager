<template>
  <div class="bg-white rounded-xl border border-gray-200 p-4">
    <p class="text-xs text-gray-500 mb-1">{{ label }}</p>
    <p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
    <p v-if="sub" class="text-xs mt-1" :class="subColor ? `text-${subColor}-600` : 'text-gray-400'">{{ sub }}</p>
  </div>
</template>

<script setup>
defineProps({ label: String, value: [String, Number], sub: String, subColor: String })
</script>
