<template>
  <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs hover:shadow-xs transition-shadow">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-gray-500 tracking-wide uppercase">{{ title }}</span>
      <div :class="['w-8 h-1 rounded-full', indicatorColor]"></div>
    </div>
    <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ value }}</div>
    <p v-if="subtext" class="text-xs text-gray-400 mt-2 font-medium">{{ subtext }}</p>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    title: string
    value: string | number
    subtext?: string
    color?: 'green' | 'orange' | 'blue' | 'red' | 'gray'
  }>(),
  {
    color: 'green'
  }
)

const indicatorColor = computed(() => {
  switch (props.color) {
    case 'green': return 'bg-emerald-500'
    case 'orange': return 'bg-amber-500'
    case 'blue': return 'bg-blue-600'
    case 'red': return 'bg-red-500'
    default: return 'bg-gray-300'
  }
})
</script>
