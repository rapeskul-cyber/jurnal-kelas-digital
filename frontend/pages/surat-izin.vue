<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Pengajuan Surat Izin Belajar"
      subtitle="Ajukan permohonan absen kelas resmi secara daring"
      :show-back="true"
    />

    <main class="px-4 pb-6 space-y-5">
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
              v-model="form.student_id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
              required
            >
              <option value="" disabled>-- Pilih Siswa yang Berhalangan --</option>
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

          <!-- Tanda Tangan Canvas -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                Tanda Tangan Pemohon
              </label>
              <button
                type="button"
                @click="clearCanvas"
                class="text-[11px] text-brand-red hover:underline font-semibold"
              >
                Hapus Tanda Tangan
              </button>
            </div>
            <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50/50">
              <canvas
                ref="canvasRef"
                width="400"
                height="120"
                class="w-full h-28 touch-none cursor-crosshair bg-white"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawingTouch"
                @touchmove="drawTouch"
                @touchend="stopDrawing"
              ></canvas>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Goreskan tanda tangan digital langsung pada kotak di atas.</p>
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
              <StatusBadge :status="lv.status" />
            </div>

            <p class="text-xs text-gray-600 bg-white p-2.5 rounded-lg border border-gray-100">
              <span class="font-semibold text-gray-700">Alasan:</span> {{ lv.reason }}
            </p>

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
  RefreshCw as RefreshCwIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let isDrawing = false
let hasSignature = false

const students = ref<any[]>([])
const leaveList = ref<any[]>([])
const loadingLeaves = ref(false)
const submitting = ref(false)
const successMsg = ref('')

const form = ref({
  student_id: '',
  type: 'SAKIT',
  start_date: new Date().toISOString().slice(0, 10),
  end_date: new Date().toISOString().slice(0, 10),
  reason: '',
  signature: ''
})

const initCanvas = () => {
  const canvas = canvasRef.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.strokeStyle = '#1F2937'
  ctx.lineWidth = 2
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
}

const startDrawing = (e: MouseEvent) => {
  isDrawing = true
  hasSignature = true
  const canvas = canvasRef.value
  if (!canvas) return
  const rect = canvas.getBoundingClientRect()
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.beginPath()
  ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top)
}

const draw = (e: MouseEvent) => {
  if (!isDrawing) return
  const canvas = canvasRef.value
  if (!canvas) return
  const rect = canvas.getBoundingClientRect()
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top)
  ctx.stroke()
}

const stopDrawing = () => {
  isDrawing = false
  if (canvasRef.value) {
    form.value.signature = canvasRef.value.toDataURL()
  }
}

const startDrawingTouch = (e: TouchEvent) => {
  if (e.touches.length === 0) return
  const touch = e.touches[0]
  isDrawing = true
  hasSignature = true
  const canvas = canvasRef.value
  if (!canvas) return
  const rect = canvas.getBoundingClientRect()
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.beginPath()
  ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top)
}

const drawTouch = (e: TouchEvent) => {
  if (!isDrawing || e.touches.length === 0) return
  const touch = e.touches[0]
  const canvas = canvasRef.value
  if (!canvas) return
  const rect = canvas.getBoundingClientRect()
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top)
  ctx.stroke()
}

const clearCanvas = () => {
  const canvas = canvasRef.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  form.value.signature = ''
  hasSignature = false
}

const fetchStudents = async () => {
  try {
    const res: any = await $fetch(`${config.public.apiBase}/attendance/today`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    students.value = (res.students || []).map((s: any) => ({
      id: s.id,
      name: s.name,
      nip_nis: s.nis || '10293'
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
  if (!form.value.student_id) {
    alert('Pilih siswa terlebih dahulu')
    return
  }
  submitting.value = true
  successMsg.value = ''
  try {
    await $fetch(`${config.public.apiBase}/leave-requests`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: form.value
    })
    successMsg.value = 'Permohonan surat izin berhasil dikirimkan ke Guru Pembimbing!'
    form.value.student_id = ''
    form.value.reason = ''
    clearCanvas()
    await fetchLeaves()
  } catch (err: any) {
    alert(err?.data?.message || 'Gagal mengajukan surat izin')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  initCanvas()
  fetchStudents()
  fetchLeaves()
})
</script>
