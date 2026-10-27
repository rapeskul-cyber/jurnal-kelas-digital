<template>
  <div
    :class="[
      isDark ? 'bg-[#0b0f19] text-slate-100' : 'bg-white text-slate-800',
      'min-h-screen flex flex-col font-sans antialiased transition-colors duration-300 selection:bg-[#0f47ad] selection:text-white relative'
    ]"
  >
    <!-- FIXED NAVBAR (Always pinned to the top on scroll) -->
    <header
      class="fixed top-0 left-0 right-0 z-50 h-20 transition-all duration-300 border-b select-none backdrop-blur-md"
      :class="[
        isDark
          ? 'bg-[#0b0f19]/90 border-slate-800/90 shadow-lg shadow-black/20'
          : 'bg-white/90 border-slate-200/90 shadow-xs'
      ]"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
        <!-- Brand / Logo -->
        <NuxtLink to="/" class="flex items-center gap-3.5 group cursor-pointer">
          <div
            class="w-11 h-11 rounded-xl p-1.5 transition-all duration-200 group-hover:scale-105 flex items-center justify-center border shadow-xs"
            :class="isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-200'"
          >
            <img src="/images/logo-smkn71.png" alt="Logo SMK Digital" class="w-full h-full object-contain" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-extrabold text-base sm:text-lg tracking-tight transition-colors duration-200" :class="isDark ? 'text-white' : 'text-slate-900'">
                SMK DIGITAL
              </span>
              <span
                class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider border transition-colors"
                :class="isDark ? 'bg-blue-500/15 text-blue-300 border-blue-500/30' : 'bg-blue-50 text-[#0f47ad] border-blue-200'"
              >
                Jakarta
              </span>
            </div>
            <p class="text-[11px] font-medium tracking-wide transition-colors" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
              Presensi & Jurnal Digital
            </p>
          </div>
        </NuxtLink>

        <!-- Navigation Links (Desktop) with Smooth Click & Red Active Underline Indicator -->
        <nav class="hidden md:flex items-center gap-1 lg:gap-2 h-full text-xs font-semibold">
          <button
            v-for="link in navLinks"
            :key="link.id"
            type="button"
            @click="scrollToSection(link.id)"
            class="relative px-3.5 py-2 rounded-lg transition-all duration-200 cursor-pointer h-10 flex items-center justify-center"
            :class="[
              activeSection === link.id
                ? (isDark ? 'text-[#60A5FA] font-bold' : 'text-[#0f47ad] font-bold')
                : (isDark ? 'text-slate-300 hover:text-white hover:bg-slate-800/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')
            ]"
          >
            <span>{{ link.name }}</span>
            <!-- Blue Active Indicator Underline -->
            <span
              v-if="activeSection === link.id"
              class="absolute bottom-0 left-2.5 right-2.5 h-[2.5px] bg-[#0f47ad] rounded-full transition-all duration-300"
            ></span>
          </button>
        </nav>

        <!-- Right Controls (Theme Toggle & Auth Button) -->
        <div class="flex items-center gap-2.5 sm:gap-3">
          <!-- Dark / Light Mode Toggle Button -->
          <button
            type="button"
            @click="toggleTheme"
            class="relative w-10 h-10 rounded-xl flex items-center justify-center border transition-all duration-200 active:scale-90 cursor-pointer shadow-xs"
            :class="[
              isDark
                ? 'bg-slate-800/90 border-slate-700 text-amber-400 hover:bg-slate-800 hover:border-amber-400/50'
                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300'
            ]"
            :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
            aria-label="Toggle Theme"
          >
            <Transition name="theme-flip" mode="out-in">
              <Sun v-if="isDark" class="w-5 h-5 transition-transform duration-300 rotate-0" />
              <Moon v-else class="w-5 h-5 transition-transform duration-300 -rotate-12" />
            </Transition>
          </button>

          <!-- Auth Action Button -->
          <NuxtLink
            v-if="authStore.isAuthenticated"
            to="/dashboard"
            class="px-4 sm:px-5 py-2.5 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs font-bold transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-sm hover:shadow-md active:scale-95 group"
          >
            <span>Buka Dashboard</span>
            <ChevronRight class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
          </NuxtLink>
          <NuxtLink
            v-else
            to="/login"
            class="px-4 sm:px-5 py-2.5 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs font-bold transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-sm hover:shadow-md active:scale-95 group"
          >
            <span>Masuk Portal</span>
            <ChevronRight class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
          </NuxtLink>

          <!-- Mobile Menu Hamburger Button -->
          <button
            type="button"
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden w-10 h-10 rounded-xl flex items-center justify-center border transition-all active:scale-90"
            :class="isDark ? 'bg-slate-800/90 border-slate-700 text-slate-200' : 'bg-white border-slate-200 text-slate-700'"
            aria-label="Toggle Mobile Menu"
          >
            <X v-if="isMobileMenuOpen" class="w-5 h-5" />
            <Menu v-else class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Navigation -->
      <Transition name="fade-slide">
        <div
          v-if="isMobileMenuOpen"
          class="md:hidden border-t px-4 py-3 space-y-1 backdrop-blur-xl transition-all shadow-lg"
          :class="isDark ? 'bg-[#0b0f19]/95 border-slate-800' : 'bg-white/95 border-slate-200'"
        >
          <button
            v-for="link in navLinks"
            :key="link.id"
            type="button"
            @click="scrollToSection(link.id)"
            class="w-full text-left px-4 py-2.5 rounded-lg text-xs font-semibold transition-colors flex items-center justify-between"
            :class="[
              activeSection === link.id
                ? (isDark ? 'text-[#60A5FA] bg-blue-500/10 font-bold' : 'text-[#0f47ad] bg-blue-50 font-bold')
                : (isDark ? 'text-slate-300 hover:bg-slate-800' : 'text-slate-700 hover:bg-slate-100')
            ]"
          >
            <span>{{ link.name }}</span>
            <span v-if="activeSection === link.id" class="w-1.5 h-1.5 rounded-full bg-[#0f47ad]"></span>
          </button>
        </div>
      </Transition>
    </header>

    <!-- PADDING FOR FIXED NAVBAR -->
    <div class="h-20"></div>

    <!-- HERO SECTION -->
    <section id="tentang" class="py-14 sm:py-20 lg:py-24 border-b transition-colors duration-300" :class="isDark ? 'border-slate-800/80 bg-[#0e1320]' : 'border-slate-200/80 bg-slate-50/70'">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
          <!-- Left Column: Editorial Headline & Copy -->
          <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
            <!-- Plain Text Label (Replaces Gambar 1 pill/ping badge) -->
            <p class="text-xs font-bold tracking-wider uppercase text-[#0f47ad] transition-colors">
              Tahun Ajaran 2026/2027 • Semester Ganjil
            </p>

            <!-- Grounded Human Headline (No artificial gradient text) -->
            <h1
              class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.2] transition-colors"
              :class="isDark ? 'text-white' : 'text-slate-900'"
            >
              Pusat Pelaporan KBM &
              <span class="text-[#0f47ad]">Presensi Digital</span>
            </h1>

            <p
              class="text-sm sm:text-base leading-relaxed max-w-2xl mx-auto lg:mx-0 transition-colors"
              :class="isDark ? 'text-slate-300' : 'text-slate-600'"
            >
              Mendukung tata kelola administrasi akademik SMK Digital Terpadu. Pencatatan kehadiran siswa per jam pembelajaran oleh sekretaris kelas, agenda materi harian guru, verifikasi surat izin dokter wali kelas, serta rekap semester otomatis.
            </p>

            <!-- Action Buttons with Smooth Interactions -->
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
              <NuxtLink
                to="/login"
                class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#0f47ad] hover:bg-[#0c398a] text-white text-xs sm:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2.5 cursor-pointer shadow-sm hover:shadow-md active:scale-95 group"
              >
                <span>Buka Halaman Login</span>
                <ChevronRight class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
              </NuxtLink>

              <button
                type="button"
                @click="scrollToSection('kejuruan')"
                class="w-full sm:w-auto px-6 py-3.5 rounded-xl border text-xs sm:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                :class="[
                  isDark
                    ? 'bg-slate-900/80 border-slate-700 text-slate-200 hover:bg-slate-800 hover:border-slate-600'
                    : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400'
                ]"
              >
                <span>Lihat Jurusan</span>
              </button>
            </div>

            <!-- Authentic Highlights Grid (Clean cards, no AI slop) -->
            <div
              class="pt-6 border-t grid grid-cols-3 gap-3 sm:gap-4 text-left max-w-lg mx-auto lg:mx-0 transition-colors"
              :class="isDark ? 'border-slate-800' : 'border-slate-200'"
            >
              <div
                class="p-4 rounded-xl border transition-all duration-200"
                :class="isDark ? 'bg-slate-900/70 border-slate-800' : 'bg-white border-slate-200/90 shadow-2xs'"
              >
                <div class="text-xl sm:text-2xl font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">3 Rombel</div>
                <div class="text-[11px] font-medium mt-0.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">RPL • Animasi • DKV</div>
              </div>

              <div
                class="p-4 rounded-xl border transition-all duration-200"
                :class="isDark ? 'bg-slate-900/70 border-slate-800' : 'bg-white border-slate-200/90 shadow-2xs'"
              >
                <div class="text-xl sm:text-2xl font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">Real-Time</div>
                <div class="text-[11px] font-medium mt-0.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">Presensi Jam Ke-N</div>
              </div>

              <div
                class="p-4 rounded-xl border transition-all duration-200"
                :class="isDark ? 'bg-slate-900/70 border-slate-800' : 'bg-white border-slate-200/90 shadow-2xs'"
              >
                <div class="text-xl sm:text-2xl font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">WhatsApp</div>
                <div class="text-[11px] font-medium mt-0.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">Notifikasi Terpadu</div>
              </div>
            </div>
          </div>

          <!-- Right Column: Campus Image (Gambar 2 modified: Title SMK Digital Terpadu, Aktif badge removed) -->
          <div class="lg:col-span-5">
            <div
              class="rounded-2xl p-2.5 border transition-all duration-300"
              :class="[
                isDark
                  ? 'bg-slate-900 border-slate-800 shadow-md'
                  : 'bg-white border-slate-200 shadow-sm'
              ]"
            >
              <div
                class="relative rounded-xl overflow-hidden aspect-[4/3]"
                :class="isDark ? 'bg-slate-800' : 'bg-slate-100'"
              >
                <img
                  src="/images/gedung-smkn71.jpg"
                  alt="Kampus SMK Digital Terpadu"
                  class="w-full h-full object-cover object-center"
                />
                <!-- Subtle gradient overlay for readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>
                
                <!-- Gambar 2 Update: Title "SMK Digital Terpadu", Tulisan Aktif Dihapus -->
                <div class="absolute bottom-3 left-3 right-3 p-3 rounded-xl bg-black/60 border border-white/10 text-white shadow-md backdrop-blur-xs">
                  <p class="text-xs sm:text-sm font-bold truncate">SMK Digital Terpadu</p>
                  <p class="text-[11px] text-slate-200 truncate mt-0.5">Jl. Pendidikan Kejuruan No. 71, Kawasan Industri Modern</p>
                </div>
              </div>

              <!-- Real School Accreditation & Kemendikbud Details -->
              <div class="p-3 pt-3.5 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2.5">
                  <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs border"
                    :class="isDark ? 'bg-blue-500/10 border-blue-500/30 text-blue-400' : 'bg-blue-50 border-blue-200 text-[#0f47ad]'"
                  >
                    SMK
                  </div>
                  <div>
                    <p class="font-bold leading-tight" :class="isDark ? 'text-white' : 'text-slate-900'">Akreditasi A • NPSN 20109312</p>
                    <p class="text-[10px]" :class="isDark ? 'text-slate-400' : 'text-slate-500'">Dinas Pendidikan DKI Jakarta</p>
                  </div>
                </div>

                <a
                  href="#"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-3 py-1.5 rounded-lg border text-[11px] font-semibold transition-colors flex items-center gap-1.5 cursor-pointer active:scale-95"
                  :class="[
                    isDark
                      ? 'border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white'
                      : 'border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                  ]"
                >
                  <span>Website</span>
                  <ExternalLink class="w-3 h-3" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- KOMPETENSI KEAHLIAN / JURUSAN SECTION -->
    <section
      id="kejuruan"
      class="py-16 sm:py-20 border-b transition-colors duration-300"
      :class="isDark ? 'border-slate-800/80 bg-[#0b0f19]' : 'border-slate-200/80 bg-white'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header (Clean, no AI slop chip) -->
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-12">
          <p class="text-xs font-bold text-[#0f47ad] uppercase tracking-wider">
            Kompetensi Keahlian
          </p>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight" :class="isDark ? 'text-white' : 'text-slate-900'">
            Jurusan Unggulan SMK Digital Terpadu
          </h2>
          <p class="text-xs sm:text-sm max-w-xl mx-auto" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
            Presensi dan jurnal mengajar mencakup seluruh rombongan belajar kejuruan berikut.
          </p>
        </div>

        <!-- 3 Jurusan Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
          <!-- 1. RPL -->
          <div
            class="rounded-2xl p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/60 border-slate-800 hover:border-slate-700'
                : 'bg-white border-slate-200 hover:border-slate-300 shadow-xs'
            ]"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div
                  class="w-13 h-13 rounded-xl p-2.5 border flex items-center justify-center"
                  :class="isDark ? 'bg-slate-800 border-slate-700' : 'bg-slate-50 border-slate-200'"
                >
                  <img src="/images/logo-rpl.png" alt="RPL" class="w-full h-full object-contain" />
                </div>
                <span
                  class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider border"
                  :class="isDark ? 'bg-red-500/10 text-red-400 border-red-500/30' : 'bg-red-50 text-red-700 border-red-200'"
                >
                  RPL
                </span>
              </div>

              <div>
                <h3 class="text-base font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                  Rekayasa Perangkat Lunak
                </h3>
                <p class="text-xs mt-2 leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                  Pemrograman web, mobile, manajemen basis data, dan rekayasa software skala industri.
                </p>
              </div>
            </div>

            <div
              class="pt-4 border-t mt-6 text-[11px] font-medium"
              :class="isDark ? 'border-slate-800 text-slate-400' : 'border-slate-100 text-slate-500'"
            >
              Rombel: Kelas X, XI, XII RPL
            </div>
          </div>

          <!-- 2. Animasi -->
          <div
            class="rounded-2xl p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/60 border-slate-800 hover:border-slate-700'
                : 'bg-white border-slate-200 hover:border-slate-300 shadow-xs'
            ]"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div
                  class="w-13 h-13 rounded-xl p-2.5 border flex items-center justify-center"
                  :class="isDark ? 'bg-slate-800 border-slate-700' : 'bg-slate-50 border-slate-200'"
                >
                  <img src="/images/logo-animasi.png" alt="Animasi" class="w-full h-full object-contain" />
                </div>
                <span
                  class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider border"
                  :class="isDark ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-amber-50 text-amber-700 border-amber-200'"
                >
                  Animasi
                </span>
              </div>

              <div>
                <h3 class="text-base font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                  Animasi 2D & 3D
                </h3>
                <p class="text-xs mt-2 leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                  Pembuatan gerak visual, 3D modeling, visual effects, storyboard, dan produksi multimedia.
                </p>
              </div>
            </div>

            <div
              class="pt-4 border-t mt-6 text-[11px] font-medium"
              :class="isDark ? 'border-slate-800 text-slate-400' : 'border-slate-100 text-slate-500'"
            >
              Rombel: Kelas X, XI, XII Animasi
            </div>
          </div>

          <!-- 3. DKV -->
          <div
            class="rounded-2xl p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/60 border-slate-800 hover:border-slate-700'
                : 'bg-white border-slate-200 hover:border-slate-300 shadow-xs'
            ]"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div
                  class="w-13 h-13 rounded-xl p-2.5 border flex items-center justify-center"
                  :class="isDark ? 'bg-slate-800 border-slate-700' : 'bg-slate-50 border-slate-200'"
                >
                  <img src="/images/logo-dkv.png" alt="DKV" class="w-full h-full object-contain" />
                </div>
                <span
                  class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider border"
                  :class="isDark ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                >
                  DKV
                </span>
              </div>

              <div>
                <h3 class="text-base font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                  Desain Komunikasi Visual
                </h3>
                <p class="text-xs mt-2 leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                  Perancangan identitas visual, tipografi, fotografi, ilustrasi grafis, dan media komunikasi.
                </p>
              </div>
            </div>

            <div
              class="pt-4 border-t mt-6 text-[11px] font-medium"
              :class="isDark ? 'border-slate-800 text-slate-400' : 'border-slate-100 text-slate-500'"
            >
              Rombel: Kelas X, XI, XII DKV
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ALUR SISTEM SECTION -->
    <section
      id="alur"
      class="py-16 sm:py-20 border-b transition-colors duration-300"
      :class="isDark ? 'border-slate-800/80 bg-[#0e1320]' : 'border-slate-200/80 bg-slate-50/70'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-12">
          <p class="text-xs font-bold text-[#0f47ad] uppercase tracking-wider">
            Alur Sistem
          </p>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight" :class="isDark ? 'text-white' : 'text-slate-900'">
            Peran & Tanggung Jawab Pengguna
          </h2>
          <p class="text-xs sm:text-sm max-w-xl mx-auto" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
            Pembagian tanggung jawab operasional pada sistem presensi dan jurnal kelas.
          </p>
        </div>

        <!-- 4 Role Cards Grid (Clean, institutional design) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- 1: Sekretaris Kelas -->
          <div
            class="rounded-2xl p-5 sm:p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/70 border-slate-800'
                : 'bg-white border-slate-200 shadow-2xs'
            ]"
          >
            <div class="space-y-3">
              <span
                class="inline-block text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border"
                :class="isDark ? 'bg-blue-500/10 text-blue-400 border-blue-500/30' : 'bg-blue-50 text-blue-700 border-blue-200'"
              >
                Langkah 1
              </span>
              <h3 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                Sekretaris Kelas
              </h3>
              <p class="text-xs leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                Mencatat presensi siswa hadir, izin, sakit, atau alpa di setiap jam pelajaran.
              </p>
            </div>
            <div
              class="pt-3 border-t mt-4 text-[11px] font-semibold"
              :class="isDark ? 'border-slate-800 text-blue-400' : 'border-slate-100 text-blue-600'"
            >
              Input Presensi Harian
            </div>
          </div>

          <!-- 2: Guru Pengajar -->
          <div
            class="rounded-2xl p-5 sm:p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/70 border-slate-800'
                : 'bg-white border-slate-200 shadow-2xs'
            ]"
          >
            <div class="space-y-3">
              <span
                class="inline-block text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border"
                :class="isDark ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
              >
                Langkah 2
              </span>
              <h3 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                Guru Pengajar
              </h3>
              <p class="text-xs leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                Mengisi jurnal materi pelajaran, capaian kompetensi, dan catatan ketertiban KBM.
              </p>
            </div>
            <div
              class="pt-3 border-t mt-4 text-[11px] font-semibold"
              :class="isDark ? 'border-slate-800 text-emerald-400' : 'border-slate-100 text-emerald-600'"
            >
              Agenda Jurnal Materi
            </div>
          </div>

          <!-- 3: Wali Kelas -->
          <div
            class="rounded-2xl p-5 sm:p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/70 border-slate-800'
                : 'bg-white border-slate-200 shadow-2xs'
            ]"
          >
            <div class="space-y-3">
              <span
                class="inline-block text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border"
                :class="isDark ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-amber-50 text-amber-700 border-amber-200'"
              >
                Langkah 3
              </span>
              <h3 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                Wali Kelas
              </h3>
              <p class="text-xs leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                Memvalidasi surat keterangan sakit/izin dokter dan memantau rekap absensi rombel.
              </p>
            </div>
            <div
              class="pt-3 border-t mt-4 text-[11px] font-semibold"
              :class="isDark ? 'border-slate-800 text-amber-400' : 'border-slate-100 text-amber-600'"
            >
              Approval & Rekapitulasi
            </div>
          </div>

          <!-- 4: Super Administrator -->
          <div
            class="rounded-2xl p-5 sm:p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between"
            :class="[
              isDark
                ? 'bg-slate-900/70 border-slate-800'
                : 'bg-white border-slate-200 shadow-2xs'
            ]"
          >
            <div class="space-y-3">
              <span
                class="inline-block text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border"
                :class="isDark ? 'bg-blue-500/10 text-blue-400 border-blue-500/30' : 'bg-blue-50 text-[#0f47ad] border-blue-200'"
              >
                Langkah 4
              </span>
              <h3 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">
                Super Administrator
              </h3>
              <p class="text-xs leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                Pengelolaan akun pengguna, master data mata pelajaran, dan konfigurasi sistem.
              </p>
            </div>
            <div
              class="pt-3 border-t mt-4 text-[11px] font-semibold"
              :class="isDark ? 'border-slate-800 text-blue-400' : 'border-slate-100 text-[#0f47ad]'"
            >
              Master Data & Gateway WA
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CALL TO ACTION SECTION (Clean, Solid Brand Blue) -->
    <section class="py-12 sm:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="rounded-2xl p-8 sm:p-10 border text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6"
          :class="[
            isDark
              ? 'bg-slate-900 border-slate-800 text-white'
              : 'bg-[#0f47ad] border-blue-700 text-white'
          ]"
        >
          <div class="space-y-1.5 max-w-xl">
            <h3 class="text-xl sm:text-2xl font-bold tracking-tight">
              Akses Portal Jurnal Kelas Digital
            </h3>
            <p
              class="text-xs sm:text-sm leading-relaxed"
              :class="isDark ? 'text-slate-300' : 'text-blue-100'"
            >
              Silakan masuk menggunakan akun terdaftar untuk mencatat presensi harian atau melihat rekap KBM.
            </p>
          </div>

          <div class="flex-shrink-0">
            <NuxtLink
              to="/login"
              class="px-6 py-3.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer active:scale-95 group shadow-sm"
              :class="[
                isDark
                  ? 'bg-[#0f47ad] hover:bg-[#0c398a] text-white'
                  : 'bg-white hover:bg-slate-100 text-[#0f47ad]'
              ]"
            >
              <span>Buka Halaman Login</span>
              <ChevronRight class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- KONTAK SECTION (Authentic Kemendikbud Data for SMK Digital Terpadu) -->
    <section
      id="kontak"
      class="py-14 text-xs border-t transition-colors duration-300"
      :class="isDark ? 'border-slate-800 bg-[#080c14]' : 'border-slate-200 bg-white'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
          <!-- Identitas Sekolah Asli -->
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl border p-1 flex items-center justify-center"
                :class="isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-200'"
              >
                <img src="/images/logo-smkn71.png" alt="SMK Digital" class="w-full h-full object-contain" />
              </div>
              <div>
                <span class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">SMK Digital Terpadu</span>
                <p class="text-[10px]" :class="isDark ? 'text-slate-400' : 'text-slate-500'">NPSN: 20109312 • Akreditasi A</p>
              </div>
            </div>
            <p class="leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
              Sekolah Menengah Kejuruan Negeri di lingkungan Dinas Pendidikan Provinsi DKI Jakarta yang membina kompetensi Rekayasa Perangkat Lunak, Animasi, dan Desain Komunikasi Visual.
            </p>
          </div>

          <!-- Alamat Resmi Asli Lengkap -->
          <div class="space-y-2">
            <div class="font-bold uppercase text-[11px] tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-900'">
              Alamat Sekolah
            </div>
            <p class="leading-relaxed" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
              Jl. Pendidikan Kejuruan No. 71, Kawasan Industri Edukasi Terpadu
            </p>
            <p class="pt-1 font-medium" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
              Kontak: <span class="font-semibold">021-8555-0071</span> • Email: <a href="mailto:admin@sekolah.sch.id" class="text-[#0f47ad] hover:underline font-semibold">admin@sekolah.sch.id</a>
            </p>
          </div>

          <!-- Tautan Terkait Resmi -->
          <div class="space-y-2">
            <div class="font-bold uppercase text-[11px] tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-900'">
              Tautan Terkait
            </div>
            <ul class="space-y-2 font-medium" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
              <li>
                <NuxtLink to="/login" class="hover:text-[#0f47ad] transition-colors flex items-center gap-1.5 group">
                  <span class="text-[#0f47ad] font-bold">›</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">Masuk Portal Presensi Siswa & Guru</span>
                </NuxtLink>
              </li>
              <li>
                <a href="#" target="_blank" rel="noopener noreferrer" class="hover:text-[#0f47ad] transition-colors flex items-center gap-1.5 group">
                  <span class="text-[#0f47ad] font-bold">›</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">Website Resmi SMK Digital Terpadu</span>
                  <ExternalLink class="w-3 h-3 opacity-60" />
                </a>
              </li>
              <li>
                <a href="#" target="_blank" rel="noopener noreferrer" class="hover:text-[#0f47ad] transition-colors flex items-center gap-1.5 group">
                  <span class="text-[#0f47ad] font-bold">›</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">LMS Pembelajaran Digital</span>
                  <ExternalLink class="w-3 h-3 opacity-60" />
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER COPYRIGHT -->
    <footer
      class="py-6 text-xs border-t transition-colors duration-300"
      :class="isDark ? 'bg-[#05080e] border-slate-900 text-slate-500' : 'bg-slate-50 border-slate-200 text-slate-500'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left text-[11px]">
        <div>
          © {{ new Date().getFullYear() }} <span class="font-bold" :class="isDark ? 'text-slate-300' : 'text-slate-700'">SMK Digital Terpadu</span>. Hak Cipta Dilindungi.
        </div>
        <div>
          Sistem Jurnal KBM & Presensi Kelas Digital
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import {
  ChevronRight,
  Sun,
  Moon,
  ExternalLink,
  Menu,
  X,
} from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false
})

