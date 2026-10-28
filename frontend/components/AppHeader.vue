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

      <!-- Kritik & Saran Link -->
      <a
        href="https://saran.rapiuw.ai.id"
        target="_blank"
        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg md:rounded-xl bg-blue-50 border border-blue-200 text-primary hover:bg-blue-100 text-xs font-semibold transition shadow-2xs"
        title="Kirim kritik dan saran portal"
      >
        <span>Kritik dan Saran</span>
      </a>

      <!-- Notification Bell -->
      <NuxtLink
        v-if="!hideNotifications"
        to="/notifikasi"
        class="relative w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs"
        title="Pemberitahuan"
      >
        <Bell class="w-4 h-4 md:w-5 md:h-5" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 w-4 h-4 md:w-5 md:h-5 bg-primary text-white text-[9px] md:text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
        >
          {{ unreadCount }}
        </span>
      </NuxtLink>

      <!-- Profile Link on Mobile with Avatar -->
      <NuxtLink
        to="/profile"
        class="md:hidden flex items-center justify-center p-0.5 rounded-full ring-2 ring-gray-200 hover:ring-primary transition overflow-hidden w-8 h-8"
        title="Profil Saya"
      >
        <img
          v-if="(authStore.user as any)?.avatar_url"
          :src="(authStore.user as any)?.avatar_url"
          :alt="authStore.user?.name || 'User'"
          class="w-full h-full object-cover"
        />
        <User v-else class="w-4 h-4 text-gray-600" />
      </NuxtLink>

      <!-- Logout Button -->
      <button
        v-if="!hideLogout"
        @click="handleLogout"
        type="button"
        class="flex items-center gap-1.5 md:gap-2 px-2.5 py-1.5 md:px-3.5 md:py-2 bg-white border border-gray-200 hover:border-red-200 hover:text-primary rounded-lg md:rounded-xl text-xs font-semibold text-gray-700 transition shadow-2xs cursor-pointer"
      >
        <LogOut class="w-4 h-4" />
        <span class="hidden sm:inline">Keluar</span>
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
