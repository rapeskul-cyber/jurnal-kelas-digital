<template>
  <header class="bg-white md:bg-transparent border-b md:border-b-0 border-gray-200 px-4 md:px-8 py-3 md:py-0 md:h-20 select-none sticky md:static top-0 z-30 shadow-2xs md:shadow-none flex items-center justify-between min-w-0">
    <div class="flex items-center gap-3 min-w-0 flex-1">
      <button
        v-if="showBack"
        @click="handleBack"
        type="button"
        class="flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-50 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 transition shadow-2xs shrink-0 cursor-pointer active:scale-95"
      >
        <ArrowLeft class="w-4 h-4 md:w-5 md:h-5" />
        <span class="hidden sm:inline">Kembali</span>
      </button>

      <div v-else class="md:hidden w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white shrink-0 shadow-xs">
        <Book class="w-5 h-5" />
      </div>

      <div class="min-w-0 flex-1">
        <h2 class="text-base sm:text-lg md:text-2xl font-bold text-gray-900 tracking-tight truncate leading-tight">{{ title }}</h2>
        <p v-if="subtitle" class="text-xs md:text-sm text-gray-500 mt-0.5 truncate leading-normal">{{ subtitle }}</p>
      </div>
    </div>

    <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
      <slot name="actions" />

      <!-- Notification Bell -->
      <NuxtLink
        v-if="!hideNotifications"
        to="/notifikasi"
        class="relative p-2 md:p-0 md:w-10 md:h-10 rounded-xl md:bg-white md:border md:border-gray-200 md:shadow-2xs flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 md:hover:bg-gray-50 md:hover:border-gray-300 transition active:scale-95 cursor-pointer"
        title="Pemberitahuan"
      >
        <Bell class="w-5 h-5" />
        <span
          v-if="unreadCount > 0"
          class="absolute top-1 right-1 md:-top-1 md:-right-1 w-4 h-4 md:w-5 md:h-5 bg-primary text-white text-[9px] md:text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
        >
          {{ unreadCount }}
        </span>
      </NuxtLink>

      <!-- Profile Icon on Mobile -->
      <NuxtLink
        to="/profile"
        class="md:hidden p-2 text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition active:scale-95 cursor-pointer"
        title="Profil Saya"
      >
        <User class="w-5 h-5" />
      </NuxtLink>

      <!-- Logout Button (Desktop untuk semua role) -->
      <button
        v-if="!hideLogout"
        @click="handleLogout"
        type="button"
        class="hidden md:flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 hover:border-red-200 hover:text-primary rounded-xl text-xs sm:text-sm font-semibold text-gray-700 transition shadow-2xs cursor-pointer active:scale-95"
      >
        <LogOut class="w-4 h-4 md:w-4.5 md:h-4.5" />
        <span>Keluar</span>
      </button>

      <!-- Logout Button (Khusus Mobile untuk role SISWA saja, karena role lain logout via menu 'Lainnya') -->
      <button
        v-if="!hideLogout && authStore.user?.role === 'SISWA'"
        @click="handleLogout"
        type="button"
        class="md:hidden p-2 text-gray-500 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition active:scale-95 cursor-pointer"
        title="Keluar Akun"
      >
        <LogOut class="w-5 h-5" />
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Bell, LogOut, ArrowLeft, Book, User } from 'lucide-vue-next'
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
