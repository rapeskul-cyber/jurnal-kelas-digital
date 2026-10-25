<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Pemberitahuan Terbaru"
      subtitle="Pantau aktivitas terbaru, update materi, dan status persetujuan"
      :show-back="true"
      :hide-notifications="true"
    >
      <template #actions>
        <button
          @click="markAllRead"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <CheckCheckIcon class="w-3.5 h-3.5 text-emerald-600" />
          <span>Tandai Semua Dibaca</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Card Section 1: Pemberitahuan Terbaru -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
        <!-- Filter Pills: Semua, Belum Dibaca, Jurnal, Surat Izin (Matching mockups) -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3.5">
          <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
            <button
              @click="switchFilter('ALL')"
              :class="[
                'px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                filterType === 'ALL'
                  ? 'bg-brand-red text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              Semua
            </button>

            <button
              @click="switchFilter('UNREAD')"
              :class="[
                'px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5',
                filterType === 'UNREAD'
                  ? 'bg-brand-red text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Belum Dibaca</span>
              <span
                v-if="unreadCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'UNREAD' ? 'bg-white text-brand-red' : 'bg-red-100 text-brand-red'"
              >
                {{ unreadCount }}
              </span>
            </button>

            <button
              @click="switchFilter('JURNAL')"
              :class="[
                'px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5',
                filterType === 'JURNAL'
                  ? 'bg-brand-red text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Jurnal</span>
              <span
                v-if="unreadJurnalCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'JURNAL' ? 'bg-white text-brand-red' : 'bg-red-100 text-brand-red'"
              >
                {{ unreadJurnalCount }}
              </span>
            </button>

            <button
              @click="switchFilter('SURAT_IZIN')"
              :class="[
                'px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5',
                filterType === 'SURAT_IZIN'
                  ? 'bg-brand-red text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Surat Izin</span>
              <span
                v-if="unreadSuratIzinCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'SURAT_IZIN' ? 'bg-white text-brand-red' : 'bg-red-100 text-brand-red'"
              >
                {{ unreadSuratIzinCount }}
              </span>
            </button>
          </div>

          <button
            @click="markCategoryRead"
            type="button"
            class="px-2.5 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-[11px] font-semibold text-gray-700 transition flex items-center gap-1 cursor-pointer shrink-0"
            :title="categoryMarkTooltip"
          >
            <CheckCheckIcon class="w-3.5 h-3.5 text-emerald-600" />
            <span>Tandai Dibaca</span>
          </button>
        </div>

        <!-- List Notifikasi Items -->
        <div v-if="loading" class="py-12 text-center text-gray-400 text-xs">
          Memuat data notifikasi...
        </div>

        <div v-else-if="filteredNotifications.length === 0" class="py-12 text-center text-gray-400 text-xs">
          <BellOffIcon class="w-8 h-8 text-gray-300 mx-auto mb-2" />
          Tidak ada notifikasi dalam kategori ini.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="item in filteredNotifications"
            :key="item.id"
            @click="handleNotificationClick(item)"
            class="p-4 rounded-xl border transition flex items-start gap-3.5 cursor-pointer hover:shadow-xs group"
            :class="[
              item.is_read
                ? 'bg-white border-gray-150 hover:border-gray-200'
                : 'bg-red-50/30 border-red-200/80 shadow-2xs hover:bg-red-50/50'
            ]"
          >
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
              :class="[
                item.type === 'JURNAL'
                  ? 'bg-blue-50 text-blue-600'
                  : item.type === 'SURAT_IZIN'
                    ? 'bg-emerald-50 text-emerald-600'
                    : 'bg-red-50 text-brand-red'
              ]"
            >
              <BookOpenIcon v-if="item.type === 'JURNAL'" class="w-4 h-4" />
              <CheckCircle2Icon v-else-if="item.type === 'SURAT_IZIN'" class="w-4 h-4" />
              <UserPlusIcon v-else-if="item.type === 'USER'" class="w-4 h-4" />
              <BellIcon v-else class="w-4 h-4" />
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <h3 class="text-xs font-bold text-gray-900 truncate flex items-center gap-1.5 group-hover:text-primary transition-colors">
                  <span>{{ item.title }}</span>
                  <span v-if="!item.is_read" class="w-2 h-2 rounded-full bg-brand-red inline-block shrink-0"></span>
                </h3>
                <span class="text-[11px] text-gray-400 shrink-0">{{ item.time || item.time_ago }}</span>
              </div>
              <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                {{ item.body }}
              </p>
              <div class="mt-2.5 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-brand-red text-brand-red hover:text-white border border-red-200 text-xs font-bold transition-all shadow-2xs group-hover:shadow-xs">
                  <span>Buka Rincian</span>
                  <span class="transition-transform group-hover:translate-x-1">→</span>
                </span>
                <span v-if="!item.is_read" class="text-[10px] font-semibold text-brand-red bg-red-50 px-2 py-0.5 rounded-md border border-red-200">
                  Belum Dibaca
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Section 2: Log Audit Aktivitas Pengguna (Matching 05-Pembimbing.png) -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-gray-800">Log Audit Aktivitas Pengguna</h2>
            <p class="text-xs text-gray-400 mt-0.5">Catatan riwayat aksi dan aktivitas sistem secara real-time</p>
          </div>
          <button
            @click="fetchAuditLogs"
            class="text-xs text-brand-red hover:underline font-semibold flex items-center gap-1 cursor-pointer"
          >
            <RefreshCwIcon class="w-3.5 h-3.5" /> Segarkan
          </button>
        </div>

        <div v-if="loadingAudit" class="py-12 text-center text-gray-400 text-xs">
          Memuat log audit...
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase text-[10px] tracking-wider">
                <th class="py-3 px-4 w-36">Waktu Kejadian</th>
                <th class="py-3 px-4 w-48">Pengguna</th>
                <th class="py-3 px-4">Deskripsi Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="log in auditLogs"
                :key="log.id"
                class="hover:bg-gray-50/50 transition"
              >
                <td class="py-3.5 px-4 font-medium text-gray-500 whitespace-nowrap">
                  {{ log.time }}
                </td>
                <td class="py-3.5 px-4 font-bold text-gray-900">
                  {{ log.user }}
                </td>
                <td class="py-3.5 px-4 text-gray-600">
                  {{ log.action }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Bell as BellIcon,
  BellOff as BellOffIcon,
  CheckCheck as CheckCheckIcon,
  BookOpen as BookOpenIcon,
  CheckCircle2 as CheckCircle2Icon,
  UserPlus as UserPlusIcon,
  RefreshCw as RefreshCwIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterType = ref('ALL')
const loading = ref(false)
const loadingAudit = ref(false)

const unreadCount = ref(0)
const unreadSuratIzinCount = ref(0)
const unreadJurnalCount = ref(0)

const defaultNotifications: any[] = []

const notifications = ref<any[]>([])
const auditLogs = ref<any[]>([])

const filteredNotifications = computed(() => {
  if (filterType.value === 'ALL') return notifications.value
  if (filterType.value === 'UNREAD') return notifications.value.filter(n => !n.is_read)
  return notifications.value.filter(n => n.type === filterType.value)
})

const switchFilter = (type: string) => {
  filterType.value = type
}

const categoryMarkTooltip = computed(() => {
  if (filterType.value === 'SURAT_IZIN') return 'Tandai notifikasi Surat Izin telah dibaca'
  if (filterType.value === 'JURNAL') return 'Tandai notifikasi Jurnal telah dibaca'
  return 'Tandai notifikasi telah dibaca'
})

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/notifications`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      params: { filter: 'ALL' }
    })
    notifications.value = res.notifications || []
    unreadCount.value = res.unread_count ?? 0
    unreadSuratIzinCount.value = typeof res.unread_surat_izin_count === 'number'
      ? res.unread_surat_izin_count
      : notifications.value.filter(n => n.type === 'SURAT_IZIN' && !n.is_read).length
    unreadJurnalCount.value = typeof res.unread_jurnal_count === 'number'
      ? res.unread_jurnal_count
      : notifications.value.filter(n => n.type === 'JURNAL' && !n.is_read).length
  } catch (err: any) {
    // Keep empty
  } finally {
    loading.value = false
  }
}

const fetchAuditLogs = async () => {
  loadingAudit.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/audit-logs`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res && res.length > 0) {
      auditLogs.value = res
    }
  } catch (err: any) {
    // Keep defaults
  } finally {
    loadingAudit.value = false
  }
}

