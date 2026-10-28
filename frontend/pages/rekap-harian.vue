<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Rekap Kehadiran Siswa"
      subtitle="Laporan dan ringkasan data presensi siswa Kelas Industri RPL"
    >
      <template #actions>
        <button
          @click="exportExcel"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer whitespace-nowrap"
        >
          <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
          <span class="hidden sm:inline">Ekspor Excel</span>
        </button>
        <button
          @click="printReport"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer whitespace-nowrap"
        >
          <PrinterIcon class="w-3.5 h-3.5" />
          <span>Cetak PDF</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 sm:px-8 pb-10 space-y-5">

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 md:p-5 flex flex-wrap items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3">
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Pilih Tanggal</label>
          <input
            v-model="filterDate"
            type="date"
            @change="fetchRecap"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
          />
        </div>
        <!-- Dropdown Filter Kelas (Hanya untuk Superadmin/Guru Pengajar multi-kelas) -->
        <div v-if="authStore.user?.role !== 'GURU_PEMBIMBING' && authStore.user?.role !== 'SEKRETARIS' && authStore.user?.role !== 'SISWA'">
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kelas</label>
          <select
            v-model="filterClass"
            @change="fetchRecap"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer min-h-[38px]"
          >
            <option v-for="cls in classOptions" :key="cls.id" :value="String(cls.id)">
              {{ cls.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Quick Search input -->
      <div class="w-full md:w-64">
        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Cari Siswa</label>
        <div class="relative">
          <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Ketik nama atau NIS..."
            class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </div>
      </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium">Hadir</p>
          <h3 class="text-2xl font-bold text-green-600 mt-1">{{ summary.hadir }}</h3>
          <p class="text-[11px] text-gray-400 mt-0.5">Siswa hadir</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center font-bold">
          ✓
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium">Sakit</p>
          <h3 class="text-2xl font-bold text-orange-600 mt-1">{{ summary.sakit }}</h3>
          <p class="text-[11px] text-gray-400 mt-0.5">Surat dokter / izin</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
          S
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium">Izin</p>
          <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ summary.izin }}</h3>
          <p class="text-[11px] text-gray-400 mt-0.5">Permohonan resmi</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
          I
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium">ALFA</p>
          <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ summary.alpha }}</h3>
          <p class="text-[11px] text-gray-400 mt-0.5">Tanpa keterangan</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
          A
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">Daftar Kehadiran Siswa</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ dateFormatted }} - {{ className }}</p>
        </div>
        <span class="text-xs text-gray-500 font-medium">Total: {{ filteredRecords.length }} Siswa</span>
      </div>

      <div v-if="loading" class="py-16 text-center text-gray-400 text-sm">
        Memuat rekap data kehadiran...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
              <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
              <th class="py-3 px-4 whitespace-nowrap">Nama Siswa</th>
              <th class="py-3 px-4 whitespace-nowrap">NIS / NISN</th>
              <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
              <th class="py-3 px-4 whitespace-nowrap">Waktu / Keterangan</th>
              <th class="py-3 px-4 text-center whitespace-nowrap">Verifikasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="item in filteredRecords"
              :key="item.no"
              class="hover:bg-gray-50/50 transition"
            >
              <td class="py-3.5 px-4 text-center font-medium text-gray-500 whitespace-nowrap">{{ item.no }}</td>
              <td class="py-3.5 px-4 whitespace-nowrap">
                <div class="font-semibold text-gray-900 text-sm">{{ item.name }}</div>
              </td>
              <td class="py-3.5 px-4 text-gray-500 font-mono whitespace-nowrap">{{ item.nis }}</td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <StatusBadge :status="item.status" />
              </td>
              <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                {{ item.time_note }}
              </td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 whitespace-nowrap">
                  <CheckCircle2Icon class="w-3 h-3" />
                  {{ item.verification }}
                </span>
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
  Download as DownloadIcon,
  Printer as PrinterIcon,
  Search as SearchIcon,
  CheckCircle2 as CheckCircle2Icon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterDate = ref(new Date().toISOString().slice(0, 10))
const filterClass = ref('1')
const searchQuery = ref('')
const loading = ref(false)

const dateFormatted = ref('')
const className = ref('Kelas Industri RPL')
const summary = ref({ hadir: 0, sakit: 0, izin: 0, alpha: 0 })
const records = ref<any[]>([])

const filteredRecords = computed(() => {
  if (!searchQuery.value) return records.value
  const q = searchQuery.value.toLowerCase()
  return records.value.filter(
    r => r.name.toLowerCase().includes(q) || (r.nis && r.nis.includes(q))
  )
})

const fetchRecap = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/attendance/daily-recap`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      params: {
        date: filterDate.value,
        class_id: filterClass.value
      }
    })
    dateFormatted.value = res.date_formatted
    className.value = res.class_name
    summary.value = res.summary
    records.value = res.records || []
  } catch (err: any) {
    console.error('Error fetching daily recap:', err)
  } finally {
    loading.value = false
  }
}

const exportExcel = () => {
  if (!students.value || students.value.length === 0) {
    alert('Tidak ada data presensi harian untuk diekspor.')
    return
  }

  const headers = ['No', 'Nama Siswa', 'NIS / NIP', 'Status Kehadiran', 'Keterangan']
  const rows = students.value.map((s, idx) => [
    idx + 1,
    `"${s.name}"`,
    `"${s.nis}"`,
    `"${s.status}"`,
    `"${s.note || '-'}"`
  ])

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `rekap_presensi_harian_kelas_${selectedClass.value}_${filterDate.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const classOptions = ref<any[]>([
  { id: 1, name: 'Kelas Industri RPL' },
  { id: 12, name: 'XI RPL 1' },
  { id: 13, name: 'XI RPL 2' },
  { id: 18, name: 'XII RPL 1' }
])

const fetchClasses = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/master/classes`, {
      headers: { Authorization: 'Bearer ' + authStore.token }
    })
    if (Array.isArray(res) && res.length > 0) {
      classOptions.value = res
      
      const u: any = authStore.user
      if (u?.role === 'GURU_PEMBIMBING' || u?.role === 'SEKRETARIS' || u?.role === 'SISWA') {
        const userClassId = u?.class_id || u?.school_class?.id
        if (userClassId) {
          filterClass.value = String(userClassId)
          return
        }
      }

      if (!classOptions.value.some(c => String(c.id) === String(filterClass.value))) {
        filterClass.value = String(res[0].id)
      }
    }
  } catch (e) {
    // Keep fallback
  }
}

onMounted(async () => {
  await fetchClasses()
  fetchRecap()
})
</script>
