<template>
  <header class="h-20 bg-transparent flex items-center justify-between px-6 md:px-8 select-none">
    <div class="flex items-center gap-3 min-w-0">
      <button
        v-if="showBack"
        @click="handleBack"
        type="button"
        class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 transition shadow-2xs flex-shrink-0 cursor-pointer"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Kembali</span>
      </button>

      <div class="min-w-0">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight truncate">{{ title }}</h2>
        <p v-if="subtitle" class="text-xs text-gray-500 mt-0.5 truncate">{{ subtitle }}</p>
      </div>
    </div>

    <div class="flex items-center gap-3 flex-shrink-0">
      <slot name="actions" />

      <!-- Notification Bell -->
      <NuxtLink
        v-if="!hideNotifications"
        to="/notifikasi"
        class="relative w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs"
        title="Pemberitahuan"
      >
        <Bell class="w-5 h-5" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
        >
          {{ unreadCount }}
        </span>
      </NuxtLink>

      <!-- Logout Button -->
      <button
        v-if="!hideLogout"
        @click="handleLogout"
        type="button"
        class="flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 hover:border-red-200 hover:text-primary rounded-xl text-xs font-semibold text-gray-700 transition shadow-2xs cursor-pointer"
      >
        <LogOut class="w-4 h-4" />
        <span class="hidden sm:inline">Keluar</span>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Bell, LogOut, ArrowLeft } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

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
const authStore = useAuthStore()
const unreadCount = ref(0)

onMounted(async () => {
  if (authStore.token) {
    try {
      const res = await $fetch<{ unread_count: number }>(`${authStore.apiBase}/notifications`, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      })
      if (res && typeof res.unread_count === 'number') {
        unreadCount.value = res.unread_count
      }
    } catch (e) {
      // fallback
    }
  }
})

function handleBack() {
  if (props.backTo) {
    navigateTo(props.backTo)
  } else {
    router.back()
  }
}

async function handleLogout() {
  await authStore.logout()
}
</script>
