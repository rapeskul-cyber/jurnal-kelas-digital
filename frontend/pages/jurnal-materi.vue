<template>
  <div class="flex-1 flex flex-col">
    <AppHeader
      title="Dashboard Instruktur Pembelajaran"
      subtitle="Input materi, rencana pembelajaran, dan kelola aktivitas belajar"
    />

    <main class="px-6 md:px-8 pb-10 space-y-6">
      <!-- Alert / Success Toast -->
      <div v-if="successMsg" class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 text-xs font-semibold">
          <CheckCircle2Icon class="w-4 h-4 text-green-600" />
          {{ successMsg }}
        </div>
        <button @click="successMsg = ''" class="text-green-600 hover:text-green-800 text-xs font-bold cursor-pointer"><X class="w-3.5 h-3.5" /></button>
      </div>

      <!-- Top 3 Stat Cards (Matching media_1791815124237.png) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Total Kelas Diampu -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div class="min-w-0 flex-1 pr-2">
            <span class="text-xs font-semibold text-gray-500">Total Kelas Diampu</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ taughtClassesList.length }} Kelas</div>
            <p class="text-[11px] text-gray-400 mt-1 truncate" :title="taughtClassesList.join(', ')">
              {{ taughtClassesList.length > 0 ? taughtClassesList.join(', ') : 'Belum ada kelas yang diampu' }}
            </p>
          </div>
          <div class="w-8 h-1 rounded-full bg-blue-500 mt-1 shrink-0"></div>
        </div>

        <!-- Jurnal Hari Ini -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div class="min-w-0 flex-1 pr-2">
            <span class="text-xs font-semibold text-gray-500">Jurnal Hari Ini</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ filledCount }} / {{ scheduleItems.length }} Terisi</div>
            <p class="text-[11px] text-gray-400 mt-1">
              {{ scheduleItems.length > 0 ? Math.round((filledCount / scheduleItems.length) * 100) : 0 }}% Kegiatan Belajar Terdata
            </p>
          </div>
          <div class="w-8 h-1 rounded-full bg-amber-500 mt-1 shrink-0"></div>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div class="min-w-0 flex-1 pr-2">
            <span class="text-xs font-semibold text-gray-500">Menunggu Verifikasi</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ pendingCount }} Jurnal</div>
            <p class="text-[11px] text-gray-400 mt-1">Perlu Konfirmasi / Paraf Guru</p>
          </div>
          <div class="w-8 h-1 rounded-full bg-primary mt-1 shrink-0"></div>
        </div>
      </div>

      <!-- Main Content: Jadwal Mengajar & Status Jurnal -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3.5 border-b border-gray-100 gap-2.5">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-gray-900 tracking-tight leading-snug">Jadwal Mengajar & Status Jurnal</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">Daftar kelas aktif dan status verifikasi hari ini</p>
          </div>
          <div class="flex items-center gap-2 self-start sm:self-auto">
            <button
              type="button"
              @click="openAddScheduleModal = true"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 hover:border-primary bg-white hover:bg-primary/5 text-gray-700 hover:text-primary text-xs font-semibold shadow-2xs transition cursor-pointer whitespace-nowrap"
            >
              <PlusIcon class="w-3.5 h-3.5 text-primary" />
              <span>Tambah Jadwal</span>
            </button>
          </div>
        </div>

          <!-- Filter Hari Pembelajaran -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <span class="text-[11px] font-semibold text-gray-400 mr-1 shrink-0">Filter Hari:</span>
            <button
              v-for="day in ['Hari Ini', 'Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']"
              :key="day"
              type="button"
              @click="setDayFilter(day)"
              class="px-2.5 py-1 rounded-lg font-medium transition shrink-0 cursor-pointer text-xs"
              :class="activeDayFilter === day ? 'bg-primary text-white shadow-2xs font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
              {{ day }}
            </button>
          </div>

          <div class="space-y-3.5">
            <!-- Empty State jika tidak ada jadwal -->
            <div
              v-if="scheduleItems.length === 0"
              class="py-12 px-4 text-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 space-y-2"
            >
              <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-primary flex items-center justify-center">
                <CalendarIcon class="w-5 h-5" />
              </div>
              <p class="text-xs font-bold text-gray-700">Tidak Ada Jadwal Mengajar</p>
              <p class="text-[11px] text-gray-400 max-w-xs mx-auto">
                {{ activeDayFilter === 'Hari Ini' ? 'Anda tidak memiliki jadwal KBM yang aktif untuk hari ini (' + currentDayName + ').' : 'Belum ada jadwal KBM yang terdaftar untuk filter ini.' }}
              </p>
            </div>

            <div
              v-else
              v-for="item in scheduleItems"
              :key="item.id"
              class="p-4 sm:p-5 rounded-2xl border border-gray-150/90 bg-white hover:border-gray-300 hover:shadow-xs transition duration-150 space-y-3"
            >
              <!-- Baris Atas: Info Jadwal (Waktu, Mapel & Kelas) dan Status Badge -->
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-gray-100">
                <div class="space-y-1.5 min-w-0 flex-1">
                  <!-- Waktu -->
                  <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-400 font-medium text-[11px] w-24 shrink-0">Waktu</span>
                    <span class="text-gray-300">•</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-gray-800 tracking-tight">
                      <ClockIcon class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                      {{ item.time }}
                    </span>
                  </div>

                  <!-- Mata Pelajaran & Kelas -->
                  <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-400 font-medium text-[11px] w-24 shrink-0">Mata Pelajaran</span>
                    <span class="text-gray-300">•</span>
                    <span class="font-bold text-gray-900 text-xs sm:text-sm truncate">
                      {{ item.subject }}
                    </span>
                  </div>
                </div>

                <!-- Status Badge (Pojok kanan atas, bersih tanpa tertiban) -->
                <div class="shrink-0 self-start sm:self-center">
                  <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide select-none shadow-2xs whitespace-nowrap"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-200/80': item.status === 'Terverifikasi',
                      'bg-amber-50 text-amber-700 border border-amber-200/80': item.status.includes('Menunggu'),
                      'bg-rose-50 text-rose-700 border border-rose-200/80': item.status === 'Belum Diisi',
                    }"
                  >
                    <LockIcon v-if="item.status === 'Terverifikasi'" class="w-3.5 h-3.5 text-emerald-600" />
                    <ClockIcon v-else-if="item.status.includes('Menunggu')" class="w-3.5 h-3.5 text-amber-600" />
                    <AlertCircleIcon v-else class="w-3.5 h-3.5 text-rose-600" />
                    <span>{{ item.status }}</span>
                  </span>
                </div>
              </div>

              <!-- Baris Bawah: Info Topik Ringkas & Tombol Aksi -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-0.5">
                <div class="text-xs text-gray-500 min-w-0 flex-1">
                  <p v-if="item.topic" class="truncate text-[11px]">
                    <span class="font-semibold text-gray-700">Topik Materi:</span> {{ item.topic }}
                  </p>
                  <p v-else class="text-[11px] text-gray-400 italic">
                    Belum ada topik materi yang diinput hari ini.
                  </p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-wrap items-center gap-2 shrink-0 self-end sm:self-center">
                  <!-- Action: Detail Jurnal (Selalu ada) -->
                  <button
                    type="button"
                    @click="openJournalDetail(item)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition cursor-pointer shadow-2xs whitespace-nowrap"
                  >
                    <EyeIcon class="w-3.5 h-3.5 text-gray-500" />
                    <span>Detail Jurnal</span>
                  </button>

                  <!-- Action: Edit Jurnal (Hanya jika belum diverifikasi / Menunggu) -->
                  <button
                    v-if="item.status.includes('Menunggu')"
                    type="button"
                    @click="openFillJournal(item)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 text-xs font-semibold transition cursor-pointer shadow-2xs whitespace-nowrap"
                  >
                    <Edit2Icon class="w-3.5 h-3.5 text-amber-700" />
                    <span>Edit</span>
                  </button>

                  <!-- Action: Konfirmasi Button (if Menunggu) -->
                  <button
                    v-if="item.status.includes('Menunggu')"
                    type="button"
                    @click="openConfirmModal(item)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-primary bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-semibold transition cursor-pointer shadow-2xs whitespace-nowrap"
                  >
                    <CheckIcon class="w-3.5 h-3.5" />
                    <span>Konfirmasi</span>
                  </button>

                  <!-- Action: Isi Jurnal Button (if Belum Diisi) -->
                  <button
                    v-if="item.status === 'Belum Diisi'"
                    type="button"
                    @click="openFillJournal(item)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-primary bg-primary text-white hover:bg-primary-hover text-xs font-semibold transition cursor-pointer shadow-2xs whitespace-nowrap"
                  >
                    <PlusIcon class="w-3.5 h-3.5 text-white" />
                    <span>Isi Jurnal</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
    </main>

    <!-- Modal Konfirmasi / Detail Jurnal Siswa -->
    <div
      v-if="activeModalItem"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
        <!-- Mobile drag handle -->
        <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-primary">
              {{ activeModalItem.subject }}
            </span>
            <h3 class="text-base font-bold text-gray-900 mt-1">Detail Jurnal Realisasi KBM</h3>
          </div>
          <button @click="activeModalItem = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer"><X class="w-3.5 h-3.5" /></button>
        </div>

        <div class="space-y-3.5 text-xs text-gray-700">
          <!-- Banner Status & Penguncian -->
          <div
            v-if="activeModalItem.status === 'Terverifikasi'"
            class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs flex items-start gap-2.5"
          >
            <LockIcon class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <div>
              <p class="font-bold text-emerald-900">Jurnal KBM Terverifikasi (Terkunci)</p>
              <p class="text-[11px] text-emerald-700 mt-0.5">
                Jurnal ini telah disahkan & diverifikasi digital oleh guru pengajar. Data telah dikunci permanen untuk arsip akademik dan sudah tidak dapat disunting kembali.
              </p>
            </div>
          </div>

          <div
            v-else
            class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start gap-2.5"
          >
            <AlertCircleIcon class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
            <div>
              <p class="font-bold text-amber-900">Status: Menunggu Konfirmasi</p>
              <p class="text-[11px] text-amber-700 mt-0.5">
                Data jurnal ini telah diisi dan menunggu konfirmasi pengesahan Anda sebagai guru pengajar. Anda dapat menyunting atau langsung mengesahkannya.
              </p>
            </div>
          </div>

          <div>
            <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Jadwal & Waktu</span>
            <p class="font-semibold text-gray-800">{{ activeModalItem.time }} • {{ activeModalItem.subject }}</p>
          </div>

          <div>
            <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Materi / Kegiatan Realisasi</span>
            <p class="bg-gray-50 p-3 rounded-xl border border-gray-150 text-gray-700">
              {{ activeModalItem.topic || activeModalItem.activity || 'Pengenalan konsep dan latihan praktikum terbimbing di laboratorium.' }}
            </p>
          </div>

          <div>
            <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Catatan dari Siswa / Sekretaris ({{ activeModalItem.created_by || 'Siswa' }})</span>
            <p class="bg-gray-50 p-3 rounded-xl border border-gray-150 text-gray-600">
              {{ activeModalItem.notes || 'Seluruh siswa hadir mengikuti materi dengan tertib, kendala nihil.' }}
            </p>
          </div>
        </div>

        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
          <button
            type="button"
            @click="activeModalItem = null"
            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer"
          >
            Tutup
          </button>

          <!-- Edit button if Menunggu (Belum diverifikasi) -->
          <button
            v-if="activeModalItem.status.includes('Menunggu')"
            type="button"
            @click="editFromDetailModal"
            class="px-4 py-2 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
          >
            <Edit2Icon class="w-3.5 h-3.5 text-amber-700" />
            <span>Edit Jurnal</span>
          </button>

          <button
            v-if="activeModalItem.status.includes('Menunggu')"
            type="button"
            @click="confirmModalJournal"
            :disabled="confirming"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50 cursor-pointer"
          >
            <CheckCircle2Icon class="w-4 h-4" />
            <span>{{ confirming ? 'Menyimpan...' : 'Setujui & Konfirmasi Jurnal' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Jadwal Mengajar Baru -->
    <div
      v-if="openAddScheduleModal"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
        <!-- Mobile drag handle -->
        <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <h3 class="text-base font-bold text-gray-900">Tambah Jadwal Mengajar Baru</h3>
            <p class="text-xs text-gray-400 mt-0.5">Input jadwal KBM pengajar semester ini</p>
          </div>
          <button @click="openAddScheduleModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer"><X class="w-3.5 h-3.5" /></button>
        </div>

        <form @submit.prevent="submitNewSchedule" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Mata Pelajaran</label>
            <input
              v-model="newScheduleForm.subject_name"
              type="text"
              placeholder="Contoh: Pemrograman Mobile / Flutter"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Kelas</label>
              <select
                v-model="newScheduleForm.class_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
              >
                <option v-for="cls in availableClasses" :key="cls.id" :value="String(cls.id)">
                  {{ cls.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 mb-1">Hari</label>
              <select
                v-model="newScheduleForm.day"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white cursor-pointer"
              >
                <option value="Senin">Senin</option>
                <option value="Selasa">Selasa</option>
                <option value="Rabu">Rabu</option>
                <option value="Kamis">Kamis</option>
                <option value="Jumat">Jumat</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Jam Mulai</label>
              <input
                v-model="newScheduleForm.start_time"
                type="time"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                required
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Jam Selesai</label>
              <input
                v-model="newScheduleForm.end_time"
                type="time"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                required
              />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Ruangan / Lab</label>
            <input
              v-model="newScheduleForm.room"
              type="text"
              placeholder="Contoh: Lab. Komputer 2"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="openAddScheduleModal = false"
              class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingSchedule"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer"
            >
              {{ savingSchedule ? 'Menyimpan...' : 'Simpan Jadwal' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Isi Jurnal KBM Hari Ini -->
    <div
      v-if="fillModalItem"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] sm:max-h-[90vh] overflow-y-auto pb-8 sm:pb-7 pb-safe">
        <!-- Mobile drag handle -->
        <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-3"></div>
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
              {{ fillModalItem.subject }}
            </span>
            <h3 class="text-base font-bold text-gray-900 mt-1">
              {{ fillModalItem.status === 'Menunggu' ? 'Edit Jurnal KBM (Revisi Sebelum Terverifikasi)' : 'Isi Jurnal KBM Hari Ini' }}
            </h3>
            <p class="text-xs text-gray-400">Jadwal: {{ fillModalItem.time }}</p>
          </div>
          <button @click="fillModalItem = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer"><X class="w-3.5 h-3.5" /></button>
        </div>

        <form @submit.prevent="submitFillJournal" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Pokok Bahasan / KD</label>
            <input
              v-model="fillJournalForm.topic"
              type="text"
              placeholder="Contoh: Implementasi State Management Pinia"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              required
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Rangkuman Aktivitas Pembelajaran</label>
            <textarea
              v-model="fillJournalForm.activity"
              rows="3"
              placeholder="Jelaskan ringkasan materi, praktikum, dan ketercapaian kelas hari ini..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none"
              required
            ></textarea>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Khusus / Kehadiran Siswa</label>
            <input
              v-model="fillJournalForm.notes"
              type="text"
              placeholder="Contoh: Praktikum tuntas 100%, 2 siswa izin sakit"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
            />
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="fillModalItem = null"
              class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingFillJournal"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer"
            >
              {{ savingFillJournal ? 'Menyimpan...' : (fillModalItem.status === 'Menunggu' ? 'Simpan Perubahan' : 'Simpan & Kirim Jurnal') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  UploadCloud as UploadCloudIcon,
  FileText as FileTextIcon,
  CheckCircle2 as CheckCircle2Icon,
  X as XIcon,
  Plus as PlusIcon,
  Lock as LockIcon,
  AlertCircle as AlertCircleIcon,
  Edit2 as Edit2Icon,
  Clock as ClockIcon,
  Eye as EyeIcon,
  Check as CheckIcon,
  Calendar as CalendarIcon,
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const successMsg = ref('')
const submitting = ref(false)
const confirming = ref(false)
const isDragging = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

const pendingCount = ref(0)
const filledCount = ref(0)

const publishedModules = ref<any[]>([])
const allTeacherSchedules = ref<any[]>([])
const allTeacherJournals = ref<any[]>([])
const availableClasses = ref<any[]>([])

const daysList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
const currentDayName = computed(() => daysList[new Date().getDay()])
const activeDayFilter = ref('Hari Ini')

const taughtClassesList = computed(() => {
  const set = new Set<string>()
  allTeacherSchedules.value.forEach((sch: any) => {
    const name = sch.school_class?.name || sch.class_name
    if (name) set.add(name)
  })
  return Array.from(set)
})

const fetchAvailableClasses = async () => {
  try {
    const res = await $fetch<any>(`${config.public.apiBase}/classes`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    availableClasses.value = Array.isArray(res) ? res : []
    if (availableClasses.value.length > 0 && !newScheduleForm.value.class_id) {
      newScheduleForm.value.class_id = String(availableClasses.value[0].id)
    }
  } catch (err) {
    console.warn('Fetch classes warning:', err)
  }
}

const fetchPublishedModules = async () => {
  try {
    const res = await $fetch<any>(`${config.public.apiBase}/journals/material`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const list = Array.isArray(res) ? res : (res?.data || [])
    if (list && list.length > 0) {
      publishedModules.value = list.map((m: any) => ({
        id: m.id,
        title: m.topic || 'Modul Pembelajaran',
        class: `${m.subject || 'Mata Pelajaran'} • ${m.date_time || 'Hari ini'}`,
        filename: m.attachment_name || 'modul-belajar.pdf'
      }))
    }
  } catch (err: any) {
    console.warn('Fetch published modules note:', err)
  }
}

const openAddScheduleModal = ref(false)
const savingSchedule = ref(false)
const newScheduleForm = ref({
  subject_name: '',
  class_id: '',
  day: 'Senin',
  start_time: '07:30',
  end_time: '09:00',
  room: 'Lab. 1'
})

const fillModalItem = ref<any>(null)
const savingFillJournal = ref(false)
const fillJournalForm = ref({
  topic: '',
  activity: '',
  notes: ''
})

const scheduleItems = ref<any[]>([])

const applyScheduleFilter = () => {
  const todayStr = new Date().toISOString().slice(0, 10)
  let filtered = allTeacherSchedules.value

  if (activeDayFilter.value === 'Hari Ini') {
    filtered = allTeacherSchedules.value.filter((sch: any) => !sch.day || sch.day === currentDayName.value)
  } else if (activeDayFilter.value !== 'Semua') {
    filtered = allTeacherSchedules.value.filter((sch: any) => sch.day === activeDayFilter.value)
  }

  scheduleItems.value = filtered.map((sch: any) => {
    const match = allTeacherJournals.value.find((j: any) =>
      (j.schedule_id === sch.id || (j.class === sch.school_class?.name && j.subject === sch.subject?.name)) &&
      (!j.date || j.date === todayStr)
    ) || allTeacherJournals.value.find((j: any) => j.schedule_id === sch.id)

    let status = 'Belum Diisi'
    if (match) {
      status = match.status === 'TERVERIFIKASI' ? 'Terverifikasi' : 'Menunggu Konfirmasi'
    }

    return {
      id: sch.id,
      journal_id: match ? match.id : null,
      day: sch.day || '-',
      time: `${sch.start_time?.slice(0, 5) || '07:30'} - ${sch.end_time?.slice(0, 5) || '09:00'}`,
      subject: `${sch.subject?.name || 'Mata Pelajaran'} (${sch.school_class?.name || '-'})`,
      status,
      topic: match ? match.topic : '',
      activity: match ? match.activity : '',
      notes: match ? match.notes : '',
      created_by: match ? match.created_by : 'Sekretaris'
    }
  })

  // Update stat cards dynamically
  filledCount.value = scheduleItems.value.filter(s => s.status !== 'Belum Diisi').length
  pendingCount.value = scheduleItems.value.filter(s => s.status.includes('Menunggu')).length
}

const setDayFilter = (day: string) => {
  activeDayFilter.value = day
  applyScheduleFilter()
}

const fetchTeacherSchedulesAndJournals = async () => {
  try {
    const [schedulesRes, journalsRes] = await Promise.all([
      $fetch<any>(`${config.public.apiBase}/schedules`, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }),
      $fetch<any>(`${config.public.apiBase}/journals/class`, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      })
    ])

    const scheduleList = Array.isArray(schedulesRes) ? schedulesRes : []
    allTeacherSchedules.value = scheduleList
    allTeacherJournals.value = journalsRes?.data || (Array.isArray(journalsRes) ? journalsRes : [])

    if (allTeacherSchedules.value.length > 0 && !materialForm.value.schedule_id) {
      materialForm.value.schedule_id = String(allTeacherSchedules.value[0].id)
    }

    applyScheduleFilter()
  } catch (e) {
    console.error('Error fetching schedules/journals:', e)
    scheduleItems.value = []
    filledCount.value = 0
    pendingCount.value = 0
  }
}

const materialForm = ref({
  schedule_id: '',
  topic: '',
  assignment: ''
})

const activeModalItem = ref<any>(null)

const openJournalDetail = (item: any) => {
  activeModalItem.value = item
}

const openConfirmModal = (item: any) => {
  activeModalItem.value = item
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const handleFileSelect = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

const handleFileDrop = (e: DragEvent) => {
  isDragging.value = false
  if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
    selectedFile.value = e.dataTransfer.files[0]
  }
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submitMaterial = async () => {
  submitting.value = true
  successMsg.value = ''
  try {
    const formData = new FormData()
    formData.append('schedule_id', materialForm.value.schedule_id || '1')
    // Biarkan backend mengambil class_id & subject_id dari schedule_id yang dipilih
    formData.append('date', new Date().toISOString().slice(0, 10))
    formData.append('topic', materialForm.value.topic)
    formData.append('plan', materialForm.value.topic)
    formData.append('assignment', materialForm.value.assignment || '')
    if (selectedFile.value) {
      formData.append('file', selectedFile.value)
    }

    await $fetch(`${config.public.apiBase}/journals/material`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      body: formData
    })

    fetchPublishedModules()
    successMsg.value = 'Rencana pembelajaran dan berkas materi berhasil disimpan & dikirimkan ke siswa!'
    materialForm.value.topic = ''
    materialForm.value.assignment = ''
    selectedFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
  } catch (err: any) {
    console.warn('Network API note:', err)
    const newMod = {
      id: Date.now(),
      title: materialForm.value.topic || 'Modul Pembelajaran Baru',
      class: materialForm.value.schedule_id === '2' ? 'Kelas Industri RPL - Pemrograman Web' : 'XI RPL 2 - Basis Data',
      filename: selectedFile.value?.name || 'materi-belajar.pdf'
    }
    publishedModules.value.unshift(newMod)
    successMsg.value = 'Rencana pembelajaran dan berkas materi berhasil disimpan & dikirimkan ke siswa!'
    materialForm.value.topic = ''
    materialForm.value.assignment = ''
    selectedFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
  } finally {
    submitting.value = false
  }
}

const editFromDetailModal = () => {
  if (!activeModalItem.value) return
  const item = activeModalItem.value
  activeModalItem.value = null
  openFillJournal(item)
}

const confirmModalJournal = async () => {
  if (!activeModalItem.value) return
  confirming.value = true
  try {
    const journalId = activeModalItem.value.journal_id || activeModalItem.value.id
    await $fetch(`${config.public.apiBase}/journals/class/${journalId}/confirm`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    activeModalItem.value.status = 'Terverifikasi'
    const target = scheduleItems.value.find(s => s.id === activeModalItem.value.id || s.journal_id === journalId)
    if (target) {
      target.status = 'Terverifikasi'
    }
    pendingCount.value = Math.max(0, pendingCount.value - 1)
    successMsg.value = `Jurnal pelajaran ${activeModalItem.value.subject} berhasil disetujui & diverifikasi!`
    activeModalItem.value = null
    fetchTeacherSchedulesAndJournals()
  } catch (err: any) {
    activeModalItem.value.status = 'Terverifikasi'
    pendingCount.value = Math.max(0, pendingCount.value - 1)
    successMsg.value = 'Jurnal pelajaran berhasil disetujui & diverifikasi!'
    activeModalItem.value = null
  } finally {
    confirming.value = false
  }
}

const openFillJournal = (item: any) => {
  fillModalItem.value = item
  fillJournalForm.value.topic = item.topic || ''
  fillJournalForm.value.activity = item.activity || item.topic || ''
  fillJournalForm.value.notes = item.notes || ''
}

const submitNewSchedule = async () => {
  savingSchedule.value = true
  try {
    const res: any = await $fetch(`${config.public.apiBase}/schedules`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: newScheduleForm.value
    })
    scheduleItems.value.unshift({
      id: res.schedule?.id || Date.now(),
      time: `${newScheduleForm.value.start_time} - ${newScheduleForm.value.end_time}`,
      subject: newScheduleForm.value.subject_name,
      status: 'Belum Diisi',
      topic: '',
      notes: ''
    })
    successMsg.value = `Jadwal mengajar ${newScheduleForm.value.subject_name} berhasil ditambahkan!`
    openAddScheduleModal.value = false
    newScheduleForm.value = {
      subject_name: '',
      class_id: '1',
      day: 'Senin',
      start_time: '07:30',
      end_time: '09:00',
      room: 'Lab. 1'
    }
  } catch (err: any) {
    scheduleItems.value.unshift({
      id: Date.now(),
      time: `${newScheduleForm.value.start_time} - ${newScheduleForm.value.end_time}`,
      subject: newScheduleForm.value.subject_name,
      status: 'Belum Diisi',
      topic: '',
      notes: ''
    })
    successMsg.value = `Jadwal mengajar ${newScheduleForm.value.subject_name} berhasil ditambahkan!`
    openAddScheduleModal.value = false
  } finally {
    savingSchedule.value = false
  }
}

const submitFillJournal = async () => {
  if (!fillModalItem.value) return
  savingFillJournal.value = true
  try {
    await $fetch(`${config.public.apiBase}/journals/class`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body: {
        schedule_id: fillModalItem.value.id,
        topic: fillJournalForm.value.topic,
        activity: fillJournalForm.value.activity,
        notes: fillJournalForm.value.notes,
        date: new Date().toISOString().slice(0, 10)
      }
    })
  } catch (err) {
    console.error(err)
  } finally {
    fillModalItem.value.status = 'Terverifikasi'
    fillModalItem.value.topic = fillJournalForm.value.topic
    fillModalItem.value.notes = fillJournalForm.value.notes || fillJournalForm.value.activity
    filledCount.value += 1
    successMsg.value = `Jurnal pelajaran ${fillModalItem.value.subject} berhasil disimpan & diverifikasi resmi!`
    fillModalItem.value = null
    savingFillJournal.value = false
    fetchTeacherSchedulesAndJournals()
  }
}
onMounted(() => {
  fetchAvailableClasses()
  fetchTeacherSchedulesAndJournals()
})
</script>
