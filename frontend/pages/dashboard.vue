<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="dashboardTitle"
      :subtitle="dashboardSubtitle"
    />

    <main class="px-4 sm:px-8 pb-8 space-y-5">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-pulse">
        <div v-for="i in 4" :key="i" class="h-28 bg-gray-200 rounded-2xl"></div>
      </div>

      <!-- ============================================== -->
      <!-- ROLE: SISWA (Presensi Mandiri & Jurnal Siswa)  -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SISWA'">
        <!-- Kartu Presensi Mandiri Siswa Hari Ini (Tanpa Perlu Diabsen Sekretaris Satu-Satu) -->
        <div class="bg-white rounded-2xl border border-blue-200 p-5 sm:p-6 shadow-2xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
          <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-primary border border-blue-200">
                Presensi Mandiri Siswa
              </span>
              <span class="text-xs text-gray-500 font-medium">
                {{ data?.today_attendance?.date_formatted || 'Hari Ini' }} • {{ authStore.user?.school_class?.name || '-' }}
              </span>
            </div>
            <h3 class="text-base font-bold text-gray-900">
              Status Kehadiran Anda Hari Ini:
              <span
                :class="[
                  'ml-1 px-2.5 py-0.5 rounded-lg text-xs font-bold',
                  data?.today_attendance?.status === 'HADIR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                  data?.today_attendance?.status === 'SAKIT' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                  data?.today_attendance?.status === 'IZIN' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                  'bg-gray-100 text-gray-700'
                ]"
              >
                {{ data?.today_attendance?.status === 'BELUM_PRESENSI' ? 'Belum Presensi' : (data?.today_attendance?.status || 'HADIR') }}
              </span>
            </h3>
            <p class="text-xs text-gray-500">
              {{ checkInMessage || data?.today_attendance?.note || 'Klik tombol Konfirmasi Hadir di samping untuk mencatat kehadiran mandiri hari ini.' }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <button
              type="button"
              @click="handleSelfCheckIn"
              :disabled="isCheckingIn"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ isCheckingIn ? 'Menyimpan...' : 'Konfirmasi Hadir Mandiri' }}</span>
            </button>

            <NuxtLink
              to="/surat-izin"
              class="px-3.5 py-2.5 rounded-xl bg-white border border-gray-200 hover:border-primary text-gray-700 hover:text-primary text-xs font-semibold transition flex items-center gap-1.5"
            >
              <Mail class="w-4 h-4 text-primary" />
              <span>Ajukan Surat Izin</span>
            </NuxtLink>

            <NuxtLink
              to="/jurnal-pelajaran"
              class="px-3.5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5"
            >
              <FileText class="w-4 h-4" />
              <span>Isi Jurnal Kegiatan</span>
            </NuxtLink>
          </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Hadir Bulan Ini" :value="data?.stats?.hadir_hari ?? '0 Hari'" :subtext="data?.stats?.hadir_percent ?? 'Bulan Ini'" color="green" />
          <StatCard title="Total Sakit" :value="data?.stats?.sakit_hari ?? '0 Hari'" subtext="Surat Terlampir" color="orange" />
          <StatCard title="Total Izin Disetujui" :value="data?.stats?.izin_hari ?? '0 Hari'" subtext="Disetujui Wali Kelas" color="blue" />
          <StatCard title="Alpha / Tanpa Kabar" :value="data?.stats?.alpha_hari ?? '0 Hari'" subtext="Tanpa Keterangan" color="red" />
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
                    <th class="pb-3 font-semibold">Keterangan / Waktu</th>
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

          <!-- Jurnal Kegiatan & Materi Pembelajaran -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-bold text-gray-900">Jurnal Kegiatan & Materi Pembelajaran</h3>
              <NuxtLink to="/jurnal-pelajaran" class="text-xs text-primary font-semibold hover:underline">
                Buka Jurnal Kegiatan →
              </NuxtLink>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Tanggal</th>
                    <th class="pb-3 font-semibold">Mata Pelajaran</th>
                    <th class="pb-3 font-semibold">Judul Materi / Kegiatan</th>
                    <th class="pb-3 font-semibold text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="mat in data?.recent_materials || []" :key="mat.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-medium text-gray-900">{{ mat.date }}</td>
                    <td class="py-3 text-gray-700">{{ mat.subject }}</td>
                    <td class="py-3 text-gray-500 truncate max-w-[160px]">{{ mat.topic }}</td>
                    <td class="py-3 text-right">
                      <NuxtLink to="/jurnal-pelajaran" class="inline-flex px-2.5 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg text-[11px] font-semibold text-gray-700">
                        Buka
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
      <!-- ROLE: ORANG TUA (Pantauan Kehadiran & Jurnal)  -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'ORANG_TUA'">
        <!-- Kartu Identitas Anak & Status Kehadiran Real-Time -->
        <div class="bg-white rounded-2xl border border-emerald-200 p-5 sm:p-6 shadow-2xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
          <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                Portal Pantauan Orang Tua / Wali Murid
              </span>
              <span class="text-xs text-gray-500 font-medium">
                {{ data?.child?.date_formatted || 'Hari Ini' }}
              </span>
            </div>
            <h3 class="text-base font-bold text-gray-900">
              Siswa: {{ data?.child?.name || 'Siswa' }}
              <span class="text-xs font-normal text-gray-500 ml-1">
                (NIS: {{ data?.child?.nis || '-' }} • {{ data?.child?.class_name || '-' }} • Wali Kelas: {{ data?.child?.wali_kelas || '-' }})
              </span>
            </h3>
            <p class="text-xs text-gray-600">
              Status Hari Ini:
              <span class="font-bold text-emerald-700">{{ data?.child?.today_status || 'HADIR' }}</span>
              — {{ data?.child?.today_note || 'Presensi Mandiri Terverifikasi' }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <NuxtLink
              to="/jurnal-pelajaran"
              class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition flex items-center gap-1.5"
            >
              <FileText class="w-4 h-4" />
              <span>Lihat Jurnal Kegiatan Anak</span>
            </NuxtLink>
            <NuxtLink
              to="/riwayat-kehadiran"
              class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 hover:border-primary text-gray-700 text-xs font-semibold transition flex items-center gap-1.5"
            >
              <ClipboardCheck class="w-4 h-4 text-primary" />
              <span>Detail Kehadiran</span>
            </NuxtLink>
          </div>
        </div>

        <!-- 4 Stat Cards Kehadiran Anak -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Hadir Bulan Ini" :value="data?.stats?.hadir_hari ?? '0 Hari'" :subtext="data?.stats?.hadir_percent ?? 'Bulan Ini'" color="green" />
          <StatCard title="Sakit" :value="data?.stats?.sakit_hari ?? '0 Hari'" subtext="Disertai Surat" color="orange" />
          <StatCard title="Izin Disetujui" :value="data?.stats?.izin_hari ?? '0 Hari'" subtext="Disetujui Wali Kelas" color="blue" />
          <StatCard title="Alpha" :value="data?.stats?.alpha_hari ?? '0 Hari'" subtext="Tanpa Keterangan" color="red" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Jurnal Kegiatan Belajar Anak di Sekolah -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-bold text-gray-900">Jurnal Kegiatan Belajar Anak di Sekolah</h3>
                <p class="text-xs text-gray-400">Laporan aktivitas pembelajaran harian siswa dan guru di kelas</p>
              </div>
              <NuxtLink to="/jurnal-pelajaran" class="text-xs text-primary font-semibold hover:underline">
                Lihat Semua
              </NuxtLink>
            </div>

            <div class="space-y-3">
              <div
                v-for="j in data?.recent_journals || []"
                :key="j.id"
                class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 space-y-1.5"
              >
                <div class="flex items-center justify-between text-xs">
                  <span class="font-bold text-primary">{{ j.subject }}</span>
                  <span class="text-[11px] text-gray-400">{{ j.date }}</span>
                </div>
                <p class="text-xs font-semibold text-gray-900">{{ j.activity }}</p>
                <p v-if="j.notes" class="text-[11px] text-gray-500">{{ j.notes }}</p>
                <div class="flex items-center justify-between pt-1 text-[10px] text-gray-400">
                  <span>Dilaporkan oleh: <strong class="text-gray-700">{{ j.student_author }}</strong></span>
                  <span>Guru: {{ j.teacher }}</span>
                </div>
              </div>
              <div v-if="!data?.recent_journals?.length" class="text-center py-8 text-xs text-gray-400">
                Belum ada jurnal kegiatan tercatat.
              </div>
            </div>
          </div>

          <!-- Riwayat Kehadiran & Surat Izin Anak -->
          <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-bold text-gray-900">Riwayat Kehadiran Harian Anak</h3>
                <p class="text-xs text-gray-400">Catatan jam presensi mandiri dan surat izin siswa</p>
              </div>
              <NuxtLink to="/surat-izin" class="text-xs text-primary font-semibold hover:underline">
                Riwayat Izin →
              </NuxtLink>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                    <th class="pb-3 font-semibold">Hari / Tanggal</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Waktu / Catatan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="rec in data?.recent_attendance || []" :key="rec.id" class="hover:bg-gray-50/50">
                    <td class="py-3 font-medium text-gray-900">{{ rec.date }}</td>
                    <td class="py-3"><StatusBadge :status="rec.status" /></td>
                    <td class="py-3 text-gray-500">{{ rec.note }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: SEKRETARIS (Rekap Kelas & Jurnal Kelas)  -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SEKRETARIS'">
        <!-- 4 Stat Cards Hari Ini -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Hadir" :value="data?.stats?.hadir_count ?? '0 Siswa'" :subtext="data?.stats?.hadir_percent ?? '0% Kehadiran'" color="green" />
          <StatCard title="Izin" :value="data?.stats?.izin_count ?? '0 Siswa'" subtext="Disetujui Wali Kelas" color="blue" />
          <StatCard title="Sakit" :value="data?.stats?.sakit_count ?? '0 Siswa'" subtext="Disetujui Wali Kelas" color="orange" />
          <StatCard title="Alfa" :value="data?.stats?.alpha_count ?? '0 Siswa'" subtext="Tanpa Keterangan" color="red" />
        </div>

        <!-- Presensi Kelas Hari Ini Table -->
        <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <div>
              <h3 class="text-sm font-bold text-gray-900">Rekap Presensi Mandiri {{ data?.class_name || 'Kelas Industri RPL' }} Hari Ini</h3>
              <p class="text-xs text-gray-400 mt-0.5">Data terisi otomatis dari Presensi Mandiri Siswa & Surat Izin yang disetujui Wali Kelas</p>
            </div>
            <div class="flex items-center gap-2">
              <NuxtLink
                to="/surat-izin"
                class="px-3 py-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-2xs"
              >
                <Mail class="w-3.5 h-3.5 text-primary" />
                <span class="hidden sm:inline">Izin Saya</span>
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
                <span>Pantau Presensi</span>
              </NuxtLink>
            </div>
          </div>

          <div class="overflow-x-auto -mx-2 sm:mx-0">
            <table class="w-full text-left text-xs min-w-[550px]">
              <thead>
                <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100 font-bold">
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap w-10">No</th>
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap">Nama Siswa</th>
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap">NIS</th>
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap text-center">Status Presensi</th>
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap">Keterangan</th>
                  <th class="pb-3 px-2 font-semibold whitespace-nowrap text-right">Verifikasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="item in data?.attendance_list || []" :key="item.student_id" class="hover:bg-gray-50/50 transition">
                  <td class="py-3 px-2 text-gray-400 whitespace-nowrap">{{ item.no }}</td>
                  <td class="py-3 px-2 font-semibold text-gray-900 whitespace-nowrap">{{ item.name }}</td>
                  <td class="py-3 px-2 text-gray-500 whitespace-nowrap">{{ item.nis }}</td>
                  <td class="py-3 px-2 text-center whitespace-nowrap"><StatusBadge :status="item.status" /></td>
                  <td class="py-3 px-2 text-gray-500 max-w-xs truncate" :title="item.note || '-'">{{ item.note || '-' }}</td>
                  <td class="py-3 px-2 text-right whitespace-nowrap">
                    <span v-if="item.verified" class="inline-flex items-center text-emerald-600 font-semibold gap-1 text-[11px] whitespace-nowrap">
                      <CheckCircle2 class="w-3.5 h-3.5" /> Terverifikasi
                    </span>
                    <span v-else class="text-gray-400 text-[11px] whitespace-nowrap">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: GURU PENGAJAR                            -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'GURU_PENGAJAR'">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
          <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Kelas Diampu</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">4 Kelas</div>
            <p class="text-[10px] text-gray-400 mt-0.5 truncate">X dan XI - Kompetensi RPL</p>
          </div>
          <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Jam Mengajar</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">5 Jam</div>
            <p class="text-[10px] text-gray-400 mt-0.5 truncate">Hari ini terjadwal</p>
          </div>
          <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Jurnal Terisi</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ data?.stats?.jurnal_terisi ?? '0/3' }}</div>
            <p class="text-[10px] text-gray-400 mt-0.5 truncate">Pertemuan KBM</p>
          </div>
          <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-2xs">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Belum Diisi</span>
            <div class="text-xl sm:text-2xl font-bold text-rose-600 mt-1">{{ data?.stats?.jurnal_belum ?? '3' }}</div>
            <p class="text-[10px] text-gray-400 mt-0.5 truncate">Pertemuan Menunggu</p>
          </div>
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

            <div class="overflow-x-auto -mx-6 px-6">
              <table class="w-full text-left text-xs whitespace-nowrap min-w-[500px]">
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
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-900">Pengingat Pembelajaran</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                  {{ (data?.reminders || []).length }} Tugas
                </span>
              </div>
              <ul class="space-y-2.5 text-xs text-gray-600">
                <li v-for="(rem, idx) in data?.reminders || []" :key="idx" class="flex items-start gap-2 bg-gray-50/70 p-2.5 rounded-xl border border-gray-100">
                  <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 flex-shrink-0"></span>
                  <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ rem }}</p>
                    <NuxtLink
                      v-if="rem.includes('jurnal belum diisi')"
                      to="/jurnal-materi"
                      class="inline-block mt-1 text-[11px] font-bold text-brand-red hover:underline"
                    >
                      Buka Form Jurnal KBM →
                    </NuxtLink>
                    <NuxtLink
                      v-else-if="rem.includes('surat izin')"
                      to="/persetujuan-izin"
                      class="inline-block mt-1 text-[11px] font-bold text-primary hover:underline"
                    >
                      Buka Persetujuan Izin →
                    </NuxtLink>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </template>

      <!-- ============================================== -->
      <!-- ROLE: WALI KELAS (eks GURU_PEMBIMBING)         -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'GURU_PEMBIMBING'">
        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatCard title="Kelas Binaan (Wali Kelas)" :value="data?.stats?.total_kelas ?? '0 Kelas'" subtext="Semester Ini" color="blue" />
          <StatCard title="Jurnal Kegiatan Terisi" :value="data?.stats?.jurnal_terisi ?? '0 Kelas'" subtext="Hari Ini" color="green" />
          <StatCard title="Surat Izin Siswa Menunggu" :value="data?.stats?.surat_izin_pending ?? '0 Permohonan'" subtext="Perlu Verifikasi Wali Kelas" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Pemantauan Real-time Jurnal Kelas (2 cols) -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Pemantauan Real-time Jurnal & Kehadiran Kelas Binaan</h3>
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
              <h3 class="text-sm font-bold text-gray-900">Persetujuan Surat Izin Siswa</h3>
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

      <!-- ============================================== -->
      <!-- ROLE: SUPERADMIN (Pusat Pengawasan Akademik)    -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SUPERADMIN'">
        <!-- 6 Academic Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
          <StatCard title="Wali Kelas" :value="data?.stats?.total_guru_pembimbing ?? '0'" subtext="Wali Kelas Aktif" color="green" />
          <StatCard title="Guru Pengajar" :value="data?.stats?.total_guru_pengajar ?? '0'" subtext="Pengampu Mapel" color="blue" />
          <StatCard title="Total Siswa" :value="data?.stats?.total_siswa ?? '0'" subtext="Terdata di Rombel" color="blue" />
          <StatCard title="Rombel" :value="data?.stats?.total_kelas ?? '0'" subtext="Kelas Industri RPL" color="orange" />
          <StatCard title="Rata-rata Presensi" :value="data?.stats?.kehadiran_rata_rata ?? '0%'" subtext="Hari Ini" color="green" />
          <StatCard title="Jurnal Terisi" :value="data?.stats?.jurnal_terisi_today ?? '0'" subtext="Hari Ini" color="blue" />
        </div>

        <!-- 2 Column Master Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left: Pemantauan Seluruh Rombongan Belajar (2 Cols) -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900">Pemantauan Presensi & Jurnal Kelas Hari Ini</h3>
                <p class="text-xs text-gray-400 mt-0.5">Pantau status pengisian presensi dan jurnal rombongan belajar kelas industri RPL</p>
              </div>

              <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold shrink-0 self-start sm:self-center">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                <span>Kelas Industri RPL</span>
              </div>
            </div>

            <div class="overflow-x-auto -mx-2 sm:mx-0">
              <table class="w-full text-left text-xs border-collapse min-w-[620px]">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100 font-bold bg-gray-50/50">
                    <th class="py-2.5 px-3 whitespace-nowrap">Rombel</th>
                    <th class="py-2.5 px-3 whitespace-nowrap">Jurusan</th>
                    <th class="py-2.5 px-3 whitespace-nowrap">Wali Kelas</th>
                    <th class="py-2.5 px-3 whitespace-nowrap">Sekretaris</th>
                    <th class="py-2.5 px-3 whitespace-nowrap text-center">Status Sesi</th>
                    <th class="py-2.5 px-3 whitespace-nowrap text-right">Kehadiran</th>
                    <th class="py-2.5 px-3 whitespace-nowrap text-center">Jurnal KBM</th>
                    <th class="py-2.5 px-3 whitespace-nowrap text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="cls in filteredClassesMonitoring" :key="cls.id" class="hover:bg-gray-50/70 transition">
                    <td class="py-3 px-3 font-bold text-gray-900 whitespace-nowrap">{{ cls.class_name }}</td>
                    <td class="py-3 px-3 whitespace-nowrap">
                      <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap">
                        RPL
                      </span>
                    </td>
                    <td class="py-3 px-3 text-gray-600 truncate max-w-[130px] whitespace-nowrap" :title="cls.advisor_name">{{ cls.advisor_name }}</td>
                    <td class="py-3 px-3 text-gray-600 truncate max-w-[110px] whitespace-nowrap" :title="cls.secretary_name">{{ cls.secretary_name }}</td>
                    <td class="py-3 px-3 text-center whitespace-nowrap">
                      <span
                        v-if="cls.session_status === 'LOCKED'"
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap inline-flex items-center gap-1"
                      >
                        🔒 Terkunci
                      </span>
                      <span
                        v-else-if="cls.session_status === 'DRAFT'"
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap inline-flex items-center gap-1"
                      >
                        📝 Aktif
                      </span>
                      <span
                        v-else
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-500 border border-gray-200 whitespace-nowrap inline-flex items-center gap-1"
                      >
                        ○ Belum Buka
                      </span>
                    </td>
                    <td class="py-3 px-3 text-right font-bold text-gray-800 whitespace-nowrap">
                      {{ cls.attendance_percent }}%
                      <span class="block text-[10px] font-normal text-gray-400 whitespace-nowrap">{{ cls.present_ratio }}</span>
                    </td>
                    <td class="py-3 px-3 text-center whitespace-nowrap">
                      <StatusBadge :status="cls.journal_status" />
                    </td>
                    <td class="py-3 px-3 text-center whitespace-nowrap">
                      <button
                        v-if="cls.session_status === 'LOCKED'"
                        @click="handleForceUnlock(cls.id)"
                        class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-[10px] font-bold transition cursor-pointer whitespace-nowrap inline-flex items-center gap-1"
                        title="Buka kunci sesi presensi agar dapat diperbaiki"
                      >
                        Buka Kunci
                      </button>
                      <span v-else class="text-gray-300 text-xs">—</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Right: Navigasi Cepat & Live Global Audit Trail (1 Col) -->
          <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-3">
              <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Pusat Kendali Cepat</h3>
              <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                <NuxtLink
                  to="/manajemen-guru"
                  class="p-3 bg-gray-50 hover:bg-blue-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <Users class="w-5 h-5 text-gray-500" />
                  <span>Kelola Guru</span>
                </NuxtLink>
                <NuxtLink
                  to="/master-data"
                  class="p-3 bg-gray-50 hover:bg-blue-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <Layers class="w-5 h-5 text-gray-500" />
                  <span>Jadwal & Jurusan</span>
                </NuxtLink>
                <NuxtLink
                  to="/wa-gateway"
                  class="p-3 bg-gray-50 hover:bg-blue-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <MessageSquare class="w-5 h-5 text-emerald-600" />
                  <span>WA Gateway</span>
                </NuxtLink>
                <NuxtLink
                  to="/presensi-override"
                  class="p-3 bg-gray-50 hover:bg-blue-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <ShieldAlert class="w-5 h-5 text-amber-600" />
                  <span>Buka Kunci</span>
                </NuxtLink>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-3">
              <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-gray-900">Aktivitas Terkini Sistem</h3>
                <NuxtLink to="/pengaturan-sistem" class="text-[11px] text-primary font-bold hover:underline">
                  Semua Log
                </NuxtLink>
              </div>

              <div class="space-y-3">
                <div
                  v-for="log in data?.recent_audit_logs || []"
                  :key="log.id"
                  class="text-xs border-l-2 border-primary pl-3 py-1 space-y-0.5"
                >
                  <div class="flex items-center justify-between text-[10px] text-gray-400">
                    <span class="font-bold text-gray-800">{{ log.user }}</span>
                    <span>{{ log.time }}</span>
                  </div>
                  <p class="text-gray-600 text-[11px] leading-tight">{{ log.action }}</p>
                  <p class="text-[10px] text-gray-400 font-mono">IP: {{ log.ip_address }}</p>
                </div>

                <div v-if="!data?.recent_audit_logs?.length" class="text-center py-6 text-gray-400 text-xs">
                  Belum ada log aktivitas tercatat.
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  ClipboardCheck,
  CheckCircle2,
  Mail,
  FileText,
  ShieldAlert,
  Users,
  Layers,
  MessageSquare,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const data = ref<any>(null)
