<template>
  <div class="md:hidden">
    <!-- Top Mobile Bar -->
    <div class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 px-4 flex items-center justify-between z-30">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
          <Book class="w-4 h-4" />
        </div>
        <span class="font-bold text-gray-900 text-sm">Jurnal Kelas</span>
      </div>

      <div class="flex items-center gap-2">
        <NuxtLink to="/profile" class="p-2 text-gray-600 hover:text-primary" title="Profil Akun">
          <User class="w-5 h-5" />
        </NuxtLink>
        <NuxtLink to="/notifikasi" class="relative p-2 text-gray-600">
          <Bell class="w-5 h-5" />
          <span v-if="unreadCount > 0" class="absolute top-1 right-1 w-4 h-4 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center">
            {{ unreadCount }}
          </span>
        </NuxtLink>
        <!-- Khusus Siswa: Tombol Logout di bar atas karena Siswa tidak memiliki drawer 'Lainnya' -->
        <button
          v-if="authStore.user?.role === 'SISWA'"
          @click="authStore.logout"
          type="button"
          class="p-2 text-gray-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
          title="Keluar Akun"
        >
          <LogOut class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-2 py-2 flex items-center justify-around z-30 shadow-lg pb-safe">
      <NuxtLink
        v-for="item in primaryMenus"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center gap-1 py-1 px-1.5 rounded-lg text-[10px] font-medium transition-colors"
        :class="isActive(item.to) ? 'text-primary font-bold' : 'text-gray-500'"
      >
        <component :is="resolveIcon(item.icon)" class="w-5 h-5 shrink-0" />
        <span class="truncate max-w-[62px] text-center leading-tight">{{ item.name }}</span>
      </NuxtLink>

      <!-- More Menu Button if menus > 4 -->
      <button
        v-if="hasMore"
        @click="showMore = !showMore"
        class="flex flex-col items-center gap-1 py-1 px-1.5 rounded-lg text-[10px] font-medium text-gray-500 hover:text-gray-700"
      >
        <MoreHorizontal class="w-5 h-5 shrink-0" />
        <span class="truncate max-w-[62px] text-center leading-tight">Lainnya</span>
      </button>
    </nav>

    <!-- More Sheet Modal -->
    <div v-if="showMore" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs flex items-end">
      <div class="w-full bg-white rounded-t-3xl p-6 shadow-2xl space-y-3 max-h-[70vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <h3 class="font-bold text-gray-900 text-sm">Menu Lainnya</h3>
          <button @click="showMore = false" class="text-gray-400 hover:text-gray-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2">
          <NuxtLink
            v-for="item in secondaryMenus"
            :key="item.to"
            :to="item.to"
            @click="showMore = false"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50 text-xs font-semibold text-gray-700"
          >
            <component :is="resolveIcon(item.icon)" class="w-4 h-4 text-primary" />
            <span class="truncate">{{ item.name }}</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Book,
  Bell,
  LogOut,
  Home,
  BookOpen,
  CalendarCheck,
  ClipboardCheck,
  FileText,
  Mail,
  Calendar,
  FolderOpen,
  Edit3,
  BarChart2,
  MailCheck,
  FileSpreadsheet,
  Users,
  MoreHorizontal,
  X,
  User,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { ROLE_MENUS } from '~/config/access'

const route = useRoute()
const authStore = useAuthStore()
const showMore = ref(false)
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

const allMenus = computed(() => {
  const role = authStore.user?.role || 'SISWA'
  return ROLE_MENUS[role] || ROLE_MENUS.SISWA
})

const primaryMenus = computed(() => allMenus.value.slice(0, 4))
const secondaryMenus = computed(() => allMenus.value.slice(4))
const hasMore = computed(() => allMenus.value.length > 4)

function isActive(path: string) {
  if (path === '/dashboard') return route.path === '/dashboard' || route.path === '/'
  return route.path.startsWith(path)
}

function resolveIcon(iconName: string) {
  const map: Record<string, any> = {
    Home,
    BookOpen,
    CalendarCheck,
    ClipboardCheck,
    FileText,
    Mail,
    Calendar,
    FolderOpen,
    Edit3,
    BarChart2,
    MailCheck,
    FileSpreadsheet,
    Users,
  }
  return map[iconName] || Home
}
</script>
