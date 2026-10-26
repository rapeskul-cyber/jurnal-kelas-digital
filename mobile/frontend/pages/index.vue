<template>
  <div class="min-h-screen bg-white text-slate-800 flex flex-col font-sans antialiased">
    <!-- Navbar Utama -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-2xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo & Brand SMKN 71 -->
        <NuxtLink to="/" class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-xl p-1 bg-white border border-slate-200 shadow-2xs flex items-center justify-center">
            <img src="/images/logo-smkn71.png" alt="Logo SMKN 71" class="w-full h-full object-contain" />
          </div>
          <div>
            <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">SMK NEGERI 71 JAKARTA</span>
          </div>
        </NuxtLink>

        <!-- Menu Navigasi -->
        <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-600">
          <a href="#tentang" class="hover:text-blue-700 transition">Tentang</a>
          <a href="#kejuruan" class="hover:text-blue-700 transition">Kompetensi Keahlian</a>
          <a href="#alur" class="hover:text-blue-700 transition">Alur Presensi</a>
          <a href="#kontak" class="hover:text-blue-700 transition">Kontak</a>
        </nav>
      </div>
    </header>

    <!-- 3. Hero Section: Terang, Tenang, Humanis & Proporsional -->
    <section id="tentang" class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          
          <!-- Teks Kiri (7 Kolom) -->
          <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
              Pusat Pelaporan KBM &amp; Presensi Harian Siswa
            </h1>

            <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
              Mendukung tata kelola administrasi akademik SMKN 71 Jakarta. Pencatatan kehadiran siswa per jam pembelajaran oleh sekretaris kelas, agenda materi harian guru, verifikasi surat izin dokter wali kelas, serta rekap semester otomatis.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
              <NuxtLink
                to="/login"
                class="w-full sm:w-auto px-6 py-3 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs"
              >
                <span>Buka Halaman Login</span>
                <ChevronRight class="w-4 h-4" />
              </NuxtLink>
              <a
                href="#kejuruan"
                class="w-full sm:w-auto px-5 py-3 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2"
              >
                <span>Lihat Jurusan</span>
              </a>
            </div>

            <!-- Info Bar Singkat -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-3 gap-4 text-left max-w-md mx-auto lg:mx-0">
              <div>
                <div class="text-lg font-bold text-slate-900">3 Rombel</div>
                <div class="text-[11px] text-slate-500">RPL &bull; Animasi &bull; DKV</div>
              </div>
              <div>
                <div class="text-lg font-bold text-slate-900">Real-Time</div>
                <div class="text-[11px] text-slate-500">Presensi Jam Ke-N</div>
              </div>
              <div>
                <div class="text-lg font-bold text-slate-900">WhatsApp</div>
                <div class="text-[11px] text-slate-500">Notifikasi Terpadu</div>
              </div>
            </div>
          </div>

          <!-- Gambar Gedung Sekolah Kanan (5 Kolom) - Alami & Nyata -->
          <div class="lg:col-span-5">
            <div class="bg-white p-2.5 rounded-2xl border border-slate-200 shadow-sm">
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
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                  Aktif
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- 4. Kompetensi Keahlian (Tampilan Putih & Abu-abu Netral) -->
    <section id="kejuruan" class="py-14 sm:py-16 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-10">
          <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Kompetensi Keahlian</span>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
            Jurusan Unggulan SMKN 71 Jakarta
          </h2>
          <p class="text-xs sm:text-sm text-slate-500">
            Presensi dan jurnal mengajar mencakup seluruh rombongan belajar kejuruan berikut.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <!-- RPL -->
          <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-12 h-12 bg-white rounded-lg p-2 border border-slate-200 shadow-2xs flex items-center justify-center">
                <img src="/images/logo-rpl.png" alt="RPL" class="w-full h-full object-contain" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-900">Rekayasa Perangkat Lunak</h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                  Pemrograman web, mobile, manajemen basis data, dan rekayasa software skala industri.
                </p>
              </div>
            </div>
            <div class="pt-4 border-t border-slate-200 mt-5 text-[11px] font-medium text-slate-500">
              Rombel: Kelas X, XI, XII RPL
            </div>
          </div>

          <!-- Animasi -->
          <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-12 h-12 bg-white rounded-lg p-2 border border-slate-200 shadow-2xs flex items-center justify-center">
                <img src="/images/logo-animasi.png" alt="Animasi" class="w-full h-full object-contain" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-900">Animasi 2D &amp; 3D</h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                  Pembuatan gerak visual, 3D modeling, visual effects, storyboard, dan produksi multimedia.
                </p>
              </div>
            </div>
            <div class="pt-4 border-t border-slate-200 mt-5 text-[11px] font-medium text-slate-500">
              Rombel: Kelas X, XI, XII Animasi
            </div>
          </div>

          <!-- DKV -->
          <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-12 h-12 bg-white rounded-lg p-2 border border-slate-200 shadow-2xs flex items-center justify-center">
                <img src="/images/logo-dkv.png" alt="DKV" class="w-full h-full object-contain" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-900">Desain Komunikasi Visual</h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                  Perancangan identitas visual, tipografi, fotografi, ilustrasi grafis, dan media komunikasi.
                </p>
              </div>
            </div>
            <div class="pt-4 border-t border-slate-200 mt-5 text-[11px] font-medium text-slate-500">
              Rombel: Kelas X, XI, XII DKV
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- 5. Alur Layanan KBM (Struktur Jelas) -->
    <section id="alur" class="py-14 sm:py-16 bg-slate-50 border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center space-y-2 mb-10">
          <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Alur Sistem</span>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
            Peran &amp; Tanggung Jawab Pengguna
          </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center">1</div>
            <div class="font-bold text-slate-900">Sekretaris Kelas</div>
            <p class="text-slate-500 leading-relaxed">Mencatat presensi siswa hadir, izin, sakit, atau alpa di setiap jam pelajaran.</p>
          </div>

          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center">2</div>
            <div class="font-bold text-slate-900">Guru Pengajar</div>
            <p class="text-slate-500 leading-relaxed">Mengisi jurnal materi pelajaran, capaian kompetensi, dan catatan ketertiban KBM.</p>
          </div>

          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center">3</div>
            <div class="font-bold text-slate-900">Guru Pembimbing</div>
            <p class="text-slate-500 leading-relaxed">Memvalidasi surat keterangan sakit/izin dokter dan memantau rekap absensi rombel.</p>
          </div>

          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center">4</div>
            <div class="font-bold text-slate-900">Super Administrator</div>
            <p class="text-slate-500 leading-relaxed">Pengelolaan akun pengguna, master data mata pelajaran, dan konfigurasi sistem.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. Informasi Kampus & Kontak (Standar Web Sekolah) -->
    <section id="kontak" class="py-12 bg-white text-xs border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <div class="space-y-2.5">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 p-1 flex items-center justify-center">
                <img src="/images/logo-smkn71.png" alt="SMKN 71" class="w-full h-full object-contain" />
              </div>
              <span class="font-bold text-slate-900">SMK Negeri 71 Jakarta</span>
            </div>
            <p class="text-slate-500 leading-relaxed">
              Sekolah Menengah Kejuruan Negeri di lingkungan Dinas Pendidikan Provinsi DKI Jakarta.
            </p>
          </div>

          <div class="space-y-1.5">
            <div class="font-bold text-slate-900 uppercase text-[11px]">Alamat Sekolah</div>
            <p class="text-slate-500 leading-relaxed">
              Jl. Raden Saleh No. 45, Cempaka Putih Timur, Kec. Cempaka Putih, Kota Jakarta Pusat, DKI Jakarta 10510
            </p>
            <p class="text-slate-500 pt-1">Telp: (021) 4241646 &bull; Email: info@smkn71jakarta.sch.id</p>
          </div>

          <div class="space-y-1.5">
            <div class="font-bold text-slate-900 uppercase text-[11px]">Tautan Terkait</div>
            <ul class="space-y-1 text-slate-600 font-medium">
              <li><NuxtLink to="/login" class="hover:text-blue-700 transition">&bull; Masuk Portal Presensi</NuxtLink></li>
              <li><a href="https://smkn71jakarta.sch.id" target="_blank" class="hover:text-blue-700 transition">&bull; Website Resmi Sekolah &nearr;</a></li>
              <li><a href="https://siap71.smkn71jakarta.sch.id" target="_blank" class="hover:text-blue-700 transition">&bull; LMS SIAP 71 &nearr;</a></li>
            </ul>
          </div>

        </div>
      </div>
    </section>

    <!-- 7. Footer Hak Cipta -->
    <footer class="bg-slate-100 py-6 text-xs text-slate-500 border-t border-slate-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left text-[11px]">
        <div>
          &copy; {{ new Date().getFullYear() }} <span class="font-semibold text-slate-700">SMK Negeri 71 Jakarta</span>. Hak Cipta Dilindungi.
        </div>
        <div>
          Sistem Jurnal KBM &amp; Presensi Kelas
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ArrowRight, ChevronRight } from 'lucide-vue-next'

definePageMeta({
  layout: false
})
</script>
