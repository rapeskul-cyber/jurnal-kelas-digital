<template>
  <div>
    <!-- Bottom Navigation Bar (mobile-first, always visible) -->
    <nav
      class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 px-2 pt-2 flex items-center justify-around z-30 shadow-lg"
      style="padding-bottom: calc(8px + env(safe-area-inset-bottom))"
    >
      <NuxtLink
        v-for="item in primaryMenus"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center gap-1 py-1 px-2 min-w-12 min-h-11 rounded-xl text-[10px] font-medium transition-colors active:scale-95"
        :class="isActive(item.to) ? 'text-primary font-bold bg-primary-subtle' : 'text-gray-500'"
      >
        <component :is="resolveIcon(item.icon)" class="w-5 h-5" />
        <span class="truncate max-w-16">{{ item.name }}</span>
      </NuxtLink>

      <!-- More Menu Button when menus > 4 -->
      <button
        v-if="hasMore"
        @click="showMore = !showMore"
        class="flex flex-col items-center gap-1 py-1 px-2 min-w-12 min-h-11 rounded-xl text-[10px] font-medium text-gray-500 active:scale-95"
      >
        <MoreHorizontal class="w-5 h-5" />
        <span>Lainnya</span>
      </button>
    </nav>

    <!-- More Sheet Modal -->
    <div v-if="showMore" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-end" @click.self="showMore = false">
      <div class="w-full bg-white rounded-t-3xl p-6 shadow-2xl space-y-3 max-h-[70vh] overflow-y-auto" style="padding-bottom: calc(24px + env(safe-area-inset-bottom))">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <h3 class="font-bold text-gray-900 text-sm">Menu Lainnya</h3>
          <button @click="showMore = false" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500" aria-label="Tutup">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2">
          <NuxtLink
            v-for="item in secondaryMenus"
            :key="item.to"
            :to="item.to"
            @click="showMore = false"
            class="flex items-center gap-3 p-3 min-h-11 rounded-xl border border-gray-100 bg-gray-50 text-xs font-semibold text-gray-700 active:scale-95"
          >
            <component :is="resolveIcon(item.icon)" class="w-4 h-4 text-primary flex-shrink-0" />
            <span class="truncate">{{ item.name }}</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
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
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { ROLE_MENUS } from '~/config/access'

const route = useRoute()
const authStore = useAuthStore()
const showMore = ref(false)

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
