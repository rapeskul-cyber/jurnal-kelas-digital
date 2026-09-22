<script setup lang="ts">
const route = useRoute()
const router = useRouter()

// Check if user is authenticated
const isAuthenticated = ref(false)

// Toggle sidebar for mobile
const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-slate-50">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex w-64 flex-col border-r border-slate-200 bg-white">
      <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-6">
        <svg class="h-6 w-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="text-lg font-bold text-slate-800">JurnalKelas</span>
      </div>

      <nav class="flex-1 space-y-1 px-3 py-4">
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          :class="[
            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all',
            route.path === item.href
              ? 'bg-brand-50 text-brand-700'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          ]"
        >
          <component :is="item.icon" class="h-5 w-5" />
          {{ item.label }}
        </a>
      </nav>

      <div class="border-t border-slate-200 p-4">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold">G</div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-800 truncate">Pak Budi</p>
            <p class="text-xs text-slate-500 truncate">Guru Matematika</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <slot />
    </main>
  </div>
</template>

<script lang="ts">
const navItems = [
  { label: 'Jurnal', href: '/', icon: 'BookOpen' },
  { label: 'Kehadiran', href: '/kehadiran', icon: 'ClipboardCheck' },
  { label: 'Rating Guru', href: '/rating-guru', icon: 'Star' },
  { label: 'Laporan', href: '/laporan', icon: 'FileText' },
  { label: 'Siswa', href: '/siswa', icon: 'Users' },
]
</script>