<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Rekapitulasi Presensi Semester"
      subtitle="Analisis ketertiban presensi siswa per semester"
    >
      <template #actions>
        <button
          @click="exportExcel"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <FileSpreadsheetIcon class="w-3.5 h-3.5 text-green-600" />
          <span class="hidden sm:inline">Ekspor Excel</span>
        </button>
        <button
          @click="printPdf"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <PrinterIcon class="w-3.5 h-3.5" />
          <span>Cetak PDF</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 sm:px-8 pb-10 space-y-5">

    <!-- Filter & Selectors -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 md:p-5 flex flex-wrap items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3">
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Tahun Ajaran / Semester</label>
          <select
            v-model="filterTerm"
            @change="fetchData"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
          >
            <option value="2026-1">2026/2027 - Ganjil (Aktif)</option>
            <option value="2025-2">2025/2026 - Genap</option>
            <option value="2025-1">2025/2026 - Ganjil</option>
          </select>
        </div>
        <!-- Filter Kelas: Hanya muncul untuk Superadmin & Guru Pengajar yang mengampu banyak kelas -->
        <div v-if="!isClassLocked">
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kelas</label>
          <select
            v-model="filterClass"
            @change="fetchData"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer min-h-[38px]"
          >
            <option v-for="cls in classOptions" :key="cls.id" :value="String(cls.id)">
              {{ cls.name }}
            </option>
          </select>
        </div>
      </div>

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

    <!-- Summary KPI cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hari Efektif</span>
          <CalendarIcon class="w-5 h-5 text-primary" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900">{{ effectiveDays }}</span>
          <span class="text-xs text-gray-500 font-medium">Hari Pembelajaran</span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Total pertemuan terjadwal semester ini</p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rata-rata Kehadiran</span>
          <TrendingUpIcon class="w-5 h-5 text-green-600" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-green-600">{{ avgAttendance }}</span>
          <span class="text-xs px-2 py-0.5 rounded-full font-semibold" :class="predicateBadgeClass">
            {{ predicate }}
          </span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">{{ targetNote }}</p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Siswa Aktif</span>
          <UsersIcon class="w-5 h-5 text-blue-600" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900">{{ students.length }}</span>
          <span class="text-xs text-gray-500 font-medium">Siswa Kelas Industri RPL</span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Terdaftar resmi di database Dapodik</p>
      </div>
    </div>

    <!-- Table of Student Semester Recap -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">Daftar Kehadiran Kumulatif Siswa</h2>
          <p class="text-xs text-gray-400 mt-0.5">Rincian angka presensi Hadir, Sakit, Izin, ALFA per siswa</p>
        </div>
        <span class="text-xs text-gray-500 font-medium">{{ filteredStudents.length }} Siswa Ditampilkan</span>
      </div>

      <div v-if="loading" class="py-16 text-center text-gray-400 text-sm">
        Memuat rekapitulasi semester...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
              <th class="py-3 px-4 w-12 text-center whitespace-nowrap">No</th>
              <th class="py-3 px-4 whitespace-nowrap">Nama Siswa</th>
              <th class="py-3 px-4 whitespace-nowrap">NIS / NISN</th>
              <th class="py-3 px-4 text-center text-emerald-700 whitespace-nowrap">Hadir</th>
              <th class="py-3 px-4 text-center text-amber-700 whitespace-nowrap">Sakit</th>
              <th class="py-3 px-4 text-center text-blue-700 whitespace-nowrap">Izin</th>
              <th class="py-3 px-4 text-center text-rose-700 whitespace-nowrap">ALFA</th>
              <th class="py-3 px-4 text-center whitespace-nowrap">Persentase</th>
              <th class="py-3 px-4 text-center whitespace-nowrap">Predikat</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="(st, index) in filteredStudents"
              :key="st.id"
              class="hover:bg-gray-50/50 transition"
            >
              <td class="py-3 px-4 text-center font-medium text-gray-500 whitespace-nowrap">{{ index + 1 }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                <div class="font-bold text-gray-900">{{ st.name }}</div>
              </td>
              <td class="py-3 px-4 text-gray-500 font-mono whitespace-nowrap">{{ st.nis }}</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-600 whitespace-nowrap">{{ st.hadir }}</td>
              <td class="py-3 px-4 text-center font-semibold text-amber-600 whitespace-nowrap">{{ st.sakit }}</td>
              <td class="py-3 px-4 text-center font-semibold text-blue-600 whitespace-nowrap">{{ st.izin }}</td>
              <td class="py-3 px-4 text-center font-semibold text-rose-600 whitespace-nowrap">{{ st.alpha }}</td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <span class="font-bold text-gray-900">{{ st.percentage }}</span>
              </td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <span
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap"
                  :class="parseFloat(st.percentage) >= 90 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ parseFloat(st.percentage) >= 90 ? 'Sangat Baik' : 'Cukup' }}
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
  FileSpreadsheet as FileSpreadsheetIcon,
  Printer as PrinterIcon,
  Search as SearchIcon,
  Calendar as CalendarIcon,
  TrendingUp as TrendingUpIcon,
  Users as UsersIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const filterTerm = ref('2026-1')
const filterClass = ref('1')
const searchQuery = ref('')
const loading = ref(false)

const effectiveDays = ref(0)
const avgAttendance = ref('0%')
const predicate = ref('Sangat Baik')
const targetNote = ref('Target sekolah di atas 90% tercapai')
const predicateBadgeClass = ref('text-green-700 bg-green-50')
const students = ref<any[]>([])

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value
  const q = searchQuery.value.toLowerCase()
  return students.value.filter(
    s => s.name.toLowerCase().includes(q) || (s.nis && s.nis.includes(q))
  )
})

