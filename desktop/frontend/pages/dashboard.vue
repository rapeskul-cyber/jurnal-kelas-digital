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
          <StatCard title="HADIR BULAN INI" :value="data?.stats?.hadir_hari ?? '0 Hari'" color="green" />
          <StatCard title="Total Izin Disetujui" :value="data?.stats?.izin_hari ?? '0 Hari'" color="blue" />
          <StatCard title="ALFA" :value="data?.stats?.alpha_hari ?? '0 Hari'" color="red" />
          <StatCard title="Total Sakit" :value="data?.stats?.sakit_hari ?? '0 Hari'" color="orange" />
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <StatCard title="Hadir" :value="data?.stats?.hadir_count ?? '0 Siswa'" :subtext="data?.stats?.hadir_percent ?? '0% Kehadiran'" color="green" />
          <StatCard title="Izin" :value="data?.stats?.izin_count ?? '0 Siswa'" subtext="Verifikasi Surat" color="blue" />
          <StatCard title="Sakit" :value="data?.stats?.sakit_count ?? '0 Siswa'" subtext="Konfirmasi Wali" color="orange" />
          <StatCard title="Alfa" :value="data?.stats?.alpha_count ?? '0 Siswa'" subtext="Tanpa Keterangan" color="red" />
        </div>

        <!-- Presensi Kelas Hari Ini Table / List -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs space-y-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <div>
              <h3 class="text-sm font-bold text-gray-900">Presensi Kelas {{ data?.class_name || authStore.user?.school_class?.name || 'Kelas Industri' }} Hari ini</h3>
              <p class="text-xs text-gray-400 mt-0.5">Status sesi: <span class="font-semibold text-primary">{{ data?.session_status || 'DRAFT' }}</span></p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
              <NuxtLink
                to="/presensi"
                class="w-full sm:w-auto px-4 py-2 bg-primary hover:bg-primary-hover active:scale-[0.99] text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-2xs transition"
              >
                <ClipboardCheck class="w-4 h-4" />
                <span>Input Presensi Lengkap →</span>
              </NuxtLink>
            </div>
          </div>

          <!-- Mobile Friendly List View (< sm) -->
          <div class="sm:hidden divide-y divide-gray-100">
            <div
              v-for="item in data?.attendance_list || []"
              :key="item.student_id"
              class="py-3 flex items-center justify-between gap-3"
            >
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-gray-400 w-5">{{ item.no }}.</span>
                  <p class="text-sm font-bold text-gray-900 truncate">{{ item.name }}</p>
                </div>
                <p class="text-xs text-gray-500 pl-7 truncate mt-0.5">
                  NIS: {{ item.nis }} <span v-if="item.note" class="text-gray-400">• {{ item.note }}</span>
                </p>
              </div>
              <div class="shrink-0 flex items-center gap-1.5">
                <StatusBadge :status="item.status" />
              </div>
            </div>
          </div>

          <!-- Desktop Table View (>= sm) -->
          <div class="hidden sm:block overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            <table class="w-full text-left text-sm whitespace-nowrap min-w-[560px]">
              <thead>
                <tr class="text-gray-400 uppercase text-xs border-b border-gray-100">
                  <th class="pb-3.5 font-semibold w-10">No</th>
                  <th class="pb-3.5 font-semibold min-w-[160px]">Nama Siswa</th>
                  <th class="pb-3.5 font-semibold w-28">NIS</th>
                  <th class="pb-3.5 font-semibold text-center w-32">Status</th>
                  <th class="pb-3.5 font-semibold min-w-[160px]">Keterangan</th>
                  <th class="pb-3.5 font-semibold text-right w-32">Verifikasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in data?.attendance_list || []" :key="item.student_id" class="hover:bg-gray-50/50">
                  <td class="py-3.5 text-gray-400 font-medium">{{ item.no }}</td>
                  <td class="py-3.5 font-semibold text-gray-900">{{ item.name }}</td>
                  <td class="py-3.5 text-gray-500 font-mono text-xs">{{ item.nis }}</td>
                  <td class="py-3.5 text-center"><StatusBadge :status="item.status" /></td>
                  <td class="py-3.5 text-gray-500 max-w-[240px] truncate" :title="item.note">{{ item.note || '-' }}</td>
                  <td class="py-3.5 text-right">
                    <span v-if="item.verified" class="inline-flex items-center text-emerald-600 font-semibold gap-1 text-xs">
                      <CheckCircle2 class="w-4 h-4" /> Terverifikasi
                    </span>
                    <span v-else class="text-gray-400 text-xs">-</span>
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
      <!-- Main Content Cards with Safe Minimum Width -->
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
      <!-- ROLE: GURU PEMBIMBING (Mockup 26)               -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'GURU_PEMBIMBING'">
        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatCard title="Kelas Binaan" :value="data?.stats?.total_kelas ?? '0 Kelas'" subtext="Semester Ini" color="blue" />
          <StatCard title="Jurnal Terisi" :value="data?.stats?.jurnal_terisi ?? '0 Kelas'" subtext="Hari Ini" color="green" />
          <StatCard title="Izin Menunggu" :value="data?.stats?.surat_izin_pending ?? '0 Permohonan'" subtext="Perlu Verifikasi" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Pemantauan Jurnal Kelas (2 cols) -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Pemantauan Jurnal Kelas Binaan</h3>
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

      <!-- ============================================== -->
      <!-- ROLE: SUPERADMIN (Pusat Pengawasan Akademik)    -->
      <!-- ============================================== -->
      <template v-else-if="authStore.user?.role === 'SUPERADMIN'">
        <!-- 6 Academic Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
          <StatCard title="Guru Pembimbing" :value="data?.stats?.total_guru_pembimbing ?? '0'" subtext="Wali Kelas Aktif" color="green" />
          <StatCard title="Guru Pengajar" :value="data?.stats?.total_guru_pengajar ?? '0'" subtext="Pengampu Mapel" color="blue" />
          <StatCard title="Total Siswa" :value="data?.stats?.total_siswa ?? '0'" subtext="Terdata di Rombel" color="blue" />
          <StatCard title="Rombel" :value="data?.stats?.total_kelas ?? '0'" :subtext="`RPL: ${data?.stats?.majors?.rpl || 0} • DKV: ${data?.stats?.majors?.dkv || 0} • ANI: ${data?.stats?.majors?.animasi || 0}`" color="orange" />
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
                <p class="text-xs text-gray-400 mt-0.5">Pantau status pengisian presensi dan jurnal per rombongan belajar</p>
              </div>

              <!-- Filter Jurusan -->
              <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs select-none flex-shrink-0">
                <button
                  type="button"
                  @click="selectedMajorFilter = 'ALL'"
                  class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer"
                  :class="selectedMajorFilter === 'ALL' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'"
                >
                  Semua
                </button>
                <button
                  type="button"
                  @click="selectedMajorFilter = 'RPL'"
                  class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                  :class="selectedMajorFilter === 'RPL' ? 'bg-red-600 text-white shadow-2xs' : 'text-gray-600 hover:text-red-700 hover:bg-red-50'"
                >
                  <span class="w-2 h-2 rounded-full bg-red-400 inline-block" v-if="selectedMajorFilter !== 'RPL'"></span>
                  RPL
                </button>
                <button
                  type="button"
                  @click="selectedMajorFilter = 'DKV'"
                  class="w-16 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                  :class="selectedMajorFilter === 'DKV' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-gray-600 hover:text-emerald-700 hover:bg-emerald-50'"
                >
                  <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block" v-if="selectedMajorFilter !== 'DKV'"></span>
                  DKV
                </button>
                <button
                  type="button"
                  @click="selectedMajorFilter = 'Animasi'"
                  class="w-18 py-1.5 text-center font-bold text-xs rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1"
                  :class="selectedMajorFilter === 'Animasi' ? 'bg-amber-500 text-white shadow-2xs' : 'text-gray-600 hover:text-amber-700 hover:bg-amber-50'"
                >
                  <span class="w-2 h-2 rounded-full bg-amber-400 inline-block" v-if="selectedMajorFilter !== 'Animasi'"></span>
                  Animasi
                </button>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100 font-bold bg-gray-50/50">
                    <th class="py-2.5 px-3">Rombel</th>
                    <th class="py-2.5 px-3">Jurusan</th>
                    <th class="py-2.5 px-3">Wali / Pembimbing</th>
                    <th class="py-2.5 px-3">Sekretaris</th>
                    <th class="py-2.5 px-3 text-center">Status Sesi</th>
                    <th class="py-2.5 px-3 text-right">Kehadiran</th>
                    <th class="py-2.5 px-3">Jurnal KBM</th>
                    <th class="py-2.5 px-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="cls in filteredClassesMonitoring" :key="cls.id" class="hover:bg-gray-50/70 transition">
                    <td class="py-3 px-3 font-bold text-gray-900">{{ cls.class_name }}</td>
                    <td class="py-3 px-3">
                      <span
                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold"
                        :class="[
                          cls.major === 'RPL' ? 'bg-red-50 text-red-700 border border-red-200' :
                          cls.major === 'DKV' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                          'bg-amber-50 text-amber-800 border border-amber-300'
                        ]"
                      >
                        <img
                          :src="cls.major === 'RPL' ? '/images/logo-rpl.png' : cls.major === 'DKV' ? '/images/logo-dkv.png' : '/images/logo-animasi.png'"
                          :alt="cls.major"
                          class="w-3.5 h-3.5 object-contain"
                        />
                        {{ cls.major }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-gray-600 truncate max-w-[130px]" :title="cls.advisor_name">{{ cls.advisor_name }}</td>
                    <td class="py-3 px-3 text-gray-600 truncate max-w-[110px]" :title="cls.secretary_name">{{ cls.secretary_name }}</td>
                    <td class="py-3 px-3 text-center">
                      <span
                        v-if="cls.session_status === 'LOCKED'"
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200"
                      >
                        🔒 Terkunci
                      </span>
                      <span
                        v-else-if="cls.session_status === 'DRAFT'"
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                      >
                        📝 Draft
                      </span>
                      <span
                        v-else
                        class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-500 border border-gray-200"
                      >
                        ○ Belum Buka
                      </span>
                    </td>
                    <td class="py-3 px-3 text-right font-bold text-gray-800">
                      {{ cls.attendance_percent }}%
                      <span class="block text-[10px] font-normal text-gray-400">{{ cls.present_ratio }}</span>
                    </td>
                    <td class="py-3 px-3">
                      <StatusBadge :status="cls.journal_status" />
                    </td>
                    <td class="py-3 px-3 text-center">
                      <button
                        v-if="cls.session_status === 'LOCKED'"
                        @click="handleForceUnlock(cls.id)"
                        class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-[10px] font-bold transition cursor-pointer"
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
            <!-- Navigasi Akses Cepat -->
            <div class="bg-white rounded-2xl border border-gray-150 p-6 shadow-2xs space-y-3">
              <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Pusat Kendali Cepat</h3>
              <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                <NuxtLink
                  to="/manajemen-guru"
                  class="p-3 bg-gray-50 hover:bg-red-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <Users class="w-5 h-5 text-gray-500" />
                  <span>Kelola Guru</span>
                </NuxtLink>
                <NuxtLink
                  to="/master-data"
                  class="p-3 bg-gray-50 hover:bg-red-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <Layers class="w-5 h-5 text-gray-500" />
                  <span>Jadwal & Jurusan</span>
                </NuxtLink>
                <NuxtLink
                  to="/wa-gateway"
                  class="p-3 bg-gray-50 hover:bg-red-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <MessageSquare class="w-5 h-5 text-emerald-600" />
                  <span>WA Gateway</span>
                </NuxtLink>
                <NuxtLink
                  to="/presensi-override"
                  class="p-3 bg-gray-50 hover:bg-red-50 hover:text-primary rounded-xl border border-gray-100 transition flex flex-col items-center justify-center text-center gap-1.5"
                >
                  <ShieldAlert class="w-5 h-5 text-amber-600" />
                  <span>Buka Kunci</span>
                </NuxtLink>
              </div>
            </div>

            <!-- Live Audit Log Feed -->
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
const selectedMajorFilter = ref('ALL')

const filteredClassesMonitoring = computed(() => {
  if (!data.value?.classes_monitoring) return []
  if (selectedMajorFilter.value === 'ALL') return data.value.classes_monitoring
  return data.value.classes_monitoring.filter((c: any) => c.major === selectedMajorFilter.value)
})

const dashboardTitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SUPERADMIN') return 'Dashboard Super Administrator'
  if (role === 'SISWA') return 'Dashboard Presensi Mandiri Siswa'
  if (role === 'SEKRETARIS') return 'Dashboard Presensi ' + (data.value?.class_name || authStore.user?.school_class?.name || 'Kelas Industri')
  if (role === 'GURU_PENGAJAR') return 'Dashboard Pengajar'
  if (role === 'GURU_PEMBIMBING') return authStore.user?.name || 'Nurhayatul Fadilla, S.Pd'
  return 'Dashboard'
})

const dashboardSubtitle = computed(() => {
  const role = authStore.user?.role
  if (role === 'SUPERADMIN') return 'Pengawasan operasional akademik, guru pembimbing, guru pengajar, dan presensi seluruh rombel'
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
