# SESSION STATE: JURNAL KELAS DIGITAL
**Terakhir Diperbarui:** 31 Oktober 2026  
**Dokumentasi Konteks Sesi & Status Pengerjaan (Agar Tidak Mulai Dari Nol)**

---

## 1. Arsitektur & Lingkungan Sistem
- **Frontend**: Nuxt 3 (SSR: false / SPA mode, Tailwind CSS, Pinia, Lucide Icons). Port dev: `3001`, Port prod VPS: `3010`.
- **Backend**: Laravel 11 REST API, SQLite (`database/database.sqlite`), Laravel Sanctum. Port prod VPS: `8010`.
- **Server VPS Produksi**:
  - IP: `100.86.2.20` (User: `agentuser`, SSH Key: `C:\Users\User\.ssh\id_ed25519`)
  - Domain Publik: `https://jurnal.rapiuw.ai.id/`
  - Nginx Reverse Proxy di VPS: `/api/` -> `127.0.0.1:8010`, `/` -> `127.0.0.1:3010`.
- **Cloudflare Turnstile (Anti-Bot)**:
  - Site Key: `0x4AAAAAAFtrjddNjoj2MXoQ`
  - Secret Key: `[Dikonfigurasi via .env: TURNSTILE_SECRET_KEY]`
  - Endpoint login `/api/auth/login` memvalidasi Turnstile ke Cloudflare API `siteverify` dengan strict fail-closed.

---

## 2. Aturan & Standar Proyek
1. **Skema Warna**: Wajib konsisten Blue & White (`#0f47ad`, `#ffffff`, `bg-primary`, `text-primary`). Hindari warna acak.
2. **Keterpaduan Antar Role**: Jika mengubah fitur di satu role yang terhubung ke role lain, **wajib mengupdate kedua role** agar sinkron.
3. **Dokumentasi & Integritas**: Tidak boleh menghapus komentar dan logika yang sudah berjalan.

---

## 3. Status Terkini Seluruh 6 Role

### A. Role yang Sudah Selesai 1000% (Teruji & Terverifikasi di VPS)
1. **SISWA**:
   - Presensi mandiri (jam masuk & pulang sesuai jadwal).
   - Pengajuan surat izin (sakit, izin, dispensasi) + upload berkas bukti valid.
   - Alur penolakan izin: Alasan dari walas tampil jelas, tombol presensi mandiri terkunci, diarahkan verifikasi fisik ke Sekretaris di kelas.
   - Jurnal Kegiatan Mandiri (`/jurnal-pelajaran`): Privat (hanya siswa dan orang tuanya yang bisa membaca catatan refleksi, walas tidak dibebani).
   - Riwayat kehadiran dinamis dan profil akun terhubung WhatsApp.

2. **SEKRETARIS**:
   - Presensi Kelas Harian (`/presensi`): Absensi per jam, verifikasi fisik, penanda khusus siswa izin ditolak, dan kunci presensi harian.
   - Buku Agenda KBM Kelas (`/jurnal-kelas`): Pencatatan mapel, materi yang diajarkan, dan guru pengajar.
   - Identitas ganda: Dilengkapi kartu status kehadiran mandiri sekretaris di dashboard, menu catatan refleksi privat, dan pengajuan izin sendiri jika berhalangan.
   - Regulasi kuota mutlak: Maksimal 2 dan minimal 1 sekretaris per kelas dengan modal cerdas pergantian.
   - Akun orang tua sekretaris: Dibuatkan dan terintegrasi otomatis.

3. **ORANG TUA**:
   - Portal pantauan status kehadiran anak hari ini secara realtime.
   - Pantauan catatan kegiatan/refleksi belajar harian anak.
   - Riwayat kehadiran & surat izin anak lengkap dengan status persetujuan walas.
   - Akun dibuat dan dikelola langsung oleh Wali Kelas.

