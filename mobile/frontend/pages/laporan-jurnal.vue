<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="pageTitle"
      :subtitle="pageSubtitle"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 md:p-5 flex flex-wrap items-center justify-between gap-4">
      <div class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Periode Bulan & Tahun</label>
          <MonthYearPicker v-model="selectedMonth" @change="fetchJournals" />
        </div>

        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Status Verifikasi</label>
          <select
            v-model="statusFilter"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
          >
            <option value="ALL">Semua Status</option>
            <option value="TERVERIFIKASI">Terverifikasi</option>
            <option value="PENDING">Menunggu Konfirmasi Guru Pengajar</option>
          </select>
        </div>

        <button
          @click="exportReport"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer h-[38px]"
        >
          <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
          <span>Ekspor Log Jurnal</span>
        </button>
      </div>

      <div class="w-full md:w-72">
        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Cari Materi</label>
        <div class="relative">
          <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari topik atau nama guru..."
            class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
          />
        </div>
      </div>
    </div>

    <!-- Table of Journals -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">Daftar Aktivitas Jurnal</h2>
        </div>
        <span class="text-xs text-gray-500 font-medium">Total: {{ filteredJournals.length }} Log</span>
      </div>

      <div v-if="loading" class="py-16 text-center text-gray-400 text-sm">
        Memuat laporan jurnal...
      </div>

      <div v-else-if="filteredJournals.length === 0" class="py-16 text-center text-gray-400 text-sm">
        <BookOpenIcon class="w-10 h-10 text-gray-300 mx-auto mb-2" />
        Tidak ada data jurnal yang sesuai filter.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
              <th class="py-3 px-4">Tanggal & Waktu</th>
              <th class="py-3 px-4">Mata Pelajaran</th>
              <th class="py-3 px-4">Guru Pengajar</th>
              <th class="py-3 px-4">Rangkuman Aktivitas</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="j in filteredJournals"
              :key="j.id"
              class="hover:bg-gray-50/50 transition"
            >
              <td class="py-3.5 px-4 font-medium text-gray-700 whitespace-nowrap">
                {{ j.date_time }}
              </td>
              <td class="py-3.5 px-4 font-semibold text-gray-900">
                {{ j.subject }}
              </td>
              <td class="py-3.5 px-4 text-gray-600">
                {{ j.teacher }}
              </td>
              <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate">
                {{ j.activity }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <StatusBadge :status="j.status" />
              </td>
              <td class="py-3.5 px-4 text-center">
                <button
                  @click="selectedJournal = j"
                  class="px-2.5 py-1 rounded-lg text-brand-red bg-red-50 hover:bg-red-100 text-[11px] font-semibold transition"
                >
                  Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Detail Jurnal -->
    <div
      v-if="selectedJournal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="text-base font-bold text-gray-900">{{ selectedJournal.subject }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ selectedJournal.date_time }} - {{ selectedJournal.class }}</p>
          </div>
          <button @click="selectedJournal = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
        </div>

        <div class="space-y-3 text-xs text-gray-700">
          <div>
            <span class="font-semibold text-gray-900">Guru Pengajar:</span>
            <p class="text-gray-600">{{ selectedJournal.teacher }}</p>
          </div>
          <div>
            <span class="font-semibold text-gray-900">Rangkuman Aktivitas & Materi:</span>
            <p class="text-gray-600 mt-1 whitespace-pre-line bg-gray-50 p-3 rounded-xl border border-gray-100">
              {{ selectedJournal.activity }}
            </p>
          </div>
          <div v-if="selectedJournal.notes">
            <span class="font-semibold text-amber-800">Catatan Khusus:</span>
            <p class="text-amber-700 mt-1 bg-amber-50 p-3 rounded-xl border border-amber-100">
              {{ selectedJournal.notes }}
            </p>
          </div>
          <div class="flex items-center justify-between pt-2">
            <span class="font-semibold text-gray-900">Status Verifikasi:</span>
            <StatusBadge :status="selectedJournal.status" />
          </div>
        </div>

        <div class="pt-3 border-t border-gray-100 flex justify-end">
          <button
            @click="selectedJournal = null"
            type="button"
            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Download as DownloadIcon,
  Search as SearchIcon,
  BookOpen as BookOpenIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const pageTitle = computed(() => {
  if (authStore.user?.role === 'SEKRETARIS') return 'Jurnal Instruktur Pembelajaran'
  return 'Laporan Jurnal Kelas'
})

const pageSubtitle = computed(() => {
  if (authStore.user?.role === 'SEKRETARIS') return 'Dokumentasi agenda, materi mengajar, dan verifikasi instruktur'
  return 'Rekapitulasi log aktivitas pembelajaran dan status verifikasi guru pengajar'
})

const loading = ref(false)
const journals = ref<any[]>([])

const now = new Date()
const currentYearMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const selectedMonth = ref(currentYearMonth)
const statusFilter = ref('ALL')
const searchQuery = ref('')
const selectedJournal = ref<any | null>(null)

const filteredJournals = computed(() => {
  return journals.value.filter(j => {
    const matchStatus = statusFilter.value === 'ALL' || j.status === statusFilter.value
    const matchSearch =
      !searchQuery.value ||
      j.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      j.teacher.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      j.activity.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

const fetchJournals = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/journals/class?month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    journals.value = res.data || res || []
  } catch (err: any) {
    console.error('Error fetching class journals:', err)
  } finally {
    loading.value = false
  }
}

const exportReport = () => {
  if (!filteredJournals.value || filteredJournals.value.length === 0) {
    alert('Tidak ada data jurnal kelas untuk diekspor pada filter ini.')
    return
  }

  const headers = ['Tanggal & Waktu', 'Mata Pelajaran', 'Guru Pengajar', 'Rangkuman Aktivitas', 'Catatan', 'Status']
  const rows = filteredJournals.value.map(j => [
    `"${j.date_time || ''}"`,
    `"${j.subject || ''}"`,
    `"${j.teacher || ''}"`,
    `"${(j.activity || '').replace(/"/g, '""')}"`,
    `"${(j.notes || '').replace(/"/g, '""')}"`,
    `"${j.status || ''}"`
  ])

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `laporan_jurnal_kelas_${selectedMonth.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

onMounted(() => {
  fetchJournals()
})
</script>
