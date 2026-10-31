<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="authStore.user?.role === 'ORANG_TUA' ? 'Riwayat Surat Izin Anak' : 'Pengajuan Surat Izin Belajar'"
      :subtitle="authStore.user?.role === 'ORANG_TUA' ? 'Pantau status pengajuan izin belajar putra/putri Anda' : 'Ajukan permohonan izin/sakit secara mandiri langsung kepada Wali Kelas'"
    />

    <main class="px-4 sm:px-8 pb-10 space-y-5">
      <!-- Alert / Notif -->
      <div v-if="successMsg" class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm font-medium">
          <CheckCircle2Icon class="w-5 h-5 text-green-600" />
          {{ successMsg }}
        </div>
        <button @click="successMsg = ''" class="text-green-500 hover:text-green-700 text-sm cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Input (Kiri) - Tampil untuk Siswa / Sekretaris -->
        <div
          v-if="authStore.user?.role !== 'ORANG_TUA'"
          class="lg:col-span-6 bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4"
        >
          <div class="border-b border-gray-100 pb-3">
            <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
              <MailIcon class="w-5 h-5 text-primary" />
              Formulir Permohonan Izin Mandiri
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Surat izin akan langsung diteruskan ke Wali Kelas untuk diverifikasi</p>
          </div>

          <!-- Banner Notifikasi: Jika siswa sudah memiliki izin yang DISETUJUI hari ini -->
          <div
            v-if="activeApprovedLeaveToday"
            class="p-4 rounded-2xl bg-blue-50 border-2 border-primary/30 space-y-2 text-slate-800"
          >
            <div class="flex items-center gap-2">
              <CheckCircle2Icon class="w-5 h-5 text-primary shrink-0" />
              <h4 class="font-bold text-xs text-primary">Surat Izin Hari Ini Telah Disetujui Wali Kelas</h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
              Anda telah memiliki izin resmi berstatus <strong>DISETUJUI ({{ activeApprovedLeaveToday.type }})</strong> untuk tanggal <strong>{{ activeApprovedLeaveToday.start_date }} s/d {{ activeApprovedLeaveToday.end_date }}</strong>. Status absensi Anda telah tercatat otomatis tidak masuk sekolah.
            </p>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Informasi Pemohon (Otomatis sesuai akun Siswa yang login) -->
            <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-200/80 space-y-2.5">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Informasi Pemohon (Otomatis)</span>
                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-primary text-[10px] font-semibold border border-blue-200">
                  Pengajuan Mandiri Siswa
                </span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                <div>
                  <span class="text-[10px] text-gray-400 block">Nama Lengkap</span>
                  <span class="font-bold text-gray-900">{{ authStore.user?.name || 'Siswa' }}</span>
                </div>
                <div>
                  <span class="text-[10px] text-gray-400 block">NIS / NISN</span>
                  <span class="font-bold text-gray-900 font-mono">{{ authStore.user?.nip_nis || '-' }}</span>
                </div>
                <div>
                  <span class="text-[10px] text-gray-400 block">Kelas</span>
                  <span class="font-bold text-gray-900">{{ authStore.user?.school_class?.name || '-' }}</span>
                </div>
              </div>
            </div>

            <!-- Jenis Izin -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Jenis Halangan / Izin *
              </label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  type="button"
                  @click="form.type = 'SAKIT'"
                  :class="[
                    'py-2.5 rounded-xl text-xs font-semibold border transition text-center cursor-pointer',
                    form.type === 'SAKIT'
                      ? 'bg-blue-50 border-primary text-primary font-bold shadow-2xs'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                  ]"
                >
                  Sakit
                </button>
                <button
                  type="button"
                  @click="form.type = 'IZIN'"
                  :class="[
                    'py-2.5 rounded-xl text-xs font-semibold border transition text-center cursor-pointer',
                    form.type === 'IZIN'
                      ? 'bg-blue-50 border-primary text-primary font-bold shadow-2xs'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                  ]"
                >
                  Izin Keperluan
                </button>
                <button
                  type="button"
                  @click="form.type = 'DISPENSASI'"
                  :class="[
                    'py-2.5 rounded-xl text-xs font-semibold border transition text-center cursor-pointer',
                    form.type === 'DISPENSASI'
                      ? 'bg-blue-50 border-primary text-primary font-bold shadow-2xs'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                  ]"
                >
                  Dispensasi
                </button>
              </div>
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Tanggal Mulai *
                </label>
                <input
                  v-model="form.start_date"
                  type="date"
                  class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Tanggal Selesai *
                </label>
                <input
                  v-model="form.end_date"
                  type="date"
                  class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white"
                  required
                />
              </div>
            </div>

            <!-- Peringatan Jika Tanggal Sudah Memiliki Izin Disetujui / Pending -->
            <div
              v-if="conflictingLeave"
              class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1"
            >
              <div class="flex items-center gap-1.5 font-bold text-amber-800">
                <AlertCircleIcon class="w-4 h-4 shrink-0 text-amber-600" />
                <span>Tanggal Sudah Terdaftar ({{ conflictingLeave.status === 'APPROVED' ? 'Telah Disetujui' : 'Menunggu Konfirmasi' }})</span>
              </div>
              <p class="leading-relaxed text-amber-800">
                Anda sudah memiliki permohonan surat izin untuk tanggal <strong>{{ conflictingLeave.start_date }} s/d {{ conflictingLeave.end_date }}</strong>. Anda tidak dapat mengirimkan surat izin ganda pada rentang waktu yang sama.
              </p>
            </div>

            <!-- Ditujukan Kepada -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Ditujukan Kepada *
              </label>
              <select
                v-model="form.recipient"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
              >
                <option value="Wali Kelas">Wali Kelas</option>
                <option value="Wali Kelas & Guru Pengajar">Wali Kelas & Guru Pengajar</option>
              </select>
            </div>

            <!-- Alasan Izin -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Alasan Tidak Hadir *
              </label>
              <textarea
                v-model="form.reason"
                rows="3"
                placeholder="Contoh: Mengalami gejala demam tinggi dan flu sejak semalam, disarankan istirahat oleh dokter..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none"
                required
              ></textarea>
            </div>

            <!-- Unggah Berkas Bukti Fisik / Surat Dokter (Opsional) -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Unggah Lampiran Pendukung / Surat Dokter (Opsional)
              </label>
              <div class="flex items-center gap-2">
                <input
                  ref="fileInputRef"
                  type="file"
                  accept=".pdf,.png,.jpg,.jpeg,image/*"
                  @change="handleFileChange"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="fileInputRef?.click()"
                  class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 transition cursor-pointer"
                >
                  <UploadCloudIcon class="w-4 h-4 text-primary" />
                  <span>{{ selectedFile ? selectedFile.name : 'Pilih Berkas (PDF, JPG, PNG)' }}</span>
                </button>
                <button
                  v-if="selectedFile"
                  type="button"
                  @click="selectedFile = null"
                  class="text-xs text-rose-600 hover:underline font-semibold cursor-pointer"
                >
                  Batal
                </button>
              </div>
              <p class="text-[11px] text-gray-400 mt-1">Maksimal 5 MB. Bukti surat keterangan dokter atau surat izin orang tua/kegiatan.</p>
            </div>

            <button
              type="submit"
              :disabled="submitting || !!conflictingLeave"
              class="w-full py-3 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white font-semibold text-sm transition shadow-xs flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
              <SendIcon class="w-4 h-4" />
              <span>
                {{
                  conflictingLeave
                    ? (conflictingLeave.status === 'APPROVED' ? 'Izin Sudah Disetujui (Terkunci)' : 'Izin Sedang Diproses (Pending)')
                    : (submitting ? 'Mengirimkan ke Wali Kelas...' : 'Kirim Permohonan ke Wali Kelas')
                }}
              </span>
            </button>
          </form>
        </div>

        <!-- Riwayat Surat Izin (Kanan / Full untuk Orang Tua) -->
        <div
          :class="[
            authStore.user?.role === 'ORANG_TUA' ? 'lg:col-span-12' : 'lg:col-span-6',
            'bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4'
          ]"
        >
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <h2 class="text-base font-bold text-gray-800">Riwayat Pengajuan Izin</h2>
              <p class="text-xs text-gray-400 mt-0.5">Status verifikasi permohonan izin oleh Wali Kelas</p>
            </div>
            <button
              @click="fetchLeaves"
              class="text-xs text-primary hover:underline font-semibold flex items-center gap-1 cursor-pointer"
            >
              <RefreshCwIcon class="w-3.5 h-3.5" /> Segarkan
            </button>
          </div>

          <div v-if="loadingLeaves" class="py-12 text-center text-gray-400 text-sm">
            Memuat riwayat permohonan...
          </div>

          <div v-else-if="leaveList.length === 0" class="py-12 text-center text-gray-400 text-sm">
            Belum ada permohonan surat izin yang diajukan.
          </div>

          <div v-else class="space-y-3.5 max-h-[580px] overflow-y-auto pr-1">
            <div
              v-for="lv in leaveList"
              :key="lv.id"
              class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 space-y-2.5 transition hover:border-gray-200"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-900">{{ lv.student_name }}</span>
                    <span class="text-[11px] text-gray-400 font-mono">({{ lv.nis }})</span>
                  </div>
                  <div class="text-[11px] text-gray-500 mt-0.5">
                    Tipe: <span class="font-semibold text-gray-700">{{ lv.type }}</span> • {{ lv.start_date }} s/d {{ lv.end_date }} • Tujuan: <span class="font-semibold text-gray-700">{{ lv.recipient || 'Wali Kelas' }}</span>
                  </div>
                </div>
                <StatusBadge :status="lv.status" context="pembimbing" />
              </div>

              <p class="text-xs text-gray-600 bg-white p-2.5 rounded-lg border border-gray-100">
                <span class="font-semibold text-gray-700">Alasan:</span> {{ lv.reason }}
              </p>

              <div v-if="lv.attachment || lv.attachment_url" class="pt-1">
                <a
                  :href="lv.attachment_url || `/api/leave-requests/file/${lv.attachment}`"
                  target="_blank"
                  download
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold border border-blue-200 transition"
                >
                  <FileTextIcon class="w-3.5 h-3.5 text-blue-600" />
                  <span class="truncate max-w-[200px]">{{ lv.attachment || 'Unduh Berkas Lampiran' }}</span>
                  <DownloadIcon class="w-3.5 h-3.5 text-blue-500" />
                </a>
              </div>

              <div
                v-if="lv.status === 'REJECTED'"
                class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1"
              >
                <div class="flex items-center gap-1.5 font-bold text-rose-700">
                  <AlertCircleIcon class="w-4 h-4 shrink-0 text-rose-600" />
                  <span>Permohonan Izin Ditolak oleh Wali Kelas</span>
                </div>
                <p class="leading-relaxed">
                  <strong>Alasan Penolakan:</strong> "{{ lv.review_note || 'Tidak memenuhi kriteria izin / bukti fisik tidak sah.' }}"
                </p>
                <p class="text-[11px] text-rose-600 font-medium pt-0.5">
                  Catatan: Anda tercatat tidak memiliki izin resmi untuk tanggal tersebut. Silakan hadir di sekolah atau ajukan permohonan ulang dengan bukti yang sah.
                </p>
              </div>
              <div v-else-if="lv.review_note" class="text-[11px] text-gray-600 italic">
                Catatan Wali Kelas: "{{ lv.review_note }}"
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useToastStore } from '~/stores/toast'
import {
  Mail as MailIcon,
  CheckCircle2 as CheckCircle2Icon,
  AlertCircle as AlertCircleIcon,
  Send as SendIcon,
  RefreshCw as RefreshCwIcon,
  UploadCloud as UploadCloudIcon,
  FileText as FileTextIcon,
  Download as DownloadIcon,
  X,
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToastStore()

const fileInputRef = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

const leaveList = ref<any[]>([])
const loadingLeaves = ref(false)
const submitting = ref(false)
const successMsg = ref('')

const form = ref({
  type: 'SAKIT',
  start_date: new Date().toISOString().slice(0, 10),
  end_date: new Date().toISOString().slice(0, 10),
  recipient: 'Wali Kelas',
  reason: ''
})

const todayStr = computed(() => new Date().toISOString().slice(0, 10))

// Cek apakah siswa sudah memiliki surat izin berstatus APPROVED yang aktif hari ini
const activeApprovedLeaveToday = computed(() => {
  return leaveList.value.find(lv =>
    lv.status === 'APPROVED' &&
    lv.start_date <= todayStr.value &&
    lv.end_date >= todayStr.value
  )
})

// Cek apakah rentang tanggal yang dipilih siswa bertabrakan dengan izin APPROVED atau PENDING
const conflictingLeave = computed(() => {
  if (!form.value.start_date || !form.value.end_date) return null
  const s1 = form.value.start_date
  const e1 = form.value.end_date
  return leaveList.value.find(lv => {
    if (lv.status !== 'APPROVED' && lv.status !== 'PENDING') return false
    const s2 = lv.start_date
    const e2 = lv.end_date
    return (s1 <= e2 && e1 >= s2)
  })
})

const fetchLeaves = async () => {
  loadingLeaves.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/leave-requests`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    leaveList.value = res || []
  } catch (err: any) {
    console.error('Error fetching leaves:', err)
  } finally {
    loadingLeaves.value = false
  }
}

const handleSubmit = async () => {
  if (conflictingLeave.value) {
    const statusText = conflictingLeave.value.status === 'APPROVED' ? 'DISETUJUI' : 'MENUNGGU VERIFIKASI (PENDING)'
    toast.error(`Anda sudah memiliki surat izin berstatus ${statusText} pada tanggal tersebut.`)
    return
  }

  submitting.value = true
  successMsg.value = ''
  try {
    const studentIdNum = Number(authStore.user?.id || 1)
    if (selectedFile.value) {
      const formData = new FormData()
      formData.append('student_id', String(studentIdNum))
      formData.append('type', form.value.type)
      formData.append('start_date', form.value.start_date)
      formData.append('end_date', form.value.end_date)
      formData.append('recipient', form.value.recipient)
      formData.append('reason', form.value.reason)
      formData.append('file', selectedFile.value)

      await $fetch(`${config.public.apiBase}/leave-requests`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` },
        body: formData
      })
    } else {
      await $fetch(`${config.public.apiBase}/leave-requests`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` },
        body: {
          ...form.value,
          student_id: studentIdNum
        }
      })
    }
    successMsg.value = 'Permohonan surat izin berhasil dikirimkan langsung ke Wali Kelas!'
    form.value.reason = ''
    selectedFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    await fetchLeaves()
  } catch (err: any) {
    toast.error(err?.data?.message || 'Gagal mengajukan surat izin')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchLeaves()
})
</script>
