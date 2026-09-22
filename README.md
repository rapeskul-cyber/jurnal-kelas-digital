# Jurnal Kelas Digital v2.0 - Laravel 12 + Nuxt 3

## 📋 Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Nuxt 3 (Vue 3)
- **Styling:** Tailwind CSS
- **UI Animations:** Vue CSS transitions (Framer Motion equivalent)
- **Database:** MySQL 8.0 / PostgreSQL 15

## 🚀 Quick Start

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configure .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurnal_kelas
DB_USERNAME=youruser
DB_PASSWORD=yourpass

php artisan migrate
php artisan serve --host=0.0.0.0
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
# Access at http://localhost:3000
```

## 📁 Project Structure

### Backend (Laravel 12)

```
backend/
├── app/
│   ├── Console/Commands/
│   │   └── GenerateDailyReport.php    # 18:00 daily cron job
│   ├── Http/Controllers/
│   │   ├── JournalController.php     # CRUD jurnal
│   │   ├── AttendanceController.php  # Kehadiran siswa
│   │   ├── RatingController.php      # Rating guru
│   │   └── DashboardController.php   # Auth & summary
│   ├── Models/
│   │   ├── User.php
│   │   ├── ClassModel.php
│   │   ├── JournalEntry.php
│   │   ├── Attendance.php
│   │   └── TeacherRating.php
│   └── ...
├── database/migrations/
│   └── 2025_01_01_000001_create_all_tables.php
├── routes/
│   ├── api.php                        # API endpoints
│   └── console.php                    # Schedule cron jobs
└── composer.json
```

### Frontend (Nuxt 3)

```
frontend/
├── app.vue
├── layouts/
│   └── default.vue                    # Sidebar + main layout
├── pages/
│   └── index.vue                      # Jurnal feed with animations
├── assets/styles/
│   └── main.css                       # Tailwind + animations
├── nuxt.config.ts
├── tailwind.config.js
└── package.json
```

## 📊 Database Schema

| Table | Key Features |
|-------|-------------|
| `users` | Role-based access (admin/guru/siswa/ortu) |
| `classes` | Academic year grouping |
| `journal_entries` | Full-text search, attachments, tags |
| `attendances` | UNIQUE(student_id, subject_id, date) |
| `teacher_ratings` | UNIQUE(student_id, teacher_id, date) |

## 🎨 UI Features

- **Sidebar navigation** - responsive collapse
- **Real-time search** - filter journals instantly
- **Animated cards** - Vue transitions (Framer Motion equivalent)
- **Mobile-first** - responsive design
- **Empty states** - animated fallbacks

## 🔧 API Endpoints

```
GET    /api/v1/health                # Health check
POST   /api/v1/auth/login            # Login
GET    /api/v1/journals              # List journals
POST   /api/v1/journals              # Create journal
POST   /api/v1/attendance            # Record attendance
GET    /api/v1/attendance            # List attendance
POST   /api/v1/ratings               # Rate teacher
GET    /api/v1/ratings/{id}          # Teacher ratings summary
```

## ⏰ Cron Jobs

- **18:00 daily** - Auto-generate PDF/Excel daily reports

## 📦 Deploy

```bash
# Backend → Railway/Render/Hostinger
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan migrate

# Frontend → Vercel/Netlify/Nitro
npm install
npm run build
npm run preview
```

## 📄 Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0 / PostgreSQL 15
