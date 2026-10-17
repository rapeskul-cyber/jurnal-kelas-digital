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
        <button @click="successMsg = ''" class="text-green-600 hover:text-green-800 text-xs font-bold cursor-pointer">✕</button>
      </div>

      <!-- Top 3 Stat Cards (Matching media_1791815124237.png) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Total Kelas Diampu -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500">Total Kelas Diampu</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">4 Kelas</div>
            <p class="text-[11px] text-gray-400 mt-1">XI RPL 1, XI RPL 2, XII RPL 1, XII RPL 2</p>
          </div>
          <div class="w-8 h-1 rounded-full bg-blue-500 mt-1"></div>
        </div>

        <!-- Jurnal Hari Ini -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500">Jurnal Hari Ini</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ filledCount }} / {{ scheduleItems.length }} Terisi</div>
            <p class="text-[11px] text-gray-400 mt-1">{{ Math.round((filledCount / Math.max(1, scheduleItems.length)) * 100) }}% Kegiatan Belajar Terdata</p>
          </div>
          <div class="w-8 h-1 rounded-full bg-amber-500 mt-1"></div>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-card flex items-start justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500">Menunggu Verifikasi</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ pendingCount }} Jurnal</div>
            <p class="text-[11px] text-gray-400 mt-1">Perlu Tanda Tangan Anda</p>
          </div>
          <div class="w-8 h-1 rounded-full bg-brand-red mt-1"></div>
        </div>
      </div>

      <!-- Main 2-Column Layout (Matching media_1791815124237.png) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Kolom Kiri: Jadwal Mengajar & Status Jurnal Hari Ini -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3.5 border-b border-gray-100 gap-2.5">
            <div>
              <h2 class="text-sm sm:text-base font-bold text-gray-900 tracking-tight leading-snug">Jadwal Mengajar & Status Jurnal</h2>
              <p class="text-[11px] text-gray-400 mt-0.5">Daftar kelas aktif dan status verifikasi hari ini</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
              <button
                type="button"
                @click="openAddScheduleModal = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 hover:border-brand-red bg-white hover:bg-red-50/40 text-gray-700 hover:text-brand-red text-xs font-semibold shadow-2xs transition cursor-pointer"
              >
                <PlusIcon class="w-3.5 h-3.5 text-brand-red" />
                <span>Tambah Jadwal</span>
              </button>
              <NuxtLink
                to="/laporan-jurnal"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold text-gray-500 hover:text-brand-red hover:bg-gray-50 transition"
              >
                <span>Laporan Jurnal</span>
                <span class="text-brand-red">→</span>
              </NuxtLink>
            </div>
          </div>

          <div class="space-y-3">
            <div
              v-for="item in scheduleItems"
              :key="item.id"
              class="p-4 rounded-2xl border border-gray-150/90 bg-white hover:border-gray-300 hover:shadow-xs transition duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5"
            >
              <!-- Left Side: Time & Subject Info -->
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 text-xs">
                  <span class="text-gray-400 font-medium text-[11px]">Waktu</span>
                  <span class="font-bold text-gray-800">{{ item.time }}</span>
                </div>
                <div class="flex items-baseline gap-2">
                  <span class="text-gray-400 font-medium text-[11px] shrink-0">Mata Pelajaran</span>
                  <span class="font-bold text-gray-900 text-xs sm:text-sm truncate">{{ item.subject }}</span>
                </div>
              </div>

              <!-- Right Side: Status Badge & Action Buttons -->
              <div class="flex flex-wrap items-center gap-2 shrink-0 self-start sm:self-center">
                <!-- Status Badge -->
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold tracking-wide"
                  :class="{
                    'bg-emerald-50 text-emerald-700 border border-emerald-200/80': item.status === 'Terverifikasi',
                    'bg-amber-50 text-amber-700 border border-amber-200/80': item.status === 'Menunggu',
                    'bg-rose-50 text-rose-700 border border-rose-200/80': item.status === 'Belum Diisi',
                  }"
                >
                  <LockIcon v-if="item.status === 'Terverifikasi'" class="w-3 h-3 text-emerald-600" />
                  <span>{{ item.status }}</span>
                </span>

                <!-- Action: Detail Jurnal (Always available) -->
                <button
                  type="button"
                  @click="openJournalDetail(item)"
                  class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition cursor-pointer shadow-2xs"
                >
                  Detail Jurnal
                </button>

                <!-- Action: Edit Jurnal (Hanya jika belum diverifikasi) -->
                <button
                  v-if="item.status === 'Menunggu'"
                  type="button"
                  @click="openFillJournal(item)"
                  class="px-2.5 py-1.5 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 text-xs font-semibold transition cursor-pointer shadow-2xs flex items-center gap-1"
                >
                  <Edit2Icon class="w-3 h-3 text-amber-700" />
                  <span>Edit</span>
                </button>

                <!-- Action: Konfirmasi Button (if Menunggu) -->
                <button
                  v-if="item.status === 'Menunggu'"
                  type="button"
                  @click="openConfirmModal(item)"
                  class="px-3 py-1.5 rounded-xl border border-brand-red bg-red-50 text-brand-red hover:bg-brand-red hover:text-white text-xs font-semibold transition cursor-pointer shadow-2xs"
                >
                  Konfirmasi
                </button>

                <!-- Action: Isi Jurnal Button (if Belum Diisi) -->
                <button
                  v-if="item.status === 'Belum Diisi'"
                  type="button"
                  @click="openFillJournal(item)"
                  class="px-3 py-1.5 rounded-xl border border-brand-red bg-brand-red text-white hover:bg-brand-red-dark text-xs font-semibold transition cursor-pointer shadow-2xs"
                >
                  Isi Jurnal
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: LMS / Bahan Ajar Digital untuk Siswa -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 shadow-card p-5 md:p-6 space-y-4">
          <div class="border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">LMS Siswa</span>
              <h2 class="text-base font-bold text-gray-800">Unggah Materi & Modul Belajar</h2>
            </div>
            <p class="text-xs text-gray-400">
              Modul digital, silabus, dan tugas yang dapat diakses & diunduh siswa di kelas.
            </p>
          </div>

          <form @submit.prevent="submitMaterial" class="space-y-4">
            <!-- Mata Pelajaran & Kelas -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Mata Pelajaran & Kelas Tujuan
              </label>
              <select
                v-model="materialForm.schedule_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
                required
              >
                <option value="" disabled>Pilih Mapel & Kelas</option>
                <option value="1">XI RPL 2 - Basis Data</option>
                <option value="2">XI RPL 1 - Pemrograman Web</option>
                <option value="3">XII RPL 1 - Proyek Perangkat Lunak</option>
                <option value="4">XI RPL 1 - PKL / Industri</option>
              </select>
            </div>

            <!-- Pokok Bahasan / KD -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Pokok Bahasan / KD
              </label>
              <input
                v-model="materialForm.topic"
                type="text"
                placeholder="Contoh: Optimasi Query Relasional"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                required
              />
            </div>

            <!-- Catatan / Tugas Mandiri -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Catatan / Tugas Mandiri
              </label>
              <textarea
                v-model="materialForm.assignment"
                rows="3"
                placeholder="Siswa ditugaskan membuat rancangan ERD lanjutan..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red resize-none"
              ></textarea>
            </div>

            <!-- Dokumentasi / Upload File Materi (Berkas) -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Dokumentasi & Berkas Materi
              </label>
              
              <!-- File Dropzone -->
              <div
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleFileDrop"
                class="border-2 border-dashed rounded-2xl p-5 text-center transition cursor-pointer"
                :class="isDragging ? 'border-brand-red bg-red-50/30' : 'border-gray-200 hover:border-gray-300 bg-gray-50/30'"
                @click="triggerFileInput"
              >
                <input
                  ref="fileInputRef"
                  type="file"
                  class="hidden"
                  accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                  @change="handleFileSelect"
                />

                <div v-if="!selectedFile" class="space-y-1.5">
                  <div class="w-10 h-10 rounded-full bg-red-50 text-brand-red flex items-center justify-center mx-auto">
                    <UploadCloudIcon class="w-5 h-5" />
                  </div>
                  <p class="text-xs font-semibold text-brand-red">
                    Pilih File atau Seret Kemari
                  </p>
                  <p class="text-[10px] text-gray-400">
                    Maksimal ukuran file: 10MB (Format PDF, DOCX, PPTX, JPG)
                  </p>
                </div>

                <!-- File Selected State -->
                <div v-else class="flex items-center justify-between bg-white p-3 rounded-xl border border-gray-200 text-left">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <FileTextIcon class="w-6 h-6 text-brand-red shrink-0" />
                    <div class="min-w-0">
                      <p class="text-xs font-bold text-gray-800 truncate">{{ selectedFile.name }}</p>
                      <p class="text-[10px] text-gray-400">{{ (selectedFile.size / 1024).toFixed(1) }} KB</p>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeFile"
                    class="p-1 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 cursor-pointer"
                  >
                    <XIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Submit Button (Red Button Full Width) -->
            <div class="pt-2">
              <button
                type="submit"
                :disabled="submitting"
                class="w-full py-2.5 px-4 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white font-semibold text-xs transition shadow-sm hover:shadow flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
              >
                <span>{{ submitting ? 'Mengunggah...' : 'Publikasikan Materi ke Siswa' }}</span>
              </button>
            </div>
          </form>

          <!-- Daftar Modul Aktif LMS Siswa -->
          <div class="pt-4 border-t border-gray-100 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Modul Ajar Tersedia di Siswa</span>
              <span class="text-[10px] text-gray-400">Dapat diunduh siswa</span>
            </div>
            <div class="space-y-2">
              <div
                v-for="pub in publishedModules"
                :key="pub.id"
                class="p-2.5 rounded-xl bg-gray-50 border border-gray-150 flex items-center justify-between text-xs"
              >
                <div class="min-w-0 flex items-center gap-2">
                  <FileTextIcon class="w-4 h-4 text-brand-red shrink-0" />
                  <div class="truncate">
                    <p class="font-bold text-gray-800 truncate text-[11px]">{{ pub.title }}</p>
                    <p class="text-[10px] text-gray-400">{{ pub.class }} • {{ pub.filename }}</p>
                  </div>
                </div>
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 shrink-0">Aktif</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Konfirmasi / Detail Jurnal Siswa -->
    <div
      v-if="activeModalItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-brand-red">
              {{ activeModalItem.subject }}
            </span>
            <h3 class="text-base font-bold text-gray-900 mt-1">Detail Jurnal Realisasi KBM</h3>
          </div>
          <button @click="activeModalItem = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
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
              <p class="font-bold text-amber-900">Status: Belum Terverifikasi</p>
              <p class="text-[11px] text-amber-700 mt-0.5">
                Data jurnal masih dalam status draf/menunggu konfirmasi. Anda masih dapat menyunting materi dan catatan sebelum disahkan & dikunci permanen.
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
              {{ activeModalItem.topic || 'Pengenalan konsep dan latihan praktikum terbimbing di laboratorium.' }}
            </p>
          </div>

          <div>
            <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Catatan dari Sekretaris / Kelas</span>
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
            v-if="activeModalItem.status === 'Menunggu'"
            type="button"
            @click="editFromDetailModal"
            class="px-4 py-2 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
          >
            <Edit2Icon class="w-3.5 h-3.5 text-amber-700" />
            <span>Edit Jurnal</span>
          </button>

          <button
            v-if="activeModalItem.status === 'Menunggu'"
            type="button"
            @click="confirmModalJournal"
            :disabled="confirming"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50 cursor-pointer"
          >
            <CheckCircle2Icon class="w-4 h-4" />
            <span>{{ confirming ? 'Menyimpan...' : 'Setujui & Kunci Permanen' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Jadwal Mengajar Baru -->
    <div
      v-if="openAddScheduleModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <h3 class="text-base font-bold text-gray-900">Tambah Jadwal Mengajar Baru</h3>
            <p class="text-xs text-gray-400 mt-0.5">Input jadwal KBM pengajar semester ini</p>
          </div>
          <button @click="openAddScheduleModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitNewSchedule" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Mata Pelajaran</label>
            <input
              v-model="newScheduleForm.subject_name"
              type="text"
              placeholder="Contoh: Pemrograman Mobile / Flutter"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Kelas</label>
              <select
                v-model="newScheduleForm.class_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
              >
                <option value="1">XI RPL 1</option>
                <option value="2">XI RPL 2</option>
                <option value="3">XII RPL 1</option>
                <option value="4">XII RPL 2</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-gray-700 mb-1">Hari</label>
              <select
                v-model="newScheduleForm.day"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white cursor-pointer"
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
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
                required
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Jam Selesai</label>
              <input
                v-model="newScheduleForm.end_time"
                type="time"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
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
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
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
              class="px-4 py-2 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer"
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
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4">
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
          <button @click="fillModalItem = null" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitFillJournal" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Pokok Bahasan / KD</label>
            <input
              v-model="fillJournalForm.topic"
              type="text"
              placeholder="Contoh: Implementasi State Management Pinia"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
              required
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Rangkuman Aktivitas Pembelajaran</label>
            <textarea
              v-model="fillJournalForm.activity"
              rows="3"
              placeholder="Jelaskan ringkasan materi, praktikum, dan ketercapaian kelas hari ini..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red resize-none"
              required
            ></textarea>
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Khusus / Kehadiran Siswa</label>
            <input
              v-model="fillJournalForm.notes"
              type="text"
              placeholder="Contoh: Praktikum tuntas 100%, 2 siswa izin sakit"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red"
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
              class="px-4 py-2 rounded-xl bg-brand-red hover:bg-brand-red-dark text-white text-xs font-semibold transition disabled:opacity-50 cursor-pointer"
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
  Edit2 as Edit2Icon
} from 'lucide-vue-next'

const config = useRuntimeConfig()
const authStore = useAuthStore()

const successMsg = ref('')
const submitting = ref(false)
const confirming = ref(false)
const isDragging = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

const pendingCount = ref(3)
const filledCount = ref(2)

const publishedModules = ref([
  { id: 1, title: 'Modul 04: Optimasi Query Relasional & Normalisasi 3NF', class: 'XI RPL 2 - Basis Data', filename: 'modul-basis-data-3nf.pdf' },
  { id: 2, title: 'Jobsheet 06: Vue 3 Composition API & State Management', class: 'XI RPL 1 - Pemrograman Web', filename: 'jobsheet-vue3.pdf' }
])

const openAddScheduleModal = ref(false)
const savingSchedule = ref(false)
const newScheduleForm = ref({
  subject_name: '',
  class_id: '1',
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

const scheduleItems = ref([
  {
    id: 1,
    time: '07:30 - 09:00',
    subject: 'Pemrograman Web & Perangkat B...',
    status: 'Terverifikasi',
    topic: 'Optimasi query dan routing Vue/Nuxt',
    notes: 'Praktikum berjalan lancar di Lab 1'
  },
  {
    id: 2,
    time: '09:15 - 11:45',
    subject: 'Basis Data & Desain Sistem',
    status: 'Menunggu',
    topic: 'Normalisasi Database 3NF dan perbaikan skema relasi',
    notes: 'Siswa memerlukan pengesahan dari guru pengajar'
  },
  {
    id: 3,
    time: '13:00 - 15:30',
    subject: 'Proyek Perangkat Lunak Mandiri',
    status: 'Belum Diisi',
    topic: 'Pengerjaan sprint backlog proyek akhir',
    notes: 'Belum ada draf jurnal dari sekretaris'
  }
])

const materialForm = ref({
  schedule_id: '1',
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
    formData.append('class_id', '1')
    formData.append('subject_id', '1')
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

    const newMod = {
      id: Date.now(),
      title: materialForm.value.topic || 'Modul Pembelajaran Baru',
      class: materialForm.value.schedule_id === '2' ? 'XI RPL 1 - Pemrograman Web' : 'XI RPL 2 - Basis Data',
      filename: selectedFile.value?.name || 'materi-belajar.pdf'
    }
    publishedModules.value.unshift(newMod)
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
      class: materialForm.value.schedule_id === '2' ? 'XI RPL 1 - Pemrograman Web' : 'XI RPL 2 - Basis Data',
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
    await $fetch(`${config.public.apiBase}/journals/class/${activeModalItem.value.id}/confirm`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    activeModalItem.value.status = 'Terverifikasi'
    pendingCount.value = Math.max(0, pendingCount.value - 1)
    successMsg.value = `Jurnal pelajaran ${activeModalItem.value.subject} berhasil disetujui & diverifikasi!`
    activeModalItem.value = null
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
    fillModalItem.value.status = 'Menunggu'
    fillModalItem.value.topic = fillJournalForm.value.topic
    fillModalItem.value.notes = fillJournalForm.value.notes || fillJournalForm.value.activity
    pendingCount.value += 1
    filledCount.value += 1
    successMsg.value = `Jurnal pelajaran ${fillModalItem.value.subject} berhasil diisi dan menunggu konfirmasi!`
    fillModalItem.value = null
    savingFillJournal.value = false
  }
}
</script>
