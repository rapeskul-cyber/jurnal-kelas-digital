<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Jurnal Kegiatan"
      subtitle="Materi terbaru dan bahan ajar yang dipublikasikan oleh guru"
    />

    <main class="px-8 pb-8 space-y-6">
      <!-- Filter Bar & Schedule Pop-up Button -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Left: Month picker & search -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
          <MonthYearPicker v-model="selectedMonth" @change="fetchMaterials" />

          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari materi / mapel..."
              class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>
        </div>

        <!-- Right: Jadwal Pelajaran Button (Pop-up Scrollable Modal) -->
        <button
          type="button"
          @click="showScheduleModal = true"
          class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:border-brand-red text-gray-700 hover:text-brand-red rounded-xl text-xs font-semibold shadow-2xs hover:shadow-xs transition cursor-pointer shrink-0"
        >
          <Calendar class="w-4 h-4 text-brand-red" />
          <span>Jadwal Pelajaran</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-brand-red border border-red-200">
            {{ todaySchedules.length }}
          </span>
        </button>
      </div>

      <!-- Main Material Table -->
      <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Daftar Materi & Modul Pembelajaran</h3>
            <p class="text-xs text-gray-400">Berkas modul digital yang diunggah instruktur untuk dipelajari</p>
          </div>
          <button
            v-if="search"
            @click="search = ''"
            class="text-xs text-brand-red hover:underline font-semibold cursor-pointer"
          >
            Reset Pencarian
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                <th class="pb-3 font-semibold">Tanggal / Jam</th>
                <th class="pb-3 font-semibold">Instruktur</th>
                <th class="pb-3 font-semibold">Mata Pelajaran</th>
                <th class="pb-3 font-semibold">Bahasan & Rencana Belajar</th>
                <th class="pb-3 font-semibold text-center">Lampiran</th>
                <th class="pb-3 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in filteredMaterials" :key="item.id" class="hover:bg-gray-50/50">
                <td class="py-4 font-bold text-gray-900 whitespace-nowrap">{{ item.date_time }}</td>
                <td class="py-4 text-gray-700 whitespace-nowrap">{{ item.teacher }}</td>
                <td class="py-4 font-bold text-gray-900 whitespace-nowrap">{{ item.subject }}</td>
                <td class="py-4 text-gray-600 max-w-xs">{{ item.topic }}</td>
                <td class="py-4 text-center">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ item.attachments_count }} file
                  </span>
                </td>
                <td class="py-4 text-right">
                  <button
                    type="button"
                    @click="selectedMaterial = item"
                    class="px-3 py-1.5 rounded-lg border border-amber-500/70 text-amber-700 bg-amber-50/30 hover:bg-amber-100 text-xs font-semibold transition-colors cursor-pointer"
                  >
                    Baca Materi
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Detail Modal Dialog -->
      <div v-if="selectedMaterial" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-primary">
                {{ selectedMaterial.subject }}
              </span>
              <h3 class="text-base font-bold text-gray-900 mt-1">{{ selectedMaterial.topic }}</h3>
            </div>
            <button @click="selectedMaterial = null" class="text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="space-y-3 text-xs text-gray-700">
            <div>
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Rencana Belajar</span>
              <p class="bg-gray-50 p-3 rounded-xl border border-gray-100">{{ selectedMaterial.plan || 'Mempelajari arsitektur dan komponen aplikasi.' }}</p>
            </div>
            <div>
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Catatan / Tugas Siswa</span>
              <p class="bg-gray-50 p-3 rounded-xl border border-gray-100">{{ selectedMaterial.assignment || 'Tugas mandiri sesuai materi pelajaran.' }}</p>
            </div>
            <div class="pt-2">
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-2">Lampiran Dokumentasi</span>
              <div class="flex items-center gap-2">
                <a
                  :href="selectedMaterial.attachment_url || '#'"
                  :download="selectedMaterial.attachment_name || 'Modul_Belajar_Kelas.pdf'"
                  target="_blank"
                  class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 text-brand-red hover:bg-red-100 rounded-xl font-semibold border border-red-200 text-xs"
                >
                  <Download class="w-4 h-4" />
                  <span>{{ selectedMaterial.attachment_name || 'Modul_Belajar_Kelas.pdf' }}</span>
                </a>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button
              @click="selectedMaterial = null"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- Modal / Card Pop-up Jadwal Pelajaran (Scrollable) -->
      <div
        v-if="showScheduleModal"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
      >
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4">
          <!-- Header Pop-up -->
          <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-brand-red">
                  Jadwal KBM Siswa
                </span>
                <span class="text-xs text-gray-400">{{ todayFormatted }}</span>
              </div>
              <h3 class="text-base font-bold text-gray-900 mt-1">Jadwal Pembelajaran Kelas</h3>
              <p class="text-xs text-gray-500">Jadwal tatap muka yang ditambahkan oleh guru pengajar</p>
            </div>
            <button
              @click="showScheduleModal = false"
              class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Scrollable Card List -->
          <div class="max-h-[360px] overflow-y-auto space-y-2.5 pr-1">
            <div
              v-for="sch in todaySchedules"
              :key="sch.id"
              class="p-3.5 rounded-2xl border border-gray-150 bg-gray-50/70 hover:bg-white hover:border-gray-300 hover:shadow-xs transition duration-150 flex items-center justify-between gap-3"
            >
              <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2 text-xs">
                  <span class="font-bold text-gray-900 flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-brand-red" />
                    {{ sch.time }}
                  </span>
                  <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                    {{ sch.room || 'Lab. 1' }}
                  </span>
                  <span class="text-[11px] text-gray-400 font-medium">
                    • {{ sch.day || 'Senin' }}
                  </span>
                </div>
                <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate">{{ sch.subject }}</h4>
                <p class="text-[11px] text-gray-500 flex items-center gap-1 truncate">
                  <User class="w-3 h-3 text-gray-400 shrink-0" />
                  <span>{{ sch.teacher }}</span>
                </p>
              </div>

              <button
                type="button"
                @click="selectScheduleAndFilter(sch.subject)"
                class="px-2.5 py-1.5 rounded-xl border border-red-200 bg-red-50 hover:bg-brand-red text-brand-red hover:text-white text-xs font-semibold transition shrink-0 cursor-pointer shadow-2xs"
              >
                Lihat Materi
              </button>
            </div>
          </div>

          <!-- Pop-up Footer -->
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="text-gray-400 text-[11px]">Total {{ todaySchedules.length }} jadwal terdaftar</span>
            <button
              @click="showScheduleModal = false"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition cursor-pointer"
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
import { Calendar, Search, X, Download, Clock, User } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const search = ref('')
const selectedMonth = ref('2026-02')
const materials = ref<any[]>([])
const selectedMaterial = ref<any>(null)
const showScheduleModal = ref(false)

