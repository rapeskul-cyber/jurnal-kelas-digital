<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Monitoring & Rekap Presensi Mandiri Kelas"
      :subtitle="dateFormatted + ' • ' + (authStore.user?.school_class?.name || '-')"
    />

    <main class="px-4 sm:px-8 pb-8 space-y-5">
      <!-- Banner Sinkronisasi Otomatis Presensi Mandiri Siswa -->
      <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200 text-blue-900 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
        <div>
          <span class="font-bold">Sinkronisasi Presensi Mandiri Aktif:</span>
          Data di bawah terisi otomatis dari <strong>Presensi Mandiri Siswa</strong> dan <strong>Surat Izin</strong> yang telah disetujui Wali Kelas. Sekretaris / Wali Kelas cukup memantau atau mengoreksi bila diperlukan.
        </div>
      </div>

      <!-- 5 Summary Metric Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white border border-gray-150 rounded-2xl p-4 shadow-2xs">
          <p class="text-[11px] font-semibold text-gray-400 uppercase">Total Siswa</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ summary.total }}</p>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-4 shadow-2xs">
          <p class="text-[11px] font-semibold text-emerald-600 uppercase">Hadir</p>
          <p class="text-2xl font-bold text-emerald-600 mt-1">{{ summary.hadir }}</p>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-4 shadow-2xs">
          <p class="text-[11px] font-semibold text-amber-600 uppercase">Sakit</p>
          <p class="text-2xl font-bold text-amber-600 mt-1">{{ summary.sakit }}</p>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-4 shadow-2xs">
          <p class="text-[11px] font-semibold text-blue-600 uppercase">Izin</p>
          <p class="text-2xl font-bold text-blue-600 mt-1">{{ summary.izin }}</p>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-4 shadow-2xs">
          <p class="text-[11px] font-semibold text-red-600 uppercase">ALFA</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.alpha }}</p>
        </div>
      </div>

      <!-- Action Bar: Refresh & Search -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="() => loadAttendance(false)"
            :disabled="isRefreshing"
            class="px-3.5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-2 shadow-2xs"
            title="Segarkan data presensi"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin text-primary': isRefreshing }" />
            <span>{{ isRefreshing ? 'Menyinkronkan...' : 'Segarkan Data' }}</span>
          </button>
        </div>

        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama siswa..."
            class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-2xs"
          />
        </div>
      </div>

      <!-- Main Layout: Table + Verification Panel -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Student Table (3 cols) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs flex flex-col justify-between">
          <!-- Mobile Card List View -->
          <div class="block md:hidden space-y-3">
            <div v-if="isLoading && students.length === 0" v-for="i in 6" :key="'skel-m-' + i" class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50 animate-pulse space-y-3">
              <div class="h-4 w-32 bg-gray-200 rounded"></div>
              <div class="h-10 w-full bg-gray-200 rounded-xl"></div>
            </div>

            <div v-else-if="filteredStudents.length > 0" v-for="student in filteredStudents" :key="'mob-' + student.student_id" class="p-3.5 sm:p-4 rounded-2xl border border-gray-200 bg-white shadow-2xs space-y-3">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-500 font-bold text-xs flex items-center justify-center shrink-0">{{ student.no }}</span>
                    <span class="font-bold text-gray-900 text-sm truncate">{{ student.name }}</span>
                  </div>
                  <p class="text-xs text-gray-500 font-mono mt-0.5 ml-8">NIS: {{ student.nis }}</p>
                </div>
                <span
                  v-if="student.is_rejected_leave"
                  class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shrink-0"
                >
                  Izin Ditolak
                </span>
              </div>

              <!-- Mobile Segmented Buttons with 44px min tap height -->
              <div class="grid grid-cols-4 gap-1 p-1 bg-gray-100 rounded-xl">
                <button
                  type="button"
                  @click="setStudentStatus(student, 'HADIR')"
                  class="py-2.5 rounded-lg text-xs font-bold transition-all text-center cursor-pointer min-h-[44px] flex items-center justify-center touch-manipulation active:scale-95"
                  :class="student.status === 'HADIR' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                >
                  Hadir
                </button>
                <button
                  type="button"
                  @click="setStudentStatus(student, 'SAKIT')"
                  class="py-2.5 rounded-lg text-xs font-bold transition-all text-center cursor-pointer min-h-[44px] flex items-center justify-center touch-manipulation active:scale-95"
                  :class="student.status === 'SAKIT' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                >
                  Sakit
                </button>
                <button
                  type="button"
                  @click="setStudentStatus(student, 'IZIN')"
                  class="py-2.5 rounded-lg text-xs font-bold transition-all text-center cursor-pointer min-h-[44px] flex items-center justify-center touch-manipulation active:scale-95"
                  :class="student.status === 'IZIN' ? 'bg-blue-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                >
                  Izin
                </button>
                <button
                  type="button"
                  @click="setStudentStatus(student, 'ALPHA')"
                  class="py-2.5 rounded-lg text-xs font-bold transition-all text-center cursor-pointer min-h-[44px] flex items-center justify-center touch-manipulation active:scale-95"
                  :class="student.status === 'ALPHA' ? 'bg-rose-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                >
                  ALFA
                </button>
              </div>

              <!-- Note input -->
              <input
                v-model="student.note"
                type="text"
                placeholder="Catatan kehadiran (opsional)..."
                class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-gray-50/50"
              />
            </div>

            <div v-else class="py-12 text-center text-gray-500">
              <div v-if="loadError" class="space-y-3">
                <div class="inline-flex p-3 bg-red-50 text-red-600 rounded-full mb-1">
                  <AlertCircle class="w-6 h-6" />
                </div>
                <p class="text-xs text-red-600 font-semibold max-w-sm mx-auto">{{ loadError }}</p>
                <div>
                  <button
                    type="button"
                    @click="() => loadAttendance(false)"
                    class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer min-h-[44px]"
                  >
                    Coba Lagi
                  </button>
                </div>
              </div>
              <div v-else class="text-xs text-gray-400 py-6">
                Tidak ada siswa yang cocok dengan pencarian "{{ searchQuery }}".
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                  <th class="pb-3 font-semibold w-10 whitespace-nowrap">No</th>
                  <th class="pb-3 font-semibold whitespace-nowrap">Nama Siswa</th>
                  <th class="pb-3 font-semibold whitespace-nowrap">NIS</th>
                  <th class="pb-3 font-semibold text-center whitespace-nowrap">Status Kehadiran</th>
                  <th class="pb-3 font-semibold whitespace-nowrap">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <!-- Skeleton rows during initial loading -->
                <tr v-if="isLoading && students.length === 0" v-for="i in 8" :key="'skel-' + i" class="animate-pulse">
                  <td class="py-3.5 whitespace-nowrap"><div class="h-3 w-4 bg-gray-200 rounded"></div></td>
                  <td class="py-3.5 whitespace-nowrap"><div class="h-3.5 w-36 bg-gray-200 rounded"></div></td>
                  <td class="py-3.5 whitespace-nowrap"><div class="h-3 w-16 bg-gray-200 rounded"></div></td>
                  <td class="py-3.5 whitespace-nowrap"><div class="h-7 w-48 bg-gray-200 rounded-xl mx-auto"></div></td>
                  <td class="py-3.5 whitespace-nowrap"><div class="h-7 w-full bg-gray-100 rounded-lg"></div></td>
                </tr>

                <!-- Actual Student Rows -->
                <tr v-else-if="filteredStudents.length > 0" v-for="student in filteredStudents" :key="student.student_id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3 text-gray-400 whitespace-nowrap">{{ student.no }}</td>
                  <td class="py-3 font-semibold text-gray-900 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span>{{ student.name }}</span>
                      <span
                        v-if="student.is_rejected_leave"
                        class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                        title="Surat izin ditolak Wali Kelas - Wajib verifikasi fisik di kelas"
                      >
                        Izin Ditolak Walas
                      </span>
                    </div>
                  </td>
                  <td class="py-3 text-gray-500 whitespace-nowrap">{{ student.nis }}</td>
                  <!-- Segmented Buttons for 4 statuses -->
                  <td class="py-3 whitespace-nowrap text-center">
                    <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-1">
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'HADIR')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer whitespace-nowrap',
                          student.status === 'HADIR' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Hadir
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'SAKIT')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer whitespace-nowrap',
                          student.status === 'SAKIT' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Sakit
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'IZIN')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer whitespace-nowrap',
                          student.status === 'IZIN' ? 'bg-blue-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Izin
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'ALPHA')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer whitespace-nowrap',
                          student.status === 'ALPHA' ? 'bg-rose-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        ALFA
                      </button>
                    </div>
                  </td>
                  <!-- Note Input -->
                  <td class="py-3">
                    <input
                      v-model="student.note"
                      type="text"
                      placeholder="Catatan..."
                      class="w-full px-2.5 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    />
                  </td>
                </tr>

                <!-- Error or Empty Search -->
                <tr v-else>
                  <td colspan="5" class="py-12 text-center text-gray-500">
                    <div v-if="loadError" class="space-y-3">
                      <div class="inline-flex p-3 bg-red-50 text-red-600 rounded-full mb-1">
                        <AlertCircle class="w-6 h-6" />
                      </div>
                      <p class="text-xs text-red-600 font-semibold max-w-sm mx-auto">{{ loadError }}</p>
                      <div>
                        <button
                          type="button"
                          @click="() => loadAttendance(false)"
                          class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer"
                        >
                          Coba Lagi
                        </button>
                      </div>
                    </div>
                    <div v-else class="text-xs text-gray-400 py-6">
                      Tidak ada siswa yang cocok dengan pencarian "{{ searchQuery }}".
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Bar -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-100 text-xs text-gray-500">
            <span>Menampilkan {{ filteredStudents.length }} dari {{ students.length }} siswa</span>
            <div class="flex items-center gap-1">
              <button class="px-3 py-1 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 text-xs disabled:opacity-50">
                Sebelumnya
              </button>
              <button class="px-3 py-1 rounded-lg bg-primary text-white text-xs font-bold">1</button>
              <button class="px-3 py-1 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 text-xs">
                Berikutnya
              </button>
            </div>
          </div>
        </div>

        <!-- Verification Panel (1 col) -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs space-y-6 h-fit">
          <h3 class="text-sm font-bold text-gray-900 pb-3 border-b border-gray-100">Verifikasi Status</h3>

          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Sekretaris Kelas</span>
              <span
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="sessionStatus === 'LOCKED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600'"
              >
                {{ sessionStatus === 'LOCKED' ? 'Selesai Dikunci' : 'Draf Belum Dikunci' }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Guru Pengajar</span>
              <span
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="verificationStatus === 'TERVERIFIKASI' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
              >
                {{ verificationStatus === 'TERVERIFIKASI' ? 'Terverifikasi' : 'Menunggu Pengesahan' }}
              </span>
            </div>
            <p v-if="sessionStatus === 'LOCKED'" class="text-[11px] text-gray-500 italic pt-1 border-t border-gray-50">
              * Presensi hari ini sudah pernah dikunci. Anda masih dapat mengubah data kehadiran lalu menekan "Kunci Ulang Presensi" dengan memasukkan password Anda.
            </p>
          </div>

          <!-- Aksi untuk Guru Pengajar / Pembimbing: Konfirmasi Langsung -->
          <div v-if="authStore.user?.role === 'GURU_PENGAJAR' || authStore.user?.role === 'GURU_PEMBIMBING'" class="space-y-3 pt-2 border-t border-gray-100">
            <div v-if="verificationStatus !== 'TERVERIFIKASI'">
              <p class="text-[11px] text-gray-500 mb-2">Sebagai Guru Pengajar/Pembimbing, Anda dapat mengesahkan presensi harian kelas ini secara langsung:</p>
              <button
                type="button"
                @click="handleConfirmTeacher"
                :disabled="isSubmitting"
                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-2xs transition-colors cursor-pointer flex items-center justify-center gap-2"
              >
                <Check class="w-4 h-4" />
                <span>Sahkan & Verifikasi Presensi</span>
              </button>
            </div>
            <div v-else class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
              <Check class="w-4 h-4 text-emerald-600" />
              <span>Presensi harian ini telah Anda verifikasi resmi.</span>
            </div>
          </div>

          <!-- PIN / Password Lock Input (Hanya untuk Sekretaris) -->
          <template v-else>
            <div class="space-y-2 pt-2 border-t border-gray-100">
              <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                PIN / Password Sekretaris
              </label>
              <input
                v-model="pin"
                type="password"
                placeholder="••••••••••••"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
              <p class="text-[10px] text-gray-400">
                Masukkan password akun Anda untuk mengunci atau memperbarui presensi harian.
              </p>
            </div>

            <!-- Buttons -->
            <div class="space-y-2 pt-2">
              <button
                type="button"
                @click="handleLock"
                :disabled="isSubmitting"
                class="w-full py-3 bg-primary hover:bg-primary-hover disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-2xs transition-colors cursor-pointer flex items-center justify-center gap-2"
              >
                <Lock class="w-3.5 h-3.5" />
                <span>{{ sessionStatus === 'LOCKED' ? 'Kunci Ulang Presensi' : 'Simpan & Kunci Presensi' }}</span>
              </button>

              <button
                type="button"
                @click="handleDraft"
                :disabled="isSubmitting"
                class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50"
              >
                Simpan Draft
              </button>
            </div>
          </template>

          <!-- Feedback Toast -->
          <div v-if="toastMessage" class="p-3 rounded-xl text-xs" :class="toastError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
            {{ toastMessage }}
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Check,
  Search,
  Lock,
  RefreshCw,
  AlertCircle,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

function getLocalDateString(d: Date = new Date()) {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function formatReadableDate(dateStr: string) {
  try {
    const [y, m, d] = dateStr.split('-').map(Number)
    const dateObj = new Date(y, m - 1, d)
    return new Intl.DateTimeFormat('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    }).format(dateObj)
  } catch {
    return dateStr
  }
}

const targetClassId = computed(() => {
  return (route.query.class_id as string) || (authStore.user?.class_id ? String(authStore.user.class_id) : '1')
})

const targetDate = computed(() => {
  return (route.query.date as string) || getLocalDateString()
})

const cacheKey = computed(() => `cache_presensi_class_${targetClassId.value}`)

const dateFormatted = ref(formatReadableDate(targetDate.value))
const searchQuery = ref('')
const pin = ref('')
const sessionStatus = ref('DRAFT')
const verificationStatus = ref('BELUM_KONFIRMASI')
const isSubmitting = ref(false)
const isLoading = ref(true)
const isRefreshing = ref(false)
const loadError = ref('')
const toastMessage = ref('')
const toastError = ref(false)

const students = ref<any[]>([])

// 1. HIDRASI INSTAN DARI CACHE LOKAL (0ms DELAY)
if (process.client) {
  try {
    const cached = localStorage.getItem(`cache_presensi_class_${authStore.user?.class_id || 1}`)
    if (cached) {
      const parsed = JSON.parse(cached)
      if (parsed.students && Array.isArray(parsed.students) && parsed.students.length > 0) {
        students.value = parsed.students
        isLoading.value = false
        if (parsed.session) {
          sessionStatus.value = parsed.session.status || 'DRAFT'
          verificationStatus.value = parsed.session.verification_status || 'BELUM_KONFIRMASI'
          if (parsed.session.date) {
            dateFormatted.value = formatReadableDate(parsed.session.date)
          }
        }
      }
    }
  } catch (e) {
    console.error('Failed to parse presensi cache:', e)
  }
}

const summary = computed(() => {
  const total = students.value.length
  const hadir = students.value.filter(s => s.status === 'HADIR').length
  const sakit = students.value.filter(s => s.status === 'SAKIT').length
  const izin = students.value.filter(s => s.status === 'IZIN').length
  const alpha = students.value.filter(s => s.status === 'ALPHA').length
  return { total, hadir, sakit, izin, alpha }
})

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value
  const q = searchQuery.value.toLowerCase()
  return students.value.filter(s => s.name?.toLowerCase().includes(q) || s.nis?.includes(q))
})

function setStudentStatus(student: any, status: string) {
  student.status = status
}

function getAuthToken(): string | null {
  if (authStore.token) return authStore.token
  if (process.client) {
    const saved = localStorage.getItem('token')
    if (saved) {
      authStore.token = saved
      return saved
    }
  }
  return null
}

async function loadAttendance(silent = false) {
  if (!silent && !students.value.length) {
    isLoading.value = true
  } else {
    isRefreshing.value = true
  }
  loadError.value = ''

  let token = getAuthToken()
  if (!token) {
    // Jeda sejenak mengantisipasi Pinia store hydrating dari localStorage
    await new Promise(r => setTimeout(r, 100))
    token = getAuthToken()
  }

  if (!token) {
    isLoading.value = false
    isRefreshing.value = false
    loadError.value = 'Sesi autentikasi belum siap. Silakan klik tombol Coba Lagi.'
    return
  }

  try {
    const res = await $fetch<any>(`${authStore.apiBase}/attendance/today`, {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        class_id: targetClassId.value,
        date: targetDate.value
      }
    })

    if (res.students && res.students.length > 0) {
      students.value = res.students
    }

    if (res.session) {
      sessionStatus.value = res.session.status || 'DRAFT'
      verificationStatus.value = res.session.verification_status || 'BELUM_KONFIRMASI'
      if (res.session.date) {
        dateFormatted.value = formatReadableDate(res.session.date)
      }
    }

    // Perbarui cache lokal agar kunjungan berikutnya instan
    if (process.client && students.value.length > 0) {
      localStorage.setItem(cacheKey.value, JSON.stringify({
        students: students.value,
        session: res.session
      }))
    }
  } catch (e: any) {
    console.error('Attendance fetch error:', e)
    if (!students.value.length) {
      loadError.value = e?.data?.message || 'Gagal memuat data presensi dari server. Periksa koneksi Anda.'
    }
  } finally {
    isLoading.value = false
    isRefreshing.value = false
  }
}

