<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Rekap Kehadiran Siswa"
      subtitle="Laporan dan ringkasan data presensi siswa kelas XI RPL 1"
    >
      <template #actions>
        <button
          v-if="authStore.user?.role === 'SEKRETARIS' || authStore.user?.role === 'GURU_PENGAJAR'"
          @click="showAddJournalModal = true"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-brand-red text-white hover:bg-brand-red-dark transition-colors text-xs font-semibold flex items-center gap-2 shadow-2xs cursor-pointer"
        >
          <PlusIcon class="w-3.5 h-3.5" />
          <span>Isi Jurnal KBM</span>
        </button>
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
      <!-- Success Toast -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2Icon class="w-4 h-4 text-emerald-600" />
          {{ successMsg }}
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
      </div>
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

          <!-- Filter Kelas: Terkunci untuk Siswa / Sekretaris, Pilihan untuk Guru -->
          <div class="relative">
            <div
              v-if="authStore.user?.role === 'SEKRETARIS' || authStore.user?.role === 'SISWA'"
              class="px-3.5 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 shadow-2xs flex items-center gap-1.5"
            >
              <span class="w-2 h-2 rounded-full bg-brand-red"></span>
              <span>Kelas: {{ authStore.user?.class_name || (authStore.user?.class_id === 2 ? 'XI RPL 2' : 'XI RPL 1') }}</span>
            </div>
            <select
              v-else
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

      <!-- Modal Input Jurnal KBM (Sekretaris / Pengajar) -->
      <div
        v-if="showAddJournalModal"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
      >
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <h3 class="text-base font-bold text-gray-900">Pengisian Jurnal Kelas Harian</h3>
              <p class="text-xs text-gray-400 mt-0.5">Input catatan agenda & realisasi tatap muka KBM</p>
            </div>
            <button
              type="button"
              @click="showAddJournalModal = false"
              class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
            >
              <XIcon class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitJournal" class="space-y-4">
            <!-- Pilih Jadwal Pelajaran -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Jadwal / Mata Pelajaran
              </label>
              <select
                v-model="journalForm.schedule_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
                required
              >
                <option value="" disabled>-- Pilih Jadwal Pelajaran --</option>
                <option v-for="sch in schedules" :key="sch.id" :value="sch.id">
                  {{ sch.start_time }} - {{ sch.end_time }} | {{ sch.subject?.name }} ({{ sch.teacher?.name }})
                </option>
              </select>
            </div>

            <!-- Tanggal -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Tanggal Pembelajaran
              </label>
              <input
                v-model="journalForm.date"
                type="date"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
                required
              />
            </div>

            <!-- Pokok Bahasan / KD -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Pokok Bahasan / KD
              </label>
              <input
                v-model="journalForm.topic"
                type="text"
                placeholder="Contoh: Optimasi Query Relasional & Desain API"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                required
              />
            </div>

            <!-- Rangkuman Aktivitas -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Rangkuman Aktivitas & Materi KBM
              </label>
              <textarea
                v-model="journalForm.activity"
                rows="3"
                placeholder="Rangkuman jalannya pembelajaran kelas, topik bahasan, dan kegiatan siswa..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                required
              ></textarea>
            </div>

            <!-- Catatan Tambahan -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Catatan dari Sekretaris / Kelas
              </label>
              <input
                v-model="journalForm.notes"
                type="text"
                placeholder="Contoh: Praktikum berlangsung tertib di Lab Komputer 1"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
              />
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-gray-100">
              <button
                type="button"
                @click="showAddJournalModal = false"
                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isSubmittingJournal"
                class="px-5 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white text-xs font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer"
              >
                {{ isSubmittingJournal ? 'Mengirim...' : 'Kirim Jurnal KBM' }}
              </button>
            </div>
          </form>
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
  BookOpen as BookOpenIcon,
  Plus as PlusIcon,
  CheckCircle2 as CheckCircle2Icon,
  X as XIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterDate = ref(new Date().toISOString().slice(0, 10))
const filterClass = ref(authStore.user?.class_id ? String(authStore.user.class_id) : '1')
const searchQuery = ref('')
const loading = ref(false)
const successMsg = ref('')
const showAddJournalModal = ref(false)
const isSubmittingJournal = ref(false)
const schedules = ref<any[]>([])

const journalForm = reactive({
  schedule_id: '',
  date: new Date().toISOString().slice(0, 10),
  topic: '',
  activity: '',
  notes: ''
})

const summary = ref({
  hadir: 0,
  sakit: 0,
  izin: 0,
  alpha: 0
})

const records = ref<any[]>([])

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

const fetchSchedules = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/schedules`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    schedules.value = res || []
    if (schedules.value.length > 0 && !journalForm.schedule_id) {
      journalForm.schedule_id = schedules.value[0].id
    }
  } catch (e) {
    console.error('Error fetching schedules:', e)
  }
}

const submitJournal = async () => {
  if (!journalForm.schedule_id || !journalForm.topic || !journalForm.activity) {
    alert('Harap lengkapi semua kolom jurnal yang wajib diisi.')
    return
  }
  isSubmittingJournal.value = true
  try {
    await $fetch(`${config.public.apiBase}/journals/class`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        schedule_id: journalForm.schedule_id,
        class_id: filterClass.value,
        date: journalForm.date,
        topic: journalForm.topic,
        activity: journalForm.activity,
        notes: journalForm.notes
      }
    })
    successMsg.value = 'Jurnal kelas harian berhasil disimpan dan menunggu verifikasi guru pengajar!'
    showAddJournalModal.value = false
    journalForm.topic = ''
    journalForm.activity = ''
    journalForm.notes = ''
  } catch (err: any) {
    alert(err?.data?.message || 'Gagal menyimpan jurnal kelas.')
  } finally {
    isSubmittingJournal.value = false
  }
}

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
    records.value = res.records || []
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
  fetchSchedules()
})
</script>