4. **GURU_PEMBIMBING (Wali Kelas)**:
   - Persetujuan Surat Izin (`/persetujuan-izin`): Setujui / tolak dengan input alasan wajib.
   - Manajemen Murid (`/manajemen-murid`): Isolasi kelas ketat, integrasi pembuatan Akun Ortu Murid, dan pengangkatan sekretaris kelas (kuota 1-2).
   - Sebanyak 22 akun Wali Kelas sudah aktif untuk seluruh 21 kelas.

5. **GURU_PENGAJAR (Guru Mata Pelajaran)** *(Tuntas 100%)*:
   - `frontend/pages/jurnal-materi.vue`: Seluruh kartu ringkasan (Total Kelas Diampu, Jurnal Hari Ini, Menunggu Verifikasi) 100% dinamis terhitung dari API jadwal dan jurnal KBM riil.
   - Fallback jadwal dummy telah dihapus; diganti empty state dinamis dan tab navigasi filter hari (Hari Ini, Semua, Senin-Jumat).
   - Dropdown pilihan kelas tujuan pada LMS materi dan modal Tambah Jadwal 100% dinamis dari database.
   - Alur konfirmasi dan validasi paraf agenda KBM terhubung ke API backend `/journals/class/{id}/confirm`.

---

### B. Role / Fitur yang Masih Perlu Ditesting & Dipoles (Opsional / Admin Level)
1. **SUPERADMIN (Administrator Sistem)**:
   - **Tampilan Badge & Tabel Pengguna (`/manajemen-user`)**:
     - *Fixed*: Memperbaiki bug styling warna teks/background badge `Super Administrator` (`ADMIN001` / `superadmin@sekolah.sch.id`) dari blok gelap pekat `bg-primary text-white border border-primary` menjadi konsisten sesuai tema Blue & White (`bg-blue-50 text-primary border border-blue-200`).
   - **WhatsApp Gateway Pairing (`/wa-gateway`)**: Memastikan perangkat WhatsApp terhubung ke gateway lokal di VPS (port 3000) jika ingin mengaktifkan pengiriman notifikasi broadcast via WA.
   - **Testing End-to-End Presensi Override (`/presensi-override`)**: Membuka kunci sesi kelas tertentu secara live dan memverifikasi sekretaris menginput data ulang.
   - **Pengaturan Sistem Sekolah (`/pengaturan-sistem`)**: Update nama institusi sekolah, NPSN, dan jam toleransi sekolah via antarmuka UI.

---

## 4. Hardening & Audit Keamanan Terkini (31 Oktober 2026)
1. **Rate Limiting & Cloudflare Turnstile pada OTP WhatsApp (`POST /api/auth/forgot-password/step-1`)**:
   - Diberlakukan middleware `throttle:3,10` (maksimal 3 percobaan per 10 menit per IP).
   - Validasi ketat token Turnstile di baris awal sebelum trigger pengiriman OTP via WA Gateway; request tanpa Turnstile/dummy ditolak HTTP 422.
   - Frontend `forgot-password.vue` terintegrasi dengan widget Turnstile.
2. **Server Information Disclosure Closed (Leak Nginx Error 405)**:
   - Diaktifkan `server_tokens off;` di `/etc/nginx/nginx.conf`.
   - Error 405 pada Nginx ditangani via named handler `@json_405` mengembalikan JSON `{"message":"Method Not Allowed."}` tanpa header versi/OS (`Server: nginx`).
   - Exception handler di Laravel `bootstrap/app.php` menangani `MethodNotAllowedHttpException` dalam format JSON standar.
   - Geo-blocking untuk endpoint API mengembalikan JSON 403 Forbidden langsung alih-alih redirect ke file HTML statis.
3. **Strict Cloudflare Turnstile Enforcement pada Login (`POST /api/auth/login`)**:
   - Pengecekan `turnstile_token` dieksekusi di baris paling awal controller `AuthController::login` sebelum memvalidasi email atau memverifikasi password.
   - Request login tanpa parameter `turnstile_token` langsung ditolak seketika (HTTP 422).
   - Reverse proxy di Laravel dikonfigurasi `trustProxies(at: '*')` sehingga IP asli klien Cloudflare terdeteksi dengan tepat untuk rate limiting.

