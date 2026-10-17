<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Persetujuan Surat Izin Belajar"
      subtitle="Daftar permohonan absen siswa yang membutuhkan verifikasi Anda"
    >
      <template #actions>
        <button
          @click="fetchLeaves"
          type="button"
          class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 hover:bg-gray-50 font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <RefreshCwIcon class="w-3.5 h-3.5 text-gray-500" />
          <span>Segarkan Data</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 pb-6 space-y-5">

    <!-- Alert / Toast -->
    <div v-if="successMsg" class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-between">
      <div class="flex items-center gap-2 text-sm font-medium">
        <CheckCircle2Icon class="w-5 h-5 text-green-600" />
        {{ successMsg }}
      </div>
      <button @click="successMsg = ''" class="text-green-500 hover:text-green-700 text-sm">✕</button>
    </div>

    <!-- Main Content: Split View (Mockup 27) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Kolom Kiri: Daftar Pengajuan -->
      <div class="lg:col-span-5 space-y-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-gray-800">Daftar Permohonan</h2>
            <span class="text-xs bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded-full border border-amber-200">
              {{ pendingLeaves.length }} Menunggu
            </span>
          </div>

          <div v-if="loading" class="py-12 text-center text-gray-400 text-xs">
            Memuat daftar permohonan...
          </div>

          <div v-else-if="allLeaves.length === 0" class="py-12 text-center text-gray-400 text-xs">
            Tidak ada permohonan surat izin.
          </div>

          <div v-else class="space-y-2.5 max-h-[620px] overflow-y-auto pr-1">
            <div
              v-for="item in allLeaves"
              :key="item.id"
              @click="selectedItem = item"
              class="p-3.5 rounded-xl border cursor-pointer transition text-left space-y-2"
              :class="[
                selectedItem?.id === item.id
                  ? 'border-brand-red bg-red-50/20 shadow-xs'
                  : 'border-gray-100 bg-white hover:border-gray-200 hover:bg-gray-50/50'
              ]"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <h3 class="text-xs font-bold text-gray-900">{{ item.student_name }}</h3>
                  <p class="text-[11px] text-gray-400 mt-0.5">{{ item.class }} • NIS: {{ item.nis }}</p>
                </div>
                <StatusBadge :status="item.status" />
              </div>

              <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1 border-t border-gray-100/60">
                <span class="font-semibold text-gray-700 uppercase">{{ item.type }}</span>
                <span>{{ item.start_date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Detail & Aksi Persetujuan -->
      <div class="lg:col-span-7">
        <div v-if="selectedItem" class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-5">
          <!-- Header Detail -->
          <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-brand-red bg-red-50 px-2 py-0.5 rounded">
                Detail Permohonan Izin
              </span>
              <h2 class="text-lg font-bold text-gray-900 mt-1">{{ selectedItem.student_name }}</h2>
              <p class="text-xs text-gray-400">{{ selectedItem.class }} — NIS: {{ selectedItem.nis }}</p>
            </div>
            <StatusBadge :status="selectedItem.status" />
          </div>

          <!-- Rincian Data -->
          <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50/70 p-4 rounded-xl border border-gray-100">
            <div>
              <span class="text-gray-400 font-medium">Jenis Izin:</span>
              <p class="font-bold text-gray-800 mt-0.5 uppercase">{{ selectedItem.type }}</p>
            </div>
            <div>
              <span class="text-gray-400 font-medium">Rentang Waktu:</span>
              <p class="font-bold text-gray-800 mt-0.5">{{ selectedItem.start_date }} s/d {{ selectedItem.end_date }}</p>
            </div>
            <div>
              <span class="text-gray-400 font-medium">Tujuan / Ditujukan Kepada:</span>
              <p class="font-semibold text-gray-700 mt-0.5">{{ selectedItem.recipient || 'Wali Kelas / Pembimbing' }}</p>
            </div>
            <div>
              <span class="text-gray-400 font-medium">Status Verifikasi:</span>
              <p class="font-semibold text-gray-700 mt-0.5">{{ selectedItem.status }}</p>
            </div>
          </div>

          <!-- Alasan / Penjelasan -->
          <div>
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-1.5">Alasan Permohonan</h4>
            <div class="p-3.5 rounded-xl bg-white border border-gray-200 text-xs text-gray-700 leading-relaxed whitespace-pre-line">
              {{ selectedItem.reason }}
            </div>
          </div>

          <!-- Lampiran / Tanda Tangan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
              <span class="text-[11px] font-semibold text-gray-500 block mb-1">Lampiran Dokumen</span>
              <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 bg-white p-2.5 rounded-lg border border-gray-200">
                <FileTextIcon class="w-4 h-4 text-blue-500" />
                <span>{{ selectedItem.attachment || 'surat_keterangan.pdf' }}</span>
              </div>
            </div>

            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
              <span class="text-[11px] font-semibold text-gray-500 block mb-1">Tanda Tangan Pemohon</span>
              <div class="h-14 bg-white rounded-lg border border-gray-200 flex items-center justify-center p-1">
                <span class="text-xs italic text-gray-400">Tanda tangan digital terverifikasi</span>
              </div>
            </div>
          </div>

          <!-- Input Catatan Reviewer (jika masih PENDING) -->
          <div v-if="selectedItem.status === 'PENDING'" class="space-y-3 pt-2 border-t border-gray-100">
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Catatan Pembimbing <span class="text-gray-400 font-normal">(Opsional)</span>
              </label>
              <textarea
                v-model="reviewNote"
                rows="2"
                placeholder="Tuliskan catatan verifikasi atau alasan bila menolak..."
                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red resize-none"
              ></textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <button
                @click="submitReview('REJECTED')"
                :disabled="processingReview"
                class="px-4 py-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold transition flex items-center gap-1.5 disabled:opacity-50"
              >
                <XIcon class="w-4 h-4" />
                <span>Tolak Izin</span>
              </button>
              <button
                @click="submitReview('APPROVED')"
                :disabled="processingReview"
                class="px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50"
              >
                <CheckIcon class="w-4 h-4" />
                <span>Setujui Permohonan</span>
              </button>
            </div>
          </div>

          <div v-else class="p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600">
            <span class="font-semibold text-gray-800">Catatan Reviewer:</span>
            {{ selectedItem.review_note || 'Telah diverifikasi sesuai prosedur sekolah.' }}
          </div>
        </div>

        <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-card p-12 text-center text-gray-400 text-sm">
          Pilih salah satu permohonan izin di daftar sebelah kiri untuk melihat detailnya.
        </div>
      </div>
    </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  RefreshCw as RefreshCwIcon,
  CheckCircle2 as CheckCircle2Icon,
  FileText as FileTextIcon,
  Check as CheckIcon,
  X as XIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const loading = ref(false)
const processingReview = ref(false)
const allLeaves = ref<any[]>([])
const selectedItem = ref<any | null>(null)
const reviewNote = ref('')
const successMsg = ref('')

const pendingLeaves = computed(() => {
  return allLeaves.value.filter(l => l.status === 'PENDING')
})

const fetchLeaves = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/leave-requests`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    allLeaves.value = res || []
    if (allLeaves.value.length > 0 && !selectedItem.value) {
      selectedItem.value = allLeaves.value[0]
    } else if (selectedItem.value) {
      selectedItem.value = allLeaves.value.find(l => l.id === selectedItem.value.id) || allLeaves.value[0]
    }
  } catch (err: any) {
    console.error('Error fetching leave requests:', err)
  } finally {
    loading.value = false
  }
}

const submitReview = async (status: 'APPROVED' | 'REJECTED') => {
  if (!selectedItem.value) return
  processingReview.value = true
  successMsg.value = ''
  try {
    await $fetch(`${config.public.apiBase}/leave-requests/${selectedItem.value.id}/review`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        status,
        review_note: reviewNote.value
      }
    })
    successMsg.value = `Permohonan izin berhasil di-${status === 'APPROVED' ? 'setujui' : 'tolak'}!`
    reviewNote.value = ''
    await fetchLeaves()
  } catch (err: any) {
    alert(err?.data?.message || 'Gagal memproses permohonan izin')
  } finally {
    processingReview.value = false
  }
}

onMounted(() => {
  fetchLeaves()
})
</script>
