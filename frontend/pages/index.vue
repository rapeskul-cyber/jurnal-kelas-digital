<template>
  <div class="min-h-screen flex flex-col font-sans antialiased bg-white text-slate-800 selection:bg-[#0f47ad] selection:text-white">
    <!-- MAIN NAVBAR (SOLID WHITE, NO TRANSPARENCY, NO HOLOGRAM, EXACTLY 1 LOGIN BUTTON) -->
    <header class="sticky top-0 left-0 right-0 z-40 h-16 sm:h-20 bg-white border-b border-slate-200 select-none shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
        <!-- Logo & Title (Icon menyatu dengan background tanpa border container) -->
        <NuxtLink to="/" class="flex items-center gap-3.5 group cursor-pointer">
          <img
            src="/images/logo-smkn71.png"
            alt="Logo SMKN 71"
            class="w-9 h-9 sm:w-12 sm:h-12 object-contain"
          />
          <div>
            <span class="font-extrabold text-sm sm:text-lg tracking-tight block text-slate-900">
              SMKN 71 JAKARTA
            </span>
            <p class="text-[10px] sm:text-xs font-bold text-[#0f47ad]">
              Jurnal Kelas Digital
            </p>
          </div>
        </NuxtLink>

        <!-- Navigation Links (Standard Font Size) -->
        <nav class="hidden md:flex items-center gap-2 text-sm font-semibold">
          <button
            v-for="link in navLinks"
            :key="link.id"
            type="button"
            @click="scrollToSection(link.id)"
            class="px-4 py-2 rounded-xl transition-colors cursor-pointer"
            :class="[
              activeSection === link.id
                ? 'text-[#0f47ad] font-bold bg-blue-50'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
            ]"
          >
            {{ link.name }}
          </button>
        </nav>

        <!-- Right Controls: SATU-SATUNYA TOMBOL LOGIN DI SELURUH HALAMAN -->
        <div class="flex items-center gap-3">
          <NuxtLink
            v-if="authStore.isAuthenticated"
            to="/dashboard"
            class="px-3.5 py-1.5 sm:px-6 sm:py-2.5 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs sm:text-sm font-bold transition-colors flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-sm active:scale-95"
          >
            <span>Buka Dashboard</span>
            <ChevronRight class="w-4 h-4" />
          </NuxtLink>
          <NuxtLink
            v-else
            to="/login"
            class="px-3.5 py-1.5 sm:px-6 sm:py-2.5 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs sm:text-sm font-bold transition-colors flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-sm active:scale-95"
          >
            <span>Masuk ke Akun</span>
            <ChevronRight class="w-4 h-4" />
          </NuxtLink>

          <!-- Mobile Hamburger -->
          <button
            type="button"
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 cursor-pointer"
            aria-label="Toggle Menu"
          >
            <X v-if="isMobileMenuOpen" class="w-6 h-6" />
            <Menu v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown -->
      <div
        v-if="isMobileMenuOpen"
        class="md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1.5 shadow-lg"
      >
        <button
          v-for="link in navLinks"
          :key="link.id"
          type="button"
          @click="scrollToSection(link.id)"
          class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer"
          :class="[
            activeSection === link.id
              ? 'text-[#0f47ad] bg-blue-50 font-bold'
              : 'text-slate-700 hover:bg-slate-50'
          ]"
        >
          {{ link.name }}
        </button>
      </div>
    </header>

    <!-- HERO SECTION (CLEAN SOLID BLUE & WHITE) -->
    <section id="tentang" class="py-8 sm:py-16 lg:py-20 border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
          <!-- Left Column -->
          <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-slate-900">
              Jurnal Kelas Digital <br />
              <span class="text-[#0f47ad]">SMKN 71 JAKARTA</span>
            </h1>

            <p class="text-xs sm:text-sm lg:text-base leading-relaxed text-slate-600 max-w-xl mx-auto lg:mx-0">
              Aplikasi pencatatan agenda materi pembelajaran harian guru, catatan ketertiban kelas, rekapitulasi kehadiran per jam pelajaran oleh sekretaris kelas, dan pelaporan progres belajar siswa secara terpusat tanpa buku jurnal kertas manual.
            </p>

            <!-- Action Buttons: Navigasi scroll tanpa tombol login duplikat -->
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-1">
              <button
                type="button"
                @click="scrollToSection('format-jurnal')"
                class="w-full sm:w-auto px-5 py-2.5 sm:px-7 sm:py-3 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs sm:text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-95"
              >
                <span>Lihat Format Jurnal</span>
                <ChevronRight class="w-4 h-4" />
              </button>

              <button
                type="button"
                @click="scrollToSection('alur')"
                class="w-full sm:w-auto px-5 py-2.5 sm:px-6 sm:py-3 rounded-xl border-2 border-[#0f47ad] text-[#0f47ad] hover:bg-blue-50 text-xs sm:text-sm font-bold cursor-pointer transition-colors"
              >
                Alur Operasional
              </button>
            </div>

            <!-- Key Indicators -->
            <div class="pt-4 sm:pt-6 border-t border-slate-200 grid grid-cols-3 gap-2 sm:gap-4 text-left max-w-md mx-auto lg:mx-0">
              <div>
                <span class="text-sm sm:text-lg font-bold block text-slate-900">Per Jam KBM</span>
                <span class="text-[10px] sm:text-xs text-slate-500">Sesi 1 - 10</span>
              </div>
              <div>
                <span class="text-sm sm:text-lg font-bold block text-slate-900">3 Jurusan</span>
                <span class="text-[10px] sm:text-xs text-slate-500">RPL, Animasi, DKV</span>
              </div>
              <div>
                <span class="text-sm sm:text-lg font-bold block text-slate-900">Paperless</span>
                <span class="text-[10px] sm:text-xs text-slate-500">Arsip Digital</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Real Class Journal Sheet Mockup (Clean Blue & White) -->
          <div class="lg:col-span-6">
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 sm:border-2 p-3.5 sm:p-6 shadow-xs sm:shadow-sm bg-white text-left">
              <!-- Journal Sheet Header -->
              <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                  <span class="text-xs font-bold uppercase tracking-wider text-[#0f47ad]">Lembar Jurnal KBM Harian</span>
                  <h3 class="text-base font-bold text-slate-900">Kelas XII RPL 1</h3>
                </div>
                <div class="text-xs font-mono font-bold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 bg-slate-50 flex items-center gap-2 shadow-2xs">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                  <span>{{ currentRealtimeDate }}</span>
                  <span class="text-[#0f47ad] hidden sm:inline font-mono">• {{ currentRealtimeTime }} WIB</span>
                </div>
              </div>

              <!-- Journal Rows (Real KBM Table) -->
              <div class="py-4 space-y-3 text-xs sm:text-sm">
                <!-- Sesi 1: Selesai -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-1.5">
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-[#0f47ad]">Jam Ke 1 - 4 (06:45 - 09:45)</span>
                    <span class="text-[#0f47ad] font-bold flex items-center gap-1">
                      <CheckCircle2 class="w-4 h-4 text-[#0f47ad]" />
                      Terverifikasi Guru
                    </span>
                  </div>
                  <div class="font-bold text-sm text-slate-900">
                    Pemrograman Berorientasi Objek — Drs. Budi Santoso
                  </div>
                  <p class="text-xs leading-relaxed text-slate-600">
                    Materi: Konsep Inheritance, Polymorphism, dan Abstraksi Class dalam Java.
                  </p>
                  <div class="text-xs text-slate-500 pt-1 flex gap-4 font-semibold">
                    <span>Hadir: <strong class="text-slate-900">36</strong></span>
                    <span>Sakit: <strong class="text-slate-900">0</strong></span>
                    <span>Izin: <strong class="text-slate-900">0</strong></span>
                    <span>Alpa: <strong class="text-slate-900">0</strong></span>
                  </div>
                </div>

                <!-- Sesi 2: Sedang Berlangsung -->
                <div class="p-4 rounded-2xl border-2 border-blue-200 bg-blue-50/40 space-y-1.5">
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-[#0f47ad]">Jam Ke 5 - 7 (10:00 - 12:15)</span>
                    <span class="text-[#0f47ad] font-bold flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-[#0f47ad]"></span>
                      Sesi KBM Berlangsung
                    </span>
                  </div>
                  <div class="font-bold text-sm text-slate-900">
                    Pemrograman Web & Mobile — Nanda Arsyia Murti, S.Kom
                  </div>
                  <p class="text-xs leading-relaxed text-slate-600">
                    Materi: Implementasi REST API Laravel dan integrasi komponen dashboard Nuxt 3.
                  </p>
                  <div class="text-xs text-slate-500 pt-1 flex gap-4 font-semibold">
                    <span>Hadir: <strong class="text-slate-900">35</strong></span>
                    <span class="text-[#0f47ad]">Sakit: <strong>1</strong> (Izin Dokter Disetujui)</span>
                    <span>Izin: <strong class="text-slate-900">0</strong></span>
                    <span>Alpa: <strong class="text-slate-900">0</strong></span>
                  </div>
                </div>

                <!-- Sesi 3: Mendatang -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-white flex items-center justify-between text-xs text-slate-600">
                  <div>
                    <span class="font-bold block text-slate-800">Jam Ke 8 - 10 (13:00 - 15:15)</span>
                    <span>Basis Data Spasial</span>
                  </div>
                  <span class="font-semibold text-[#0f47ad]">Sesi Mendatang</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FORMAT JURNAL SECTION (SOLID BLUE & WHITE, NO CONTAINER EDGES ON ICONS) -->
    <section id="format-jurnal" class="py-8 sm:py-16 border-b border-slate-200 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-12">
          <p class="text-xs font-bold text-[#0f47ad] uppercase tracking-wider">
            Format Dokumen KBM
          </p>
          <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Komponen Isi Jurnal Kelas Digital
          </h2>
          <p class="text-sm text-slate-600">
            Standar format isian jurnal pembelajaran harian yang berlaku di lingkungan SMKN 71 JAKARTA.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Item 01: Nomor tampil langsung tanpa container box tepi -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white space-y-2 sm:space-y-3 shadow-2xs">
            <span class="text-2xl sm:text-3xl font-black text-[#0f47ad] block tracking-tighter">01</span>
            <h4 class="text-base font-bold text-slate-900">Identitas Jam Pelajaran</h4>
            <p class="text-xs leading-relaxed text-slate-600">
              Pencatatan tanggal, jam KBM ke-N, nama mata pelajaran, dan identitas guru pengajar yang bertugas.
            </p>
          </div>

          <!-- Item 02 -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white space-y-2 sm:space-y-3 shadow-2xs">
            <span class="text-2xl sm:text-3xl font-black text-[#0f47ad] block tracking-tighter">02</span>
            <h4 class="text-base font-bold text-slate-900">Agenda Materi Pembelajaran</h4>
            <p class="text-xs leading-relaxed text-slate-600">
              Uraian ringkas materi pokok yang diajarkan, capaian kompetensi, dan kegiatan praktikum yang diselesaikan siswa.
            </p>
          </div>

          <!-- Item 03 -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white space-y-2 sm:space-y-3 shadow-2xs">
            <span class="text-2xl sm:text-3xl font-black text-[#0f47ad] block tracking-tighter">03</span>
            <h4 class="text-base font-bold text-slate-900">Rekap Absensi Per Jam</h4>
            <p class="text-xs leading-relaxed text-slate-600">
              Daftar siswa yang hadir, izin, sakit, atau tanpa keterangan pada jam tersebut untuk mencegah siswa meninggalkan kelas.
            </p>
          </div>

          <!-- Item 04 -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white space-y-2 sm:space-y-3 shadow-2xs">
            <span class="text-2xl sm:text-3xl font-black text-[#0f47ad] block tracking-tighter">04</span>
            <h4 class="text-base font-bold text-slate-900">Catatan Khusus & Ketertiban</h4>
            <p class="text-xs leading-relaxed text-slate-600">
              Catatan kejadian penting kelas, kedisiplinan belajar siswa, dan verifikasi persetujuan guru pengajar.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ALUR OPERASIONAL (LANGKAH 1 S/D LANGKAH 4, STRICTLY BLUE & WHITE) -->
    <section id="alur" class="py-8 sm:py-16 border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-12">
          <p class="text-xs font-bold text-[#0f47ad] uppercase tracking-wider">
            Alur Operasional
          </p>
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Alur Pengisian & Pengelolaan Jurnal
          </h2>
          <p class="text-sm text-slate-600">
            Tahapan kerja pengelolaan agenda pembelajaran di SMKN 71 JAKARTA.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Langkah 1: Sekretaris Kelas -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white flex flex-col justify-between shadow-2xs">
            <div class="space-y-3">
              <span class="text-xs font-extrabold text-[#0f47ad] uppercase tracking-wider block">Langkah 1</span>
              <h3 class="text-lg font-bold text-slate-900">Sekretaris Kelas</h3>
              <p class="text-xs leading-relaxed text-slate-600">
                Mencatat presensi siswa hadir, izin, sakit, atau alpa di setiap jam pelajaran.
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100">
              <span class="text-xs font-bold text-[#0f47ad] bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 inline-block">
                Input Presensi Harian
              </span>
            </div>
          </div>

          <!-- Langkah 2: Guru Pengajar -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white flex flex-col justify-between shadow-2xs">
            <div class="space-y-3">
              <span class="text-xs font-extrabold text-[#0f47ad] uppercase tracking-wider block">Langkah 2</span>
              <h3 class="text-lg font-bold text-slate-900">Guru Pengajar</h3>
              <p class="text-xs leading-relaxed text-slate-600">
                Mengisi jurnal materi pelajaran, capaian kompetensi, dan catatan ketertiban KBM.
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100">
              <span class="text-xs font-bold text-[#0f47ad] bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 inline-block">
                Agenda Jurnal Materi
              </span>
            </div>
          </div>

          <!-- Langkah 3: Wali Kelas -->
          <div class="p-4 sm:p-6 rounded-2xl border border-slate-200 bg-white flex flex-col justify-between shadow-2xs">
            <div class="space-y-3">
              <span class="text-xs font-extrabold text-[#0f47ad] uppercase tracking-wider block">Langkah 3</span>
              <h3 class="text-lg font-bold text-slate-900">Wali Kelas</h3>
              <p class="text-xs leading-relaxed text-slate-600">
                Memvalidasi surat keterangan sakit/izin dokter dan memantau rekap absensi rombel.
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100">
              <span class="text-xs font-bold text-[#0f47ad] bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 inline-block">
                Approval & Rekapitulasi
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- JURUSAN SMKN 71 JAKARTA (TANPA BADGE TEKS DI SAMPING, TANPA FOOTER ROMBEL) -->
    <section id="jurusan" class="py-8 sm:py-16 border-b border-slate-200 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-12">
          <p class="text-xs font-bold text-[#0f47ad] uppercase tracking-wider">
            Rombongan Belajar
          </p>
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            3 Jurusan SMKN 71 JAKARTA
          </h2>
          <p class="text-sm text-slate-600">
            Penerapan Jurnal Kelas Digital mencakup seluruh konsentrasi keahlian di SMKN 71 JAKARTA.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
          <!-- RPL -->
          <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white space-y-3 shadow-2xs">
            <img
              src="/images/logo-rpl.png"
              alt="Logo RPL"
              class="w-12 h-12 sm:w-16 sm:h-16 object-contain"
            />
            <div>
              <h3 class="text-base sm:text-lg font-bold text-slate-900">Rekayasa Perangkat Lunak</h3>
              <p class="text-xs sm:text-sm mt-1.5 leading-relaxed text-slate-600">
                Agenda materi pemrograman web, aplikasi mobile, basis data, dan rekayasa software di Lab Komputer R.409.
              </p>
            </div>
          </div>

          <!-- Animasi -->
          <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white space-y-3 shadow-2xs">
            <img
              src="/images/logo-animasi.png"
              alt="Logo Animasi"
              class="w-12 h-12 sm:w-16 sm:h-16 object-contain"
            />
            <div>
              <h3 class="text-base sm:text-lg font-bold text-slate-900">Animasi</h3>
              <p class="text-xs sm:text-sm mt-1.5 leading-relaxed text-slate-600">
                Agenda materi storyboard, 3D character modeling, rigging, visual effects, dan render farm di Studio Animasi R.203.
              </p>
            </div>
          </div>

          <!-- DKV -->
          <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white space-y-3 shadow-2xs">
            <img
              src="/images/logo-dkv.png"
              alt="Logo DKV"
              class="w-12 h-12 sm:w-16 sm:h-16 object-contain"
            />
            <div>
              <h3 class="text-base sm:text-lg font-bold text-slate-900">Desain Komunikasi Visual</h3>
              <p class="text-xs sm:text-sm mt-1.5 leading-relaxed text-slate-600">
                Agenda materi perancangan visual, tipografi, fotografi studio, ilustrasi, dan video editing di Lab iMac R.303.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- KONTAK RESMI SEKOLAH (SOLID BLUE & WHITE) -->
    <section id="kontak" class="py-8 sm:py-12 border-t border-slate-200 bg-white text-xs sm:text-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
          <!-- Identitas Sekolah (Logo menyatu langsung tanpa container) -->
          <div class="space-y-3">
            <div class="flex items-center gap-3.5">
              <img
                src="/images/logo-smkn71.png"
                alt="SMKN 71"
                class="w-10 h-10 sm:w-12 sm:h-12 object-contain"
              />
              <div>
                <h4 class="font-extrabold text-base text-slate-900">SMKN 71 JAKARTA</h4>
                <p class="text-xs text-slate-500 font-semibold">Jurnal Kelas Digital</p>
              </div>
            </div>
            <p class="text-xs leading-relaxed text-slate-600">
              Sistem administrasi pencatatan agenda KBM dan jurnal kelas digital resmi SMKN 71 JAKARTA.
            </p>
          </div>

          <!-- Alamat Lengkap -->
          <div class="space-y-2">
            <h5 class="font-bold uppercase text-xs tracking-wider text-slate-900">
              Alamat Sekolah
            </h5>
            <p class="text-xs leading-relaxed text-slate-600">
              Jl. Raden Inten II No. 1, Duren Sawit, Kec. Duren Sawit, Kota Jakarta Timur, DKI Jakarta 13440
            </p>
            <p class="text-xs text-slate-600 pt-1">
              Telepon: <span class="font-bold text-slate-900">(021) 8660-8451</span> • Email: <span class="text-[#0f47ad] font-bold">info@smkn71jakarta.sch.id</span>
            </p>
          </div>

          <!-- Tautan Navigasi Halaman -->
          <div class="space-y-2">
            <h5 class="font-bold uppercase text-xs tracking-wider text-slate-900">
              Navigasi Halaman
            </h5>
            <ul class="space-y-2 text-xs text-slate-600 font-semibold">
              <li>
                <button type="button" @click="scrollToSection('tentang')" class="hover:text-[#0f47ad] transition-colors cursor-pointer">
                  › Tentang Jurnal Kelas Digital
                </button>
              </li>
              <li>
                <button type="button" @click="scrollToSection('format-jurnal')" class="hover:text-[#0f47ad] transition-colors cursor-pointer">
                  › Format Isian Jurnal KBM
                </button>
              </li>
              <li>
                <button type="button" @click="scrollToSection('alur')" class="hover:text-[#0f47ad] transition-colors cursor-pointer">
                  › Alur Pengisian & Pengelolaan
                </button>
              </li>
              <li>
                <button type="button" @click="scrollToSection('jurusan')" class="hover:text-[#0f47ad] transition-colors cursor-pointer">
                  › 3 Jurusan SMKN 71 JAKARTA
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER COPYRIGHT (STRICTLY BLUE & WHITE) -->
    <footer class="py-5 text-xs border-t border-slate-200 bg-white text-slate-500 select-none">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left text-xs">
        © {{ new Date().getFullYear() }} <span class="font-bold text-slate-800">SMKN 71 JAKARTA</span>. Hak Cipta Dilindungi.
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import {
  ChevronRight,
  Menu,
  X,
  CheckCircle2,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false
})

const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)
const activeSection = ref('tentang')

