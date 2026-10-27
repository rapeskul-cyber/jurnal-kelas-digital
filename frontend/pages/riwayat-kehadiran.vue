<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="authStore.user?.role === 'ORANG_TUA' ? 'Riwayat Kehadiran Lengkap Anak' : 'Riwayat & Presensi Mandiri Siswa'"
      :subtitle="authStore.user?.role === 'ORANG_TUA' ? 'Ringkasan dan grafik statistik presensi harian putra/putri Anda di sekolah' : 'Lakukan presensi mandiri harian dan pantau statistik kehadiran pribadi Anda'"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <button
            v-if="authStore.user?.role === 'SISWA' || authStore.user?.role === 'SEKRETARIS'"
            @click="handleSelfCheckIn"
            :disabled="isCheckingIn"
            type="button"
            class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition text-xs font-bold flex items-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50"
          >
            <CheckCircle2Icon class="w-3.5 h-3.5" />
            <span>{{ isCheckingIn ? 'Menyimpan...' : 'Presensi Mandiri Hari Ini' }}</span>
          </button>
          <button
            @click="exportCsv"
            type="button"
            class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition text-xs font-semibold flex items-center gap-2 shadow-2xs cursor-pointer"
          >
            <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
            <span>Ekspor CSV</span>
          </button>
        </div>
      </template>
    </AppHeader>

    <main class="px-4 sm:px-8 pb-8 space-y-5">
      <!-- Alert Toast if Checked In -->
      <div v-if="checkInMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2Icon class="w-4 h-4 text-emerald-600" />
          <span>{{ checkInMsg }}</span>
        </div>
        <button @click="checkInMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- 4 Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard title="Hadir Bulan Ini" :value="(summary?.hadir ?? 0) + ' Hari'" :subtext="(summary?.hadir_percent ?? 0) + '% Kehadiran'" color="green" />
        <StatCard title="Total Sakit" :value="(summary?.sakit ?? 0) + ' Hari'" subtext="Dilengkapi Surat Dokter" color="orange" />
        <StatCard title="Total Izin Disetujui" :value="(summary?.izin ?? 0) + ' Hari'" subtext="Disetujui Wali Kelas" color="blue" />
        <StatCard title="ALFA / Tanpa Kabar" :value="(summary?.alpha ?? 0) + ' Hari'" subtext="Catatan Kehadiran Riil" color="red" />
      </div>

      <!-- Diagram Kehadiran Card -->
      <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
        <div>
          <h3 class="text-sm font-bold text-gray-900">Diagram Kehadiran</h3>
          <p class="text-xs text-gray-400 mt-0.5">
            Ringkasan kehadiran periode terpilih: {{ summary?.total ?? 0 }} hari total, {{ summary?.hadir ?? 0 }} hadir, {{ summary?.sakit ?? 0 }} sakit, {{ summary?.izin ?? 0 }} izin disetujui, {{ summary?.alpha ?? 0 }} ALFA.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
          <!-- Donut Summary -->
          <div class="flex flex-col items-center">
            <h4 class="text-xs font-bold text-gray-700 mb-4 self-start">Kehadiran Bulan Ini</h4>
            <div class="relative w-44 h-44 flex items-center justify-center">
              <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                <path
                  class="text-gray-100"
                  stroke-width="3.5"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <path
                  class="text-emerald-500"
                  :stroke-dasharray="`${summary?.hadir_percent || 0}, 100`"
                  stroke-width="3.5"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                <span class="text-[11px] text-gray-400 font-medium">Total</span>
                <span class="text-xl font-extrabold text-gray-900">{{ summary?.total ?? 0 }} Hari</span>
              </div>
            </div>

            <!-- Legend -->
            <div class="w-full max-w-xs space-y-2 mt-6 text-xs">
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-xs bg-emerald-600"></span> Hadir</span>
                <span class="font-bold text-gray-900">{{ summary?.hadir ?? 0 }} ({{ summary?.hadir_percent ?? 0 }}%)</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-xs bg-orange-500"></span> Sakit</span>
                <span class="font-bold text-gray-900">{{ summary?.sakit ?? 0 }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-xs bg-blue-600"></span> Izin</span>
                <span class="font-bold text-gray-900">{{ summary?.izin ?? 0 }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-xs bg-red-600"></span> ALFA</span>
                <span class="font-bold text-gray-900">{{ summary?.alpha ?? 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Weekly Bar Chart -->
          <div class="flex flex-col justify-between">
            <h4 class="text-xs font-bold text-gray-700 mb-4">Kehadiran per Minggu</h4>
            <div class="flex-1 flex items-end justify-around gap-4 pt-6 pb-2 border-b border-gray-100 min-h-[180px]">
              <div v-for="(w, i) in weeklyData" :key="i" class="flex flex-col items-center gap-2 w-12">
                <span class="text-[10px] font-bold text-gray-400">{{ w.week }}</span>
                <div class="w-7 bg-gray-100 rounded-t-lg h-36 flex items-end overflow-hidden">
                  <div class="w-full bg-emerald-600 rounded-t-lg transition-all duration-500" :style="{ height: w.height + '%' }"></div>
                </div>
                <span class="text-[11px] font-bold text-gray-700">5 hr</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Kehadiran Terperinci Table -->
      <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Riwayat Kehadiran Terperinci</h3>
            <p class="text-xs text-gray-400">Filter: {{ formattedMonthLabel }} • {{ filteredDetails.length }} hari tercatat</p>
          </div>
          <div class="flex items-center gap-2">
            <MonthYearPicker v-model="selectedMonth" @change="fetchHistory" />
            <select
              v-model="statusFilter"
              class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
              <option value="ALL">Semua Status</option>
              <option value="HADIR">Hadir</option>
              <option value="SAKIT">Sakit</option>
              <option value="IZIN">Izin</option>
              <option value="ALPHA">ALFA</option>
            </select>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100 bg-gray-50/50">
                <th class="py-3 px-3 font-semibold">Hari / Tanggal</th>
                <th class="py-3 px-3 font-semibold">Ruangan</th>
                <th class="py-3 px-3 font-semibold">Mata Pelajaran</th>
                <th class="py-3 px-3 font-semibold">Jam Sekolah</th>
                <th class="py-3 px-3 font-semibold">Status</th>
                <th class="py-3 px-3 font-semibold">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in filteredDetails" :key="item.id" class="hover:bg-gray-50/50">
                <td class="py-3.5 px-3 font-semibold text-gray-900">{{ item.day_date }}</td>
                <td class="py-3.5 px-3">
                  <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                    {{ item.room }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-gray-700">{{ item.subject }}</td>
                <td class="py-3.5 px-3 text-gray-400">{{ item.hours }}</td>
                <td class="py-3.5 px-3"><StatusBadge :status="item.status" /></td>
                <td class="py-3.5 px-3 text-gray-400">{{ item.note }}</td>
              </tr>
              <tr v-if="filteredDetails.length === 0">
                <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                  Tidak ada catatan presensi untuk filter ini.
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
import { Download as DownloadIcon, CheckCircle2 as CheckCircle2Icon } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const summary = ref<any>(null)
const details = ref<any[]>([])
const now = new Date()
const currentYearMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const selectedMonth = ref(currentYearMonth)
const statusFilter = ref('ALL')
const isCheckingIn = ref(false)
const checkInMsg = ref('')

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const formattedMonthLabel = computed(() => {
  if (!selectedMonth.value) return 'Oktober 2026'
  const [y, m] = selectedMonth.value.split('-').map(Number)
  const name = monthNames[m - 1] || 'Oktober'
  return `${name} ${y}`
})

const filteredDetails = computed(() => {
  if (statusFilter.value === 'ALL') return details.value
  return details.value.filter(d => d.status === statusFilter.value)
})

const weeklyData = [
  { week: 'Minggu 1', height: 95 },
  { week: 'Minggu 2', height: 90 },
  { week: 'Minggu 3', height: 90 },
  { week: 'Minggu 4', height: 85 },
]

const fetchHistory = async () => {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/attendance/history?month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    summary.value = res.summary
    details.value = res.details || []
  } catch (e) {
    console.error(e)
  }
}

const handleSelfCheckIn = async () => {
  isCheckingIn.value = true
  checkInMsg.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/attendance/check-in`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {}
    })
    checkInMsg.value = res?.message || 'Presensi Mandiri berhasil dicatat!'
    await fetchHistory()
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal melakukan presensi mandiri.')
  } finally {
    isCheckingIn.value = false
  }
}

const exportCsv = () => {
  if (!filteredDetails.value || filteredDetails.value.length === 0) {
    alert('Tidak ada data riwayat presensi untuk diekspor pada bulan ini.')
    return
  }

  const headers = ['Hari / Tanggal', 'Ruangan', 'Mata Pelajaran', 'Jam Sekolah', 'Status', 'Keterangan']
  const rows = filteredDetails.value.map(r => [
    `"${r.day_date || r.date || ''}"`,
    `"${r.room || 'Ruang Kelas'}"`,
    `"${r.subject || 'Kegiatan Pembelajaran'}"`,
    `"${r.hours || 'Jam Sekolah'}"`,
    `"${r.status || '-'}"`,
    `"${r.note || '-'}"`
  ])

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `riwayat_kehadiran_${selectedMonth.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

onMounted(() => {
  fetchHistory()
})
</script>
