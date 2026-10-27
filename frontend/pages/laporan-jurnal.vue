<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="pageTitle"
      :subtitle="pageSubtitle"
    >
      <template #actions>
        <button
          @click="exportReport"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
          <span>Ekspor Log Jurnal</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Success Toast -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2Icon class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- Filter Card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 md:p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Periode Bulan & Tahun</label>
            <MonthYearPicker v-model="selectedMonth" @change="fetchJournals" />
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Status Verifikasi</label>
            <select
              v-model="statusFilter"
              class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
            >
              <option value="ALL">Semua Status</option>
              <option value="TERVERIFIKASI">Terverifikasi</option>
              <option value="PENDING">Menunggu Konfirmasi</option>
            </select>
          </div>
        </div>

        <div class="w-full md:w-72">
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Cari Kegiatan / Siswa / Mapel</label>
          <div class="relative">
            <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari kegiatan, siswa, atau guru..."
              class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>
        </div>
      </div>

      <!-- Table of Journals -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-gray-800">Daftar Aktivitas Jurnal Kegiatan Siswa & Kelas</h2>
            <p class="text-xs text-gray-400 mt-0.5">Laporan realisasi kegiatan belajar harian yang diinput oleh siswa/kelas dan diverifikasi guru</p>
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
                <th class="py-3 px-4 whitespace-nowrap">Tanggal & Waktu</th>
                <th class="py-3 px-4 whitespace-nowrap">Pelapor (Siswa)</th>
                <th class="py-3 px-4 whitespace-nowrap">Mata Pelajaran</th>
                <th class="py-3 px-4 whitespace-nowrap">Guru Pengajar</th>
                <th class="py-3 px-4 whitespace-nowrap">Kegiatan Belajar</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
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
                <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                  {{ j.created_by || 'Siswa' }}
                </td>
                <td class="py-3.5 px-4 font-semibold text-primary whitespace-nowrap">
                  {{ j.subject }}
                </td>
                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                  {{ j.teacher }}
                </td>
                <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate">
                  {{ j.activity }}
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <StatusBadge :status="j.status" />
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <button
                      v-if="canConfirm && j.status !== 'TERVERIFIKASI'"
                      @click="confirmJournal(j)"
                      :disabled="confirmingId === j.id"
                      class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition cursor-pointer whitespace-nowrap disabled:opacity-50"
                    >
                      {{ confirmingId === j.id ? '...' : 'Verifikasi' }}
                    </button>
                    <button
                      @click="selectedJournal = j"
                      class="px-2.5 py-1 rounded-lg text-primary bg-primary/10 hover:bg-primary/20 text-[11px] font-semibold transition cursor-pointer whitespace-nowrap"
                    >
                      Detail
                    </button>
                  </div>
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
              <p class="text-xs text-gray-400 mt-0.5">{{ selectedJournal.date_time }} • {{ selectedJournal.class }}</p>
            </div>
            <button @click="selectedJournal = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
          </div>

          <div class="space-y-3 text-xs text-gray-700">
            <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
              <div>
                <span class="text-gray-400 font-medium block">Dilaporkan Oleh:</span>
                <p class="font-bold text-gray-900 mt-0.5">{{ selectedJournal.created_by || 'Siswa' }}</p>
              </div>
              <div>
                <span class="text-gray-400 font-medium block">Guru Pengajar:</span>
                <p class="font-bold text-gray-900 mt-0.5">{{ selectedJournal.teacher }}</p>
              </div>
            </div>
            <div>
              <span class="font-semibold text-gray-900">Rangkuman Kegiatan Belajar:</span>
              <p class="text-gray-600 mt-1 whitespace-pre-line bg-gray-50 p-3 rounded-xl border border-gray-100">
                {{ selectedJournal.activity }}
              </p>
            </div>
            <div v-if="selectedJournal.notes">
              <span class="font-semibold text-amber-800">Hasil / Catatan Tambahan:</span>
              <p class="text-amber-700 mt-1 bg-amber-50 p-3 rounded-xl border border-amber-100">
                {{ selectedJournal.notes }}
              </p>
            </div>
            <div class="flex items-center justify-between pt-2">
              <span class="font-semibold text-gray-900">Status Verifikasi:</span>
              <StatusBadge :status="selectedJournal.status" />
            </div>
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
            <button
              @click="selectedJournal = null"
              type="button"
              class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition cursor-pointer"
            >
              Tutup
            </button>
            <button
              v-if="canConfirm && selectedJournal.status !== 'TERVERIFIKASI'"
              @click="confirmJournal(selectedJournal)"
              :disabled="confirmingId === selectedJournal.id"
              type="button"
              class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition cursor-pointer disabled:opacity-50"
            >
              {{ confirmingId === selectedJournal.id ? 'Memverifikasi...' : 'Verifikasi & Setujui Jurnal' }}
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
  BookOpen as BookOpenIcon,
  CheckCircle2 as CheckCircle2Icon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const canConfirm = computed(() => {
  return ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'].includes(authStore.user?.role || '')
})

