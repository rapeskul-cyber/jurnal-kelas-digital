<template>
  <div class="bg-white rounded-2xl border border-gray-150 p-3.5 sm:p-5 shadow-2xs hover:shadow-xs transition-shadow flex flex-col justify-between overflow-hidden">
    <div class="flex items-center justify-between gap-1 mb-1.5">
      <span class="text-[10px] sm:text-xs font-semibold text-gray-500 tracking-wide uppercase truncate" :title="title">{{ title }}</span>
      <div :class="['w-5 sm:w-8 h-1 rounded-full shrink-0', indicatorColor]"></div>
    </div>
    
    <div class="my-0.5">
      <div class="text-sm sm:text-2xl font-extrabold text-gray-900 tracking-tight leading-snug break-words">
        {{ value }}
      </div>
    </div>

    <p v-if="subtext" class="text-[9px] sm:text-xs text-gray-400 font-medium truncate mt-0.5" :title="subtext">
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