---

## 5. Adaptasi & Optimasi Mobile UI/UX (Selesai 100% & Terverifikasi CDP)
- **Viewport & Global Breakpoints (Tahap 1)**:
  - Meta Viewport: `width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover`.
  - PWA meta tags: `theme-color: #0f47ad`, `apple-mobile-web-app-capable: yes`.
  - Global CSS: `max-width: 100vw; overflow-x: hidden; -webkit-text-size-adjust: 100%` pada `html, body`.
- **Navigasi & Shell Aplikasi (Tahap 2)**:
  - Desktop sidebar disembunyikan di `< 768px`.
  - Navigasi mobile menggunakan **Bottom Navigation Bar** (`MobileNav.vue`) dengan touch target >= 48px dan drawer "Lainnya" ber-safe area.
  - Safe-area insets (`env(safe-area-inset-top)`, `env(safe-area-inset-bottom)`) diterapkan pada top header, bottom bar, dan bottom sheets.
- **Transformasi Data Tables ke Card List & Modal ke Bottom Sheet (Tahap 3)**:
  - Seluruh tabel berat (presensi siswa, agenda KBM kelas, refleksi belajar, modul/materi guru, manajemen user, manajemen murid, manajemen guru, master data kelas/mapel/jadwal/semester, audit log, rekapitulasi semester, dan presensi override) ditransformasi:
    - Di desktop: `hidden md:block overflow-x-auto`.
    - Di mobile: `block md:hidden` dengan tata letak kartu mandiri yang bersih, ergonomis, dan mudah dibaca tanpa geser horizontal.
  - Seluruh modal popup desktop tengah ditransformasi menjadi **Bottom Sheet Drawer** (`flex items-end sm:items-center`, `rounded-t-3xl sm:rounded-2xl`, drag handle bar atas, `pb-safe`, scrolling vertikal `max-h-[90vh]`).
- **Ergonomi Sentuhan & Form (Tahap 4)**:
  - Seluruh tombol interaktif, link, switch, dan input memiliki hit area minimal **44x44px** atau **48x48px**.
  - Font size pada seluruh input form di mobile dipatok minimal **16px** (1rem) melalui CSS rule `@media screen and (max-width: 640px) { input, select, textarea { font-size: 16px !important; } }` sehingga 100% kebal dari auto-zoom otomatis iOS Safari.
  - Input OTP menggunakan `inputmode="numeric"` dan `pattern="[0-9]*"`.
- **Bukti Nyata Pengujian (Evidence-Gated, Tahap 5)**:
  - Build produksi lokal (`npm run build`) dan VPS Nuxt 3 lolos dengan 0 error sintaks/template.
  - Pengujian browser headless (Chrome/Edge DevTools Protocol) dijalankan pada 3 viewport mobile riil:
    - **iPhone X / 11 / 13 mini (375x812 @2x)**: ClientWidth 375px, ScrollWidth 375px, Zero Overflow Leak (0 element leaking), 100% Input >= 16px.
    - **iPhone 12 / 13 / 14 / 15 (390x844 @3x)**: ClientWidth 390px, ScrollWidth 390px, Zero Overflow Leak (0 element leaking), 100% Input >= 16px.
    - **Android Compact (360x800 @2x)**: ClientWidth 360px, ScrollWidth 360px, Zero Overflow Leak (0 element leaking), 100% Input >= 16px.

---

## 6. Cara Deploy Cepat ke Server VPS
Jika ada perubahan kode di lokal:
- Update file backend/frontend ke VPS menggunakan `scp` atau `cat` via SSH.
- Untuk backend: Jalankan `php artisan config:clear` dan restart `php artisan serve --port=8010`.
- Untuk frontend: Masuk ke folder `frontend`, jalankan `npm run build`, lalu restart proses node di port `3010`.

