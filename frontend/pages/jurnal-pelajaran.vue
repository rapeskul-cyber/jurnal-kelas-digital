<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      :title="authStore.user?.role === 'ORANG_TUA' ? 'Jurnal Kegiatan & Materi Belajar Anak' : (isStudentOrSekre ? 'Catatan Belajar Mandiri Saya' : 'Laporan Jurnal Belajar Siswa')"
      :subtitle="authStore.user?.role === 'ORANG_TUA' ? 'Pantau laporan kegiatan belajar harian anak di sekolah serta materi dari guru' : (isStudentOrSekre ? 'Catatan refleksi belajar harian Anda di sekolah yang otomatis dipantau oleh Orang Tua serta Wali Kelas' : 'Laporan catatan kegiatan belajar harian yang dilaporkan siswa dan dipantau oleh Wali Kelas serta Orang Tua')"
    >
      <template #actions>
        <button
          v-if="isStudentOrSekre"
          type="button"
          @click="showAddActivityModal = true"
          class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-2xs transition flex items-center gap-2 cursor-pointer whitespace-nowrap"
        >
          <Plus class="w-4 h-4" />
          <span>Lapor Catatan Belajar</span>
        </button>
      </template>
    </AppHeader>

    <main class="px-4 sm:px-8 pb-8 space-y-5">
      <!-- Success Toast -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ successMsg }}</span>
        </div>
        <button @click="successMsg = ''" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <!-- Filter Bar & Schedule Pop-up Button -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
          <MonthYearPicker v-model="selectedMonth" @change="fetchAll" />

          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari kegiatan / materi / mapel..."
              class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>
        </div>

        <button
          type="button"
          @click="showScheduleModal = true"
          class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:border-primary text-gray-700 hover:text-primary rounded-xl text-xs font-semibold shadow-2xs hover:shadow-xs transition cursor-pointer shrink-0 whitespace-nowrap"
        >
          <Calendar class="w-4 h-4 text-primary" />
          <span>Jadwal Pelajaran</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-primary border border-blue-200">
            {{ todaySchedules.length }}
          </span>
        </button>
      </div>

      <!-- BAGIAN 1: Laporan Jurnal Kegiatan Belajar Siswa di Sekolah (Core Aplikasi) -->
      <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900">
              {{ isStudentOrSekre ? 'Riwayat Catatan Belajar Mandiri Saya' : (authStore.user?.role === 'ORANG_TUA' ? 'Riwayat Jurnal Belajar Anak' : 'Laporan Jurnal Kegiatan Belajar Siswa di Sekolah') }}
            </h3>
            <p class="text-xs text-gray-400">
              {{ isStudentOrSekre ? 'Catatan refleksi belajar harian yang Anda laporkan dan otomatis dipantau oleh Orang Tua serta Wali Kelas' : 'Catatan kegiatan belajar harian yang dilaporkan siswa dan dipantau oleh Wali Kelas serta Orang Tua' }}
            </p>
          </div>

          <div v-if="isStudentOrSekre" class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-primary border border-blue-200">
              <User class="w-3.5 h-3.5" />
              <span>Catatan Pribadi Saya</span>
            </span>
          </div>
        </div>

        <div v-if="filteredActivities.length === 0" class="py-8 text-center text-xs text-gray-400">
          Belum ada catatan jurnal kegiatan siswa untuk periode ini.
        </div>

        <div v-else>
          <!-- Mobile Card List for Activities -->
          <div class="block md:hidden space-y-3">
            <div
              v-for="act in filteredActivities"
              :key="'mob-act-' + act.id"
              class="p-4 rounded-2xl border border-gray-200 bg-white shadow-2xs space-y-3"
            >
              <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
                <span class="text-xs font-bold text-gray-900">{{ act.date_time }}</span>
                <StatusBadge :status="act.status" />
              </div>
              <div>
                <h4 class="font-bold text-primary text-sm">{{ act.subject }}</h4>
                <p v-if="!isStudentOrSekre" class="text-xs text-gray-500 mt-0.5">Siswa: <span class="font-semibold text-gray-700">{{ act.created_by }}</span></p>
              </div>
              <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100 space-y-1 text-xs">
                <p class="text-gray-800 leading-relaxed"><span class="text-gray-400">Kegiatan:</span> {{ act.activity }}</p>
                <p v-if="act.notes" class="text-gray-500 italic text-[11px] pt-1 border-t border-gray-200/50">Catatan: {{ act.notes }}</p>
              </div>
              <button
                type="button"
                @click="selectedActivity = act"
                class="w-full py-2.5 px-3 rounded-xl border border-primary/20 text-primary bg-blue-50 hover:bg-primary hover:text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs active:scale-95 min-h-[44px]"
              >
                <BookOpen class="w-3.5 h-3.5" />
                <span>Baca Rincian Lengkap</span>
              </button>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
            <thead>
              <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                <th class="pb-3 font-semibold whitespace-nowrap">Tanggal / Jam</th>
                <th v-if="!isStudentOrSekre" class="pb-3 font-semibold whitespace-nowrap">Pelapor (Siswa)</th>
                <th class="pb-3 font-semibold whitespace-nowrap">Mata Pelajaran</th>
                <th class="pb-3 font-semibold">Kegiatan yang Dikerjakan di Sekolah</th>
                <th class="pb-3 font-semibold">Hasil / Catatan</th>
                <th class="pb-3 font-semibold text-center whitespace-nowrap">Status Pantauan</th>
                <th class="pb-3 font-semibold text-right whitespace-nowrap">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="act in filteredActivities" :key="act.id" class="hover:bg-gray-50/50 transition">
                <td class="py-3.5 font-bold text-gray-900 whitespace-nowrap">{{ act.date_time }}</td>
                <td v-if="!isStudentOrSekre" class="py-3.5 font-semibold text-gray-800 whitespace-nowrap">{{ act.created_by }}</td>
                <td class="py-3.5 font-bold text-primary whitespace-nowrap">{{ act.subject }}</td>
                <td class="py-3.5 text-gray-800 max-w-xs">
                  <div class="line-clamp-2 leading-relaxed" :title="act.activity">
                    {{ act.activity }}
                  </div>
                </td>
                <td class="py-3.5 text-gray-500 max-w-xs">
                  <div class="line-clamp-2 leading-relaxed" :title="act.notes || ''">
                    {{ act.notes || '-' }}
                  </div>
                </td>
                <td class="py-3.5 text-center whitespace-nowrap">
                  <StatusBadge :status="act.status" />
                </td>
                <td class="py-3.5 text-right whitespace-nowrap">
                  <button
                    type="button"
                    @click="selectedActivity = act"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-primary/20 text-primary bg-blue-50 hover:bg-primary hover:text-white text-xs font-bold transition-all shadow-2xs hover:shadow-xs cursor-pointer whitespace-nowrap active:scale-95"
                    title="Buka form rincian lengkap catatan kegiatan belajar"
                  >
                    <BookOpen class="w-3.5 h-3.5" />
                    <span>Baca Selengkapnya</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      </div>

      <!-- BAGIAN 2: Daftar Materi & Modul Pembelajaran dari Guru -->
      <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-6 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Daftar Materi & Modul Pembelajaran Guru</h3>
            <p class="text-xs text-gray-400">Berkas modul digital dan instruksi tugas yang diunggah Guru Pengajar</p>
          </div>
          <button
            v-if="search"
            @click="search = ''"
            class="text-xs text-primary hover:underline font-semibold cursor-pointer whitespace-nowrap"
          >
            Reset Pencarian
          </button>
        </div>

        <!-- Mobile Card List for Materials -->
        <div class="block md:hidden space-y-3">
          <div
            v-for="item in filteredMaterials"
            :key="'mob-mat-' + item.id"
            class="p-4 rounded-2xl border border-gray-200 bg-white shadow-2xs space-y-3"
          >
            <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
              <span class="text-xs font-bold text-gray-900">{{ item.date_time }}</span>
              <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ item.attachments_count }} lampiran
              </span>
            </div>
            <div>
              <h4 class="font-bold text-primary text-sm">{{ item.subject }}</h4>
              <p class="text-xs text-gray-600 mt-0.5">Guru: <span class="font-semibold text-gray-800">{{ item.teacher }}</span></p>
            </div>
            <div class="bg-gray-50/70 p-3 rounded-xl border border-gray-100 text-xs text-gray-700">
              {{ item.topic }}
            </div>
            <button
              type="button"
              @click="selectedMaterial = item"
              class="w-full py-2.5 px-3 rounded-xl border border-primary/30 text-primary bg-blue-50 hover:bg-primary hover:text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs active:scale-95 min-h-[44px]"
            >
              <FileText class="w-3.5 h-3.5" />
              <span>Buka Modul & Tugas</span>
            </button>
          </div>
          <div v-if="filteredMaterials.length === 0" class="py-8 text-center text-xs text-gray-400">
            Tidak ada materi pelajaran untuk periode ini.
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[650px]">
            <thead>
              <tr class="text-gray-400 uppercase text-[10px] border-b border-gray-100">
                <th class="pb-3 font-semibold whitespace-nowrap">Tanggal / Jam</th>
                <th class="pb-3 font-semibold whitespace-nowrap">Guru Pengajar</th>
                <th class="pb-3 font-semibold whitespace-nowrap">Mata Pelajaran</th>
                <th class="pb-3 font-semibold whitespace-nowrap">Bahasan & Rencana Belajar</th>
                <th class="pb-3 font-semibold text-center whitespace-nowrap">Lampiran</th>
                <th class="pb-3 font-semibold text-right whitespace-nowrap">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in filteredMaterials" :key="item.id" class="hover:bg-gray-50/50">
                <td class="py-4 font-bold text-gray-900 whitespace-nowrap">{{ item.date_time }}</td>
                <td class="py-4 text-gray-700 whitespace-nowrap">{{ item.teacher }}</td>
                <td class="py-4 font-bold text-gray-900 whitespace-nowrap">{{ item.subject }}</td>
                <td class="py-4 text-gray-600 max-w-xs">{{ item.topic }}</td>
                <td class="py-4 text-center">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ item.attachments_count }} file
                  </span>
                </td>
                <td class="py-4 text-right">
                  <button
                    type="button"
                    @click="selectedMaterial = item"
                    class="px-3 py-1.5 rounded-lg border border-amber-500/70 text-amber-700 bg-amber-50/30 hover:bg-amber-100 text-xs font-semibold transition-colors cursor-pointer whitespace-nowrap"
                  >
                    Baca Materi
                  </button>
                </td>
              </tr>
              <tr v-if="filteredMaterials.length === 0">
                <td colspan="6" class="py-8 text-center text-xs text-gray-400">
                  Tidak ada materi pelajaran untuk periode ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Lapor Jurnal Kegiatan Hari Ini (Oleh Siswa) -->
      <div
        v-if="showAddActivityModal"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
      >
        <div class="bg-white rounded-t-3xl sm:rounded-3xl p-5 sm:p-7 max-w-lg w-full shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
          <!-- Mobile drag handle -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
          <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-primary">
                Jurnal Kegiatan Siswa
              </span>
              <h3 class="text-base font-bold text-gray-900 mt-1">Laporkan Kegiatan Belajar Hari Ini</h3>
              <p class="text-xs text-gray-500">Laporan ini otomatis terhubung dan terpantau oleh Wali Kelas serta Orang Tua</p>
            </div>
            <button @click="showAddActivityModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitStudentActivity" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Pilih Mata Pelajaran / Sesi KBM *
              </label>
              <select
                v-model="activityForm.schedule_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                required
              >
                <option value="" disabled>-- Pilih Jadwal Pelajaran --</option>
                <option v-for="sch in todaySchedules" :key="sch.id" :value="sch.id">
                  {{ sch.subject }} ({{ sch.time }}) — {{ sch.teacher }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Kegiatan Belajar yang Dikerjakan Hari Ini *
              </label>
              <textarea
                v-model="activityForm.activity"
                rows="3"
                required
                placeholder="Contoh: Mempelajari dan mempraktikkan pembuatan REST API menggunakan Laravel serta menghubungkannya ke halaman Nuxt..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none"
              ></textarea>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                Hasil / Catatan Progres Belajar (Opsional)
              </label>
              <input
                v-model="activityForm.notes"
                type="text"
                placeholder="Contoh: Tugas praktikum modul 3 selesai dikerjakan dan diunggah ke repositori"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button
                type="button"
                @click="showAddActivityModal = false"
                class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="submittingActivity"
                class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold shadow-2xs cursor-pointer disabled:opacity-50"
              >
                {{ submittingActivity ? 'Menyimpan...' : 'Simpan Jurnal Kegiatan' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Detail Modal Dialog Jurnal Kegiatan Belajar (Form Baca Selengkapnya) -->
      <div v-if="selectedActivity" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl p-5 sm:p-7 max-w-xl w-full shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] flex flex-col pb-8 sm:pb-7 pb-safe">
          <!-- Mobile drag handle -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
          <!-- Modal Header -->
          <div class="flex items-start justify-between pb-3.5 border-b border-gray-100 shrink-0">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-primary border border-blue-200">
                  {{ selectedActivity.subject }}
                </span>
                <span class="text-[10px] font-mono text-gray-400">ID: #{{ selectedActivity.id }}</span>
              </div>
              <h3 class="text-base sm:text-lg font-bold text-gray-900 mt-1">Form Catatan Kegiatan Belajar</h3>
              <p class="text-xs text-gray-500">
                Dipantau langsung oleh Wali Kelas dan Orang Tua
              </p>
            </div>
            <button
              type="button"
              @click="selectedActivity = null"
              class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Scrollable Body -->
          <div class="overflow-y-auto space-y-4 pr-1 text-xs text-gray-700">
            <!-- Metadata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="p-3 rounded-2xl bg-gray-50 border border-gray-150 space-y-0.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pelapor (Siswa)</span>
                <div class="font-bold text-xs sm:text-sm text-gray-900 flex items-center gap-1.5">
                  <User class="w-3.5 h-3.5 text-primary" />
                  <span>{{ selectedActivity.created_by }}</span>
                </div>
              </div>

              <div class="p-3 rounded-2xl bg-gray-50 border border-gray-150 space-y-0.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Waktu Pelaporan</span>
                <div class="font-bold text-xs sm:text-sm text-gray-900 flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5 text-primary" />
                  <span>{{ selectedActivity.date_time }}</span>
                </div>
              </div>
            </div>

            <!-- Field 1: Kegiatan yang Dikerjakan (Lengkap) -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="font-bold text-gray-700 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                  <BookOpen class="w-3.5 h-3.5 text-primary" />
                  <span>Kegiatan Belajar yang Dikerjakan di Sekolah</span>
                </label>
                <span class="text-[10px] text-gray-400 font-medium">Uraian Lengkap</span>
              </div>
              <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 text-xs sm:text-sm text-gray-800 leading-relaxed whitespace-pre-wrap select-text shadow-2xs">
                {{ selectedActivity.activity }}
              </div>
            </div>

            <!-- Field 2: Hasil / Catatan Progres Belajar (Lengkap) -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="font-bold text-gray-700 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                  <FileText class="w-3.5 h-3.5 text-primary" />
                  <span>Hasil / Catatan Progres Belajar</span>
                </label>
                <span class="text-[10px] text-gray-400 font-medium">Catatan Siswa</span>
              </div>
              <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 text-xs sm:text-sm text-gray-800 leading-relaxed whitespace-pre-wrap select-text shadow-2xs">
                {{ selectedActivity.notes || 'Tidak ada catatan progres tambahan yang dicantumkan.' }}
              </div>
            </div>

            <!-- Field 3: Status Pantauan & Notifikasi Multi-Pihak -->
            <div class="p-3.5 rounded-2xl border border-gray-150 bg-blue-50/30 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Status Pantauan & Verifikasi:</span>
                <StatusBadge :status="selectedActivity.status" />
              </div>
              <div class="grid grid-cols-2 gap-2 pt-1 text-[10px] text-gray-600">
                <div class="p-2.5 rounded-xl bg-white border border-gray-200 text-center">
                  <span class="block text-gray-400">Wali Kelas</span>
                  <span class="font-bold text-emerald-600">Terpantau (Portal Walas) ✓</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border border-gray-200 text-center">
                  <span class="block text-gray-400">Orang Tua</span>
                  <span class="font-bold text-emerald-600">Terpantau (Portal Orang Tua) ✓</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="pt-3 border-t border-gray-100 flex items-center justify-end shrink-0">
            <button
              type="button"
              @click="selectedActivity = null"
              class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-xs transition cursor-pointer shadow-2xs active:scale-95"
            >
              Tutup Rincian
            </button>
          </div>
        </div>
      </div>

      <!-- Detail Modal Dialog -->
      <div v-if="selectedMaterial" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl p-5 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
          <!-- Mobile drag handle -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
          <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-primary">
                {{ selectedMaterial.subject }}
              </span>
              <h3 class="text-base font-bold text-gray-900 mt-1">{{ selectedMaterial.topic }}</h3>
            </div>
            <button @click="selectedMaterial = null" class="text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="space-y-3 text-xs text-gray-700">
            <div>
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Rencana Belajar</span>
              <p class="bg-gray-50 p-3 rounded-xl border border-gray-100">{{ selectedMaterial.plan || 'Mempelajari arsitektur dan komponen aplikasi.' }}</p>
            </div>
            <div>
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Catatan / Tugas Siswa</span>
              <p class="bg-gray-50 p-3 rounded-xl border border-gray-100">{{ selectedMaterial.assignment || 'Tugas mandiri sesuai materi pelajaran.' }}</p>
            </div>
            <div class="pt-2">
              <span class="font-bold text-gray-400 uppercase text-[10px] block mb-2">Lampiran Dokumentasi</span>
              <div class="flex items-center gap-2">
                <a
                  :href="selectedMaterial.attachment_url || '#'"
                  :download="selectedMaterial.attachment_name || 'Modul_Belajar_Kelas.pdf'"
                  target="_blank"
                  class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 text-primary hover:bg-blue-100 rounded-xl font-semibold border border-blue-200 text-xs whitespace-nowrap"
                >
                  <Download class="w-4 h-4" />
                  <span>{{ selectedMaterial.attachment_name || 'Modul_Belajar_Kelas.pdf' }}</span>
                </a>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button
              @click="selectedMaterial = null"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- Modal / Card Pop-up Jadwal Pelajaran (Scrollable) -->
      <div
        v-if="showScheduleModal"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
      >
        <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
          <!-- Mobile drag handle -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
          <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-primary whitespace-nowrap">
                  Jadwal KBM Siswa
                </span>
                <span class="text-xs text-gray-400">{{ todayFormatted }}</span>
              </div>
              <h3 class="text-base font-bold text-gray-900 mt-1">Jadwal Pembelajaran Kelas</h3>
              <p class="text-xs text-gray-500">Jadwal tatap muka yang ditambahkan oleh guru pengajar</p>
            </div>
            <button
              @click="showScheduleModal = false"
              class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="max-h-[360px] overflow-y-auto space-y-2.5 pr-1">
            <div
              v-for="sch in todaySchedules"
              :key="sch.id"
              class="p-3.5 rounded-2xl border border-gray-150 bg-gray-50/70 hover:bg-white hover:border-gray-300 hover:shadow-xs transition duration-150 flex items-center justify-between gap-3"
            >
              <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2 text-xs">
                  <span class="font-bold text-gray-900 flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-primary" />
                    {{ sch.time }}
                  </span>
                  <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                    {{ sch.room || 'Lab. 1' }}
                  </span>
                  <span class="text-[11px] text-gray-400 font-medium">
                    • {{ sch.day || 'Senin' }}
                  </span>
                </div>
                <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate">{{ sch.subject }}</h4>
                <p class="text-[11px] text-gray-500 flex items-center gap-1 truncate">
                  <User class="w-3 h-3 text-gray-400 shrink-0" />
                  <span>{{ sch.teacher }}</span>
                </p>
              </div>

              <button
                type="button"
                @click="selectScheduleAndFilter(sch.subject)"
                class="px-2.5 py-1.5 rounded-xl border border-blue-200 bg-blue-50 hover:bg-primary text-primary hover:text-white text-xs font-semibold transition shrink-0 cursor-pointer shadow-2xs whitespace-nowrap"
              >
                Lihat Materi
              </button>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="text-gray-400 text-[11px]">Total {{ todaySchedules.length }} jadwal terdaftar</span>
            <button
              @click="showScheduleModal = false"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import {
  Calendar,
  Search,
  X,
  Download,
  Clock,
  User,
  Plus,
  CheckCircle2,
  BookOpen,
  FileText,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useToastStore } from '~/stores/toast'

const authStore = useAuthStore()
const toast = useToastStore()
const isStudentOrSekre = computed(() => ['SISWA', 'SEKRETARIS'].includes(authStore.user?.role || ''))
const search = ref('')
const now = new Date()
const currentYearMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const selectedMonth = ref(currentYearMonth)
const materials = ref<any[]>([])
const activities = ref<any[]>([])
const selectedMaterial = ref<any>(null)
const selectedActivity = ref<any>(null)
const showScheduleModal = ref(false)
const showAddActivityModal = ref(false)
const submittingActivity = ref(false)
const successMsg = ref('')

const activityForm = ref({
  schedule_id: '' as string | number,
  activity: '',
  notes: ''
})

const todayFormatted = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(new Date())
})