const markAllRead = async () => {
  notifications.value.forEach(n => { n.is_read = true })
  unreadCount.value = 0
  unreadSuratIzinCount.value = 0
  unreadJurnalCount.value = 0
  try {
    await $fetch(`${config.public.apiBase}/notifications/read-all`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
  } catch (err: any) {
    console.error('Error marking all read:', err)
  }
}

const markCategoryRead = async () => {
  const currentFilter = filterType.value

  if (currentFilter === 'SURAT_IZIN') {
    const unreadSuratIzin = notifications.value.filter(n => n.type === 'SURAT_IZIN' && !n.is_read).length
    if (unreadSuratIzin === 0) return

    notifications.value.forEach(n => {
      if (n.type === 'SURAT_IZIN') n.is_read = true
    })
    unreadSuratIzinCount.value = 0
    unreadCount.value = Math.max(0, unreadCount.value - unreadSuratIzin)

    try {
      await $fetch(`${config.public.apiBase}/notifications/read-all`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` },
        body: { type: 'SURAT_IZIN' }
      })
    } catch (err: any) {
      console.error('Error marking surat izin read:', err)
    }
  } else if (currentFilter === 'JURNAL') {
    const unreadJurnal = notifications.value.filter(n => n.type === 'JURNAL' && !n.is_read).length
    if (unreadJurnal === 0) return

    notifications.value.forEach(n => {
      if (n.type === 'JURNAL') n.is_read = true
    })
    unreadJurnalCount.value = 0
    unreadCount.value = Math.max(0, unreadCount.value - unreadJurnal)

    try {
      await $fetch(`${config.public.apiBase}/notifications/read-all`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` },
        body: { type: 'JURNAL' }
      })
    } catch (err: any) {
      console.error('Error marking jurnal read:', err)
    }
  } else {
    // Current filter is 'ALL' or 'UNREAD' -> mark all as read
    await markAllRead()
  }
}

