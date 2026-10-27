<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasIndustriSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. BU FADHILAH - Wali Kelas Industri (Guru Pembimbing)
            $fadhilah = User::updateOrCreate(
                ['email' => 'fadhilah@sekolah.sch.id'],
                [
                    'name' => 'Nurhayatul Fadilla, S.Pd',
                    'password' => Hash::make('password123'),
                    'phone' => '081288990011',
                    'nip_nis' => '198706152011012001',
                    'role' => 'GURU_PEMBIMBING',
                    'specialization' => 'Wali Kelas Industri & Konsentrasi RPL',
                    'is_active' => true,
                ]
            );

            // 2. PAK BAGAS - Guru Pengajar Kelas Industri
            $bagas = User::updateOrCreate(
                ['email' => 'bagas@sekolah.sch.id'],
                [
                    'name' => 'Bagas Pratama, S.Kom',
                    'password' => Hash::make('password123'),
                    'phone' => '081288990022',
                    'nip_nis' => '199003202015021002',
                    'role' => 'GURU_PENGAJAR',
                    'specialization' => 'Pemrograman Web, Mobile & IoT Industri',
                    'is_active' => true,
                ]
            );

            // 3. SUPERADMIN
            $superadmin = User::updateOrCreate(
                ['email' => 'superadmin@sekolah.sch.id'],
                [
                    'name' => 'Super Administrator',
                    'password' => Hash::make('password123'),
                    'phone' => '081299990001',
                    'nip_nis' => '198001012005011001',
                    'role' => 'SUPERADMIN',
                    'specialization' => 'Kepala Lab & Administrator Pusat',
                    'is_active' => true,
                ]
            );

            // 4. KELAS INDUSTRI
            $kelasIndustri = SchoolClass::firstOrCreate(
                ['name' => 'Kelas Industri'],
                [
                    'major' => 'RPL',
                    'advisor_id' => $fadhilah->id,
                ]
            );
            $kelasIndustri->advisor_id = $fadhilah->id;
            $kelasIndustri->save();

            // 5. SISWA KELAS INDUSTRI (15 Siswa: 7 dari XI RPL 1, 8 dari XI RPL 2)
            $studentsData = [
                // Dari XI RPL 1
                ['name' => 'Raffi Rasyid Rizqi', 'origin' => 'XI RPL 1', 'nis' => '102401', 'is_sec' => true, 'email' => 'raffi@sekolah.sch.id'],
                ['name' => 'Faishal Hisyam', 'origin' => 'XI RPL 1', 'nis' => '102402', 'is_sec' => false, 'email' => 'faishal@sekolah.sch.id'],
                ['name' => 'Jimmy Fadhilah', 'origin' => 'XI RPL 1', 'nis' => '102403', 'is_sec' => false, 'email' => 'jimmy@sekolah.sch.id'],
                ['name' => 'Vidhie Musyafa', 'origin' => 'XI RPL 1', 'nis' => '102404', 'is_sec' => false, 'email' => 'vidhie@sekolah.sch.id'],
                ['name' => 'Dira', 'origin' => 'XI RPL 1', 'nis' => '102405', 'is_sec' => false, 'email' => 'dira@sekolah.sch.id'],
                ['name' => 'Assyifa', 'origin' => 'XI RPL 1', 'nis' => '102406', 'is_sec' => false, 'email' => 'assyifa@sekolah.sch.id'],
                ['name' => 'Muhammad Girsan A', 'origin' => 'XI RPL 1', 'nis' => '102407', 'is_sec' => false, 'email' => 'girsan@sekolah.sch.id'],
                // Dari XI RPL 2
                ['name' => 'M Favian Gunawan', 'origin' => 'XI RPL 2', 'nis' => '102408', 'is_sec' => false, 'email' => 'favian@sekolah.sch.id'],
                ['name' => 'Rifqi', 'origin' => 'XI RPL 2', 'nis' => '102409', 'is_sec' => false, 'email' => 'rifqi@sekolah.sch.id'],
                ['name' => 'Rafa', 'origin' => 'XI RPL 2', 'nis' => '102410', 'is_sec' => false, 'email' => 'rafa@sekolah.sch.id'],
                ['name' => 'Akmal', 'origin' => 'XI RPL 2', 'nis' => '102411', 'is_sec' => false, 'email' => 'akmal@sekolah.sch.id'],
                ['name' => 'Taqiy', 'origin' => 'XI RPL 2', 'nis' => '102412', 'is_sec' => false, 'email' => 'taqiy@sekolah.sch.id'],
                ['name' => 'Adit', 'origin' => 'XI RPL 2', 'nis' => '102413', 'is_sec' => false, 'email' => 'adit@sekolah.sch.id'],
                ['name' => 'Nadila', 'origin' => 'XI RPL 2', 'nis' => '102414', 'is_sec' => false, 'email' => 'nadila@sekolah.sch.id'],
                ['name' => 'Safira', 'origin' => 'XI RPL 2', 'nis' => '102415', 'is_sec' => false, 'email' => 'safira@sekolah.sch.id'],
            ];

            $createdStudents = [];
            foreach ($studentsData as $idx => $st) {
                $role = $st['is_sec'] ? 'SEKRETARIS' : 'SISWA';
                $student = User::updateOrCreate(
                    ['email' => $st['email']],
                    [
                        'name' => $st['name'],
                        'password' => Hash::make('password123'),
                        'phone' => '0857' . str_pad($idx + 10, 8, '0', STR_PAD_LEFT),
                        'nip_nis' => $st['nis'],
                        'role' => $role,
                        'specialization' => "Asal {$st['origin']}",
                        'class_id' => $kelasIndustri->id,
                        'is_active' => true,
                    ]
                );

                $student->role = $role;
                $student->class_id = $kelasIndustri->id;
                $student->specialization = "Asal {$st['origin']}";
                $student->save();

                if ($st['is_sec']) {
                    $kelasIndustri->secretary_id = $student->id;
                    $kelasIndustri->save();
                }

                $createdStudents[] = $student;
            }

            // 6. MATA PELAJARAN KELAS INDUSTRI
            $subWeb = Subject::updateOrCreate(
                ['code' => 'IND-WEB'],
                [
                    'name' => 'Pemrograman Web & Mobile Industri',
                    'major' => 'RPL',
                ]
            );

            $subIoT = Subject::updateOrCreate(
                ['code' => 'IND-IOT'],
                [
                    'name' => 'Internet of Things (IoT) & Cloud Architecture',
                    'major' => 'RPL',
                ]
            );

            $subDB = Subject::updateOrCreate(
                ['code' => 'IND-DB'],
                [
                    'name' => 'Rekayasa Basis Data & API Gateway',
                    'major' => 'RPL',
                ]
            );

            $subPKK = Subject::updateOrCreate(
                ['code' => 'IND-PKK'],
                [
                    'name' => 'Proyek Kreatif & Kewirausahaan Industri (Startup Lab)',
                    'major' => 'RPL',
                ]
            );

            // Hubungkan Pak Bagas dan Bu Fadhilah ke Kelas Industri
            $bagas->taughtClasses()->syncWithoutDetaching([$kelasIndustri->id]);
            $fadhilah->taughtClasses()->syncWithoutDetaching([$kelasIndustri->id]);

            // 7. JADWAL MINGGUAN KELAS INDUSTRI (Senin s/d Jumat)
            Schedule::where('class_id', $kelasIndustri->id)->delete();

            $schedules = [
                ['day' => 'Senin', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Senin', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subDB->id, 'teacher_id' => $fadhilah->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Selasa', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Selasa', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subPKK->id, 'teacher_id' => $bagas->id, 'room' => 'Studio Startup 71'],
                ['day' => 'Rabu', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subIoT->id, 'teacher_id' => $bagas->id, 'room' => 'Lab IoT & Embedded'],
                ['day' => 'Rabu', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subDB->id, 'teacher_id' => $fadhilah->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Kamis', 'start_time' => '07:30', 'end_time' => '11:30', 'subject_id' => $subPKK->id, 'teacher_id' => $bagas->id, 'room' => 'Studio Startup 71'],
                ['day' => 'Kamis', 'start_time' => '12:45', 'end_time' => '15:00', 'subject_id' => $subWeb->id, 'teacher_id' => $bagas->id, 'room' => 'Lab RPL Industri'],
                ['day' => 'Jumat', 'start_time' => '07:30', 'end_time' => '11:00', 'subject_id' => $subIoT->id, 'teacher_id' => $bagas->id, 'room' => 'Lab IoT & Embedded'],
            ];

            foreach ($schedules as $sc) {
                Schedule::create(array_merge($sc, ['class_id' => $kelasIndustri->id]));
            }

            // 8. SESI PRESENSI HARIAN RIIL (Hari Ini)
            $today = Carbon::today()->format('Y-m-d');
            $session = AttendanceSession::updateOrCreate(
                [
                    'class_id' => $kelasIndustri->id,
                    'date' => $today,
                ],
                [
                    'status' => 'DRAFT',
                    'verification_status' => 'TERVERIFIKASI',
                    'school_hours' => '07:30 - 15:00',
                    'room' => 'Lab RPL Industri',
                ]
            );

            // Bersihkan data presensi hari ini untuk rombel ini
            AttendanceRecord::where('session_id', $session->id)->delete();

            // Isi presensi 15 siswa:
            // 13 Hadir, 1 Sakit (Vidhie Musyafa), 1 Izin (Nadila)
            foreach ($createdStudents as $st) {
                $status = 'HADIR';
                $notes = 'Hadir tepat waktu mengikuti KBM Industri';

                if ($st->name === 'Vidhie Musyafa') {
                    $status = 'SAKIT';
                    $notes = 'Surat keterangan dokter klinik terlampir';
                } elseif ($st->name === 'Nadila') {
                    $status = 'IZIN';
                    $notes = 'Dispensasi delegasi lomba kompetensi';
                }

                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $st->id,
                    'status' => $status,
                    'note' => $notes,
                ]);
            }

            // 9. PERMOHONAN SURAT IZIN SISWA KELAS INDUSTRI
            $vidhie = User::where('email', 'vidhie@sekolah.sch.id')->first();
            $nadila = User::where('email', 'nadila@sekolah.sch.id')->first();

            LeaveRequest::whereIn('student_id', [$vidhie->id, $nadila->id])->delete();

            LeaveRequest::create([
                'student_id' => $vidhie->id,
                'submitted_by_id' => $vidhie->id,
                'type' => 'SAKIT',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDays(2),
                'recipient' => 'Wali Kelas Industri / Bu Fadhilah',
                'reason' => 'Demam dan flu berat membutuhkan istirahat rawat jalan sesuai petunjuk dokter.',
                'status' => 'PENDING',
            ]);

            LeaveRequest::create([
                'student_id' => $nadila->id,
                'submitted_by_id' => $nadila->id,
                'type' => 'IZIN',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDay(),
                'recipient' => 'Wali Kelas Industri / Bu Fadhilah',
                'reason' => 'Mengikuti pembinaan intensif persiapan LKS Web Technologies mewakili sekolah.',
                'status' => 'PENDING',
            ]);

            DB::commit();
            echo "SUCCESS: Skala Kelas Industri SMKN 71 berhasil disiapkan!\n";
            echo "- Wali Kelas: Bu Fadhilah (fadhilah@sekolah.sch.id)\n";
            echo "- Guru Pengajar: Pak Bagas (bagas@sekolah.sch.id)\n";
            echo "- 15 Siswa (7 XI RPL 1, 8 XI RPL 2), Sekretaris: Raffi Rasyid Rizqi\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
