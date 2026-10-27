<template>
  <!-- Floating Centered Modal Card Container -->
  <div
    class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5 md:p-6"
    @click.self="handleClose"
  >
    <div
      class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl border border-gray-150 flex flex-col max-h-[90vh] overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150"
    >
      <!-- Modal Header -->
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 bg-white sticky top-0 z-10">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-2xl bg-blue-50 text-primary flex items-center justify-center shrink-0 shadow-2xs">
            <BellIcon class="w-5 h-5" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h2 class="text-base font-bold text-gray-900 truncate">Pemberitahuan</h2>
              <span
                v-if="unreadCount > 0"
                class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-primary shrink-0"
              >
                {{ unreadCount }} baru
              </span>
            </div>
            <p class="text-xs text-gray-500 truncate mt-0.5">Aktivitas pembelajaran, materi, dan persetujuan</p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button
            @click="markAllRead"
            type="button"
            class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition items-center gap-1.5 shadow-2xs cursor-pointer"
            title="Tandai semua notifikasi telah dibaca"
          >
            <CheckCheckIcon class="w-3.5 h-3.5 text-emerald-600" />
            <span>Tandai Semua Dibaca</span>
          </button>

          <!-- Close Button -->
          <button
            @click="handleClose"
            type="button"
            class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
            title="Tutup jendela notifikasi"
          >
            <XIcon class="w-4 h-4 text-gray-500" />
            <span>Tutup</span>
          </button>
        </div>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="p-5 overflow-y-auto space-y-4 flex-1">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
          <div class="flex flex-wrap items-center gap-1.5">
            <button
              @click="switchFilter('ALL')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer whitespace-nowrap',
                filterType === 'ALL'
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              Semua
            </button>

            <button
              @click="switchFilter('UNREAD')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 whitespace-nowrap',
                filterType === 'UNREAD'
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Belum Dibaca</span>
              <span
                v-if="unreadCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'UNREAD' ? 'bg-white text-primary' : 'bg-blue-100 text-primary'"
              >
                {{ unreadCount }}
              </span>
            </button>

            <button
              @click="switchFilter('JURNAL')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 whitespace-nowrap',
                filterType === 'JURNAL'
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Jurnal</span>
              <span
                v-if="unreadJurnalCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'JURNAL' ? 'bg-white text-primary' : 'bg-blue-100 text-primary'"
              >
                {{ unreadJurnalCount }}
              </span>
            </button>

            <button
              @click="switchFilter('SURAT_IZIN')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 whitespace-nowrap',
                filterType === 'SURAT_IZIN'
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span>Surat Izin</span>
              <span
                v-if="unreadSuratIzinCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                :class="filterType === 'SURAT_IZIN' ? 'bg-white text-primary' : 'bg-blue-100 text-primary'"
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

        <div v-else class="space-y-2.5">
          <div
            v-for="item in filteredNotifications"
            :key="item.id"
            @click="handleNotificationClick(item)"
            class="p-3.5 rounded-2xl border transition flex items-start gap-3.5 cursor-pointer hover:shadow-xs group"
            :class="[
              item.is_read
                ? 'bg-white border-gray-150 hover:border-gray-200'
                : 'bg-blue-50/40 border-blue-200 shadow-2xs hover:bg-blue-50/70'
            ]"
          >
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
              :class="[
                item.type === 'JURNAL'
                  ? 'bg-blue-50 text-primary'
                  : item.type === 'SURAT_IZIN'
                    ? 'bg-emerald-50 text-emerald-600'
                    : 'bg-blue-50 text-primary'
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
                  <span v-if="!item.is_read" class="w-2 h-2 rounded-full bg-primary inline-block shrink-0"></span>
                </h3>
                <span class="text-[11px] text-gray-400 shrink-0 whitespace-nowrap">{{ item.time || item.time_ago }}</span>
              </div>
              <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                {{ item.body }}
              </p>
              <div class="mt-2.5 flex items-center justify-between">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 group-hover:bg-primary text-primary group-hover:text-white border border-blue-200 text-[11px] font-bold transition-all shadow-2xs">
                  <span>Buka Rincian</span>
                  <span class="transition-transform group-hover:translate-x-0.5">→</span>
                </span>
                <span v-if="!item.is_read" class="text-[10px] font-semibold text-primary bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 whitespace-nowrap">
                  Belum Dibaca
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Log Audit Aktivitas Pengguna: HANYA TAMPIL UNTUK SUPERADMIN -->
        <div v-if="authStore.user?.role === 'SUPERADMIN'" class="mt-6 pt-5 border-t border-gray-150 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-gray-900">Log Audit Aktivitas Pengguna (Khusus Superadmin)</h3>
              <p class="text-[11px] text-gray-400">Riwayat audit jejak aktivitas sistem real-time</p>
            </div>
            <button
              @click="fetchAuditLogs"
              class="text-xs text-primary hover:underline font-semibold flex items-center gap-1 cursor-pointer"
            >
              <RefreshCwIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loadingAudit }" />
              <span>Segarkan</span>
            </button>
          </div>

          <div v-if="loadingAudit" class="py-6 text-center text-gray-400 text-xs">
            Memuat log audit...
          </div>

          <div v-else-if="auditLogs.length === 0" class="py-6 text-center text-gray-400 text-xs">
            Belum ada log audit aktivitas pengguna.
          </div>

          <div v-else class="border border-gray-150 rounded-xl overflow-hidden max-h-48 overflow-y-auto text-xs">
            <table class="w-full text-left">
              <thead class="bg-gray-50 border-b border-gray-150 text-[10px] text-gray-500 uppercase">
                <tr>
                  <th class="py-2 px-3">Waktu</th>
                  <th class="py-2 px-3">Pengguna</th>
                  <th class="py-2 px-3">Aktivitas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-gray-50/50">
                  <td class="py-2 px-3 text-gray-400 text-[11px] whitespace-nowrap">{{ log.time }}</td>
                  <td class="py-2 px-3 font-semibold text-gray-900 whitespace-nowrap">{{ log.user }}</td>
                  <td class="py-2 px-3 text-gray-600">{{ log.action }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50/70 text-xs">
        <span class="text-gray-500 text-[11px]">
          Menampilkan {{ filteredNotifications.length }} notifikasi
        </span>
        <button
          @click="handleClose"
          type="button"
          class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-xl transition cursor-pointer shadow-2xs"
        >
          Tutup Pemberitahuan
        </button>
      </div>
    </div>
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
  RefreshCw as RefreshCwIcon,
  X as XIcon
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false
})

