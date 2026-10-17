# 🏫 Jurnal Kelas Digital

Sistem Informasi Manajemen Presensi, Jurnal Pembelajaran, Rekap Absensi, dan Perizinan Kelas Berbasis Web.

Repositori ini menampung dua varian arsitektur:
1. **`desktop/`**: Versi tampilan desktop (sumber asli [Jimmy0F/kelas-jurnal-digital](https://github.com/Jimmy0F/kelas-jurnal-digital)).
2. **`mobile/`**: Versi yang dioptimasi khusus untuk layar ponsel/mobile (tampilan responsif ringkas, mobile navigation bar, dan penyesuaian touch screen).

---

## 📂 Struktur Repositori

```text
jurnal-kelas-digital/
├── desktop/                  # Versi Desktop (Nuxt 3 Frontend + Laravel 11 Backend)
│   ├── backend/              # Laravel 11 REST API & Migrations
│   ├── frontend/             # Nuxt 3 Full-screen Web UI
│   └── docs/                 # Dokumentasi sistem & aset desain UI
│
├── mobile/                   # Versi Mobile-Optimized (Nuxt 3 + Laravel 11)
│   ├── backend/              # Laravel 11 API (Sanctum Auth)
│   ├── frontend/             # Nuxt 3 Mobile Viewport (MobileNav, Card-based layout)
│   └── docs/                 # Panduan implementasi & dokumen audit
│
└── README.md
```

---

## 🚀 Panduan Menjalankan

### 1. Menjalankan Versi Desktop:
```bash
# Backend (Laravel)
cd desktop/backend
composer install
php artisan migrate --seed
php artisan serve --port=8000

# Frontend (Nuxt 3)
cd ../frontend
npm install
npm run dev -- -p 3000
```

### 2. Menjalankan Versi Mobile:
```bash
# Backend (Laravel)
cd mobile/backend
composer install
php artisan migrate --seed
php artisan serve --port=8001

# Frontend (Nuxt 3 Mobile View)
cd ../frontend
npm install
npm run dev -- -p 3001
```

---

## 🔒 Catatan Keamanan
Kredensial database lokal (`.sqlite`), environment variables (`.env`), cache build, dan `node_modules` diabaikan secara ketat oleh `.gitignore` untuk mencegah kebocoran data sensitif.
