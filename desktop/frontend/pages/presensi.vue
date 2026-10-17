<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Input Presensi Kehadiran Kelas"
      :subtitle="dateFormatted + ' • ' + (authStore.user?.school_class?.name || 'XI RPL 1')"
    />

    <main class="px-8 pb-8 space-y-6">
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
          <p class="text-[11px] font-semibold text-red-600 uppercase">Alpha</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.alpha }}</p>
        </div>
      </div>

      <!-- Action Bar: Tandai Semua Hadir & Search -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <button
          type="button"
          @click="markAllPresent"
          class="px-4 py-2.5 bg-primary hover:bg-primary-hover active:scale-[0.99] text-white rounded-xl text-xs font-semibold shadow-2xs flex items-center justify-center gap-2 cursor-pointer"
        >
          <Check class="w-4 h-4" />
          <span>Tandai Semua Hadir</span>
        </button>

        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama siswa..."
            class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </div>
      </div>

      <!-- Main Layout: Table + Verification Panel -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Student Table (3 cols) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs flex flex-col justify-between">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                  <th class="pb-3 font-semibold w-10">No</th>
                  <th class="pb-3 font-semibold">Nama Siswa</th>
                  <th class="pb-3 font-semibold">NIS</th>
                  <th class="pb-3 font-semibold text-center">Status Kehadiran</th>
                  <th class="pb-3 font-semibold">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="student in filteredStudents" :key="student.student_id" class="hover:bg-gray-50/50">
                  <td class="py-3 text-gray-400">{{ student.no }}</td>
                  <td class="py-3 font-semibold text-gray-900">{{ student.name }}</td>
                  <td class="py-3 text-gray-500">{{ student.nis }}</td>
                  <!-- Segmented Buttons for 4 statuses -->
                  <td class="py-3">
                    <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-1">
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'HADIR')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer',
                          student.status === 'HADIR' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Hadir
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'SAKIT')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer',
                          student.status === 'SAKIT' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Sakit
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'IZIN')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer',
                          student.status === 'IZIN' ? 'bg-blue-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Izin
                      </button>
                      <button
                        type="button"
                        @click="setStudentStatus(student, 'ALPHA')"
                        :class="[
                          'px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer',
                          student.status === 'ALPHA' ? 'bg-red-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'
                        ]"
                      >
                        Alpha
                      </button>
                    </div>
                  </td>
                  <!-- Note Input -->
                  <td class="py-3">
                    <input
                      v-model="student.note"
                      type="text"
                      placeholder="Catatan..."
                      class="w-full px-2.5 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-primary"
                    />
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
        <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-6 h-fit">
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
                Masukkan password akun Anda untuk mengunci presensi harian.
              </p>
            </div>

            <!-- Buttons -->
            <div class="space-y-2 pt-2">
              <button
                type="button"
                @click="handleLock"
                :disabled="isSubmitting || sessionStatus === 'LOCKED'"
                class="w-full py-3 bg-primary hover:bg-primary-hover disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-2xs transition-colors cursor-pointer flex items-center justify-center gap-2"
              >
                <Lock class="w-3.5 h-3.5" />
                <span>{{ sessionStatus === 'LOCKED' ? 'Presensi Telah Dikunci' : 'Simpan & Kunci Presensi' }}</span>
              </button>

              <button
                type="button"
                @click="handleDraft"
                :disabled="isSubmitting || sessionStatus === 'LOCKED'"
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
import { Check, Search, Lock } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const dateFormatted = ref('Rabu, 28 Agustus 2026')
const searchQuery = ref('')
const pin = ref('')
const sessionStatus = ref('DRAFT')
const verificationStatus = ref('BELUM_KONFIRMASI')
const isSubmitting = ref(false)
const toastMessage = ref('')
const toastError = ref(false)

const students = ref<any[]>([])

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
  return students.value.filter(s => s.name.toLowerCase().includes(q) || s.nis.includes(q))
})

function setStudentStatus(student: any, status: string) {
  student.status = status
}

function markAllPresent() {
  students.value.forEach(s => {
    s.status = 'HADIR'
  })
}

async function loadAttendance() {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/attendance/today`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    students.value = res.students || []
    if (res.session) {
      sessionStatus.value = res.session.status || 'DRAFT'
      verificationStatus.value = res.session.verification_status || 'BELUM_KONFIRMASI'
    }
  } catch (e) {
    console.error(e)
  }
}

async function handleDraft() {
  isSubmitting.value = true
  toastMessage.value = ''
  toastError.value = false
  try {
    await $fetch(`${authStore.apiBase}/attendance/draft`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        class_id: authStore.user?.class_id || 1,
        date: new Date().toISOString().split('T')[0],
        records: students.value.map(s => ({ student_id: s.student_id, status: s.status, note: s.note }))
      }
    })
    toastMessage.value = 'Draft presensi berhasil disimpan!'
  } catch (e: any) {
    toastError.value = true
    toastMessage.value = e?.data?.message || 'Gagal menyimpan draft.'
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
  try {
    await $fetch(`${authStore.apiBase}/attendance/lock`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        class_id: authStore.user?.class_id || 1,
        date: new Date().toISOString().split('T')[0],
        pin: pin.value,
        records: students.value.map(s => ({ student_id: s.student_id, status: s.status, note: s.note }))
      }
    })
    toastMessage.value = 'Presensi berhasil dikunci & terverifikasi tingkat sekretaris!'
    sessionStatus.value = 'LOCKED'
    verificationStatus.value = 'PENDING'
    pin.value = ''
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
  try {
    const res: any = await $fetch(`${authStore.apiBase}/attendance/confirm`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        class_id: authStore.user?.class_id || 1,
        date: new Date().toISOString().split('T')[0],
      }
    })
    toastMessage.value = res.message || 'Presensi harian berhasil disahkan!'
    verificationStatus.value = 'TERVERIFIKASI'
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
