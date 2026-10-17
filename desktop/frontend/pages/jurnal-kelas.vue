<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Rekap Kehadiran Siswa"
      subtitle="Laporan dan ringkasan data presensi siswa kelas XI RPL 1"
    >
      <template #actions>
        <button
          @click="exportPdf"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-brand-red text-brand-red hover:bg-brand-red hover:text-white transition-colors text-xs font-semibold flex items-center gap-2 shadow-2xs cursor-pointer"
        >
          <FileTextIcon class="w-3.5 h-3.5" />
          <span>Export PDF</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-6 md:px-8 pb-10 space-y-5">
      <!-- Filter Controls: Tanggal, Kelas, Export PDF -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Filter Tanggal -->
          <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-xl border border-gray-200 text-xs font-medium text-gray-700 shadow-2xs">
            <CalendarIcon class="w-4 h-4 text-gray-400" />
            <input
              v-model="filterDate"
              type="date"
              @change="fetchRecap"
              class="border-none bg-transparent text-xs font-medium text-gray-800 focus:outline-none cursor-pointer"
            />
          </div>

          <!-- Filter Kelas -->
          <div class="relative">
            <select
              v-model="filterClass"
              @change="fetchRecap"
              class="px-3 py-2 pr-8 rounded-xl bg-white border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red shadow-2xs cursor-pointer"
            >
              <option value="1">Kelas: XI RPL 1</option>
              <option value="2">Kelas: XI RPL 2</option>
              <option value="3">Kelas: XII RPL 1</option>
            </select>
          </div>
        </div>

        <button
          @click="exportPdf"
          type="button"
          class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-brand-red text-brand-red hover:bg-brand-red hover:text-white transition-colors text-xs font-semibold shadow-2xs cursor-pointer"
        >
          <DownloadIcon class="w-3.5 h-3.5" />
          <span>Export PDF</span>
        </button>
      </div>

      <!-- 4 Stat Summary Cards (Hadir, Sakit, Izin, Alfa) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Hadir -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <CheckSquareIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Hadir</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ summary.hadir }}</span>
              <span class="text-[11px] font-normal text-gray-400">siswa</span>
            </div>
          </div>
        </div>

        <!-- Sakit -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            <PaperclipIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Sakit</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ summary.sakit }}</span>
              <span class="text-[11px] font-normal text-gray-400">siswa</span>
            </div>
          </div>
        </div>

        <!-- Izin -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <ClockIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Izin</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ summary.izin }}</span>
              <span class="text-[11px] font-normal text-gray-400">siswa</span>
            </div>
          </div>
        </div>

        <!-- Alfa -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-card flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <AlertCircleIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Alfa</span>
            <div class="text-base font-bold text-gray-900 flex items-baseline gap-1 mt-0.5">
              <span>{{ summary.alpha }}</span>
              <span class="text-[11px] font-normal text-gray-400">siswa</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Readonly Indicator Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div class="relative w-full sm:w-80">
          <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama atau NIS..."
            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white shadow-2xs"
          />
        </div>

        <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
          <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
          <span>Read-only - Sumber: Presensi Kehadiran</span>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div v-if="loading" class="py-16 text-center text-gray-400 text-xs">
          Memuat rekap data kehadiran kelas...
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 uppercase text-[10px] tracking-wider font-semibold">
                <th class="py-3 px-4 w-14">NO.</th>
                <th class="py-3 px-4">NAMA SISWA</th>
                <th class="py-3 px-4">NIS</th>
                <th class="py-3 px-4 text-center">STATUS</th>
                <th class="py-3 px-4">WAKTU & CATATAN</th>
                <th class="py-3 px-4 text-center">KEHADIRAN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(row, idx) in filteredRecords"
                :key="row.id || idx"
                class="hover:bg-gray-50/50 transition"
              >
                <td class="py-3.5 px-4 font-medium text-gray-400">
                  {{ String(idx + 1).padStart(2, '0') }}
                </td>
                <td class="py-3.5 px-4 font-bold text-gray-900">
                  {{ row.name }}
                </td>
                <td class="py-3.5 px-4 font-mono text-gray-500">
                  {{ row.nis }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold border"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border-emerald-200': row.status === 'HADIR',
                      'bg-blue-50 text-blue-700 border-blue-200': row.status === 'SAKIT',
                      'bg-amber-50 text-amber-700 border-amber-200': row.status === 'IZIN',
                      'bg-rose-50 text-rose-700 border-rose-200': row.status === 'ALFA',
                    }"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="{
                        'bg-emerald-500': row.status === 'HADIR',
                        'bg-blue-500': row.status === 'SAKIT',
                        'bg-amber-500': row.status === 'IZIN',
                        'bg-rose-500': row.status === 'ALFA',
                      }"
                    ></span>
                    {{ formatStatusLabel(row.status) }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-gray-600">
                  {{ row.time_note }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    v-if="row.verification && row.verification !== '-'"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold"
                    :class="row.verification.includes('Terverifikasi') ? 'text-emerald-700' : 'text-gray-500'"
                  >
                    <CalendarCheck2Icon v-if="row.verification.includes('Terverifikasi')" class="w-3.5 h-3.5 text-emerald-600" />
                    {{ row.verification }}
                  </span>
                  <span v-else class="text-gray-300">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bottom Rekap Presensi Harian Card (Matching media_1791815124022.png) -->
      <div class="rounded-2xl border-2 border-brand-red bg-white p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <BookOpenIcon class="w-5 h-5 text-brand-red shrink-0" />
          <span class="text-sm font-bold text-brand-red">Rekap Presensi Harian</span>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-gray-700">
          <span>Hadir: <strong class="text-emerald-600">{{ summary.hadir }}</strong></span>
          <span>Izin: <strong class="text-amber-600">{{ summary.izin }}</strong></span>
          <span>Sakit: <strong class="text-blue-600">{{ summary.sakit }}</strong></span>
          <span>Alfa: <strong class="text-rose-600">{{ summary.alpha }}</strong></span>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  FileText as FileTextIcon,
  Download as DownloadIcon,
  Calendar as CalendarIcon,
  CheckSquare as CheckSquareIcon,
  Paperclip as PaperclipIcon,
  Clock as ClockIcon,
  AlertCircle as AlertCircleIcon,
  Search as SearchIcon,
  CalendarCheck2 as CalendarCheck2Icon,
  BookOpen as BookOpenIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterDate = ref(new Date().toISOString().slice(0, 10))
const filterClass = ref('1')
const searchQuery = ref('')
const loading = ref(false)

const summary = ref({
  hadir: 3,
  sakit: 2,
  izin: 1,
  alpha: 1
})

const defaultMockRecords = [
  { id: 1, name: 'Nabila Azzahra', nis: '241101', status: 'HADIR', time_note: 'Tepat waktu - 06.52', verification: 'Terverifikasi' },
  { id: 2, name: 'Farhan Maulana', nis: '241102', status: 'SAKIT', time_note: 'Demam sejak pagi', verification: 'Form Attached' },
  { id: 3, name: 'Salsabila Putri', nis: '241103', status: 'IZIN', time_note: 'Keperluan keluarga', verification: 'Terverifikasi' },
  { id: 4, name: 'Rizky Ramadhan', nis: '241104', status: 'ALFA', time_note: 'Belum ada keterangan', verification: '-' },
  { id: 5, name: 'Alif Pratama', nis: '241105', status: 'HADIR', time_note: 'Tepat waktu - 06.01', verification: 'Terverifikasi' },
  { id: 6, name: 'Dinda Maharani', nis: '241106', status: 'SAKIT', time_note: 'Kontrol dokter', verification: 'Form Attached' },
  { id: 7, name: 'Hanif Akbar', nis: '241107', status: 'HADIR', time_note: 'Tepat waktu - 06.19', verification: 'Terverifikasi' },
]

const records = ref<any[]>(defaultMockRecords)

const formatStatusLabel = (st: string) => {
  switch (st) {
    case 'HADIR': return 'Hadir'
    case 'SAKIT': return 'Sakit'
    case 'IZIN': return 'Izin'
    case 'ALFA': return 'Alfa'
    default: return st
  }
}

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
    if (res.summary) summary.value = res.summary
    if (res.records && res.records.length > 0) {
      records.value = res.records
    } else {
      records.value = defaultMockRecords
    }
  } catch (err: any) {
    console.error('Error fetching recap:', err)
  } finally {
    loading.value = false
  }
}

const exportPdf = () => {
  window.print()
}

onMounted(() => {
  fetchRecap()
})
</script>