const authStore = useAuthStore()
const isDark = ref(false)
const isMobileMenuOpen = ref(false)
const activeSection = ref('tentang')

const navLinks = [
  { id: 'tentang', name: 'Tentang' },
  { id: 'kejuruan', name: 'Kompetensi Keahlian' },
  { id: 'alur', name: 'Alur Presensi' },
  { id: 'kontak', name: 'Kontak' },
]

function toggleTheme() {
  isDark.value = !isDark.value
  if (typeof window !== 'undefined') {
    localStorage.setItem('homepage_theme', isDark.value ? 'dark' : 'light')
  }
}

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
      const navOffset = 80
      const targetTop = el.getBoundingClientRect().top + window.pageYOffset - navOffset
      window.scrollTo({
        top: targetTop,
        behavior: 'smooth'
      })
    }
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    const savedTheme = localStorage.getItem('homepage_theme')
    // Default is Light Mode (false) as primary mode on initial visit
    isDark.value = savedTheme === 'dark'

    // ScrollSpy to accurately update activeSection on scroll
    const handleScroll = () => {
      if (window.scrollY < 80) {
        activeSection.value = 'tentang'
        return
      }
      const sectionIds = ['tentang', 'kejuruan', 'alur', 'kontak']
      const scrollPos = window.scrollY + 100

      for (let i = sectionIds.length - 1; i >= 0; i--) {
        const el = document.getElementById(sectionIds[i])
        if (el) {
          const top = el.offsetTop - 85
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

useHead({
  title: 'SMK Digital Terpadu - Jurnal KBM & Presensi Digital',
  meta: [
    {
      name: 'description',
      content: 'Pusat Pelaporan KBM & Presensi Harian Siswa SMK Digital Terpadu. Pencatatan kehadiran siswa per jam pembelajaran, agenda materi guru, dan rekapitulasi semester otomatis.'
    }
  ]
})
</script>

<style scoped>
/* Theme flip transition */
.theme-flip-enter-active,
.theme-flip-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.theme-flip-enter-from {
  opacity: 0;
  transform: rotate(-90deg) scale(0.6);
}
.theme-flip-leave-to {
  opacity: 0;
  transform: rotate(90deg) scale(0.6);
}

/* Mobile menu slide transition */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease-out;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Global smooth scrolling */
html {
  scroll-behavior: smooth;
}
</style>
