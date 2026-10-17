<template>
  <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-gray-100 px-4 h-14 flex items-center justify-between select-none shadow-2xs">
    <!-- Left: Back / Logo + Title -->
    <div class="flex items-center gap-2.5 min-w-0">
      <button
        v-if="showBack"
        @click="handleBack"
        type="button"
        class="w-8 h-8 rounded-full bg-gray-100 active:scale-95 flex items-center justify-center text-gray-700 transition flex-shrink-0 cursor-pointer"
        aria-label="Kembali"
      >
        <ArrowLeft class="w-4 h-4" />
      </button>
      <div v-else class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white flex-shrink-0">
        <Book class="w-4 h-4" />
      </div>

      <div class="min-w-0">
        <h1 class="text-sm font-bold text-gray-900 tracking-tight truncate leading-tight">{{ title }}</h1>
        <p v-if="subtitle" class="text-[11px] text-gray-500 truncate leading-tight">{{ subtitle }}</p>
      </div>
    </div>

    <!-- Right: Role badge + Notification + Logout -->
    <div class="flex items-center gap-1.5 flex-shrink-0">
      <span
        v-if="roleBadge"
        class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider"
        :class="roleBadge.class"
      >
        {{ roleBadge.label }}
      </span>

      <NuxtLink
        v-if="!hideNotifications"
        to="/notifikasi"
        class="relative w-8 h-8 rounded-full bg-gray-50 active:scale-95 flex items-center justify-center text-gray-600 transition"
        title="Pemberitahuan"
      >
        <Bell class="w-4 h-4" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-0.5 bg-primary text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </NuxtLink>

      <button
        v-if="!hideLogout"
        @click="handleLogout"
        type="button"
        class="w-8 h-8 rounded-full bg-gray-50 active:scale-95 flex items-center justify-center text-gray-500 transition cursor-pointer"
        title="Keluar"
      >
        <LogOut class="w-4 h-4" />
      </button>

      <slot name="actions" />
    </div>
  </header>
</template>

<script setup lang="ts">
import { Bell, LogOut, ArrowLeft, Book } from 'lucide-vue-next'
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

const roleBadge = computed(() => {
  const role = authStore.user?.role
  if (role === 'SISWA') return { label: 'Siswa', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' }
  if (role === 'SEKRETARIS') return { label: 'Sekretaris', class: 'bg-amber-50 text-amber-700 border border-amber-200' }
  if (role === 'GURU_PENGAJAR') return { label: 'Guru', class: 'bg-blue-50 text-blue-700 border border-blue-200' }
  if (role === 'GURU_PEMBIMBING') return { label: 'Pembimbing', class: 'bg-purple-50 text-purple-700 border border-purple-200' }
  return null
})

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
      // badge stays hidden when API unreachable
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