const pageTitle = computed(() => {
  if (authStore.user?.role === 'SEKRETARIS') return 'Jurnal Instruktur Pembelajaran'
  if (authStore.user?.role === 'GURU_PEMBIMBING') return 'Pemantauan Jurnal Kegiatan Siswa & Kelas'
  return 'Laporan Jurnal Kegiatan Kelas'
})

const pageSubtitle = computed(() => {
  if (authStore.user?.role === 'SEKRETARIS') return 'Dokumentasi agenda, materi mengajar, dan verifikasi instruktur'
  if (authStore.user?.role === 'GURU_PEMBIMBING') return 'Pantau dan verifikasi laporan kegiatan belajar siswa di kelas binaan Anda'
  return 'Rekapitulasi log aktivitas pembelajaran siswa dan status verifikasi guru'
})

const loading = ref(false)
const confirmingId = ref<number | null>(null)
const successMsg = ref('')
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
    const q = searchQuery.value.toLowerCase()
    const matchSearch =
      !q ||
      (j.subject && j.subject.toLowerCase().includes(q)) ||
      (j.teacher && j.teacher.toLowerCase().includes(q)) ||
      (j.created_by && j.created_by.toLowerCase().includes(q)) ||
      (j.activity && j.activity.toLowerCase().includes(q))
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

const confirmJournal = async (journal: any) => {
  confirmingId.value = journal.id
  successMsg.value = ''
  try {
    await $fetch(`${config.public.apiBase}/journals/class/${journal.id}/confirm`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    journal.status = 'TERVERIFIKASI'
    if (selectedJournal.value && selectedJournal.value.id === journal.id) {
      selectedJournal.value.status = 'TERVERIFIKASI'
      selectedJournal.value = null
    }
    successMsg.value = `Jurnal kegiatan belajar oleh ${journal.created_by || 'Siswa'} berhasil diverifikasi!`
  } catch (err: any) {
    alert(err?.data?.message || 'Gagal memverifikasi jurnal kegiatan.')
  } finally {
    confirmingId.value = null
  }
}

const exportReport = () => {
  if (!filteredJournals.value || filteredJournals.value.length === 0) {
    alert('Tidak ada data jurnal kelas untuk diekspor pada filter ini.')
    return
  }

  const headers = ['Tanggal & Waktu', 'Pelapor (Siswa)', 'Mata Pelajaran', 'Guru Pengajar', 'Rangkuman Aktivitas', 'Catatan', 'Status']
  const rows = filteredJournals.value.map(j => [
    `"${j.date_time || ''}"`,
    `"${j.created_by || 'Siswa'}"`,
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
  link.setAttribute('download', `laporan_jurnal_kegiatan_${selectedMonth.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

onMounted(() => {
  fetchJournals()
})
</script>
