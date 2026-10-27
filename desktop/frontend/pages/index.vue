<template>
  <div class="min-h-screen bg-white text-slate-800 flex flex-col font-sans antialiased">
    <!-- Navbar Utama -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-2xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo & Brand SMKN 71 -->
        <NuxtLink to="/" class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-xl p-1 bg-white border border-slate-200 shadow-2xs flex items-center justify-center">
            <img src="/images/logo-smkn71.png" alt="Logo SMKN 71" class="w-full h-full object-contain" />
          </div>
          <div>
            <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight block">SMK NEGERI 71 JAKARTA</span>
            <span class="text-[11px] text-slate-500 font-medium hidden sm:block">Sistem Informasi Jurnal KBM &amp; Presensi Kelas</span>
          </div>
        </NuxtLink>

        <!-- Menu Navigasi & Tombol Masuk Portal -->
        <div class="flex items-center gap-6">
          <nav class="hidden md:flex items-center gap-7 text-xs font-semibold text-slate-600">
            <a href="#tentang" class="hover:text-blue-700 transition">Beranda</a>
            <a href="#alur" class="hover:text-blue-700 transition">Alur Presensi</a>
            <a href="#kontak" class="hover:text-blue-700 transition">Kontak</a>
          </nav>
          
          <NuxtLink
            to="/login"
            class="px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow cursor-pointer"
          >
            <span>Masuk Portal</span>
            <ChevronRight class="w-4 h-4" />
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- Hero Section: Elegan, Terang, & Berwibawa -->
    <section id="tentang" class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          
          <!-- Teks Kiri (7 Kolom) -->
          <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-bold">
              <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
              <span>Portal Resmi KBM SMKN 71 Jakarta</span>
            </div>

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
              Pusat Pelaporan KBM &amp; Presensi Harian Siswa
            </h1>

            <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
              Platform terpadu pencatatan presensi siswa per jam KBM, pengisian agenda materi harian guru, verifikasi surat izin wali kelas, serta rekapitulasi kehadiran akademik secara akurat dan transparan.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-1">
              <NuxtLink
                to="/login"
                class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-md"
              >
                <span>Buka Formulir Presensi &amp; Jurnal</span>
                <ChevronRight class="w-4 h-4" />
              </NuxtLink>
              <a
                href="#alur"
                class="w-full sm:w-auto px-5 py-3.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-2xs"
              >
                <span>Pelajari Alur Sistem</span>
              </a>
            </div>

            <!-- Ringkasan Statistik Akademik Sekolah -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-3 gap-4 text-left max-w-md mx-auto lg:mx-0">
              <div>
                <div class="text-lg font-bold text-slate-900">19 Kelas</div>
                <div class="text-[11px] text-slate-500">RPL &bull; DKV &bull; Animasi</div>
              </div>
              <div>
                <div class="text-lg font-bold text-slate-900">Real-Time</div>
                <div class="text-[11px] text-slate-500">Presensi Jam Ke-N</div>
              </div>
              <div>
                <div class="text-lg font-bold text-slate-900">WhatsApp</div>
                <div class="text-[11px] text-slate-500">Notifikasi Otomatis</div>
              </div>
            </div>
          </div>

          <!-- Gambar Gedung Sekolah Kanan (5 Kolom) -->
          <div class="lg:col-span-5">
            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
              <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-slate-100">
                <img
                  src="/images/gedung-smkn71.jpg"
                  alt="Kampus SMKN 71 Jakarta"
                  class="w-full h-full object-cover object-center"
                />
              </div>
              <div class="p-3 text-center sm:text-left flex items-center justify-between">
                <div>
                  <div class="text-xs font-bold text-slate-900">Gedung Kampus SMKN 71 Jakarta</div>
                  <div class="text-[11px] text-slate-500">Cempaka Putih Timur, Jakarta Pusat</div>
                </div>
                <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-md">
                  Kampus Resmi
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Alur Layanan KBM (Hanya 3 Pilar Akademik: Sekretaris, Guru Pengajar, Guru Pembimbing) -->
    <section id="alur" class="py-14 sm:py-20 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center space-y-2.5 mb-12">
          <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Alur Operasional</span>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
            Peran &amp; Tanggung Jawab Pengguna
          </h2>
          <p class="text-xs sm:text-sm text-slate-500">
            Koordinasi tiga pilar utama dalam memastikan ketertiban kehadiran dan administrasi pembelajaran.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- 1. Sekretaris Kelas -->
          <div class="bg-slate-50/70 p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3.5 flex flex-col justify-between">
            <div class="space-y-3">
              <div class="w-10 h-10 rounded-xl bg-blue-100/80 text-blue-800 font-bold flex items-center justify-center text-sm">
                1
              </div>
              <h3 class="text-base font-bold text-slate-900">Sekretaris Kelas</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                Mencatat kehadiran siswa secara real-time pada setiap jam mata pelajaran, merekam alasan ketidakhadiran, serta mengonfirmasi sesi dengan PIN kelas.
              </p>
            </div>
            <div class="pt-3 border-t border-slate-200 text-[11px] font-semibold text-blue-700">
              Input Presensi Jam Ke-N
            </div>
          </div>

          <!-- 2. Guru Pengajar -->
          <div class="bg-slate-50/70 p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3.5 flex flex-col justify-between">
            <div class="space-y-3">
              <div class="w-10 h-10 rounded-xl bg-blue-100/80 text-blue-800 font-bold flex items-center justify-center text-sm">
                2
              </div>
              <h3 class="text-base font-bold text-slate-900">Guru Pengajar</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                Mengisi resume agenda materi yang diajarkan, capaian pembelajaran siswa, melampirkan modul atau tugas, serta memverifikasi ketertiban kelas.
              </p>
            </div>
            <div class="pt-3 border-t border-slate-200 text-[11px] font-semibold text-blue-700">
              Jurnal Pembelajaran &amp; Materi
            </div>
          </div>

          <!-- 3. Guru Pembimbing (Wali Kelas) -->
          <div class="bg-slate-50/70 p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3.5 flex flex-col justify-between">
            <div class="space-y-3">
              <div class="w-10 h-10 rounded-xl bg-blue-100/80 text-blue-800 font-bold flex items-center justify-center text-sm">
                3
              </div>
              <h3 class="text-base font-bold text-slate-900">Guru Pembimbing</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                Memvalidasi berkas surat izin/sakit dokter dari siswa, memantau riwayat kedisiplinan kelas binaan, dan menerima rekapitulasi semester otomatis.
              </p>
            </div>
            <div class="pt-3 border-t border-slate-200 text-[11px] font-semibold text-blue-700">
              Validasi Izin &amp; Rekap Semester
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer Institusi Tunggal Resmi -->
    <footer id="kontak" class="bg-slate-900 text-slate-300 py-12 text-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-white p-1 flex items-center justify-center shadow-xs">
                <img src="/images/logo-smkn71.png" alt="SMKN 71" class="w-full h-full object-contain" />
              </div>
              <span class="font-bold text-white text-sm">SMK Negeri 71 Jakarta</span>
            </div>
            <p class="text-slate-400 leading-relaxed text-[11px]">
              Sekolah Menengah Kejuruan Negeri di bawah naungan Dinas Pendidikan Provinsi DKI Jakarta.
            </p>
          </div>

          <div class="space-y-1.5 text-[11px]">
            <div class="font-bold text-white uppercase text-[10px] tracking-wider">Alamat Kampus</div>
            <p class="text-slate-400 leading-relaxed">
              Jl. Raden Saleh No. 45, Cempaka Putih Timur, Kec. Cempaka Putih, Kota Jakarta Pusat, DKI Jakarta 10510
            </p>
            <p class="text-slate-400 pt-1">Telp: (021) 4241646 &bull; Email: info@smkn71jakarta.sch.id</p>
          </div>

          <div class="space-y-1.5 text-[11px]">
            <div class="font-bold text-white uppercase text-[10px] tracking-wider">Tautan Cepat</div>
            <ul class="space-y-1.5 text-slate-400 font-medium">
              <li><NuxtLink to="/login" class="hover:text-white transition flex items-center gap-1">&bull; Portal Masuk Presensi</NuxtLink></li>
              <li><a href="https://smkn71jakarta.sch.id" target="_blank" class="hover:text-white transition flex items-center gap-1">&bull; Website Resmi Sekolah &nearr;</a></li>
              <li><a href="https://siap71.smkn71jakarta.sch.id" target="_blank" class="hover:text-white transition flex items-center gap-1">&bull; LMS SIAP 71 &nearr;</a></li>
            </ul>
          </div>

        </div>

        <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-500 text-[11px]">
          <div>
            &copy; {{ new Date().getFullYear() }} <span class="text-slate-300 font-semibold">SMK Negeri 71 Jakarta</span>. Hak Cipta Dilindungi.
          </div>
          <div>
            Sistem Informasi Jurnal KBM &amp; Presensi Kelas
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'

definePageMeta({
  layout: false
})
</script>
