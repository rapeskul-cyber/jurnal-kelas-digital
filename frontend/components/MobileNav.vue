<template>
  <div class="md:hidden">
    <!-- Bottom Navigation Bar with Perfect Touch Target & Active State -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 px-2 py-1 flex items-center justify-around z-40 shadow-lg pb-safe select-none">
      <NuxtLink
        v-for="item in primaryMenus"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center justify-center gap-1 py-1 px-2 rounded-xl text-[10px] transition-all min-h-[48px] min-w-[56px] active:scale-95 cursor-pointer touch-manipulation"
        :class="isActive(item.to) ? 'text-primary font-bold bg-blue-50/90 shadow-2xs' : 'text-gray-500 hover:text-gray-900 font-medium'"
      >
        <component :is="resolveIcon(item.icon)" class="w-5 h-5 transition-transform" :class="isActive(item.to) ? 'scale-110' : ''" />
        <span class="truncate max-w-[64px] leading-tight text-center">{{ item.name }}</span>
      </NuxtLink>

      <!-- More Menu Button if menus > 4 -->
      <button
        v-if="hasMore"
        @click="showMore = !showMore"
        type="button"
        class="flex flex-col items-center justify-center gap-1 py-1 px-2 rounded-xl text-[10px] transition-all min-h-[48px] min-w-[56px] active:scale-95 cursor-pointer touch-manipulation"
        :class="showMore ? 'text-primary font-bold bg-blue-50/90' : 'text-gray-500 hover:text-gray-900 font-medium'"
      >
        <MoreHorizontal class="w-5 h-5" />
        <span class="leading-tight">Lainnya</span>
      </button>
    </nav>

    <!-- More Sheet Modal (Bottom Drawer) -->
    <div v-if="showMore" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-end animate-fade-in" @click.self="showMore = false">
      <div class="w-full bg-white rounded-t-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto pb-12 pb-safe animate-slide-up">
        <!-- Handle Bar for Gesture Intuition -->
        <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-1 mb-2"></div>

        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <h3 class="font-bold text-gray-900 text-sm">Menu Tambahan</h3>
            <p class="text-[11px] text-gray-500">Pilihan menu navigasi untuk akun Anda</p>
          </div>
          <button @click="showMore = false" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 transition cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2.5 pt-1">
          <NuxtLink
            v-for="item in secondaryMenus"
            :key="item.to"
            :to="item.to"
            @click="showMore = false"
            class="flex items-center gap-3 p-3 rounded-2xl border transition active:scale-98 cursor-pointer"
            :class="isActive(item.to) ? 'bg-blue-50 border-blue-200 text-primary font-bold' : 'border-gray-150 bg-gray-50/70 hover:bg-gray-100 text-gray-700 font-medium'"
          >
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" :class="isActive(item.to) ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-primary'">
              <component :is="resolveIcon(item.icon)" class="w-4 h-4" />
            </div>
            <span class="truncate text-xs">{{ item.name }}</span>
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
  GraduationCap,
  Layers,
  ShieldAlert,
  Megaphone,
  MessageSquare,
  Settings,
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
