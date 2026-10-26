<template>
  <header class="bg-white border-b border-gray-150 px-4 py-2.5 select-none sticky top-0 z-30 shadow-2xs">
    <div class="flex items-center justify-between gap-2.5 min-w-0">
      <!-- Left: Back Button OR Logo + Title & Subtitle -->
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <button
          v-if="showBack"
          @click="handleBack"
          type="button"
          class="flex items-center justify-center w-8 h-8 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 active:scale-95 transition shrink-0 cursor-pointer"
        >
          <ArrowLeft class="w-4 h-4" />
        </button>

        <div v-else class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white shrink-0 shadow-xs">
          <Book class="w-4 h-4" />
        </div>

        <div class="min-w-0 flex-1">
          <h2 class="text-sm font-bold text-gray-900 tracking-tight truncate leading-tight">{{ title }}</h2>
          <p v-if="subtitle" class="text-[10px] text-gray-500 truncate mt-0.5 leading-none">{{ subtitle }}</p>
        </div>
      </div>

      <!-- Right: Custom Actions, Notifications, Profile, Logout -->
      <div class="flex items-center gap-1 shrink-0">
        <slot name="actions" />

        <NuxtLink
          v-if="!hideNotifications"
          to="/notifikasi"
          class="relative p-1.5 text-gray-600 rounded-lg hover:bg-gray-100 transition"
          title="Notifikasi"
        >
          <Bell class="w-4 h-4" />
          <span
            v-if="unreadCount > 0"
            class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-primary text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-1 ring-white"
          >
            {{ unreadCount }}
          </span>
        </NuxtLink>

        <NuxtLink
          to="/profile"
          class="p-1.5 text-gray-600 rounded-lg hover:bg-gray-100 transition"
          title="Profil Saya"
        >
          <User class="w-4 h-4" />
        </NuxtLink>

        <button
          v-if="authStore.user?.role === 'SISWA'"
          @click="handleLogout"
          type="button"
          class="p-1.5 text-gray-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
          title="Keluar Akun"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ArrowLeft, Book, Bell, User, LogOut } from 'lucide-vue-next'
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
const config = useRuntimeConfig()
const unreadCount = ref(0)

onMounted(async () => {
  if (authStore.token && !props.hideNotifications) {
    try {
      const res = await $fetch<{ unread_count: number }>(`${config.public.apiBase}/notifications`, {
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

function handleLogout() {
  authStore.logout()
}
</script>
