<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Term;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Broadcast;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Term
        $term = Term::create([
            'name' => '2025/2026 - Ganjil',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'effective_days' => 108,
            'is_active' => true,
        ]);

        // 0. Superadmin (Administrator Sistem)
        $superadmin = User::create([
            'name' => 'Administrator Sistem',
            'email' => 'superadmin@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'phone' => '6281299998888',
            'nip_nis' => 'ADMIN001',
            'role' => 'SUPERADMIN',
            'is_active' => true,
        ]);

        // Default School Settings
        Setting::set('school_name', 'SMK Negeri 1 Digital', 'general', 'Nama Resmi Sekolah');
        Setting::set('school_npsn', '20104567', 'general', 'Nomor Pokok Sekolah Nasional');
        Setting::set('school_address', 'Jl. Pendidikan Industri No. 45, Jakarta', 'general', 'Alamat Sekolah');
        Setting::set('headmaster_name', 'Dr. H. Bambang Soeprapto, M.Pd', 'general', 'Nama Kepala Sekolah');
        Setting::set('headmaster_nip', '197505101999031002', 'general', 'NIP Kepala Sekolah');
        Setting::set('default_school_hours', '07:30 - 15:30', 'academic', 'Jam Masuk Sekolah Standar');
        Setting::set('attendance_lock_pin', '1234', 'security', 'PIN Buka Kunci Presensi');
        Setting::set('system_status', 'NORMAL', 'system', 'Status Operasional Sistem');

        // 2. Classes across RPL, DKV, and Animasi
        $class1 = SchoolClass::create(['name' => 'XI RPL 1', 'major' => 'RPL']);
        $class2 = SchoolClass::create(['name' => 'XI RPL 2', 'major' => 'RPL']);
        $class3 = SchoolClass::create(['name' => 'XII RPL 1', 'major' => 'RPL']);
        $classDKV1 = SchoolClass::create(['name' => 'XI DKV 1', 'major' => 'DKV']);
        $classDKV2 = SchoolClass::create(['name' => 'X DKV 1', 'major' => 'DKV']);
        $classAnim1 = SchoolClass::create(['name' => 'X Animasi 1', 'major' => 'Animasi']);
        $classAnim2 = SchoolClass::create(['name' => 'XI Animasi 1', 'major' => 'Animasi']);

        // 3. Guru Pembimbing (Wali Kelas)
        $pembimbing = User::create([
            'name' => 'Siti Aminah, M.Pd',
            'email' => 'siti.aminah@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567801',
            'nip_nis' => '19890314002',
            'role' => 'GURU_PEMBIMBING',
            'class_id' => $class1->id,
            'is_active' => true,
        ]);

        $pembimbingDKV = User::create([
            'name' => 'Ratna Sari, S.Pd',
            'email' => 'ratna.sari@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567805',
            'nip_nis' => '19910420003',
            'role' => 'GURU_PEMBIMBING',
            'class_id' => $classDKV1->id,
            'is_active' => true,
        ]);

        $pembimbingAnim = User::create([
            'name' => 'Hendra Gunawan, S.Kom',
            'email' => 'hendra.gunawan@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567806',
            'nip_nis' => '19881115004',
            'role' => 'GURU_PEMBIMBING',
            'class_id' => $classAnim1->id,
            'is_active' => true,
        ]);

        // 4. Guru Pengajar (Mata Pelajaran)
        $pengajar = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi.santoso@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567802',
            'nip_nis' => '19840212001',
            'role' => 'GURU_PENGAJAR',
            'specialization' => 'Pemrograman Web & Basis Data',
            'is_active' => true,
        ]);

        $pengajarDKV = User::create([
            'name' => 'Dewi Lestari, S.Sn',
            'email' => 'dewi.lestari@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567807',
            'nip_nis' => '19870519005',
            'role' => 'GURU_PENGAJAR',
            'specialization' => 'Desain Grafis & Tipografi Digital',
            'is_active' => true,
        ]);

        $pengajarAnim = User::create([
            'name' => 'Fajar Nugroho, M.Kom',
            'email' => 'fajar.nugroho@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567808',
            'nip_nis' => '19900825006',
            'role' => 'GURU_PENGAJAR',
            'specialization' => 'Animasi 2D/3D & Character Design',
            'is_active' => true,
        ]);

        // Set advisor for classes
        $class1->update(['advisor_id' => $pembimbing->id]);
        $class2->update(['advisor_id' => $pembimbing->id]);
        $classDKV1->update(['advisor_id' => $pembimbingDKV->id]);
        $classAnim1->update(['advisor_id' => $pembimbingAnim->id]);

        // Teacher Class Relation
        $pengajar->taughtClasses()->attach([$class1->id, $class2->id, $class3->id]);
        $pengajarDKV->taughtClasses()->attach([$classDKV1->id, $classDKV2->id]);
        $pengajarAnim->taughtClasses()->attach([$classAnim1->id, $classAnim2->id]);

        // 5. Sekretaris Kelas
        $sekretaris = User::create([
            'name' => 'Rina Safitri',
            'email' => 'rina.safitri@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6285812343414',
            'nip_nis' => '102401',
            'role' => 'SEKRETARIS',
            'class_id' => $class1->id,
            'is_active' => true,
        ]);
        $class1->update(['secretary_id' => $sekretaris->id]);

        // 6. Siswa Biasa (Ahmad Fadillah)
        $siswaFadillah = User::create([
            'name' => 'Ahmad Fadillah',
            'email' => 'ahmad.fadillah@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '6281234567804',
            'nip_nis' => '102402',
            'role' => 'SISWA',
            'class_id' => $class1->id,
            'is_active' => true,
        ]);

        // 7. Siswa Lainnya di XI RPL 1 (Mockup names)
        $studentsData = [
            ['name' => 'Annisa Rahmawati', 'email' => 'annisa.rahmawati@gmail.com', 'nis' => '102403'],
            ['name' => 'Bambang Wijaya', 'email' => 'bambang.wijaya@gmail.com', 'nis' => '102404'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@gmail.com', 'nis' => '102405'],
            ['name' => 'Dinda Maharani', 'email' => 'dinda.maharani@gmail.com', 'nis' => '102406'],
            ['name' => 'Farhan Maulana', 'email' => 'farhan.maulana@gmail.com', 'nis' => '102407'],
            ['name' => 'Nabila Azzahra', 'email' => 'nabila.azzahra@gmail.com', 'nis' => '102408'],
            ['name' => 'Salsabila Putri', 'email' => 'salsabila.putri@gmail.com', 'nis' => '102409'],
            ['name' => 'Rizky Ramadhan', 'email' => 'rizky.ramadhan@gmail.com', 'nis' => '102410'],
            ['name' => 'Alif Pratama', 'email' => 'alif.pratama@gmail.com', 'nis' => '102411'],
            ['name' => 'Hanif Akbar', 'email' => 'hanif.akbar@gmail.com', 'nis' => '102412'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@gmail.com', 'nis' => '102413'],
            ['name' => 'Sarah Sinta', 'email' => 'sarah.sinta@gmail.com', 'nis' => '102414'],
            ['name' => 'Nadia Omara', 'email' => 'nadia.omara@gmail.com', 'nis' => '102415'],
            ['name' => 'Andi Susanto', 'email' => 'andi.susanto@gmail.com', 'nis' => '102416'],
            ['name' => 'Aditya Pratama', 'email' => 'aditya.pratama@gmail.com', 'nis' => '102417'],
            ['name' => 'Dimas Anggara', 'email' => 'dimas.anggara@gmail.com', 'nis' => '102418'],
            ['name' => 'Eka Prasetya', 'email' => 'eka.prasetya@gmail.com', 'nis' => '102419'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar.nugraha@gmail.com', 'nis' => '102420'],
            ['name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan@gmail.com', 'nis' => '102421'],
            ['name' => 'Hafiz Alamsyah', 'email' => 'hafiz.alamsyah@gmail.com', 'nis' => '102422'],
            ['name' => 'Indah Permata', 'email' => 'indah.permata@gmail.com', 'nis' => '102423'],
            ['name' => 'Joko Triyono', 'email' => 'joko.triyono@gmail.com', 'nis' => '102424'],
            ['name' => 'Kirana Dewi', 'email' => 'kirana.dewi@gmail.com', 'nis' => '102425'],
            ['name' => 'Luthfi Hakim', 'email' => 'luthfi.hakim@gmail.com', 'nis' => '102426'],
            ['name' => 'Mega Utami', 'email' => 'mega.utami@gmail.com', 'nis' => '102427'],
            ['name' => 'Nurul Hidayah', 'email' => 'nurul.hidayah@gmail.com', 'nis' => '102428'],
            ['name' => 'Oki Setiawan', 'email' => 'oki.setiawan@gmail.com', 'nis' => '102429'],
            ['name' => 'Putri Ayu', 'email' => 'putri.ayu@gmail.com', 'nis' => '102430'],
            ['name' => 'Qori Santika', 'email' => 'qori.santika@gmail.com', 'nis' => '102431'],
            ['name' => 'Reza Fahlevi', 'email' => 'reza.fahlevi@gmail.com', 'nis' => '102432'],
            ['name' => 'Syahrul Gunawan', 'email' => 'syahrul.gunawan@gmail.com', 'nis' => '102433'],
            ['name' => 'Taufik Hidayat', 'email' => 'taufik.hidayat@gmail.com', 'nis' => '102434'],
            ['name' => 'Wahyu Pratama', 'email' => 'wahyu.pratama@gmail.com', 'nis' => '102435'],
            ['name' => 'Zahra Amelia', 'email' => 'zahra.amelia@gmail.com', 'nis' => '102436'],
        ];

        $class1Students = [$sekretaris, $siswaFadillah];
        $allStudents = [$sekretaris, $siswaFadillah];
        foreach ($studentsData as $idx => $s) {
            $targetClassId = $class1->id;
            if ($idx >= 20 && $idx < 24) {
                $targetClassId = $classDKV1->id;
            } elseif ($idx >= 24) {
                $targetClassId = $classAnim1->id;
            }

            $student = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password123'),
                'phone' => '62858' . str_pad($idx + 30, 8, '0', STR_PAD_LEFT),
                'nip_nis' => $s['nis'],
                'role' => 'SISWA',
                'class_id' => $targetClassId,
                'is_active' => true,
            ]);
            $allStudents[] = $student;
            if ($targetClassId === $class1->id) {
                $class1Students[] = $student;
            }
        }

        // 8. Subjects across RPL, DKV, Animasi, and Umum
        $subPW = Subject::create(['name' => 'Pemrograman Web & Mobile', 'code' => 'RPL-PW', 'major' => 'RPL', 'teacher_id' => $pengajar->id]);
        $subBD = Subject::create(['name' => 'Basis Data & SQL', 'code' => 'RPL-BD', 'major' => 'RPL', 'teacher_id' => $pengajar->id]);
        $subDG = Subject::create(['name' => 'Desain Grafis & Tipografi', 'code' => 'DKV-DG', 'major' => 'DKV', 'teacher_id' => $pengajarDKV->id]);
        $subUI = Subject::create(['name' => 'UI/UX Design Digital', 'code' => 'DKV-UX', 'major' => 'DKV', 'teacher_id' => $pengajarDKV->id]);
        $subA2D = Subject::create(['name' => 'Animasi 2D & Stop Motion', 'code' => 'ANI-2D', 'major' => 'Animasi', 'teacher_id' => $pengajarAnim->id]);
        $subA3D = Subject::create(['name' => 'Animasi 3D & Rigging Karakter', 'code' => 'ANI-3D', 'major' => 'Animasi', 'teacher_id' => $pengajarAnim->id]);
        $subKWU = Subject::create(['name' => 'Kreatif Kewirausahaan', 'code' => 'UMUM-KWU', 'major' => 'UMUM', 'teacher_id' => $pembimbing->id]);

        // 9. Schedules
        $sch1 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subPW->id,
            'teacher_id' => $pengajar->id,
            'day' => 'Kamis',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Lab. RPL 1',
        ]);
        $sch2 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subBD->id,
            'teacher_id' => $pengajar->id,
            'day' => 'Kamis',
            'start_time' => '09:15',
            'end_time' => '11:45',
            'room' => 'Lab. RPL 1',
        ]);
        $sch3 = Schedule::create([
            'class_id' => $classDKV1->id,
            'subject_id' => $subDG->id,
            'teacher_id' => $pengajarDKV->id,
            'day' => 'Kamis',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'room' => 'Studio DKV',
        ]);
        $sch4 = Schedule::create([
            'class_id' => $classAnim1->id,
            'subject_id' => $subA2D->id,
            'teacher_id' => $pengajarAnim->id,
            'day' => 'Jumat',
            'start_time' => '07:30',
            'end_time' => '10:00',
            'room' => 'Studio Animasi 1',
        ]);
        $sch5 = Schedule::create([
            'class_id' => $class1->id,
            'subject_id' => $subKWU->id,
            'teacher_id' => $pembimbing->id,
            'day' => 'Jumat',
            'start_time' => '13:00',
            'end_time' => '15:30',
            'room' => 'Lab. RPL 1',
        ]);

        // 10. Attendance Sessions & Records (Today and Past Days)
        $today = Carbon::today();
        $sessionToday = AttendanceSession::create([
            'class_id' => $class1->id,
            'date' => $today->toDateString(),
            'status' => 'LOCKED',
            'locked_by_id' => $sekretaris->id,
            'locked_at' => $today->copy()->setTime(8, 0),
            'verification_status' => 'TERVERIFIKASI',
            'school_hours' => '07:30 - 15:30',
            'room' => 'Lab. 1',
        ]);

        foreach ($class1Students as $std) {
            $status = 'HADIR';
            $note = null;
            if ($std->nip_nis === '102403') { // Annisa Rahmawati
                $status = 'SAKIT';
                $note = 'Demam sejak pagi, surat dokter terlampir';
            } elseif ($std->nip_nis === '102409') { // Salsabila Putri
                $status = 'IZIN';
                $note = 'Keperluan keluarga di luar kota';
            } elseif ($std->nip_nis === '102410') { // Rizky Ramadhan
                $status = 'ALPHA';
                $note = 'Belum ada keterangan';
            }

            AttendanceRecord::create([
                'session_id' => $sessionToday->id,
                'student_id' => $std->id,
                'status' => $status,
                'note' => $note,
            ]);
        }

        // Add 3 past sessions to provide realistic charts & monthly history
        for ($i = 1; $i <= 3; $i++) {
            $pastDate = $today->copy()->subDays($i * 2);
            $pastSession = AttendanceSession::create([
                'class_id' => $class1->id,
                'date' => $pastDate->toDateString(),
                'status' => 'LOCKED',
                'locked_by_id' => $sekretaris->id,
                'locked_at' => $pastDate->copy()->setTime(8, 15),
                'verification_status' => 'TERVERIFIKASI',
                'school_hours' => '07:30 - 15:30',
                'room' => 'Lab. 1',
            ]);

            foreach ($class1Students as $std) {
                AttendanceRecord::create([
                    'session_id' => $pastSession->id,
                    'student_id' => $std->id,
                    'status' => 'HADIR',
                    'note' => 'Presensi terverifikasi',
                ]);
            }
        }

        // 11. Class Journals (Jurnal Kelas)
        ClassJournal::create([
            'class_id' => $class1->id,
            'schedule_id' => $sch1->id,
            'date' => $today->toDateString(),
            'activity' => 'Optimasi Query relasional dan implementasi routing SPA di web sekolah.',
            'notes' => 'Seluruh siswa mempraktikkan konfigurasi API.',
            'verification_status' => 'TERVERIFIKASI',
            'created_by_id' => $sekretaris->id,
            'confirmed_by_id' => $pengajar->id,
            'confirmed_at' => $today->copy()->setTime(9, 5),
        ]);

        ClassJournal::create([
            'class_id' => $class1->id,
            'schedule_id' => $sch2->id,
            'date' => $today->toDateString(),
            'activity' => 'Rancangan ERD lanjutan dan perbaikan skema database normalisasi 3NF.',
            'notes' => 'Menunggu verifikasi guru pengajar.',
            'verification_status' => 'PENDING',
            'created_by_id' => $sekretaris->id,
        ]);

        // 12. Material Journals (Jurnal Materi Guru)
        MaterialJournal::create([
            'teacher_id' => $pengajar->id,
            'class_id' => $class1->id,
            'subject_id' => $subPW->id,
            'schedule_id' => $sch1->id,
            'date' => $today->toDateString(),
            'topic' => 'Optimasi Query relasional dan teknik routing SPA.',
            'plan' => 'Membahas arsitektur komponen, state management, dan fetch data API.',
            'assignment' => 'Siswa ditugaskan membuat rancangan CRUD mandiri.',
        ]);

        MaterialJournal::create([
            'teacher_id' => $pengajar->id,
            'class_id' => $class1->id,
            'subject_id' => $subBD->id,
            'schedule_id' => $sch2->id,
            'date' => $today->toDateString(),
            'topic' => 'Rancangan ERD lanjutan dan perbaikan Schema.',
            'plan' => 'Normalisasi tabel hingga 3NF dan pembuatan index relasi.',
            'assignment' => 'Latihan migrasi database dan pengujian relasi foreign key.',
        ]);

        MaterialJournal::create([
            'teacher_id' => $pembimbing->id,
            'class_id' => $class1->id,
            'subject_id' => $subKWU->id,
            'schedule_id' => $sch5->id,
            'date' => $today->copy()->subDays(1)->toDateString(),
            'topic' => 'Briefing persiapan proposal bisnis dan portofolio.',
            'plan' => 'Penjelasan rancangan bisnis digital dan pitch deck.',
            'assignment' => 'Penyusunan resume profil portofolio siswa.',
        ]);

        // 13. Leave Requests (Surat Izin)
        $leaveAnnisa = LeaveRequest::create([
            'student_id' => $allStudents[2]->id, // Annisa Rahmawati
            'submitted_by_id' => $sekretaris->id,
            'type' => 'SAKIT',
            'start_date' => $today->toDateString(),
            'end_date' => $today->copy()->addDays(2)->toDateString(),
            'recipient' => 'Guru Pembimbing Sekolah / Wali Kelas',
            'reason' => 'Mengalami gejala demam tinggi dan flu berat sejak semalam, disarankan istirahat oleh dokter klinik keluarga demi pemulihan optimal.',
            'status' => 'PENDING',
        ]);

        $leaveFadillah = LeaveRequest::create([
            'student_id' => $siswaFadillah->id,
            'submitted_by_id' => $sekretaris->id,
            'type' => 'SAKIT',
            'start_date' => $today->copy()->subDays(4)->toDateString(),
            'end_date' => $today->copy()->subDays(3)->toDateString(),
            'recipient' => 'Guru Pembimbing Sekolah / Wali Kelas',
            'reason' => 'Demam dan flu, istirahat di rumah.',
            'status' => 'APPROVED',
            'reviewer_id' => $pembimbing->id,
            'review_note' => 'Disetujui. Semoga lekas sembuh.',
            'reviewed_at' => $today->copy()->subDays(4)->setTime(10, 0),
        ]);

        // 14. Notifications
        Notification::create([
            'user_id' => $pembimbing->id,
            'type' => 'SURAT_IZIN',
            'title' => 'Permohonan Surat Izin Baru',
            'body' => 'Sekretaris Rina Safitri mengajukan surat izin sakit untuk Annisa Rahmawati (XI RPL 1).',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $pengajar->id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Kelas Menunggu Konfirmasi',
            'body' => 'Jurnal kelas mata pelajaran Basis Data (XI RPL 1) menunggu konfirmasi dari Anda.',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $siswaFadillah->id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin Disetujui',
            'body' => 'Surat izin sakit Anda telah disetujui oleh Guru Pembimbing Siti Aminah, M.Pd.',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $siswaFadillah->id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Materi Telah Dikirim',
            'body' => 'Guru Pemateri telah mengirimkan Materi Baru untuk Pemrograman Web.',
            'read_at' => null,
        ]);

        // 15. Audit Logs
        AuditLog::create([
            'user_id' => $sekretaris->id,
            'action' => 'Mengirimkan data presensi & draf jurnal harian XI RPL 1',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $pengajar->id,
            'action' => 'Mengisi Jurnal Materi Pemrograman Web',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $sekretaris->id,
            'action' => 'Mengajukan permohonan surat izin sakit Annisa Rahmawati',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
