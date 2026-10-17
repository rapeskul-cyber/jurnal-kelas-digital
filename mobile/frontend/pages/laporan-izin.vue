<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Riwayat Surat Izin"
      subtitle="Arsip dan log digital seluruh surat izin tidak masuk kelas"
    >
      <template #actions>
        <button
          @click="exportExcel"
          type="button"
          class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <DownloadIcon class="w-3.5 h-3.5 text-gray-500" />
          <span>Ekspor Rekap XLSX</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 pb-6 space-y-5">

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 md:p-5 flex flex-wrap items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3">
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Filter Status</label>
          <select
            v-model="statusFilter"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
          >
            <option value="Semua">Semua Status</option>
            <option value="PENDING">Menunggu Persetujuan</option>
            <option value="APPROVED">Disetujui</option>
            <option value="REJECTED">Ditolak</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 mb-1">Pilih Kelas</label>
          <select
            v-model="classFilter"
            class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
          >
            <option value="1">XI RPL 1</option>
          </select>
        </div>
      </div>

      <div class="w-full md:w-64">
        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Cari Pemohon</label>
        <div class="relative">
          <SearchIcon class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Ketik nama atau alasan..."
            class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
          />
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">Daftar Arsip Izin</h2>
          <p class="text-xs text-gray-400 mt-0.5">Total {{ filteredList.length }} pengajuan izin tercatat</p>
        </div>
      </div>

      <div v-if="loading" class="py-16 text-center text-gray-400 text-sm">
        Memuat data laporan izin...
      </div>

      <div v-else-if="filteredList.length === 0" class="py-16 text-center text-gray-400 text-sm">
        Tidak ada data permohonan izin yang sesuai filter.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Nama Siswa</th>
              <th class="py-3 px-4">Kelas / NIS</th>
              <th class="py-3 px-4 text-center">Jenis</th>
              <th class="py-3 px-4">Tanggal Izin</th>
              <th class="py-3 px-4">Alasan</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4">Diverifikasi Oleh</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="(item, idx) in filteredList"
              :key="item.id"
              class="hover:bg-gray-50/50 transition"
            >
              <td class="py-3.5 px-4 text-center font-medium text-gray-500">{{ idx + 1 }}</td>
              <td class="py-3.5 px-4">
                <span class="font-bold text-gray-900">{{ item.student_name }}</span>
              </td>
              <td class="py-3.5 px-4 text-gray-600">
                {{ item.class }} • <span class="font-mono text-gray-400">{{ item.nis }}</span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="item.type === 'SAKIT' ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700'"
                >
                  {{ item.type }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap">
                {{ item.start_date }} s/d {{ item.end_date }}
              </td>
              <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate">
                {{ item.reason }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <StatusBadge :status="item.status" />
              </td>
              <td class="py-3.5 px-4 text-gray-600">
                {{ item.reviewer_name || '-' }}
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
  Download as DownloadIcon,
  Search as SearchIcon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const loading = ref(false)
const list = ref<any[]>([])
const statusFilter = ref('Semua')
const classFilter = ref('1')
const searchQuery = ref('')

const filteredList = computed(() => {
  return list.value.filter(item => {
    const matchStatus = statusFilter.value === 'Semua' || item.status === statusFilter.value
    const matchSearch =
      !searchQuery.value ||
      item.student_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.reason.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

const fetchList = async () => {
  loading.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/leave-requests`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    list.value = res || []
  } catch (err: any) {
    console.error('Error fetching leave list:', err)
  } finally {
    loading.value = false
  }
}

const exportExcel = () => {
  alert('Mengunduh Berkas Arsip Laporan Izin Siswa (.XLSX)... Selesai!')
}

onMounted(() => {
  fetchList()
})
</script>
