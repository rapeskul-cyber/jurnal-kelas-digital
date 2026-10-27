<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Log & Pengaturan Sistem"
      subtitle="Konfigurasi identitas instansi, parameter sistem, audit trail explorer, dan cadangan data"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- Tabs Navigation -->
      <div class="flex items-center gap-6 border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="activeTab = tab.id"
          class="pb-3 text-xs font-bold transition relative cursor-pointer flex items-center gap-2"
          :class="activeTab === tab.id ? 'text-primary' : 'text-gray-500 hover:text-gray-800'"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          <span>{{ tab.label }}</span>
          <span
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"
          ></span>
        </button>
      </div>

      <!-- Alerts -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <div v-if="errorMsg" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <AlertCircle class="w-4 h-4 text-rose-600" />
          <span>{{ errorMsg }}</span>
        </div>
        <button @click="errorMsg = ''" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- ============================================== -->
      <!-- TAB 1: KONFIGURASI INSTANSI & SEKOLAH          -->
      <!-- ============================================== -->
      <div v-if="activeTab === 'general'" class="space-y-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-5">
          <div class="border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-gray-900">Identitas Sekolah & Kop Surat Digital</h3>
            <p class="text-xs text-gray-400 mt-0.5">Informasi ini akan tertera pada seluruh cetakan PDF laporan rekap semester dan surat izin</p>
          </div>

          <form @submit.prevent="saveSettings" class="space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-semibold text-gray-700 mb-1">Nama Resmi Sekolah</label>
                <input
                  v-model="settings.school_name"
                  type="text"
                  required
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">Nomor Pokok Sekolah Nasional (NPSN)</label>
                <input
                  v-model="settings.school_npsn"
                  type="text"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs font-mono"
                />
              </div>

              <div class="sm:col-span-2">
                <label class="block font-semibold text-gray-700 mb-1">Alamat Lengkap Sekolah</label>
                <input
                  v-model="settings.school_address"
                  type="text"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">Nama Kepala Sekolah</label>
                <input
                  v-model="settings.headmaster_name"
                  type="text"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs"
                />
              </div>

              <div>
                <label class="block font-semibold text-gray-700 mb-1">NIP Kepala Sekolah</label>
                <input
                  v-model="settings.headmaster_nip"
                  type="text"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs font-mono"
                />
              </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
              <h4 class="text-xs font-bold text-gray-900 mb-3">Parameter Presensi & Keamanan</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-semibold text-gray-700 mb-1">Jam Sekolah Standar</label>
                  <input
                    v-model="settings.default_school_hours"
                    type="text"
                    placeholder="07:30 - 15:30"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none text-xs"
                  />
                </div>

                <div>
                  <label class="block font-semibold text-gray-700 mb-1">Default PIN Buka Kunci Presensi</label>
                  <input
                    v-model="settings.attendance_lock_pin"
                    type="text"
                    maxlength="6"
                    placeholder="1234"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 outline-none text-xs font-mono font-bold tracking-wider"
                  />
                </div>
              </div>
            </div>

            <div class="pt-3 flex justify-end">
              <button
                type="submit"
                :disabled="saving"
                class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer disabled:opacity-50"
              >
                {{ saving ? 'Menyimpan Pengaturan...' : 'Simpan Konfigurasi Sekolah' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 2: AUDIT LOG EXPLORER                      -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'audit'" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="relative w-full sm:w-80">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchLogQuery"
              @input="filterLogs"
              type="text"
              placeholder="Cari aksi, user, peran, atau IP..."
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-2xs"
            />
          </div>

          <button
            @click="exportLogsCsv"
            class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
          >
            <Download class="w-4 h-4 text-gray-500" />
            <span>Ekspor Log (CSV)</span>
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 shadow-2xs overflow-hidden">
          <div v-if="loadingLogs" class="py-12 text-center text-gray-400 text-xs">
            Memuat audit log aktivitas...
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                  <th class="py-3 px-4 w-12 text-center">No</th>
                  <th class="py-3 px-4">Waktu Kejadian</th>
                  <th class="py-3 px-4">Pengguna & Peran</th>
                  <th class="py-3 px-4">Aksi / Aktivitas Sistem</th>
                  <th class="py-3 px-4">Alamat IP</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(log, idx) in displayedLogs" :key="log.id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3 px-4 text-center font-medium text-gray-400">{{ idx + 1 }}</td>
                  <td class="py-3 px-4 font-mono text-[11px] text-gray-600 whitespace-nowrap">{{ log.time }}</td>
                  <td class="py-3 px-4 font-bold text-gray-900">{{ log.user }}</td>
                  <td class="py-3 px-4 text-gray-700 font-medium">{{ log.action }}</td>
                  <td class="py-3 px-4 font-mono text-gray-400 text-[11px]">{{ log.ip_address }}</td>
                </tr>
                <tr v-if="!displayedLogs.length">
                  <td colspan="5" class="py-8 text-center text-gray-400">
                    Tidak ditemukan rekaman log yang cocok.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 3: CADANGAN & PEMELIHARAAN (BACKUP)        -->
      <!-- ============================================== -->
      <div v-else-if="activeTab === 'backup'" class="space-y-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-5">
          <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-bold text-gray-900">Cadangan Data Institusi (Database Backup)</h3>
              <p class="text-xs text-gray-400 mt-0.5">Unduh seluruh snapshot data penting sistem sekolah dalam format JSON komprehensif</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
              Database Online
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
              <p class="text-gray-400 text-[10px] font-bold uppercase">Mesin Database</p>
              <p class="text-base font-black text-gray-900 uppercase">{{ health.db_connection || 'SQLITE' }}</p>
              <p class="text-xs text-gray-500">Ukuran Berkas: {{ health.db_size || '380 KB' }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
              <p class="text-gray-400 text-[10px] font-bold uppercase">Total Akun Terdaftar</p>
              <p class="text-base font-black text-primary">{{ health.total_users || 0 }} Akun</p>
              <p class="text-xs text-gray-500">Siswa, Guru, Admin</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
              <p class="text-gray-400 text-[10px] font-bold uppercase">Aktivitas Audit Log</p>
              <p class="text-base font-black text-emerald-600">{{ health.total_audit_logs || 0 }} Log</p>
              <p class="text-xs text-gray-500">Rekaman Terlindungi</p>
            </div>
          </div>

          <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2 text-xs text-blue-900">
            <p class="font-bold flex items-center gap-1.5">
              <span>ℹ️ Informasi Pencadangan Data:</span>
            </p>
            <p class="text-blue-800 leading-relaxed">
              Berkas backup mencakup seluruh tabel master data: Pengguna, Rombel/Kelas, Mata Pelajaran, Jadwal Pelajaran, Semester/Tahun Ajaran, Pengaturan Sekolah, serta 200 Audit Log terkini. Disarankan melakukan pencadangan berkala setiap akhir semester.
            </p>
          </div>

          <div class="pt-2">
            <button
              @click="downloadBackupJson"
              :disabled="downloadingBackup"
              class="w-full sm:w-auto px-6 py-3 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <Database class="w-4 h-4" />
              <span>{{ downloadingBackup ? 'Mengunduh Snapshot...' : 'Unduh Cadangan Snapshot (JSON)' }}</span>
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Sliders,
  FileText,
  Database,
  Search,
  Download,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase || '/api'

const tabs = [
  { id: 'general', label: 'Konfigurasi Sekolah', icon: Sliders },
  { id: 'audit', label: 'Audit Trail Explorer', icon: FileText },
  { id: 'backup', label: 'Cadangan & Pemeliharaan', icon: Database },
]

const activeTab = ref('general')
const successMsg = ref('')
const errorMsg = ref('')
const saving = ref(false)
const downloadingBackup = ref(false)

// Settings state
const settings = ref({
  school_name: 'SMK Negeri 1 Digital',
  school_npsn: '20104567',
  school_address: 'Jl. Pendidikan Industri No. 45, Jakarta',
  headmaster_name: 'Dr. H. Bambang Soeprapto, M.Pd',
  headmaster_nip: '197505101999031002',
  default_school_hours: '07:30 - 15:30',
  attendance_lock_pin: '1234',
})

// Audit logs state
const logs = ref<any[]>([])
const displayedLogs = ref<any[]>([])
const loadingLogs = ref(false)
const searchLogQuery = ref('')

// Health state
const health = ref<any>({})

async function fetchSettings() {
  try {
    const res: any = await $fetch(`${apiBase}/system/settings`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res) {
      settings.value = { ...settings.value, ...res }
    }
  } catch (e) {
    console.warn(e)
  }
}

async function saveSettings() {
  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res: any = await $fetch(`${apiBase}/system/settings`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: settings.value,
    })
    successMsg.value = res.message || 'Konfigurasi sekolah berhasil disimpan!'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

async function fetchLogs() {
  loadingLogs.value = true
  try {
    const res: any = await $fetch(`${apiBase}/audit-logs?limit=150`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    logs.value = res || []
    filterLogs()
  } catch (e) {
    console.warn(e)
  } finally {
    loadingLogs.value = false
  }
}

function filterLogs() {
  const q = searchLogQuery.value.toLowerCase().trim()
  if (!q) {
    displayedLogs.value = [...logs.value]
    return
  }
  displayedLogs.value = logs.value.filter(l =>
    (l.action && l.action.toLowerCase().includes(q)) ||
    (l.user && l.user.toLowerCase().includes(q)) ||
    (l.ip_address && l.ip_address.includes(q))
  )
}

function exportLogsCsv() {
  if (!displayedLogs.value.length) return
  const headers = ['No', 'Waktu', 'Pengguna', 'Aksi', 'Alamat IP']
  const rows = displayedLogs.value.map((l, idx) => [
    idx + 1,
    `"${l.time}"`,
    `"${l.user}"`,
    `"${l.action.replace(/"/g, '""')}"`,
    `"${l.ip_address}"`
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Audit_Logs_${new Date().toISOString().split('T')[0]}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

async function fetchHealth() {
  try {
    const res: any = await $fetch(`${apiBase}/system/health`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    health.value = res || {}
  } catch (e) {
    console.warn(e)
  }
}

async function downloadBackupJson() {
  downloadingBackup.value = true
  try {
    const res: any = await $fetch(`${apiBase}/system/backup`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(res, null, 2))
    const downloadAnchor = document.createElement('a')
    downloadAnchor.setAttribute('href', dataStr)
    downloadAnchor.setAttribute('download', `Backup_Jurnal_Kelas_${new Date().toISOString().split('T')[0]}.json`)
    document.body.appendChild(downloadAnchor)
    downloadAnchor.click()
    downloadAnchor.remove()
    successMsg.value = 'Cadangan data sistem berhasil diunduh!'
  } catch (e: any) {
    errorMsg.value = e?.data?.message || 'Gagal mengunduh cadangan data.'
  } finally {
    downloadingBackup.value = false
  }
}

onMounted(() => {
  fetchSettings()
  fetchLogs()
  fetchHealth()
})
</script>
