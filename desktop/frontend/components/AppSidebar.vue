<template>
  <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between h-screen fixed left-0 top-0 z-30 transition-all select-none">
    <!-- Top Section -->
    <div class="p-6">
      <!-- Logo Header -->
      <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-sm flex-shrink-0">
          <Book class="w-5 h-5" />
        </div>
        <div>
          <h1 class="text-base font-bold text-gray-900 leading-tight">Jurnal Kelas</h1>
          <p class="text-[10px] font-bold text-primary tracking-wider uppercase">SMKN 71 JAKARTA</p>
        </div>
      </div>

      <!-- User Card (Clickable to /profile) -->
      <NuxtLink
        to="/profile"
        class="bg-gray-50 hover:bg-red-50/60 border border-gray-100 hover:border-red-100 rounded-2xl p-3 flex items-center gap-2.5 mb-6 transition group cursor-pointer"
        title="Buka Pengaturan Profil"
      >
        <div class="w-9 h-9 rounded-full bg-red-100 text-primary font-bold flex items-center justify-center text-xs flex-shrink-0 group-hover:ring-2 group-hover:ring-primary/20 transition">
          {{ userInitial }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold text-gray-900 group-hover:text-primary transition truncate" :title="authStore.user?.name">{{ authStore.user?.name || 'Pengguna' }}</p>
          <p class="text-[11px] text-gray-500 truncate" :title="userSubtitle">{{ userSubtitle }}</p>
        </div>
        <User class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary transition flex-shrink-0" />
      </NuxtLink>

      <!-- Navigation Menu -->
      <nav class="space-y-1">
        <NuxtLink
          v-for="item in currentMenus"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
          :class="[
            isActive(item.to)
              ? 'bg-red-50 text-primary font-semibold'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
        >
          <component :is="resolveIcon(item.icon)" class="w-5 h-5 flex-shrink-0" :class="isActive(item.to) ? 'text-primary' : 'text-gray-400'" />
          <span class="truncate">{{ item.name }}</span>
        </NuxtLink>
      </nav>
    </div>

    <!-- Bottom Academic Year Footer -->
    <div class="p-6 border-t border-gray-100">
      <p class="text-[11px] text-gray-400 font-medium">Tahun Ajaran</p>
      <p class="text-xs font-bold text-gray-800 mt-0.5">2026/2027 • Semester Ganjil</p>
    </div>
  </aside>
</template>

<script setup lang="ts">
import {
  Book,
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
  User,
  Layers,
  ShieldAlert,
  Radio,
  Settings,
  GraduationCap,
  MessageSquare,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { ROLE_MENUS, ROLE_LABELS } from '~/config/access'

const route = useRoute()
const authStore = useAuthStore()

const currentMenus = computed(() => {
  const role = authStore.user?.role || 'SISWA'
  return ROLE_MENUS[role] || ROLE_MENUS.SISWA
})

const userInitial = computed(() => {
  return authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U'
})

const userSubtitle = computed(() => {
  if (authStore.user?.role === 'SUPERADMIN') {
    return 'Super Administrator'
  }
  if (authStore.user?.role === 'SISWA') {
    return authStore.user.school_class?.name ? `Siswa ${authStore.user.school_class.name}` : 'Siswa'
  }
  if (authStore.user?.role === 'SEKRETARIS') {
    return authStore.user.school_class?.name ? `Sekretaris ${authStore.user.school_class.name}` : 'Sekretaris'
  }
  return ROLE_LABELS[authStore.user?.role || ''] || 'Guru'
})

function isActive(path: string) {
  if (path === '/dashboard') return route.path === '/dashboard' || route.path === '/'
  if (path === '/laporan-jurnal') return route.path.startsWith('/laporan-jurnal') || route.path.startsWith('/rekap-harian')
  if (path === '/rekap-semester') return route.path.startsWith('/rekap-semester')
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
    Layers,
    ShieldAlert,
    Radio,
    Settings,
    GraduationCap,
    MessageSquare,
  }
  return map[iconName] || Home
}
</script>
