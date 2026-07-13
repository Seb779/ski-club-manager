<template>
  <span :class="['badge', colorClass]"><slot /></span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ color: { type: String, default: 'gray' } })

const colorClass = computed(() => ({
  green:  'badge-green',
  yellow: 'badge-yellow',
  red:    'badge-red',
  blue:   'badge-blue',
  gray:   'badge-gray',
  purple: 'badge-purple',
}[props.color] ?? 'badge-gray'))
</script>