const isLoading = ref(true)
const isCheckingIn = ref(false)
const checkInMessage = ref('')
const selectedMajorFilter = ref('ALL')

if (process.client) {
  try {
    const cached = localStorage.getItem(`cache_dashboard_${authStore.user?.role || 'default'}`)
    if (cached) {
      data.value = JSON.parse(cached)
      isLoading.value = false
    }
  } catch (e) {
    // ignore
  }
}

const filteredClassesMonitoring = computed(() => {
  if (!data.value?.classes_monitoring) return []
  if (selectedMajorFilter.value === 'ALL') return data.value.classes_monitoring
  return data.value.classes_monitoring.filter((c: any) => c.major === selectedMajorFilter.value)
})

const dashboardTitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SUPERADMIN') return 'Dashboard Super Administrator'
  if (role === 'SISWA') return 'Dashboard Presensi Mandiri Siswa'
  if (role === 'ORANG_TUA') return 'Dashboard Pantauan Orang Tua Siswa'
  if (role === 'SEKRETARIS') return 'Dashboard Presensi ' + (data.value?.class_name || 'Kelas Industri RPL')
  if (role === 'GURU_PENGAJAR') return 'Dashboard Guru Pengajar'
  if (role === 'GURU_PEMBIMBING') return (authStore.user?.name || 'Wali Kelas') + ' (Wali Kelas)'
  return 'Dashboard'
})

const dashboardSubtitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SUPERADMIN') return 'Pengawasan operasional akademik, wali kelas, guru pengajar, dan presensi seluruh rombel'
  if (role === 'SISWA') return 'Presensi mandiri harian, pengajuan surat izin ke Wali Kelas, dan jurnal kegiatan belajar siswa'
  if (role === 'ORANG_TUA') return 'Pantau kehadiran harian, surat izin, dan jurnal kegiatan belajar anak di sekolah secara real-time'
  if (role === 'SEKRETARIS') return 'Ringkasan presensi mandiri kelas dan pencatatan jurnal kegiatan KBM'
  if (role === 'GURU_PEMBIMBING') return 'Wali Kelas — Verifikasi surat izin siswa, pemantauan jurnal kegiatan, dan rekap presensi kelas'
  if (role === 'GURU_PENGAJAR') return 'Ringkasan jadwal mengajar, publikasi materi, dan konfirmasi jurnal kegiatan siswa'
  return ''
})

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

async function loadDashboard(silent = false) {
  let token = getAuthToken()
  if (!token) {
    await new Promise(r => setTimeout(r, 100))
    token = getAuthToken()
  }
  if (!token) return

  if (!silent && !data.value) {
    isLoading.value = true
  }

  try {
    const res = await $fetch<any>(`${authStore.apiBase}/dashboard`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    data.value = res
    if (process.client) {
      localStorage.setItem(`cache_dashboard_${authStore.user?.role || 'default'}`, JSON.stringify(res))
    }
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

async function handleSelfCheckIn() {
  isCheckingIn.value = true
  checkInMessage.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/attendance/check-in`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {}
    })
    checkInMessage.value = res?.message || 'Presensi Mandiri berhasil dicatat!'
    await loadDashboard(true)
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal melakukan presensi mandiri.')
  } finally {
    isCheckingIn.value = false
  }
}

async function handleForceUnlock(classId: number) {
  if (!confirm('Buka kunci sesi presensi rombel ini kembali ke status DRAFT?')) return
  try {
    const today = new Date().toISOString().split('T')[0]
    await $fetch(`${authStore.apiBase}/attendance/force-unlock`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { class_id: classId, date: today }
    })
    alert('Sesi presensi berhasil dibuka kembali.')
    loadDashboard()
  } catch (e: any) {
    alert(e?.data?.message || 'Gagal membuka kunci sesi presensi.')
  }
}

async function handleQuickReview(id: number, status: 'APPROVED' | 'REJECTED') {
  try {
    await $fetch(`${authStore.apiBase}/leave-requests/${id}/review`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: { status, review_note: status === 'APPROVED' ? 'Disetujui oleh Wali Kelas' : 'Ditolak oleh Wali Kelas' }
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
