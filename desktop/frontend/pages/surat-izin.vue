<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Pengajuan Surat Izin Belajar"
      subtitle="Ajukan permohonan absen kelas resmi secara daring"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Alert / Notif -->
      <div v-if="successMsg" class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm font-medium">
          <CheckCircle2Icon class="w-5 h-5 text-green-600" />
          {{ successMsg }}
        </div>
        <button @click="successMsg = ''" class="text-green-500 hover:text-green-700 text-sm">✕</button>
      </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Form Input (Kiri) -->
      <div class="lg:col-span-6 bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
        <div class="border-b border-gray-100 pb-3">
          <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
            <MailIcon class="w-5 h-5 text-brand-red" />
            Detail Permohonan Izin
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">Lengkapi formulir permohonan dengan tanda tangan valid</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Pilih Siswa -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
              Pilih Siswa
            </label>
            <select
              v-model.number="form.student_id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
              required
            >
              <option :value="''" disabled>-- Pilih Siswa yang Berhalangan --</option>
              <option v-for="std in students" :key="std.id" :value="std.id">
                {{ std.name }} (NIS: {{ std.nip_nis }})
              </option>
            </select>
          </div>

          <!-- Jenis Izin -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
              Jenis Permohonan
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                type="button"
                @click="form.type = 'SAKIT'"
                :class="[
                  'py-2 rounded-xl text-xs font-semibold border transition text-center',
                  form.type === 'SAKIT'
                    ? 'bg-orange-50 border-orange-500 text-orange-700 shadow-xs'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                ]"
              >
                Sakit
              </button>
              <button
                type="button"
                @click="form.type = 'IZIN'"
                :class="[
                  'py-2 rounded-xl text-xs font-semibold border transition text-center',
                  form.type === 'IZIN'
                    ? 'bg-blue-50 border-blue-500 text-blue-700 shadow-xs'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                ]"
              >
                Izin Keperluan
              </button>
              <button
                type="button"
                @click="form.type = 'DISPENSASI'"
                :class="[
                  'py-2 rounded-xl text-xs font-semibold border transition text-center',
                  form.type === 'DISPENSASI'
                    ? 'bg-purple-50 border-purple-500 text-purple-700 shadow-xs'
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
                Tanggal Mulai
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Tanggal Selesai
              </label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
                required
              />
            </div>
          </div>

          <!-- Alasan Izin -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
              Alasan Tidak Masuk
            </label>
            <textarea
              v-model="form.reason"
              rows="3"
              placeholder="Contoh: Mengalami demam tinggi dan flu, disarankan istirahat oleh dokter..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red transition resize-none"
              required
            ></textarea>
          </div>

          <!-- Unggah Berkas Bukti Fisik / Surat Dokter (Opsional) -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
              Unggah Berkas Bukti Fisik (Opsional)
            </label>
            <div class="flex items-center gap-2">
              <input
                ref="fileInputRef"
                type="file"
                accept=".pdf,.png,.jpg,.jpeg"
                @change="handleFileChange"
                class="hidden"
              />
              <button
                type="button"
                @click="fileInputRef?.click()"
                class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 transition"
              >
                <UploadCloudIcon class="w-4 h-4 text-brand-red" />
                <span>{{ selectedFile ? selectedFile.name : 'Pilih Berkas (PDF, JPG, PNG)' }}</span>
              </button>
              <button
                v-if="selectedFile"
                type="button"
                @click="selectedFile = null"
                class="text-xs text-red-600 hover:underline font-semibold"
              >
                Batal
              </button>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Maksimal 5 MB. Bukti surat keterangan dokter atau surat dinas/kegiatan.</p>
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full py-2.5 px-4 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white font-medium text-sm transition shadow-sm hover:shadow flex items-center justify-center gap-2 disabled:opacity-50"
          >
            <SendIcon class="w-4 h-4" />
            <span>{{ submitting ? 'Mengirimkan...' : 'Kirim Permohonan Surat Izin' }}</span>
          </button>
        </form>
      </div>

      <!-- Riwayat Surat Izin (Kanan) -->
      <div class="lg:col-span-6 bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h2 class="text-base font-bold text-gray-800">Riwayat Pengajuan Izin</h2>
            <p class="text-xs text-gray-400 mt-0.5">Status persetujuan permohonan izin kelas</p>
          </div>
          <button
            @click="fetchLeaves"
            class="text-xs text-brand-red hover:underline font-semibold flex items-center gap-1"
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
                  Tipe: <span class="font-semibold text-gray-700">{{ lv.type }}</span> • {{ lv.start_date }} s/d {{ lv.end_date }}
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

            <div v-if="lv.review_note" class="text-[11px] text-gray-600 italic">
              Catatan Pembimbing: "{{ lv.review_note }}"
            </div>
          </div>
        </div>
      </div>
    </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Mail as MailIcon,
  CheckCircle2 as CheckCircle2Icon,
  Send as SendIcon,
  RefreshCw as RefreshCwIcon,
  UploadCloud as UploadCloudIcon,
  FileText as FileTextIcon,
  Download as DownloadIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const fileInputRef = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

const students = ref<any[]>([])
const leaveList = ref<any[]>([])
const loadingLeaves = ref(false)
const submitting = ref(false)
const successMsg = ref('')

const route = useRoute()

const form = ref({
  student_id: route.query.student_id ? String(route.query.student_id) : '',
  type: route.query.type ? String(route.query.type) : 'SAKIT',
  start_date: new Date().toISOString().slice(0, 10),
  end_date: new Date().toISOString().slice(0, 10),
  reason: ''
})

const fetchStudents = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/attendance/today`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    students.value = (res.students || []).map((s: any) => ({
      id: s.student_id || s.id,
      name: s.name,
      nip_nis: s.nis || s.nip_nis || '-'
    }))
  } catch (err: any) {
    console.error('Error fetching students:', err)
  }
}

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
  if (!form.value.student_id || form.value.student_id === '') {
    alert('Pilih siswa terlebih dahulu')
    return
  }
  submitting.value = true
  successMsg.value = ''
  try {
    const studentIdNum = Number(form.value.student_id)
    if (selectedFile.value) {
      const formData = new FormData()
      formData.append('student_id', String(studentIdNum))
      formData.append('type', form.value.type)
      formData.append('start_date', form.value.start_date)
      formData.append('end_date', form.value.end_date)
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
    successMsg.value = 'Permohonan surat izin berhasil dikirimkan ke Guru Pembimbing!'
    form.value.student_id = ''
    form.value.reason = ''
    selectedFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    await fetchLeaves()
  } catch (err: any) {
    alert(err?.data?.message || 'Gagal mengajukan surat izin')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchStudents()
  fetchLeaves()
})
</script>
