<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Subject;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MajorClassesSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. Ensure Teachers (Guru Pengajar) for DKV & Animasi
            $pengajarDKV = User::firstOrCreate(
                ['email' => 'dewi.lestari@gmail.com'],
                [
                    'name' => 'Dewi Lestari, S.Sn',
                    'password' => Hash::make('password123'),
                    'phone' => '081234567807',
                    'nip_nis' => '19870519005',
                    'role' => 'GURU_PENGAJAR',
                    'specialization' => 'Desain Grafis & UI/UX Design',
                    'is_active' => true,
                ]
            );

            $pengajarAnim = User::firstOrCreate(
                ['email' => 'fajar.nugroho@gmail.com'],
                [
                    'name' => 'Fajar Nugroho, M.Kom',
                    'password' => Hash::make('password123'),
                    'phone' => '081234567808',
                    'nip_nis' => '19900825006',
                    'role' => 'GURU_PENGAJAR',
                    'specialization' => 'Animasi 2D/3D & Character Design',
                    'is_active' => true,
                ]
            );

            // 2. Ensure Guru Pembimbing (Wali Kelas) for DKV & Animasi
            $pembimbingDKV = User::firstOrCreate(
                ['email' => 'ratna.sari@gmail.com'],
                [
                    'name' => 'Ratna Sari, S.Pd',
                    'password' => Hash::make('password123'),
                    'phone' => '081234567805',
                    'nip_nis' => '19910420003',
                    'role' => 'GURU_PEMBIMBING',
                    'is_active' => true,
                ]
            );

            $pembimbingAnim = User::firstOrCreate(
                ['email' => 'hendra.gunawan@gmail.com'],
                [
                    'name' => 'Hendra Gunawan, S.Kom',
                    'password' => Hash::make('password123'),
                    'phone' => '081234567806',
                    'nip_nis' => '19881115004',
                    'role' => 'GURU_PEMBIMBING',
                    'is_active' => true,
                ]
            );

            // 3. Define Full Classes Roster for 3 Majors
            $classesData = [
                // RPL
                ['name' => 'X RPL 1', 'major' => 'RPL'],
                ['name' => 'X RPL 2', 'major' => 'RPL'],
                ['name' => 'XI RPL 1', 'major' => 'RPL'],
                ['name' => 'XI RPL 2', 'major' => 'RPL'],
                ['name' => 'XII RPL 1', 'major' => 'RPL'],
                ['name' => 'XII RPL 2', 'major' => 'RPL'],

                // DKV
                ['name' => 'X DKV 1', 'major' => 'DKV'],
                ['name' => 'X DKV 2', 'major' => 'DKV'],
                ['name' => 'XI DKV 1', 'major' => 'DKV', 'advisor_id' => $pembimbingDKV->id],
                ['name' => 'XI DKV 2', 'major' => 'DKV'],
                ['name' => 'XII DKV 1', 'major' => 'DKV'],
                ['name' => 'XII DKV 2', 'major' => 'DKV'],

                // Animasi
                ['name' => 'X Animasi 1', 'major' => 'Animasi'],
                ['name' => 'X Animasi 2', 'major' => 'Animasi'],
                ['name' => 'XI Animasi 1', 'major' => 'Animasi', 'advisor_id' => $pembimbingAnim->id],
                ['name' => 'XI Animasi 2', 'major' => 'Animasi'],
                ['name' => 'XII Animasi 1', 'major' => 'Animasi'],
                ['name' => 'XII Animasi 2', 'major' => 'Animasi'],
            ];

            $createdClasses = [];
            foreach ($classesData as $c) {
                $cls = SchoolClass::firstOrCreate(
                    ['name' => $c['name']],
                    [
                        'major' => $c['major'],
                        'advisor_id' => $c['advisor_id'] ?? null,
                    ]
                );
                // Ensure major is updated correctly
                $cls->major = $c['major'];
                if (isset($c['advisor_id']) && !$cls->advisor_id) {
                    $cls->advisor_id = $c['advisor_id'];
                }
                $cls->save();
                $createdClasses[$c['name']] = $cls;
            }

            // Update Wali Kelas class_id
            $pembimbingDKV->class_id = $createdClasses['XI DKV 1']->id;
            $pembimbingDKV->save();

            $pembimbingAnim->class_id = $createdClasses['XI Animasi 1']->id;
            $pembimbingAnim->save();

            // 4. Create Sekretaris for XI DKV 1 & XI Animasi 1
            $sekretarisDKV = User::firstOrCreate(
                ['email' => 'maya.anggraini@gmail.com'],
                [
                    'name' => 'Maya Anggraini',
                    'password' => Hash::make('password123'),
                    'phone' => '085810240101',
                    'nip_nis' => '102501',
                    'role' => 'SEKRETARIS',
                    'class_id' => $createdClasses['XI DKV 1']->id,
                    'is_active' => true,
                ]
            );
            $createdClasses['XI DKV 1']->update(['secretary_id' => $sekretarisDKV->id]);

            $sekretarisAnim = User::firstOrCreate(
                ['email' => 'bayu.nugraha@gmail.com'],
                [
                    'name' => 'Bayu Nugraha',
                    'password' => Hash::make('password123'),
                    'phone' => '085810240201',
                    'nip_nis' => '102601',
                    'role' => 'SEKRETARIS',
                    'class_id' => $createdClasses['XI Animasi 1']->id,
                    'is_active' => true,
                ]
            );
            $createdClasses['XI Animasi 1']->update(['secretary_id' => $sekretarisAnim->id]);

            // 5. Create Students for XI DKV 1
            $dkvStudents = [
                ['name' => 'Dimas Pratama', 'email' => 'dimas.pratama@gmail.com', 'nis' => '102502'],
                ['name' => 'Nadya Putri Utami', 'email' => 'nadya.putri@gmail.com', 'nis' => '102503'],
                ['name' => 'Kevin Sanjaya', 'email' => 'kevin.sanjaya@gmail.com', 'nis' => '102504'],
                ['name' => 'Tiara Andini', 'email' => 'tiara.andini@gmail.com', 'nis' => '102505'],
                ['name' => 'Bagas Saputra', 'email' => 'bagas.saputra@gmail.com', 'nis' => '102506'],
                ['name' => 'Clarissa Aurelia', 'email' => 'clarissa.aurelia@gmail.com', 'nis' => '102507'],
                ['name' => 'Dionisius Danu', 'email' => 'dionisius.danu@gmail.com', 'nis' => '102508'],
                ['name' => 'Elsa Melati', 'email' => 'elsa.melati@gmail.com', 'nis' => '102509'],
            ];
            foreach ($dkvStudents as $idx => $s) {
                User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name' => $s['name'],
                        'password' => Hash::make('password123'),
                        'phone' => '0858200' . str_pad($idx + 10, 4, '0', STR_PAD_LEFT),
                        'nip_nis' => $s['nis'],
                        'role' => 'SISWA',
                        'class_id' => $createdClasses['XI DKV 1']->id,
                        'is_active' => true,
                    ]
                );
            }

            // 6. Create Students for XI Animasi 1
            $animStudents = [
                ['name' => 'Cindy Claudia', 'email' => 'cindy.claudia@gmail.com', 'nis' => '102602'],
                ['name' => 'Rizka Amelia', 'email' => 'rizka.amelia@gmail.com', 'nis' => '102603'],
                ['name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan.ani@gmail.com', 'nis' => '102604'],
                ['name' => 'Alif Firmansyah', 'email' => 'alif.firmansyah@gmail.com', 'nis' => '102605'],
                ['name' => 'Zahra Maharani', 'email' => 'zahra.maharani@gmail.com', 'nis' => '102606'],
                ['name' => 'Bryan Christian', 'email' => 'bryan.christian@gmail.com', 'nis' => '102607'],
                ['name' => 'Devina Aurel', 'email' => 'devina.aurel@gmail.com', 'nis' => '102608'],
                ['name' => 'Ezra Mahendra', 'email' => 'ezra.mahendra@gmail.com', 'nis' => '102609'],
            ];
            foreach ($animStudents as $idx => $s) {
                User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name' => $s['name'],
                        'password' => Hash::make('password123'),
                        'phone' => '0858300' . str_pad($idx + 10, 4, '0', STR_PAD_LEFT),
                        'nip_nis' => $s['nis'],
                        'role' => 'SISWA',
                        'class_id' => $createdClasses['XI Animasi 1']->id,
                        'is_active' => true,
                    ]
                );
            }

            // 7. Create Subjects for DKV and Animasi
            $subjects = [
                ['name' => 'Desain Grafis & Tipografi', 'code' => 'DKV-DG', 'major' => 'DKV', 'teacher_id' => $pengajarDKV->id],
                ['name' => 'UI/UX Design Digital', 'code' => 'DKV-UX', 'major' => 'DKV', 'teacher_id' => $pengajarDKV->id],
                ['name' => 'Fotografi & Ilustrasi Digital', 'code' => 'DKV-FD', 'major' => 'DKV', 'teacher_id' => $pengajarDKV->id],
                ['name' => 'Animasi 2D & Stop Motion', 'code' => 'ANI-2D', 'major' => 'Animasi', 'teacher_id' => $pengajarAnim->id],
                ['name' => 'Animasi 3D & Modeling Karakter', 'code' => 'ANI-3D', 'major' => 'Animasi', 'teacher_id' => $pengajarAnim->id],
                ['name' => 'Storyboarding & Konsep Art', 'code' => 'ANI-SB', 'major' => 'Animasi', 'teacher_id' => $pengajarAnim->id],
            ];
            $createdSubjects = [];
            foreach ($subjects as $sub) {
                $s = Subject::firstOrCreate(
                    ['code' => $sub['code']],
                    [
                        'name' => $sub['name'],
                        'major' => $sub['major'],
                        'teacher_id' => $sub['teacher_id'],
                    ]
                );
                $createdSubjects[$sub['code']] = $s;
            }

            // 8. Attach Teacher Classes (pengajar taught classes)
            $dkvClassIds = [
                $createdClasses['XI DKV 1']->id,
                $createdClasses['XI DKV 2']->id,
                $createdClasses['X DKV 1']->id,
                $createdClasses['X DKV 2']->id,
                $createdClasses['XII DKV 1']->id,
                $createdClasses['XII DKV 2']->id,
            ];
            $pengajarDKV->taughtClasses()->syncWithoutDetaching($dkvClassIds);

            $animClassIds = [
                $createdClasses['XI Animasi 1']->id,
                $createdClasses['XI Animasi 2']->id,
                $createdClasses['X Animasi 1']->id,
                $createdClasses['X Animasi 2']->id,
                $createdClasses['XII Animasi 1']->id,
                $createdClasses['XII Animasi 2']->id,
            ];
            $pengajarAnim->taughtClasses()->syncWithoutDetaching($animClassIds);

            // 9. Schedules for XI DKV 1 & XI Animasi 1
            Schedule::firstOrCreate(
                [
                    'class_id' => $createdClasses['XI DKV 1']->id,
                    'subject_id' => $createdSubjects['DKV-DG']->id,
                    'day' => 'Senin',
                ],
                [
                    'teacher_id' => $pengajarDKV->id,
                    'start_time' => '07:30',
                    'end_time' => '10:00',
                    'room' => 'Lab. Komputer DKV',
                ]
            );

            Schedule::firstOrCreate(
                [
                    'class_id' => $createdClasses['XI DKV 1']->id,
                    'subject_id' => $createdSubjects['DKV-UX']->id,
                    'day' => 'Rabu',
                ],
                [
                    'teacher_id' => $pengajarDKV->id,
                    'start_time' => '10:15',
                    'end_time' => '12:45',
                    'room' => 'Lab. Komputer DKV',
                ]
            );

            Schedule::firstOrCreate(
                [
                    'class_id' => $createdClasses['XI Animasi 1']->id,
                    'subject_id' => $createdSubjects['ANI-2D']->id,
                    'day' => 'Selasa',
                ],
                [
                    'teacher_id' => $pengajarAnim->id,
                    'start_time' => '07:30',
                    'end_time' => '10:00',
                    'room' => 'Studio Animasi 1',
                ]
            );

            Schedule::firstOrCreate(
                [
                    'class_id' => $createdClasses['XI Animasi 1']->id,
                    'subject_id' => $createdSubjects['ANI-3D']->id,
                    'day' => 'Kamis',
                ],
                [
                    'teacher_id' => $pengajarAnim->id,
                    'start_time' => '10:15',
                    'end_time' => '12:45',
                    'room' => 'Studio Animasi 2',
                ]
            );

            DB::commit();
            echo "SUCCESS: 3 Jurusan (RPL, DKV, Animasi) berhasil diimplementasikan penuh!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
