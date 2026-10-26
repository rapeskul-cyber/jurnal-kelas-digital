<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Riwayat Kehadiran Lengkap"
      subtitle="Ringkasan dan grafik statistik presensi pribadi siswa"
    >
      <template #actions>
        <button
          @click="exportCsv"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition text-xs font-semibold flex items-center gap-2 shadow-2xs cursor-pointer"
        >
          <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
          <span>Ekspor CSV</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-8 pb-8 space-y-6">
      <!-- 4 Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard title="HADIR BULAN INI" :value="(summary?.hadir ?? 0) + ' Hari'" color="green" />
        <StatCard title="Total Izin Disetujui" :value="(summary?.izin ?? 0) + ' Hari'" color="blue" />
        <StatCard title="ALFA" :value="(summary?.alpha ?? 0) + ' Hari'" color="red" />
        <StatCard title="Total Sakit" :value="(summary?.sakit ?? 0) + ' Hari'" color="orange" />
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
                <!-- Background ring -->
                <path
                  class="text-gray-100"
                  stroke-width="3.5"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <!-- Hadir Dynamic Ring -->
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
                <span class="text-[10px] text-gray-400 font-medium">Total</span>
                <span class="text-lg font-extrabold text-gray-900">{{ summary?.total ?? 0 }} Hari</span>
              </div>
            </div>

            <!-- Legend -->
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 mt-6 text-xs w-full max-w-xs">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                  <span class="text-gray-600">Hadir</span>
                </div>
                <span class="font-bold text-gray-900">{{ summary?.hadir ?? 0 }} ({{ summary?.hadir_percent ?? 0 }}%)</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>
                  <span class="text-gray-600">Sakit</span>
                </div>
                <span class="font-bold text-gray-900">{{ summary?.sakit ?? 0 }}</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-sm bg-blue-600"></span>
                  <span class="text-gray-600">Izin</span>
                </div>
                <span class="font-bold text-gray-900">{{ summary?.izin ?? 0 }}</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span>
                  <span class="text-gray-600">ALFA</span>
                </div>
                <span class="font-bold text-gray-900">{{ summary?.alpha ?? 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Bar Chart: Kehadiran per Minggu -->
          <div class="flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-bold text-gray-700 mb-1">Kehadiran per Minggu</h4>
              <p class="text-[11px] text-gray-400">Total kehadiran efektif siswa per pekan</p>
            </div>
            <div class="flex items-end justify-between h-44 px-3 pb-2 border-b border-gray-100 gap-3 pt-6">
              <div v-for="(week, idx) in formattedWeeklyData" :key="idx" class="flex flex-col items-center gap-2 flex-1 h-full justify-end">
                <span class="text-[11px] font-bold text-emerald-700">{{ week.count }} Hari</span>
                <div
                  class="w-full max-w-[48px] bg-emerald-500 hover:bg-emerald-600 rounded-t-lg transition-all duration-300 shadow-2xs"
                  :style="{ height: week.height + '%' }"
                ></div>
                <span class="text-[11px] text-gray-600 font-semibold mt-1">{{ week.week }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Kehadiran Terperinci Table -->
      <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Riwayat Kehadiran Terperinci</h3>
            <p class="text-xs text-gray-400 mt-0.5">Filter: {{ activeMonthLabel }} • {{ filteredDetails.length }} hari total</p>
          </div>
          <div class="flex items-center gap-2">
            <MonthYearPicker v-model="selectedMonth" @change="fetchHistory" />
            <select
              v-model="statusFilter"
              class="px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-700 font-medium bg-white focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red shadow-2xs cursor-pointer"
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
              <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                <th class="pb-3 font-semibold">Hari / Tanggal</th>
                <th class="pb-3 font-semibold">Ruangan</th>
                <th class="pb-3 font-semibold">Mata Pelajaran</th>
                <th class="pb-3 font-semibold">Jam Sekolah</th>
                <th class="pb-3 font-semibold">Status</th>
                <th class="pb-3 font-semibold">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="row in filteredDetails" :key="row.id" class="hover:bg-gray-50/50">
                <td class="py-3 font-bold text-gray-900">{{ row.day_date }}</td>
                <td class="py-3">
                  <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ row.room }}
                  </span>
                </td>
                <td class="py-3 text-gray-700 font-medium">{{ row.subject }}</td>
                <td class="py-3 text-gray-500">{{ row.hours }}</td>
                <td class="py-3"><StatusBadge :status="row.status" /></td>
                <td class="py-3 text-gray-500">{{ row.note }}</td>
              </tr>
              <tr v-if="filteredDetails.length === 0">
                <td colspan="6" class="py-8 text-center text-gray-400">
                  Tidak ada catatan kehadiran untuk filter bulan dan status yang dipilih.
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
import { Download as DownloadIcon } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const summary = ref<any>(null)
const details = ref<any[]>([])

const now = new Date()
const currentYearMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const selectedMonth = ref(currentYearMonth)
const statusFilter = ref('ALL')

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const activeMonthLabel = computed(() => {
  if (!selectedMonth.value) return 'Oktober 2026'
  const [y, m] = selectedMonth.value.split('-').map(Number)
  const name = monthNames[m - 1] || 'Oktober'
  return `${name} ${y}`
})

const filteredDetails = computed(() => {
  if (statusFilter.value === 'ALL') return details.value
  return details.value.filter(d => d.status === statusFilter.value)
})

const rawWeekly = ref<any[]>([])

const formattedWeeklyData = computed(() => {
  if (rawWeekly.value && rawWeekly.value.length > 0) {
    return rawWeekly.value.map(w => {
      const count = Number(w.count) || 0
      const maxDays = 5
      const pct = Math.min(100, Math.round((count / maxDays) * 100))
      return {
        week: w.week,
        count: count,
        percent: pct,
        height: count > 0 ? Math.max(16, pct) : 6
      }
    })
  }
  return [
    { week: 'Minggu 1', count: 0, percent: 0, height: 6 },
    { week: 'Minggu 2', count: 0, percent: 0, height: 6 },
    { week: 'Minggu 3', count: 0, percent: 0, height: 6 },
    { week: 'Minggu 4', count: 0, percent: 0, height: 6 },
  ]
})

const fetchHistory = async () => {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/attendance/history?month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    summary.value = res.summary
    rawWeekly.value = res.weekly || []
    details.value = res.details || []
  } catch (e) {
    console.error(e)
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