const todaySchedules = ref<any[]>([])

const filteredMaterials = computed(() => {
  if (!search.value) return materials.value
  const q = search.value.toLowerCase()
  return materials.value.filter(m =>
    (m.topic && m.topic.toLowerCase().includes(q)) ||
    (m.subject && m.subject.toLowerCase().includes(q)) ||
    (m.teacher && m.teacher.toLowerCase().includes(q))
  )
})

const filteredActivities = computed(() => {
  if (!search.value) return activities.value
  const q = search.value.toLowerCase()
  return activities.value.filter(a =>
    (a.activity && a.activity.toLowerCase().includes(q)) ||
    (a.subject && a.subject.toLowerCase().includes(q)) ||
    (a.created_by && a.created_by.toLowerCase().includes(q))
  )
})

const filterBySubject = (subjectName: string) => {
  search.value = subjectName.split('&')[0].trim()
}

const selectScheduleAndFilter = (subjectName: string) => {
  filterBySubject(subjectName)
  showScheduleModal.value = false
}

const fetchSchedules = async () => {
  try {
    const res = await $fetch<any[]>(`${authStore.apiBase}/schedules`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (res && res.length > 0) {
      todaySchedules.value = res.map((s: any) => ({
        id: s.id,
        time: `${s.start_time || '07:30'} - ${s.end_time || '09:00'}`,
        subject: s.subject?.name || 'Mata Pelajaran',
        teacher: s.teacher?.name || 'Guru Pengajar',
        room: s.room || 'Lab. 1',
        day: s.day || 'Senin'
      }))
      if (!activityForm.value.schedule_id && todaySchedules.value.length > 0) {
        activityForm.value.schedule_id = todaySchedules.value[0].id
      }
    }
  } catch (e) {
    console.warn('API schedules note:', e)
  }
}

const fetchMaterials = async () => {
  try {
    const res = await $fetch<any[]>(`${authStore.apiBase}/journals/material?month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    materials.value = res && res.length > 0 ? res : []
  } catch (e) {
    console.warn('API materials note:', e)
    materials.value = []
  }
}

const fetchActivities = async () => {
  try {
    const res = await $fetch<any>(`${authStore.apiBase}/journals/class?type=personal&month=${selectedMonth.value}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    activities.value = res?.data || []
  } catch (e) {
    console.warn('API activities note:', e)
    activities.value = []
  }
}

const fetchAll = () => {
  fetchMaterials()
  fetchActivities()
}

const submitStudentActivity = async () => {
  submittingActivity.value = true
  successMsg.value = ''
  try {
    const res: any = await $fetch(`${authStore.apiBase}/journals/class`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        schedule_id: activityForm.value.schedule_id,
        activity: activityForm.value.activity,
        notes: activityForm.value.notes,
        type: 'personal'
      }
    })
    successMsg.value = res?.message || 'Catatan kegiatan belajar berhasil dilaporkan dan otomatis terpantau oleh Orang Tua serta Wali Kelas!'
    activityForm.value.activity = ''
    activityForm.value.notes = ''
    showAddActivityModal.value = false
    await fetchActivities()
  } catch (e: any) {
    toast.error(e?.data?.message || 'Gagal menyimpan catatan kegiatan.')
  } finally {
    submittingActivity.value = false
  }
}

onMounted(() => {
  fetchSchedules()
  fetchAll()
})
</script>
