<template>
  <div class="md:hidden">
    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-2 py-1.5 flex items-center justify-around z-30 shadow-lg pb-safe">
      <NuxtLink
        v-for="item in primaryMenus"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center gap-1 py-1 px-2 rounded-lg text-[10px] font-medium transition-colors"
        :class="isActive(item.to) ? 'text-primary font-bold' : 'text-gray-500'"
      >
        <component :is="resolveIcon(item.icon)" class="w-5 h-5" />
        <span class="truncate max-w-[64px]">{{ item.name }}</span>
      </NuxtLink>

      <!-- More Menu Button if menus > 4 -->
      <button
        v-if="hasMore"
        @click="showMore = !showMore"
        class="flex flex-col items-center gap-1 py-1 px-2 rounded-lg text-[10px] font-medium text-gray-500"
      >
        <MoreHorizontal class="w-5 h-5" />
        <span>Lainnya</span>
      </button>
    </nav>

    <!-- More Sheet Modal -->
    <div v-if="showMore" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs flex items-end">
      <div class="w-full bg-white rounded-t-3xl p-6 shadow-2xl space-y-3 max-h-[75vh] overflow-y-auto pb-10">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <h3 class="font-bold text-gray-900 text-sm">Menu Lainnya</h3>
          </div>
          <button @click="showMore = false" class="p-1.5 rounded-full bg-gray-100 text-gray-500 hover:text-gray-800">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2">
          <NuxtLink
            v-for="item in secondaryMenus"
            :key="item.to"
            :to="item.to"
            @click="showMore = false"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-150 bg-gray-50 text-xs font-semibold text-gray-700 active:scale-98 transition"
          >
            <component :is="resolveIcon(item.icon)" class="w-4 h-4 text-primary shrink-0" />
            <span class="truncate">{{ item.name }}</span>
          </NuxtLink>
        </div>

        <div class="pt-4 border-t border-gray-100">
          <button
            @click="handleLogout"
            class="w-full py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold flex items-center justify-center gap-2"
          >
            <LogOut class="w-4 h-4" />
            <span>Keluar Dari Akun</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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
  GraduationCap,
  Layers,
  ShieldAlert,
  Megaphone,
  MessageSquare,
  Settings,
  User,
  MoreHorizontal,
  X,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { ROLE_MENUS } from '~/config/access'

const route = useRoute()
const authStore = useAuthStore()
const config = useRuntimeConfig()
const showMore = ref(false)
const unreadCount = ref(0)

onMounted(async () => {
  if (authStore.token) {
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

function handleLogout() {
  showMore.value = false
  authStore.logout()
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
    GraduationCap,
    Layers,
    ShieldAlert,
    Megaphone,
    MessageSquare,
    Settings,
    User,
  }
  return map[iconName] || Home
}
</script>