const todayFormatted = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(new Date())
})

const defaultSchedules = [
  {
    id: 1,
    time: '07:30 - 09:00',
    subject: 'Pemrograman Web & Perangkat Bergerak',
    teacher: 'Budi Santoso, S.Pd',
    room: 'Lab. Komputer 1',
    day: 'Senin'
  },
  {
    id: 2,
    time: '09:15 - 11:45',
    subject: 'Basis Data & Desain Sistem',
    teacher: 'Budi Santoso, S.Pd',
    room: 'Lab. Komputer 2',
    day: 'Senin'
  },
  {
    id: 3,
    time: '13:00 - 15:30',
    subject: 'Proyek Perangkat Lunak Mandiri',
    teacher: 'Amiruddin, S.T',
    room: 'Lab. RPL',
    day: 'Senin'
  }
]

const todaySchedules = ref<any[]>(defaultSchedules)

const defaultMaterials = [
  {
    id: 1,
    date_time: '27 Feb, 07:30',
    teacher: 'Budi Santoso, S.Pd',
    subject: 'Pemrograman Web',
    topic: 'Optimasi Query relasional dan teknik routing SPA.',
    plan: 'Pembuatan komponen reactive dan pengelolaan router guard di Nuxt 3.',
    assignment: 'Selesaikan latihan routing bertingkat pada repositori kelas.',
    attachments_count: 2,
    attachment_name: 'Modul_Web_SPA.pdf',
    attachment_url: '#'
  },
  {
    id: 2,
    date_time: '27 Feb, 09:15',
    teacher: 'Budi Santoso, S.Pd',
    subject: 'Basis Data',
    topic: 'Rancangan ERD lanjutan dan perbaikan Schema.',
    plan: 'Normalisasi database 3NF dan pembuatan foreign key constraints.',
    assignment: 'Kirimkan skema ERD format PDF ke google drive kelas.',
    attachments_count: 1,
    attachment_name: 'Panduan_ERD_Lanjutan.pdf',
    attachment_url: '#'
  },
  {
    id: 3,
    date_time: '26 Feb, 10:30',
    teacher: 'Amiruddin, S.T',
    subject: 'Praktek Kerja Lapangan',
    topic: 'Briefing persiapan pendelegasian magang di industri.',
    plan: 'Tata tertib pelaksanaan PKL dan logbook harian industri.',
    assignment: 'Lengkapi berkas surat pernyataan orang tua dan CV.',
    attachments_count: 1,
    attachment_name: 'Panduan_PKL_Industri.pdf',
    attachment_url: '#'
  },
  {
    id: 4,
    date_time: '26 Feb, 13:00',
    teacher: 'Siti Aminah, M.Pd',
    subject: 'Kewirausahaan',
    topic: 'Penyusunan laporan laba-rugi mockup bisnis.',
    plan: 'Analisis BEP (Break Even Point) dan penetapan harga jual produk digital.',
    assignment: 'Kerjakan lembar kerja kalkulasi biaya operasional kelompok.',
    attachments_count: 1,
    attachment_name: 'Template_Laba_Rugi.xlsx',
    attachment_url: '#'
  }
]