// Dynamic & Realtime Date-Time System
function formatIndonesianDate(d: Date = new Date()) {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`
}

function formatIndonesianTime(d: Date = new Date()) {
  const hh = String(d.getHours()).padStart(2, '0')
  const mm = String(d.getMinutes()).padStart(2, '0')
  const ss = String(d.getSeconds()).padStart(2, '0')
  return `${hh}:${mm}:${ss}`
}

const currentRealtimeDate = ref(formatIndonesianDate())
const currentRealtimeTime = ref(formatIndonesianTime())
let clockTimer: any = null

function updateClock() {
  const now = new Date()
  currentRealtimeDate.value = formatIndonesianDate(now)
  currentRealtimeTime.value = formatIndonesianTime(now)
}

const navLinks = [
  { id: 'tentang', name: 'Tentang' },
  { id: 'format-jurnal', name: 'Format Jurnal' },
  { id: 'alur', name: 'Alur Operasional' },
  { id: 'jurusan', name: 'Jurusan' },
  { id: 'kontak', name: 'Kontak' },
]

function scrollToSection(id: string) {
  activeSection.value = id
  if (isMobileMenuOpen.value) {
    isMobileMenuOpen.value = false
  }

  if (typeof window !== 'undefined') {
    if (id === 'tentang') {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      })
      return
    }
    const el = document.getElementById(id)
    if (el) {
      const navOffset = 85
      const targetTop = el.getBoundingClientRect().top + window.pageYOffset - navOffset
      window.scrollTo({
        top: targetTop,
        behavior: 'smooth'
      })
    }
  }
}

onMounted(() => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)

  if (typeof window !== 'undefined') {
    const handleScroll = () => {
      if (window.scrollY < 80) {
        activeSection.value = 'tentang'
        return
      }
      const sectionIds = ['tentang', 'format-jurnal', 'alur', 'jurusan', 'kontak']
      const scrollPos = window.scrollY + 100

      for (let i = sectionIds.length - 1; i >= 0; i--) {
        const el = document.getElementById(sectionIds[i])
        if (el) {
          const top = el.offsetTop - 90
          if (scrollPos >= top) {
            activeSection.value = sectionIds[i]
            break
          }
        }
      }
    }

    window.addEventListener('scroll', handleScroll, { passive: true })
    handleScroll()
  }
})

onUnmounted(() => {
  if (clockTimer) {
    clearInterval(clockTimer)
    clockTimer = null
  }
})

useHead({
  title: 'Jurnal Kelas Digital - SMKN 71 JAKARTA',
  meta: [
    {
      name: 'description',
      content: 'Aplikasi resmi Jurnal Kelas Digital SMKN 71 JAKARTA. Pencatatan agenda materi pembelajaran harian KBM guru, rekapitulasi kehadiran per jam oleh sekretaris kelas, dan pelaporan belajar siswa.'
    }
  ]
})
</script>

<style scoped>
html {
  scroll-behavior: smooth;
}
</style>
