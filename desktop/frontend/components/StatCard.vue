<template>
  <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs hover:shadow-xs transition-shadow flex flex-col justify-between overflow-hidden">
    <div class="flex items-center justify-between gap-1 mb-2">
      <span class="text-xs sm:text-sm font-semibold text-gray-500 tracking-wide uppercase truncate" :title="title">{{ title }}</span>
      <div :class="['w-6 sm:w-10 h-1 rounded-full shrink-0', indicatorColor]"></div>
    </div>
    
    <div class="my-1">
      <div class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight leading-snug break-words">
        {{ value }}
      </div>
    </div>

    <p v-if="subtext" class="text-xs sm:text-sm text-gray-500 font-medium truncate mt-1" :title="subtext">
      {{ subtext }}
    </p>
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