const markOneRead = async (item: any) => {
  if (!item.is_read) {
    item.is_read = true
    unreadCount.value = Math.max(0, unreadCount.value - 1)
    if (item.type === 'SURAT_IZIN') {
      unreadSuratIzinCount.value = Math.max(0, unreadSuratIzinCount.value - 1)
    } else if (item.type === 'JURNAL') {
      unreadJurnalCount.value = Math.max(0, unreadJurnalCount.value - 1)
    }
    try {
      await $fetch(`${config.public.apiBase}/notifications/${item.id}/read`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` }
      })
    } catch (err: any) {
      // Handled in state
    }
  }
}

const handleNotificationClick = async (item: any) => {
  await markOneRead(item)

  const role = authStore.user?.role
  const type = item.type
  const title = (item.title || '').toLowerCase()
  const body = (item.body || '').toLowerCase()

  // Dynamic Routing Logic per role & notification context
  if (type === 'SURAT_IZIN' || title.includes('surat izin') || body.includes('surat izin')) {
    if (role === 'GURU_PEMBIMBING') {
      navigateTo('/persetujuan-izin')
    } else if (role === 'SISWA') {
      navigateTo('/riwayat-kehadiran')
    } else if (role === 'SEKRETARIS') {
      navigateTo('/surat-izin')
    } else {
      navigateTo('/laporan-izin')
    }
  } else if (type === 'JURNAL' || title.includes('jurnal') || body.includes('materi')) {
    if (role === 'SISWA') {
      navigateTo('/jurnal-pelajaran')
    } else if (role === 'GURU_PENGAJAR') {
      navigateTo('/jurnal-materi')
    } else if (role === 'SEKRETARIS') {
      // Jika notifikasi tentang materi baru pembelajaran atau tugas, arahkan ke jurnal-pelajaran seperti siswa
      if (title.includes('materi') || body.includes('materi') || title.includes('tugas') || body.includes('tugas')) {
        navigateTo('/jurnal-pelajaran')
      } else {
        navigateTo('/jurnal-kelas')
      }
    } else {
      navigateTo('/laporan-jurnal')
    }
  } else if (type === 'PRESENSI' || title.includes('presensi') || body.includes('presensi')) {
    if (role === 'SISWA') {
      navigateTo('/riwayat-kehadiran')
    } else if (role === 'SEKRETARIS') {
      navigateTo('/presensi')
    } else {
      navigateTo('/dashboard')
    }
  } else if (type === 'USER') {
    navigateTo(role === 'GURU_PEMBIMBING' ? '/manajemen-user' : '/profile')
  } else {
    navigateTo('/dashboard')
  }
}

onMounted(() => {
  fetchNotifications()
  fetchAuditLogs()
})
</script>
