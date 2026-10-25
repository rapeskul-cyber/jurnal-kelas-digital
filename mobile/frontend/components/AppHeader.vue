<template>
  <header class="bg-white border-b border-gray-150 px-4 py-3 select-none sticky top-16 z-20 shadow-2xs">
    <div class="flex items-center justify-between gap-3 min-w-0">
      <div class="flex items-center gap-2.5 min-w-0">
        <button
          v-if="showBack"
          @click="handleBack"
          type="button"
          class="flex items-center justify-center w-8 h-8 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 active:scale-95 transition shrink-0 cursor-pointer"
        >
          <ArrowLeft class="w-4 h-4" />
        </button>

        <div class="min-w-0">
          <h2 class="text-base font-bold text-gray-900 tracking-tight truncate">{{ title }}</h2>
          <p v-if="subtitle" class="text-[11px] text-gray-500 truncate">{{ subtitle }}</p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    title?: string
    subtitle?: string
    showBack?: boolean
    backTo?: string
    hideNotifications?: boolean
    hideLogout?: boolean
  }>(),
  {
    title: 'Jurnal Kelas',
    subtitle: '',
    showBack: false,
    backTo: '',
    hideNotifications: false,
    hideLogout: false,
  }
)

const router = useRouter()

function handleBack() {
  if (props.backTo) {
    navigateTo(props.backTo)
  } else {
    router.back()
  }
}
</script>