async function handleDraft() {
  isSubmitting.value = true
  toastMessage.value = ''
  toastError.value = false
  const token = getAuthToken()
  try {
    await $fetch(`${authStore.apiBase}/attendance/draft`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: {
        class_id: Number(targetClassId.value) || 1,
        date: targetDate.value,
        records: students.value.map(s => ({ student_id: s.student_id, status: s.status, note: s.note }))
      }
    })
    toastMessage.value = 'Draft presensi berhasil disimpan!'
    sessionStatus.value = 'DRAFT'
    if (process.client) {
      localStorage.setItem(cacheKey.value, JSON.stringify({
        students: students.value,
        session: { status: 'DRAFT', verification_status: verificationStatus.value, date: targetDate.value }
      }))
    }
    loadAttendance(true)
  } catch (e: any) {
    toastError.value = true
    toastMessage.value = e?.data?.message || 'Gagal menyimpan draft presensi.'
  } finally {
    isSubmitting.value = false
  }
}

async function handleLock() {
  if (!pin.value) {
    toastError.value = true
    toastMessage.value = 'Silakan masukkan password akun Anda untuk mengunci.'
    return
  }
  isSubmitting.value = true
  toastMessage.value = ''
  toastError.value = false
  const token = getAuthToken()
  try {
    await $fetch(`${authStore.apiBase}/attendance/lock`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: {
        class_id: Number(targetClassId.value) || 1,
        date: targetDate.value,
        pin: pin.value,
        records: students.value.map(s => ({ student_id: s.student_id, status: s.status, note: s.note }))
      }
    })
    toastMessage.value = 'Presensi berhasil dikunci & terverifikasi tingkat sekretaris!'
    sessionStatus.value = 'LOCKED'
    verificationStatus.value = 'PENDING'
    pin.value = ''
    if (process.client) {
      localStorage.setItem(cacheKey.value, JSON.stringify({
        students: students.value,
        session: { status: 'LOCKED', verification_status: 'PENDING', date: targetDate.value }
      }))
    }
    loadAttendance(true)
  } catch (e: any) {
    toastError.value = true
    toastMessage.value = e?.data?.message || 'Gagal mengunci presensi. Periksa PIN/password Anda.'
  } finally {
    isSubmitting.value = false
  }
}

async function handleConfirmTeacher() {
  isSubmitting.value = true
  toastMessage.value = ''
  toastError.value = false
  const token = getAuthToken()
  try {
    const res: any = await $fetch(`${authStore.apiBase}/attendance/confirm`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: {
        class_id: Number(targetClassId.value) || 1,
        date: targetDate.value,
      }
    })
    toastMessage.value = res.message || 'Presensi harian berhasil disahkan!'
    verificationStatus.value = 'TERVERIFIKASI'
    loadAttendance(true)
  } catch (e: any) {
    toastError.value = true
    toastMessage.value = e?.data?.message || 'Gagal mengesahkan presensi.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadAttendance()
})
</script>