const router = useRouter()
const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterType = ref('ALL')
const loading = ref(false)
const loadingAudit = ref(false)

const unreadCount = ref(0)
const unreadSuratIzinCount = ref(0)
const unreadJurnalCount = ref(0)

const notifications = ref<any[]>([])
const auditLogs = ref<any[]>([])

const filteredNotifications = computed(() => {
  if (filterType.value === 'ALL') return notifications.value
  if (filterType.value === 'UNREAD') return notifications.value.filter(n => !n.is_read)
  return notifications.value.filter(n => n.type === filterType.value)
})

function handleClose() {
  if (process.client) {
    if (window.history.length > 1) {
      router.back()
    } else {
      navigateTo('/dashboard')
    }
  }
}

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
  const token = authStore.token || (process.client ? localStorage.getItem('token') : null)
  try {
    const res: any = await $fetch(`${config.public.apiBase}/notifications`, {
      headers: { Authorization: `Bearer ${token}` },
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
  if (authStore.user?.role !== 'SUPERADMIN') return
  loadingAudit.value = true
  const token = authStore.token || (process.client ? localStorage.getItem('token') : null)
  try {
    const res: any = await $fetch(`${config.public.apiBase}/audit-logs`, {
      headers: { Authorization: `Bearer ${token}` }
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
  const token = authStore.token || (process.client ? localStorage.getItem('token') : null)
  try {
    await $fetch(`${config.public.apiBase}/notifications/read-all`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` }
    })
  } catch (err: any) {
    console.error('Error marking all read:', err)
  }
}

const markCategoryRead = async () => {
  const currentFilter = filterType.value
  const token = authStore.token || (process.client ? localStorage.getItem('token') : null)

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
        headers: { Authorization: `Bearer ${token}` },
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
        headers: { Authorization: `Bearer ${token}` },
        body: { type: 'JURNAL' }
      })
    } catch (err: any) {
      console.error('Error marking jurnal read:', err)
    }
  } else {
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
    const token = authStore.token || (process.client ? localStorage.getItem('token') : null)
    try {
      await $fetch(`${config.public.apiBase}/notifications/${item.id}/read`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
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

  if (type === 'SURAT_IZIN' || title.includes('surat izin') || body.includes('surat izin')) {
    if (role === 'GURU_PEMBIMBING') {
      navigateTo('/persetujuan-izin')
    } else if (role === 'SISWA' || role === 'ORANG_TUA') {
      navigateTo('/surat-izin')
    } else if (role === 'SEKRETARIS') {
      navigateTo('/surat-izin')
    } else {
      navigateTo('/laporan-izin')
    }
  } else if (type === 'JURNAL' || title.includes('jurnal') || body.includes('materi')) {
    if (role === 'SISWA' || role === 'ORANG_TUA') {
      navigateTo('/jurnal-pelajaran')
    } else if (role === 'GURU_PENGAJAR') {
      navigateTo('/jurnal-materi')
    } else if (role === 'SEKRETARIS') {
      if (title.includes('materi') || body.includes('materi') || title.includes('tugas') || body.includes('tugas')) {
        navigateTo('/jurnal-pelajaran')
      } else {
        navigateTo('/jurnal-kelas')
      }
    } else {
      navigateTo('/laporan-jurnal')
    }
  } else if (type === 'PRESENSI' || title.includes('presensi') || body.includes('presensi')) {
    if (role === 'SISWA' || role === 'ORANG_TUA') {
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

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    handleClose()
  }
}

onMounted(() => {
  fetchNotifications()
  if (authStore.user?.role === 'SUPERADMIN') {
    fetchAuditLogs()
  }
  if (process.client) {
    window.addEventListener('keydown', handleKeydown)
  }
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('keydown', handleKeydown)
  }
})
</script>