const filteredMaterials = computed(() => {
  if (!search.value) return materials.value
  const q = search.value.toLowerCase()
  return materials.value.filter(m =>
    (m.topic && m.topic.toLowerCase().includes(q)) ||
    (m.subject && m.subject.toLowerCase().includes(q)) ||
    (m.teacher && m.teacher.toLowerCase().includes(q))
  )
})

const filterBySubject = (subjectName: string) => {
  // Extract key words e.g. "Pemrograman Web" or "Basis Data"
  search.value = subjectName.split('&')[0].trim()
}

const selectScheduleAndFilter = (subjectName: string) => {
  filterBySubject(subjectName)
  showScheduleModal.value = false
}

const fetchSchedules = async () => {
  try {
    const res = await $fetch<any[]>(`${authStore.apiBase}/schedules`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res && res.length > 0) {
      todaySchedules.value = res.map((s: any) => ({
        id: s.id,
        time: `${s.start_time || '07:30'} - ${s.end_time || '09:00'}`,
        subject: s.subject?.name || 'Mata Pelajaran',
        teacher: s.teacher?.name || 'Guru Pengajar',
        room: s.room || 'Lab. 1',
        day: s.day || 'Senin'
      }))
    }
  } catch (e) {
    console.warn('API schedules note:', e)
  }
}

const fetchMaterials = async () => {
  try {
    const res = await $fetch<any[]>(`${authStore.apiBase}/journals/material?month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res && res.length > 0) {
      materials.value = res
    } else {
      materials.value = defaultMaterials
    }
  } catch (e) {
    console.warn('API materials note:', e)
    materials.value = defaultMaterials
  }
}

onMounted(() => {
  fetchSchedules()
  fetchMaterials()
})
</script>