const fetchData = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/attendance/semester-recap`, {
      headers: { Authorization: 'Bearer ' + authStore.token },
      params: {
        class_id: filterClass.value,
        term: filterTerm.value
      }
    })
    effectiveDays.value = res.effective_days
    avgAttendance.value = res.average_attendance
    if (res.predicate) predicate.value = res.predicate
    if (res.target_note) targetNote.value = res.target_note
    if (res.predicate_badge_class) predicateBadgeClass.value = res.predicate_badge_class
    students.value = res.students || []
  } catch (err: any) {
    console.error('Error fetching semester recap:', err)
  } finally {
    loading.value = false
  }
}

const exportExcel = () => {
  if (!filteredStudents.value || filteredStudents.value.length === 0) {
    alert('Tidak ada data siswa untuk diekspor.')
    return
  }

  const headers = ['No', 'Nama Siswa', 'NIS / NISN', 'Hadir', 'Sakit', 'Izin', 'ALFA', 'Persentase', 'Predikat']
  const rows = filteredStudents.value.map((s, idx) => [
    idx + 1,
    `"${s.name}"`,
    `"${s.nis}"`,
    s.hadir,
    s.sakit,
    s.izin,
    s.alpha,
    `"${s.percentage}"`,
    `"${parseFloat(s.percentage) >= 90 ? 'Sangat Baik' : 'Cukup'}"`
  ])

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `rekap_presensi_semester_${filterTerm.value}_kelas_${filterClass.value}.csv`)
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

const isClassLocked = computed(() => {
  const r = authStore.user?.role
  return r === 'GURU_PEMBIMBING' || r === 'SISWA' || r === 'SEKRETARIS' || r === 'ORANG_TUA'
})

const currentClassName = computed(() => {
  const u: any = authStore.user
  if (u?.school_class?.name) return u.school_class.name
  if (u?.class_name) return u.class_name
  const found = classOptions.value.find(c => String(c.id) === String(filterClass.value))
  return found ? found.name : 'Kelas Industri RPL'
})

const fetchClasses = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/master/classes`, {
      headers: { Authorization: 'Bearer ' + authStore.token }
    })
    if (Array.isArray(res) && res.length > 0) {
      classOptions.value = res
      
      const u: any = authStore.user
      if (isClassLocked.value) {
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
  fetchData()
})
</script>
