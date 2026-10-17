<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="dashboardTitle"
      :subtitle="dashboardSubtitle"
    />

    <main class="px-8 pb-8 space-y-6">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-pulse">
        <div v-for="i in 4" :key="i" class="h-28 bg-gray-200 rounded-2xl"></div>
      </div>

      <!-- ============================================== -->
      <!-- ROLE: SISWA (Mockup 13)                        -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SISWA'">
        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Hadir Bulan Ini" :value="data?.stats?.hadir_hari ?? '0 Hari'" :subtext="data?.stats?.hadir_percent ?? '0% Kehadiran'" color="green" />
          <StatCard title="Total Sakit" :value="data?.stats?.sakit_hari ?? '0 Hari'" subtext="Dilengkapi Surat Dokter" color="orange" />
          <StatCard title="Total Izin Disetujui" :value="data?.stats?.izin_hari ?? '0 Hari'" subtext="Sesuai Permohonan Online" color="blue" />
          <StatCard title="Alpha / Tanpa Kabar" :value="data?.stats?.alpha_hari ?? '0 Hari'" subtext="Catatan Presensi Riil" color="red" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Riwayat Kehadiran Terakhir -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-bold text-gray-900">Riwayat Kehadiran Terakhir</h3>
              <NuxtLink to="/riwayat-kehadiran" class="text-xs text-primary font-semibold hover:underline">
                Lihat Semua
              </NuxtLink>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Hari / Tanggal</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Keterangan / Alasan</th>
                    <th class="pb-3 font-semibold">Jam Sekolah</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="rec in data?.recent_attendance || []" :key="rec.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-medium text-gray-900">{{ rec.date }}</td>
                    <td class="py-3"><StatusBadge :status="rec.status" /></td>
                    <td class="py-3 text-gray-500">{{ rec.note }}</td>
                    <td class="py-3 text-gray-400">{{ rec.school_hours }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Jurnal Guru / Materi Pembelajaran -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-bold text-gray-900">Jurnal Guru / Materi Pembelajaran</h3>
              <NuxtLink to="/jurnal-pelajaran" class="text-xs text-primary font-semibold hover:underline">
                Lihat Semua
              </NuxtLink>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Tanggal</th>
                    <th class="pb-3 font-semibold">Mata Pelajaran</th>
                    <th class="pb-3 font-semibold">Judul Materi</th>
                    <th class="pb-3 font-semibold text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="mat in data?.recent_materials || []" :key="mat.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-medium text-gray-900">{{ mat.date }}</td>
                    <td class="py-3 text-gray-700">{{ mat.subject }}</td>
                    <td class="py-3 text-gray-500 truncate max-w-[140px]">{{ mat.topic }}</td>
                    <td class="py-3 text-right">
                      <NuxtLink to="/jurnal-pelajaran" class="inline-flex px-2.5 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg text-[11px] font-semibold text-gray-700">
                        Unduh
                      </NuxtLink>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: SEKRETARIS (Mockup 16)                    -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SEKRETARIS'">
        <!-- 4 Stat Cards Hari Ini -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Hadir Hari Ini" :value="data?.stats?.hadir_count ?? '0 Siswa'" :subtext="data?.stats?.hadir_percent ?? '0% Tingkat Kehadiran'" color="green" />
          <StatCard title="Izin Keperluan" :value="data?.stats?.izin_count ?? '0 Siswa'" subtext="Wajib Verifikasi Surat" color="blue" />
          <StatCard title="Sakit (Disertai Surat)" :value="data?.stats?.sakit_count ?? '0 Siswa'" subtext="Dalam Konfirmasi Wali Kelas" color="orange" />
          <StatCard title="Alpha (Tanpa Keterangan)" :value="data?.stats?.alpha_count ?? '0 Siswa'" subtext="Tindakan Lebih Lanjut" color="red" />
        </div>

        <!-- Presensi Kelas Hari Ini Table -->
        <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <div>
              <h3 class="text-sm font-bold text-gray-900">Isi Presensi Kelas {{ data?.class_name || 'XI RPL 1' }} Hari ini</h3>
              <p class="text-xs text-gray-400 mt-0.5">Tanggal: Hari ini, status: {{ data?.session_status }}</p>
            </div>
            <div class="flex items-center gap-2">
              <NuxtLink
                to="/surat-izin"
                class="px-3 py-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-2xs"
              >
                <Mail class="w-3.5 h-3.5 text-primary" />
                <span class="hidden sm:inline">Surat Izin</span>
              </NuxtLink>
              <NuxtLink
                to="/jurnal-kelas"
                class="px-3 py-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-2xs"
              >
                <FileText class="w-3.5 h-3.5 text-primary" />
                <span class="hidden sm:inline">Isi Jurnal</span>
              </NuxtLink>
              <NuxtLink
                to="/presensi"
                class="px-3.5 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-2xs"
              >
                <ClipboardCheck class="w-4 h-4" />
                <span>Input Presensi</span>
              </NuxtLink>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                  <th class="pb-3 font-semibold">No</th>
                  <th class="pb-3 font-semibold">Nama Siswa</th>
                  <th class="pb-3 font-semibold">NIS</th>
                  <th class="pb-3 font-semibold">Status Presensi</th>
                  <th class="pb-3 font-semibold">Keterangan</th>
                  <th class="pb-3 font-semibold text-right">TTD Digital</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="item in data?.attendance_list || []" :key="item.student_id" class="hover:bg-gray-50/50">
                  <td class="py-3 text-gray-400">{{ item.no }}</td>
                  <td class="py-3 font-semibold text-gray-900">{{ item.name }}</td>
                  <td class="py-3 text-gray-500">{{ item.nis }}</td>
                  <td class="py-3"><StatusBadge :status="item.status" /></td>
                  <td class="py-3 text-gray-500">{{ item.note || '-' }}</td>
                  <td class="py-3 text-right">
                    <span v-if="item.verified" class="inline-flex items-center text-emerald-600 font-semibold gap-1 text-[11px]">
                      <CheckCircle2 class="w-3.5 h-3.5" /> Terverifikasi
                    </span>
                    <span v-else class="text-gray-400 text-[11px]">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: GURU PENGAJAR (Mockup 22)                 -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'GURU_PENGAJAR'">
        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Kelas Diampu" :value="data?.stats?.kelas_diampu ?? '0 Kelas'" subtext="Kompetensi Terdaftar" color="blue" />
          <StatCard title="Jam Mengajar Hari Ini" :value="data?.stats?.jam_mengajar ?? '0 Jam'" subtext="Pertemuan Terjadwal" color="blue" />
          <StatCard title="Jurnal Terisi" :value="data?.stats?.jurnal_terisi ?? '0 Pertemuan'" subtext="Jurnal KBM Dilengkapi" color="orange" />
          <StatCard title="Jurnal Belum Diisi" :value="data?.stats?.jurnal_belum_diisi ?? '0 Pertemuan'" subtext="Perlu Verifikasi/Pengisian" color="red" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Jadwal Mengajar Hari Ini (2 cols) -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-sm font-bold text-gray-900">Jadwal Mengajar Hari Ini</h3>
                <p class="text-xs text-gray-400">5 pertemuan • Total 5 jam mengajar</p>
              </div>
              <NuxtLink to="/jurnal-materi" class="px-3 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-semibold">
                + Isi Jurnal Baru
              </NuxtLink>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Jam</th>
                    <th class="pb-3 font-semibold">Mata Pelajaran</th>
                    <th class="pb-3 font-semibold">Kelas</th>
                    <th class="pb-3 font-semibold">Status Jurnal</th>
                    <th class="pb-3 font-semibold text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="sch in data?.schedules || []" :key="sch.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-medium text-gray-900">{{ sch.time }}</td>
                    <td class="py-3 font-semibold text-gray-800">{{ sch.subject }}</td>
                    <td class="py-3 text-gray-600">{{ sch.class }}</td>
                    <td class="py-3"><StatusBadge :status="sch.status" /></td>
                    <td class="py-3 text-right">
                      <NuxtLink to="/jurnal-materi" class="inline-flex px-3 py-1 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-[11px] font-semibold text-gray-700">
                        Isi Jurnal
                      </NuxtLink>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Ringkasan Presensi & Pengingat (1 col) -->
          <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
              <h3 class="text-sm font-bold text-gray-900 mb-3">Ringkasan Presensi Kelas</h3>
              <p class="text-xs text-gray-400 mb-4">Persentase kehadiran hari ini</p>
              <div class="space-y-3">
                <div v-for="cls in data?.attendance_summary || []" :key="cls.class">
                  <div class="flex justify-between text-xs font-medium text-gray-700 mb-1">
                    <span>{{ cls.class }}</span>
                    <span>{{ cls.present_ratio }} <span class="font-bold text-gray-900">{{ cls.percent }}%</span></span>
                  </div>
                  <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-2 rounded-full" :style="{ width: cls.percent + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
              <h3 class="text-sm font-bold text-gray-900 mb-3">Pengingat</h3>
              <ul class="space-y-2 text-xs text-gray-600">
                <li v-for="(rem, idx) in data?.reminders || []" :key="idx" class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                  <span>{{ rem }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: GURU PEMBIMBING (Mockup 26)               -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'GURU_PEMBIMBING'">
        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatCard title="Total Kelas Binaan" :value="data?.stats?.total_kelas ?? '0 Kelas'" subtext="Kelas Binaan Terdaftar" color="blue" />
          <StatCard title="Jurnal Terisi Hari Ini" :value="data?.stats?.jurnal_terisi ?? '0 Kelas'" subtext="Administrasi Terdata" color="green" />
          <StatCard title="Surat Izin Pending" :value="data?.stats?.surat_izin_pending ?? '0 Permohonan'" subtext="Butuh Persetujuan" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Pemantauan Real-time Jurnal Kelas (2 cols) -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Pemantauan Real-time Jurnal Kelas Binaan</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Kelas</th>
                    <th class="pb-3 font-semibold">Sekretaris</th>
                    <th class="pb-3 font-semibold">Status Jurnal</th>
                    <th class="pb-3 font-semibold text-right">Kehadiran</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="cls in data?.monitored_classes || []" :key="cls.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-bold text-gray-900">{{ cls.class }}</td>
                    <td class="py-3 text-gray-600">{{ cls.secretary }}</td>
                    <td class="py-3"><StatusBadge :status="cls.journal_status" /></td>
                    <td class="py-3 text-right font-semibold text-gray-800">{{ cls.attendance_percent }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Persetujuan Surat Izin Cards (1 col) -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-bold text-gray-900">Persetujuan Surat Izin</h3>
              <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[10px] font-bold">
                {{ data?.pending_leaves?.length || 0 }} Baru
              </span>
            </div>

            <div class="space-y-4">
              <div
                v-for="lv in data?.pending_leaves || []"
                :key="lv.id"
                class="border border-gray-150 rounded-xl p-4 space-y-3 bg-gray-50/30"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-xs font-bold text-gray-900">{{ lv.student_name }}</h4>
                    <p class="text-[10px] text-gray-400">{{ lv.class }}</p>
                  </div>
                  <StatusBadge :status="lv.type" />
                </div>
                <p class="text-xs text-gray-600 line-clamp-2">{{ lv.reason }}</p>
                <div class="flex items-center gap-2 pt-1">
                  <button
                    @click="handleQuickReview(lv.id, 'APPROVED')"
                    class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                  >
                    Setujui
                  </button>
                  <button
                    @click="handleQuickReview(lv.id, 'REJECTED')"
                    class="flex-1 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                  >
                    Tolak
                  </button>
                </div>
              </div>

              <div v-if="!data?.pending_leaves?.length" class="text-center py-6 text-gray-400 text-xs">
                Tidak ada permohonan surat izin yang pending.
              </div>
            </div>
          </div>
        </div>
      </template>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ClipboardCheck, CheckCircle2, Mail, FileText } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const data = ref<any>(null)
const isLoading = ref(true)

const dashboardTitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SISWA') return 'Dashboard Presensi Mandiri Siswa'
  if (role === 'SEKRETARIS') return 'Dashboard Presensi ' + (data.value?.class_name || 'Kelas XI RPL 1')
  if (role === 'GURU_PENGAJAR') return 'Dashboard Pengajar'
  if (role === 'GURU_PEMBIMBING') return authStore.user?.name || 'Siti Aminah, M.Pd'
  return 'Dashboard'
})

const dashboardSubtitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SISWA') return 'Ringkasan presensi mandiri dan pembelajaran siswa'
  if (role === 'SEKRETARIS') return 'Ringkasan presensi harian dan aktivitas kelas'
  if (role === 'GURU_PEMBIMBING') return 'Pengawasan presensi kelas binaan, jurnal, dan perizinan siswa'
  if (role === 'GURU_PENGAJAR') return 'Ringkasan aktivitas mengajar hari ini'
  return ''
})

async function loadDashboard() {
  if (!authStore.token) return
  isLoading.value = true
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/dashboard`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    data.value = res
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

async function handleQuickReview(id: number, status: 'APPROVED' | 'REJECTED') {
  try {
    await $fetch(`${authStore.apiBase}/leave-requests/${id}/review`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { status, review_note: status === 'APPROVED' ? 'Disetujui cepat' : 'Ditolak' }
    })
    loadDashboard()
  } catch (e) {
    alert('Gagal memperbarui status izin.')
  }
}

onMounted(() => {
  loadDashboard()
})
</script>
